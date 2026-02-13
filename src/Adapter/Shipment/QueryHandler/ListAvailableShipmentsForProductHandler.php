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

namespace PrestaShop\PrestaShop\Adapter\Shipment\QueryHandler;

use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Adapter\Shop\Context as ShopContext;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\ListAvailableShipmentsForProduct;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryHandler\ListAvailableShipmentsForProductHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryResult\ShipmentsForProduct;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

#[AsQueryHandler]
class ListAvailableShipmentsForProductHandler implements ListAvailableShipmentsForProductHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $repository,
        private readonly TranslatorInterface $translator,
        private readonly ShopContext $shopContext,
        private readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @return ShipmentsForProduct[]
     */
    public function handle(ListAvailableShipmentsForProduct $query)
    {
        $orderId = $query->getOrderId()->getValue();
        $productInstance = $this->productRepository->get(new ProductId($query->getProductId()->getValue()), new ShopId($this->shopContext->getContextShopID()));
        $availableShipmentsForProductSelected = [];

        try {
            $getShipmentsFromOrder = $this->repository->findByOrderId($orderId);
        } catch (Throwable $e) {
            throw new ShipmentNotFoundException(sprintf('Could not find shipment for order id "%s"', $orderId), 0, $e);
        }

        if (empty($getShipmentsFromOrder)) {
            return $availableShipmentsForProductSelected;
        }

        foreach ($getShipmentsFromOrder as $shipment) {
            // productInstance->getCarriers() return empty array if product is handle by ALL carriers
            if (count($productInstance->getCarriers()) === 0 || in_array($shipment->getCarrierId(), array_column($productInstance->getCarriers(), 'id_carrier'))) {
                $availableShipmentsForProductSelected[] = new ShipmentsForProduct($shipment->getId(), $this->translator->trans('Shipment ', [], 'Shop.Forms.Labels') . $shipment->getId());
            }
        }

        return $availableShipmentsForProductSelected;
    }
}
