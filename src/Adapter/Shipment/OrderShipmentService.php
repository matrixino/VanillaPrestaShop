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

namespace PrestaShop\PrestaShop\Adapter\Shipment;

use Carrier;
use PrestaShop\PrestaShop\Adapter\Carrier\Repository\CarrierRepository;
use PrestaShop\PrestaShop\Adapter\Order\Repository\OrderRepository;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;

class OrderShipmentService
{
    /**
     * @var ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CarrierRepository
     */
    private $carrierRepository;

    public function __construct(ShipmentRepository $shipmentRepository, OrderRepository $orderRepository, CarrierRepository $carrierRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->carrierRepository = $carrierRepository;
    }

    /**
     * Returns the carrier used to ship a specific product within a given order.
     */
    public function getCarrierForProduct(int $orderId, int $productId): ?Carrier
    {
        $order = $this->orderRepository->get(new OrderId($orderId));
        $shipments = $this->shipmentRepository->findByOrderId($order->id);

        $orderDetails = $order->getOrderDetailList();
        $orderDetailId = null;

        foreach ($orderDetails as $orderDetail) {
            if ((int) $orderDetail['product_id'] === $productId) {
                $orderDetailId = (int) $orderDetail['id_order_detail'];
                break;
            }
        }

        foreach ($shipments as $shipment) {
            foreach ($shipment->getProducts() as $shipmentProduct) {
                if ($shipmentProduct->getOrderDetailId() === $orderDetailId) {
                    $carrierId = new CarrierId($shipment->getCarrierId());
                    $carrier = $this->carrierRepository->get($carrierId);

                    return $carrier;
                }
            }
        }

        return null;
    }

    /**
     * Returns all distinct carriers used to ship an order.
     *
     * @return Carrier[]
     */
    public function getAllCarriersForOrder(int $orderId): array
    {
        $shipments = $this->shipmentRepository->findByOrderId($orderId);

        $carriers = [];

        foreach ($shipments as $shipment) {
            if (!isset($carriers[$shipment->getCarrierId()])) {
                $carrierId = new CarrierId($shipment->getCarrierId());
                $carrier = $this->carrierRepository->get($carrierId);
                $carriers[$carrierId->getValue()] = $carrier;
            }
        }

        return $carriers;
    }

    public function orderHasShipment(int $orderId): bool
    {
        $shipments = $this->shipmentRepository->findByOrderId($orderId);

        return !empty($shipments);
    }
}
