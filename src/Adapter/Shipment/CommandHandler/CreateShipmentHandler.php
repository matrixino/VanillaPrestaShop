<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Shipment\CommandHandler;

use Exception;
use PrestaShop\PrestaShop\Adapter\Carrier\ShippingCostCalculator;
use PrestaShop\PrestaShop\Adapter\Configuration as AdapterConfiguration;
use PrestaShop\PrestaShop\Adapter\Order\Repository\OrderRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\ShippingCalculationRequest;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\CreateShipment;
use PrestaShop\PrestaShop\Core\Domain\Shipment\CommandHandler\CreateShipmentHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentException;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use PrestaShopBundle\Entity\Shipment;

#[AsCommandHandler]
class CreateShipmentHandler implements CreateShipmentHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $shipmentRepository,
        private readonly OrderRepository $orderRepository,
        private readonly ShippingCostCalculator $shippingCostCalculator,
        private readonly AdapterConfiguration $configuration,
    ) {
    }

    public function handle(CreateShipment $command): int
    {
        try {
            $order = $this->orderRepository->get($command->getOrderId());
            $carrierId = $command->getCarrierId()->getValue();
            $productId = $command->getProductId()->getValue();
            $addressId = (int) $order->id_address_delivery;

            $shippingCostTaxExcluded = 0.00;
            $shippingCostTaxIncluded = 0.00;

            if (!$this->configuration->get('PS_ORDER_RECALCULATE_SHIPPING')) {
                $product = $this->findOrderProduct($order->getProductsDetail(), $productId);
                if ($product !== null) {
                    $request = new ShippingCalculationRequest(
                        products: [
                            [
                                'id_product' => $productId,
                                'id_product_attribute' => (int) ($product['product_attribute_id'] ?? 0),
                                'quantity' => $command->getQuantity(),
                                'weight' => (float) ($product['product_weight'] ?? 0),
                                'weight_attribute' => null,
                                'is_virtual' => (bool) ($product['is_virtual'] ?? false),
                                'additional_shipping_cost' => (float) ($product['additional_shipping_cost'] ?? 0),
                                'price_wt' => (float) ($product['unit_price_tax_incl'] ?? 0),
                            ],
                        ],
                        carrierId: $carrierId,
                        zoneId: null,
                        addressId: $addressId,
                        countryZoneId: 0,
                        currencyId: (int) $order->id_currency,
                        customerId: (int) $order->id_customer,
                        orderTotal: (float) $order->total_products,
                    );

                    $result = $this->shippingCostCalculator->calculate($request);
                    if ($result !== null) {
                        $shippingCostTaxExcluded = (float) (string) $result->getTaxExcluded();
                        $shippingCostTaxIncluded = (float) (string) $result->getTaxIncluded();
                    }
                }
                $this->updateOrderShippingTotal((int) $order->id);
            }

            $shipment = new Shipment();
            $shipment->setOrderId((int) $order->id);
            $shipment->setCarrierId($carrierId);
            $shipment->setAddressId($addressId);
            $shipment->setTrackingNumber(null);
            $shipment->setShippingCostTaxExcluded($shippingCostTaxExcluded);
            $shipment->setShippingCostTaxIncluded($shippingCostTaxIncluded);
            $shipment->setDeliveredAt(null);
            $shipment->setShippedAt(null);
            $shipment->setCancelledAt(null);

            $shipmentId = $this->shipmentRepository->save($shipment);

            return $shipmentId;
        } catch (Exception $e) {
            throw new ShipmentException('Failed to create shipment', $e->getCode(), $e);
        }
    }

    private function updateOrderShippingTotal(int $orderId): void
    {
        $order = $this->orderRepository->get(new OrderId($orderId));

        $totalTaxExcluded = 0.00;
        $totalTaxIncluded = 0.00;

        foreach ($this->shipmentRepository->findByOrderId($orderId) as $shipment) {
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
