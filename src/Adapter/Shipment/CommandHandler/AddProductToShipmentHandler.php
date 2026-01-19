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

namespace PrestaShop\PrestaShop\Adapter\Shipment\CommandHandler;

use Exception;
use PrestaShop\PrestaShop\Adapter\Order\Repository\OrderDetailRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderDetailNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\AddProductToShipment;
use PrestaShop\PrestaShop\Core\Domain\Shipment\CommandHandler\AddProductToShipmentHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentNotFoundException;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use PrestaShopBundle\Entity\ShipmentProduct;

#[AsCommandHandler]
class AddProductToShipmentHandler implements AddProductToShipmentHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $shipmentRepository,
        private readonly OrderDetailRepository $orderDetailRepository
    ) {
    }

    public function handle(AddProductToShipment $command): void
    {
        $shipmentId = $command->getShipmentId()->getValue();
        $shipment = $this->shipmentRepository->findById($shipmentId);

        if ($shipment === null) {
            throw new ShipmentNotFoundException(sprintf('No shipment with id %s found', $shipment));
        }

        $orderDetail = $this->orderDetailRepository->findByOrderIdAndProductId($command->getOrderId(), $command->getProductId(), $command->getCombinationId());

        if ($orderDetail === null) {
            throw new OrderDetailNotFoundException(null, sprintf('No order detail for order id %s and product id %s found', $command->getOrderId()->getValue(), $command->getProductId()->getValue()));
        }

        $shipmentProduct = new ShipmentProduct();
        $shipmentProduct->setOrderDetailId($orderDetail->id_order_detail);
        $shipmentProduct->setQuantity($orderDetail->product_quantity);
        $shipment->addShipmentProduct($shipmentProduct);

        try {
            $this->shipmentRepository->save($shipment);
        } catch (Exception $e) {
            throw new ShipmentException(sprintf('Failed to add products from shipment with id "%s"', $shipmentId), 0, $e);
        }
    }
}
