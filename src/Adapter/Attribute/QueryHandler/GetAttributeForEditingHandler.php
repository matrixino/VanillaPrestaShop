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

namespace PrestaShop\PrestaShop\Adapter\Attribute\QueryHandler;

use ImageManager;
use PrestaShop\PrestaShop\Adapter\Attribute\Repository\AttributeRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Domain\AttributeGroup\Attribute\Query\GetAttributeForEditing;
use PrestaShop\PrestaShop\Core\Domain\AttributeGroup\Attribute\QueryHandler\GetAttributeForEditingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\AttributeGroup\Attribute\QueryResult\EditableAttribute;
use PrestaShop\PrestaShop\Core\Domain\AttributeGroup\Attribute\ValueObject\AttributeId;
use PrestaShop\PrestaShop\Core\Image\Parser\ImageTagSourceParserInterface;

/**
 * Handles query which gets attribute group for editing
 */
#[AsQueryHandler]
final class GetAttributeForEditingHandler implements GetAttributeForEditingHandlerInterface
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var ImageTagSourceParserInterface
     */
    private $imageTagSourceParser;

    public function __construct(
        AttributeRepository $attributeRepository,
        ImageTagSourceParserInterface $imageTagSourceParser
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->imageTagSourceParser = $imageTagSourceParser;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetAttributeForEditing $query): EditableAttribute
    {
        $attributeId = $query->getAttributeId();
        $attribute = $this->attributeRepository->get($attributeId);

        return new EditableAttribute(
            $attributeId->getValue(),
            (int) $attribute->id_attribute_group,
            $attribute->name,
            $attribute->color,
            $attribute->getAssociatedShops(),
            $this->getTextureImage($attributeId),
        );
    }

    /**
     * @param AttributeId $attributeId
     *
     * @return array|null
     */
    private function getTextureImage(AttributeId $attributeId)
    {
        $imageType = 'jpg';
        $image = _PS_IMG_DIR_ . 'co/' . $attributeId->getValue() . '.' . $imageType;

        if (!file_exists($image)) {
            return null;
        }

        $imageTag = ImageManager::thumbnail(
            $image,
            'attribute_texture_' . $attributeId->getValue() . '_thumb.' . $imageType,
            150,
            $imageType,
            true,
            true
        );

        if (empty($imageTag)) {
            return null;
        }

        $imageSize = filesize($image) / 1000;

        return [
            'size' => sprintf('%skB', $imageSize),
            'path' => $this->imageTagSourceParser->parse($imageTag),
        ];
    }
}
