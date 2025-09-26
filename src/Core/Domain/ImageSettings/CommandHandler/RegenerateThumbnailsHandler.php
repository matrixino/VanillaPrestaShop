<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\ImageSettings\CommandHandler;

use PrestaShop\PrestaShop\Adapter\Domain\AbstractObjectModelHandler;
use PrestaShop\PrestaShop\Adapter\ImageThumbnailsRegenerator;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\Command\RegenerateThumbnailsCommand;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\Exception\ImageTypeException;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\Exception\RegenerateThumbnailsException;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\Exception\RegenerateThumbnailsTimeoutException;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\Exception\RegenerateThumbnailsWriteException;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\ImageDomain;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\ValueObject\ImageTypeId;
use PrestaShop\PrestaShop\Core\Language\LanguageRepositoryInterface;
use PrestaShopBundle\Entity\Repository\ImageTypeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommandHandler]
final class RegenerateThumbnailsHandler extends AbstractObjectModelHandler implements RegenerateThumbnailsHandlerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ImageTypeRepository $imageTypeRepository,
        private readonly LanguageRepositoryInterface $langRepository,
        private readonly ImageThumbnailsRegenerator $imageThumbnailsRegenerator,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws ImageTypeException
     * @throws RegenerateThumbnailsException
     */
    public function handle(RegenerateThumbnailsCommand $command): void
    {
        // Get all languages
        $languages = $this->langRepository->findAll();

        $commandImageDomain = ImageDomain::from($command->getImage());
        $imageTypeId = null;
        if ($command->getImageTypeId() > 0) {
            $imageTypeId = new ImageTypeId($command->getImageTypeId());
        }

        // Iterate all images categories with thumbnails
        foreach (ImageDomain::getDomainsWithThumbnails() as $imageDomain) {
            // Check if this kind of image is selected, if not, skip
            if ($commandImageDomain !== ImageDomain::ALL && $commandImageDomain !== $imageDomain) {
                continue;
            }

            // Getting formats generation (all if 'all' selected)
            if (!$imageTypeId instanceof ImageTypeId) {
                $criteria = [$imageDomain->value => 1];
            } else {
                $criteria = ['id' => $imageTypeId->getValue(), $imageDomain->value => 1];
            }
            $imageTypes = $this->imageTypeRepository->findBy($criteria);

            // If user asked to erase images, let's do it first
            if ($command->erasePreviousImages()) {
                $this->imageThumbnailsRegenerator->deletePreviousImages(
                    $imageDomain->getDirectory(),
                    $imageTypes,
                    $imageDomain->isProduct()
                );
            }

            // Regenerate images
            $errors = $this->imageThumbnailsRegenerator->regenerateNewImages(
                $imageDomain->getDirectory(),
                $imageTypes,
                $imageDomain->isProduct()
            );
            if (false === $errors) {
                throw new RegenerateThumbnailsWriteException(
                    $this->translator->trans(
                        'Cannot write images for this type: %1$s. Please check the %2$s folder\'s writing permissions.',
                        [$imageDomain->value, $imageDomain->getDirectory()],
                        'Admin.Design.Notification'
                    )
                );
            }
            if (is_array($errors) && count($errors) > 0) {
                if (in_array('timeout', $errors, true)) {
                    throw new RegenerateThumbnailsTimeoutException($this->translator->trans('Only part of the images have been regenerated. The server timed out before finishing.', [], 'Admin.Design.Notification'));
                }
                throw new RegenerateThumbnailsWriteException(
                    $this->translator->trans(
                        'Cannot write images for this type: %1$s. Please check the %2$s folder\'s writing permissions.',
                        [$imageDomain->value, $imageDomain->getDirectory()],
                        'Admin.Design.Notification'
                    )
                );
            }

            if (
                $imageDomain->isProduct()
                && $this->imageThumbnailsRegenerator->regenerateWatermark($imageDomain->getDirectory(), $imageTypes) === 'timeout'
            ) {
                throw new RegenerateThumbnailsTimeoutException($this->translator->trans('Server timed out. The watermark may not have been applied to all images.', [], 'Admin.Design.Notification'));
            }

            if ($this->imageThumbnailsRegenerator->regenerateNoPictureImages(
                $imageDomain->getDirectory(),
                $imageTypes,
                $languages
            )) {
                throw new RegenerateThumbnailsWriteException(
                    $this->translator->trans(
                        'Cannot write images for this type: %1$s. Please check the %2$s folder\'s writing permissions.',
                        [$imageDomain->value, $imageDomain->getDirectory()],
                        'Admin.Design.Notification'
                    )
                );
            }
        }
    }
}
