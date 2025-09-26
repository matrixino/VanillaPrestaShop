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

namespace PrestaShopBundle\Command;

use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\Exception\ProductConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Search\Command\SearchIndexationCommand as SearchIndexationCommandBus;
use PrestaShop\PrestaShop\Core\Domain\Search\Exception\SearchIndexationProductNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Search\Exception\SearchIndexationShopGroupNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Search\Exception\SearchIndexationShopNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * CLI command to index search.
 */
class SearchIndexationCommand extends Command
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('prestashop:search:index')
            ->setDescription('Index products for search')
            ->addOption('full', 'f', InputOption::VALUE_NONE, 'Rebuild whole index')
            ->addOption('shop-id', 's', InputOption::VALUE_REQUIRED, 'Shop ID to index')
            ->addOption('shop-group-id', 'g', InputOption::VALUE_REQUIRED, 'Shop group ID to index')
            ->addOption('product-id', 'p', InputOption::VALUE_REQUIRED, 'Product ID to index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $full = (bool) $input->getOption('full');
        $shopId = $input->getOption('shop-id');
        $shopGroupId = $input->getOption('shop-group-id');
        $productId = $input->getOption('product-id');

        if ($shopId !== null) {
            try {
                $shopConstraint = ShopConstraint::shop((int) $shopId);
            } catch (ShopException $e) {
                $io->error(sprintf('Unable to extract shop constraint from shop id: %s', $e->getMessage()));

                return self::FAILURE;
            }
        } elseif ($shopGroupId !== null) {
            try {
                $shopConstraint = ShopConstraint::shopGroup((int) $shopGroupId);
            } catch (ShopException $e) {
                $io->error(sprintf('Unable to extract shop constraint from shop group id : %s', $e->getMessage()));

                return self::FAILURE;
            }
        } else {
            $shopConstraint = ShopConstraint::allShops();
        }

        if ($productId !== null) {
            try {
                $productId = new ProductId((int) $productId);
            } catch (ProductConstraintException $e) {
                $io->error(sprintf('Unable to extract product id : %s', $e->getMessage()));

                return self::FAILURE;
            }
        }

        try {
            $this->commandBus->handle(new SearchIndexationCommandBus($full, $shopConstraint, $productId));
            $io->success('Search indexation complete.');
        } catch (SearchIndexationProductNotFoundException $e) {
            $io->error(sprintf('Product #%s not found', $e->getProductId()->getValue()));
        } catch (SearchIndexationShopNotFoundException $e) {
            $io->error(sprintf('Shop #%s not found', $e->getShopId()->getValue()));
        } catch (SearchIndexationShopGroupNotFoundException $e) {
            $io->error(sprintf('Shop group #%s not found', $e->getShopGroupId()->getValue()));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
        }

        return self::SUCCESS;
    }
}
