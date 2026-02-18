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

use PrestaShop\PrestaShop\Adapter\Carrier\Repository\CarrierRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetOrderShipmentsWithProducts;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryHandler\GetOrderShipmentsWithProductsHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryResult\OrderShipmentWithProducts;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use Throwable;

#[AsQueryHandler]
class GetOrderShipmentsWithProductsHandler implements GetOrderShipmentsWithProductsHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $shipmentRepository,
        private readonly CarrierRepository $carrierRepository,
    ) {
    }

    /**
     * @param GetOrderShipmentsWithProducts $query
     *
     * @return OrderShipmentWithProducts[]
     */
    public function handle(GetOrderShipmentsWithProducts $query)
    {
        $orderId = $query->getOrderId()->getValue();

        try {
            $shipments = $this->shipmentRepository->findByOrderId($orderId);
        } catch (Throwable $e) {
            throw new ShipmentNotFoundException(sprintf('Could not find shipments for order with id "%s"', $orderId), 0, $e);
        }

        if (empty($shipments)) {
            return [];
        }

        $shipmentProductMapping = $this->getShipmentProductMapping($orderId);

        $result = [];
        foreach ($shipments as $shipment) {
            $shipmentId = $shipment->getId();

            $carrier = $this->carrierRepository->get(new CarrierId($shipment->getCarrierId()));

            $orderDetailIds = $shipmentProductMapping[$shipmentId] ?? [];

            $result[] = new OrderShipmentWithProducts(
                $shipmentId,
                $orderDetailIds,
                $carrier->name,
                $shipment->getTrackingNumber()
            );
        }

        return $result;
    }

    /**
     * Get shipment to order detail ID mapping.
     * Returns an array where keys are shipment IDs and values are arrays of order detail IDs.
     *
     * @param int $orderId
     *
     * @return array<int, int[]>
     */
    private function getShipmentProductMapping(int $orderId): array
    {
        $results = $this->shipmentRepository->getShipmentProductMappingByOrderId($orderId);

        if (empty($results)) {
            return [];
        }

        $mapping = [];
        foreach ($results as $row) {
            $shipmentId = (int) $row['id_shipment'];
            $orderDetailId = (int) $row['id_order_detail'];

            if (!isset($mapping[$shipmentId])) {
                $mapping[$shipmentId] = [];
            }

            $mapping[$shipmentId][] = $orderDetailId;
        }

        return $mapping;
    }
}
