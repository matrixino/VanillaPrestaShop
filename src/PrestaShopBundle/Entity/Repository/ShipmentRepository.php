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

namespace PrestaShopBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentException;
use PrestaShopBundle\Entity\Shipment;
use PrestaShopBundle\Entity\ShipmentProduct;

class ShipmentRepository extends EntityRepository
{
    /**
     * @var string
     */
    public $tablePrefix;

    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @param int $orderId
     *
     * @return Shipment[]
     */
    public function findByOrderId(int $orderId)
    {
        return $this->findBy(['orderId' => $orderId]);
    }

    public function findByOrderAndShipmentId(int $orderId, int $shipmentId): ?Shipment
    {
        return $this->findOneBy(['orderId' => $orderId, 'id' => $shipmentId]);
    }

    public function findById(int $shipmentId): ?Shipment
    {
        return $this->findOneBy(['id' => $shipmentId]);
    }

    public function findByCarrierId(int $carrierId): array
    {
        return $this->findBy(['carrierId' => $carrierId]);
    }

    public function save(Shipment $shipment): int
    {
        $this->getEntityManager()->persist($shipment);
        $this->getEntityManager()->flush();

        return $shipment->getId();
    }

    public function delete(Shipment $shipment): void
    {
        $this->getEntityManager()->remove($shipment);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Shipment $source
     * @param Shipment $target
     * @param ShipmentProduct[] $shipmentProducts
     */
    public function mergeProductsToShipment(Shipment $source, Shipment $target, array $shipmentProducts): void
    {
        $sourceProductsByOrderDetailId = $this->getShipmentProductByOrderDetailId($source);
        $targetProductsByOrderDetailId = $this->getShipmentProductByOrderDetailId($target);

        foreach ($shipmentProducts as $shipmentProduct) {
            if (empty($sourceProductsByOrderDetailId[$shipmentProduct->getOrderDetailId()])) {
                throw new ShipmentException(sprintf('Order detail with id %d does not exist in source shipment', $shipmentProduct->getOrderDetailId()));
            }
            if (empty($targetProductsByOrderDetailId[$shipmentProduct->getOrderDetailId()])) {
                $target->addShipmentProduct($shipmentProduct);
            } else {
                $targetProduct = $targetProductsByOrderDetailId[$shipmentProduct->getOrderDetailId()];
                $newQuantity = $targetProduct->getQuantity() + $shipmentProduct->getQuantity();
                $targetProduct->setQuantity($newQuantity);
            }
            $sourceProduct = $sourceProductsByOrderDetailId[$shipmentProduct->getOrderDetailId()];
            $newQuantity = $sourceProduct->getQuantity() - $shipmentProduct->getQuantity();
            if ($newQuantity <= 0) {
                $source->removeProduct($sourceProduct);
            } else {
                $sourceProduct->setQuantity($newQuantity);
            }
        }
        $this->getEntityManager()->flush();

        if ($source->getProducts()->isEmpty()) {
            $this->delete($source);
        }
    }

    /**
     * @return array<int, array{
     *     id_shipment: int,
     *     id_order: int,
     *     id_carrier: int,
     *     id_delivery_address: int,
     *     shipping_cost_tax_excl: string,
     *     shipping_cost_tax_incl: string,
     *     packed_at: string|null,
     *     shipped_at: string|null,
     *     delivered_at: string|null,
     *     cancelled_at: string|null,
     *     tracking_number: string|null,
     *     date_add: string,
     *     date_upd: string,
     *     package_weight: string|null,
     *     carrier_name: string|null
     * }>
     */
    public function getShipmentWithWeightByOrderId(int $orderId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $qb = $conn->createQueryBuilder();
        $qb->select('s.*', 'SUM(od.product_weight * sp.quantity) as package_weight, c.name as carrier_name, c.url as carrier_tracking_url')
            ->from($this->tablePrefix . 'shipment', 's')
            ->leftJoin('s', $this->tablePrefix . 'shipment_product', 'sp', 's.id_shipment = sp.id_shipment')
            ->leftJoin('sp', $this->tablePrefix . 'order_detail', 'od', 'sp.id_order_detail = od.id_order_detail')
            ->leftJoin('s', $this->tablePrefix . 'carrier', 'c', 's.id_carrier = c.id_carrier')->where('s.id_order = :orderId')
            ->setParameter('orderId', $orderId)
            ->groupBy('s.id_shipment');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    private function getShipmentProductByOrderDetailId(Shipment $shipment): array
    {
        return array_reduce($shipment->getProducts()->toArray(), function ($carry, ShipmentProduct $product) {
            $carry[$product->getOrderDetailId()] = $product;

            return $carry;
        }, []);
    }

    /**
     * @param int $carrierId
     * @param Shipment $shipmentToRemoveProduct
     * @param ShipmentProduct[] $shipmentProductsToMove
     */
    public function splitShipment(int $carrierId, Shipment $shipmentToRemoveProduct, array $shipmentProductsToMove): void
    {
        $shipmentProductsByOrderDetailId = array_reduce($shipmentToRemoveProduct->getProducts()->toArray(), function ($carry, ShipmentProduct $product) {
            $carry[$product->getOrderDetailId()] = $product;

            return $carry;
        }, []);

        $shipmentProducts = [];

        $newShipment = new Shipment();
        $newShipment->setCarrierId($carrierId);
        $newShipment->setOrderId($shipmentToRemoveProduct->getOrderId());
        $newShipment->setTrackingNumber(null);
        $newShipment->setAddressId($shipmentToRemoveProduct->getAddressId());
        $newShipment->setShippingCostTaxExcluded($shipmentToRemoveProduct->getShippingCostTaxExcluded());
        $newShipment->setShippingCostTaxIncluded($shipmentToRemoveProduct->getShippingCostTaxIncluded());

        foreach ($shipmentProductsToMove as $shipmentProductToMove) {
            $orderDetailId = $shipmentProductToMove->getOrderDetailId();
            $quantity = $shipmentProductToMove->getQuantity();
            $existingProduct = $shipmentProductsByOrderDetailId[$orderDetailId] ?? null;
            if ($existingProduct) {
                $remainingQty = $existingProduct->getQuantity() - $quantity;
                if ($remainingQty <= 0) {
                    $shipmentToRemoveProduct->removeProduct($existingProduct);
                } else {
                    $existingProduct->setQuantity($remainingQty);
                }
            } else {
                throw new ShipmentException(sprintf('Cannot find product with order detail id %s', $orderDetailId));
            }
            $shipmentProducts[] = (new ShipmentProduct())
                ->setOrderDetailId($orderDetailId)
                ->setQuantity($quantity)
                ->setShipment($newShipment);
        }

        foreach ($shipmentProducts as $shipmentProduct) {
            $newShipment->addShipmentProduct($shipmentProduct);
        }
        $this->save($newShipment);

        if ($shipmentToRemoveProduct->getProducts()->isEmpty()) {
            $this->delete($shipmentToRemoveProduct);
        }
    }
}
