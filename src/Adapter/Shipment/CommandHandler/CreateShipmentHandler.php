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
use Order;
use PrestaShop\PrestaShop\Adapter\Address\Repository\AddressRepository;
use PrestaShop\PrestaShop\Adapter\Country\Repository\CountryRepository;
use PrestaShop\PrestaShop\Adapter\Order\Repository\OrderRepository;
use PrestaShop\PrestaShop\Adapter\Product\Repository\ProductRepository;
use PrestaShop\PrestaShop\Adapter\Shipping\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Command\CreateShipment;
use PrestaShop\PrestaShop\Core\Domain\Shipment\CommandHandler\CreateShipmentHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentException;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Exception\ShipmentNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shipping\ValueObject\ShippingCalculationRequest;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use PrestaShopBundle\Entity\Shipment;

#[AsCommandHandler]
class CreateShipmentHandler implements CreateShipmentHandlerInterface
{
    public function __construct(
        private readonly ShipmentRepository $shipmentRepository,
        private readonly OrderRepository $orderRepository,
        private readonly AddressRepository $addressRepository,
        private readonly ProductRepository $productRepository,
        private readonly CountryRepository $countryRepository,
        private readonly ShippingCostCalculatorInterface $shippingCostCalculator,
    ) {
    }

    public function handle(CreateShipment $command): int
    {
        $order = $this->orderRepository->get($command->getOrderId());
        $carrierId = $command->getCarrierId()->getValue();
        $productId = $command->getProductId();
        $quantity = $command->getQuantity();

        if ($order === null) {
            throw new ShipmentNotFoundException(sprintf('No order found with id %s found', $command->getOrderId()->getValue()));
        }

        $shipment = new Shipment();
        $shipment->setOrderId((int) $order->id);
        $shipment->setCarrierId((int) $carrierId);
        $shipment->setAddressId((int) $order->id_address_delivery);
        $shipment->setTrackingNumber(null);

        $shippingCosts = $this->calculateShippingCosts($order, $carrierId, $productId, $quantity);
        $shipment->setShippingCostTaxExcluded($shippingCosts['tax_excluded']);
        $shipment->setShippingCostTaxIncluded($shippingCosts['tax_included']);

        $shipment->setDeliveredAt(null);
        $shipment->setShippedAt(null);
        $shipment->setCancelledAt(null);

        try {
            return $this->shipmentRepository->save($shipment);
        } catch (Exception $e) {
            throw new ShipmentException(sprintf('Failed to add products from shipment with id "%s"', $shipment), 0, $e);
        }
    }

    /**
     * @return array{tax_excluded: float, tax_included: float}
     */
    private function calculateShippingCosts(Order $order, int $carrierId, ProductId $productId, int $quantity): array
    {
        $product = $this->productRepository->get($productId, new ShopId(1));

        if ($product === null) {
            return [
                'tax_excluded' => 0.0,
                'tax_included' => 0.0,
            ];
        }

        $productArray = [
            'id_product' => (int) $product->id,
            'id_product_attribute' => 0,
            'quantity' => $quantity,
            'weight' => (float) $product->weight,
            'weight_attribute' => null,
            'is_virtual' => (int) $product->is_virtual,
            'additional_shipping_cost' => (float) $product->additional_shipping_cost,
            'price_wt' => (float) $product->price,
        ];

        $products = [$productArray];

        $address = $this->addressRepository->get(new AddressId((int) $order->id_address_delivery));
        $country = $this->countryRepository->get(new CountryId((int) $address->id_country));

        $productPrice = (float) $product->price;
        $orderTotal = $productPrice * $quantity;

        $request = new ShippingCalculationRequest(
            $products,
            $carrierId,
            null,
            (int) $order->id_address_delivery,
            $country,
            (int) $order->id_currency,
            (int) $order->id_customer,
            $orderTotal
        );

        $result = $this->shippingCostCalculator->calculate($request);

        if ($result === null) {
            return [
                'tax_excluded' => 0.0,
                'tax_included' => 0.0,
            ];
        }

        return [
            'tax_excluded' => $result->getTaxExcludedAsFloat(),
            'tax_included' => $result->getTaxIncludedAsFloat(),
        ];
    }
}
