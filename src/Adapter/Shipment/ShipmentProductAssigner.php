<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Shipment;

use Exception;
use Order;
use OrderDetail;
use PrestaShop\PrestaShop\Adapter\Configuration as AdapterConfiguration;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\ShippingCalculationRequest;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentNotFoundException;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use PrestaShopBundle\Entity\Shipment;
use PrestaShopBundle\Entity\ShipmentProduct;

class ShipmentProductAssigner
{
    public function __construct(
        private ShipmentRepository $shipmentRepository,
        private readonly ShippingCostCalculatorInterface $shippingCostCalculator,
        private readonly AdapterConfiguration $configuration,
    ) {
    }

    public function assign(?int $shipmentId, Order $order, OrderDetail $orderDetail, ?int $carrierId = null): void
    {
        if (empty($shipmentId)) {
            if (empty($carrierId)) {
                throw new Exception('A carrier ID is required to create a new shipment');
            }

            $shippingCostTaxExcluded = 0.00;
            $shippingCostTaxIncluded = 0.00;

            if ($this->configuration->get('PS_ORDER_RECALCULATE_SHIPPING')) {
                $product = $this->findOrderProduct($order->getProductsDetail(), (int) $orderDetail->product_id);
                if ($product !== null) {
                    $request = new ShippingCalculationRequest(
                        products: [
                            [
                                'id_product' => (int) $orderDetail->product_id,
                                'id_product_attribute' => (int) $orderDetail->product_attribute_id,
                                'quantity' => (int) $orderDetail->product_quantity,
                                'weight' => (float) $orderDetail->product_weight,
                                'weight_attribute' => null,
                                'is_virtual' => false,
                                'additional_shipping_cost' => (float) ($product['additional_shipping_cost'] ?? 0),
                                'price_wt' => (float) $orderDetail->unit_price_tax_incl,
                            ],
                        ],
                        carrierId: $carrierId,
                        zoneId: null,
                        addressId: (int) $order->id_address_delivery,
                        countryZoneId: 0,
                        currencyId: (int) $order->id_currency,
                        customerId: (int) $order->id_customer,
                        orderTotal: (float) $order->total_products,
                    );

                    $context = ShippingCostContext::createFromRequest($request);
                    $this->shippingCostCalculator->compute($context);
                    if ($context->getTaxExcluded() !== null && $context->getTaxIncluded() !== null) {
                        $shippingCostTaxExcluded = (float) (string) $context->getTaxExcluded();
                        $shippingCostTaxIncluded = (float) (string) $context->getTaxIncluded();
                    }
                }
            }

            $shipment = new Shipment();
            $shipment->setOrderId((int) $order->id);
            $shipment->setCarrierId($carrierId);
            $shipment->setAddressId((int) $order->id_address_delivery);
            $shipment->setTrackingNumber(null);
            $shipment->setShippingCostTaxExcluded($shippingCostTaxExcluded);
            $shipment->setShippingCostTaxIncluded($shippingCostTaxIncluded);
            $shipment->setDeliveredAt(null);
            $shipment->setShippedAt(null);
            $shipment->setCancelledAt(null);
        } else {
            $shipment = $this->shipmentRepository->findById($shipmentId);

            if ($shipment === null) {
                throw new ShipmentNotFoundException(sprintf('No shipment with id %d found', $shipmentId));
            }
        }

        $shipmentProduct = new ShipmentProduct();
        $shipmentProduct->setOrderDetailId((int) $orderDetail->id_order_detail);
        $shipmentProduct->setQuantity((int) $orderDetail->product_quantity);
        $shipment->addShipmentProduct($shipmentProduct);

        $this->shipmentRepository->save($shipment);

        if (empty($shipmentId) && $this->configuration->get('PS_ORDER_RECALCULATE_SHIPPING')) {
            $this->updateOrderShippingTotal($order);
        }
    }

    private function updateOrderShippingTotal(Order $order): void
    {
        $totalTaxExcluded = 0.00;
        $totalTaxIncluded = 0.00;

        foreach ($this->shipmentRepository->findByOrderId((int) $order->id) as $shipment) {
            $totalTaxExcluded += $shipment->getShippingCostTaxExcluded();
            $totalTaxIncluded += $shipment->getShippingCostTaxIncluded();
        }

        $order->total_shipping_tax_excl = $totalTaxExcluded;
        $order->total_shipping_tax_incl = $totalTaxIncluded;
        $order->total_shipping = $totalTaxIncluded;
        $order->update();
    }

    /**
     * @param array<array<string, mixed>> $products
     *
     * @return array<string, mixed>|null
     */
    private function findOrderProduct(array $products, int $productId): ?array
    {
        foreach ($products as $product) {
            if ((int) $product['product_id'] === $productId) {
                return $product;
            }
        }

        return null;
    }
}
