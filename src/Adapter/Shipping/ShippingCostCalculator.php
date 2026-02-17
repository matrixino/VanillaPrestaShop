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

namespace PrestaShop\PrestaShop\Adapter\Shipping;

use Address;
use Carrier;
use Configuration;
use Currency;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Tools;
use PrestaShop\PrestaShop\Core\Domain\Shipping\ValueObject\ShippingCalculationRequest;
use PrestaShop\PrestaShop\Core\Domain\Shipping\ValueObject\ShippingCostResult;
use Validate;

class ShippingCostCalculator implements ShippingCostCalculatorInterface
{
    private array $carrierCache = [];

    public function __construct(
        private readonly LegacyContext $context,
        private readonly Tools $tools,
    ) {
    }

    /**
     * @param ShippingCalculationRequest $request Request
     *
     * @return ShippingCostResult|null
     */
    public function calculate(ShippingCalculationRequest $request): ?ShippingCostResult
    {
        $physicalProducts = $this->filterPhysicalProducts($request->getProducts());

        if (empty($physicalProducts)) {
            return null;
        }

        $zoneId = $this->resolveZoneId($request);

        $carrier = $this->getCarrier($request->getCarrierId());

        if (!$carrier || !Validate::isLoadedObject($carrier) || !$carrier->active) {
            return $this->setFreeShippingCost($request->getCarrierId());
        }

        if ($carrier->is_free == 1) {
            return $this->setFreeShippingCost($carrier->id);
        }

        $totalWeight = $this->calculateTotalWeight($physicalProducts);

        if ($this->qualifiesForFreeShipping($request->getOrderTotal(), $totalWeight, $request->getCurrencyId())) {
            return $this->setFreeShippingCost($carrier->id);
        }

        $baseCost = $this->calculateBaseShippingCost(
            $carrier,
            $totalWeight,
            $request->getOrderTotal(),
            $zoneId,
            $request->getCurrencyId()
        );

        if ($baseCost->equals(new DecimalNumber('0'))) {
            return $this->setFreeShippingCost($carrier->id);
        }

        if ($carrier->shipping_handling && Configuration::get('PS_SHIPPING_HANDLING')) {
            $handlingCost = new DecimalNumber((string) Configuration::get('PS_SHIPPING_HANDLING'));
            $baseCost = $baseCost->plus($handlingCost);
        }

        $baseCost = $this->addProductShippingCosts($baseCost, $physicalProducts);

        $baseCost = $this->convertCurrency($baseCost, $request->getCurrencyId());

        return $this->applyTaxAndRound($baseCost, $carrier, $request->getAddressId());
    }

    private function filterPhysicalProducts(array $products): array
    {
        return array_filter($products, fn ($p) => empty($p['is_virtual']));
    }

    private function resolveZoneId(ShippingCalculationRequest $request): int
    {
        if ($request->getZoneId() !== null) {
            return $request->getZoneId();
        }

        if ($request->getAddressId() && Address::addressExists($request->getAddressId(), true)) {
            return (int) Address::getZoneById($request->getAddressId());
        }

        return (int) $request->getCountry()->id_zone;
    }

    private function getCarrier(int $carrierId): ?Carrier
    {
        if (!isset($this->carrierCache[$carrierId])) {
            $carrier = new Carrier($carrierId, (int) Configuration::get('PS_LANG_DEFAULT'));
            if (!Validate::isLoadedObject($carrier)) {
                return null;
            }
            $this->carrierCache[$carrierId] = $carrier;
        }

        return $this->carrierCache[$carrierId];
    }

    private function calculateTotalWeight(array $products): float
    {
        $totalWeight = 0;

        foreach ($products as $product) {
            if (!empty($product['is_virtual'])) {
                continue;
            }

            $weight = $product['weight_attribute'] ?? $product['weight'] ?? 0;
            $totalWeight += $weight * $product['quantity'];
        }

        return $totalWeight;
    }

    private function qualifiesForFreeShipping(float $orderTotal, float $totalWeight, int $currencyId): bool
    {
        $config = Configuration::getMultiple([
            'PS_SHIPPING_FREE_PRICE',
            'PS_SHIPPING_FREE_WEIGHT',
        ]);

        if (isset($config['PS_SHIPPING_FREE_PRICE']) && (float) $config['PS_SHIPPING_FREE_PRICE'] > 0) {
            $freeShippingPrice = \Tools::convertPrice(
                (float) $config['PS_SHIPPING_FREE_PRICE'],
                Currency::getCurrencyInstance($currencyId)
            );

            if ($orderTotal >= $freeShippingPrice) {
                return true;
            }
        }

        if (isset($config['PS_SHIPPING_FREE_WEIGHT']) && (float) $config['PS_SHIPPING_FREE_WEIGHT'] > 0) {
            if ($totalWeight >= (float) $config['PS_SHIPPING_FREE_WEIGHT']) {
                return true;
            }
        }

        return false;
    }

    private function calculateBaseShippingCost(
        Carrier $carrier,
        float $totalWeight,
        float $orderTotal,
        int $zoneId,
        int $currencyId
    ): DecimalNumber {
        $shippingMethod = $carrier->getShippingMethod();

        if ($carrier->range_behavior) {
            if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT) {
                if (Carrier::checkDeliveryPriceByWeight($carrier->id, $totalWeight, $zoneId) === false) {
                    return new DecimalNumber('0');
                }
            } elseif ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE) {
                if (Carrier::checkDeliveryPriceByPrice($carrier->id, $orderTotal, $zoneId, $currencyId) === false) {
                    return new DecimalNumber('0');
                }
            }
        }

        if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT) {
            $cost = $carrier->getDeliveryPriceByWeight($totalWeight, $zoneId);
        } else {
            $cost = $carrier->getDeliveryPriceByPrice($orderTotal, $zoneId, $currencyId);
        }

        return new DecimalNumber((string) $cost);
    }

    private function addProductShippingCosts(DecimalNumber $baseCost, array $products): DecimalNumber
    {
        $additionalCost = new DecimalNumber('0');

        foreach ($products as $product) {
            if (!empty($product['is_virtual'])) {
                continue;
            }

            if (isset($product['additional_shipping_cost']) && $product['additional_shipping_cost'] > 0) {
                $productCost = new DecimalNumber(
                    (string) ((float) $product['additional_shipping_cost'] * (int) $product['quantity'])
                );
                $additionalCost = $additionalCost->plus($productCost);
            }
        }

        return $baseCost->plus($additionalCost);
    }

    private function convertCurrency(DecimalNumber $amount, int $currencyId): DecimalNumber
    {
        $converted = \Tools::convertPrice(
            (float) (string) $amount,
            Currency::getCurrencyInstance($currencyId)
        );

        return new DecimalNumber((string) $converted);
    }

    private function applyTaxAndRound(
        DecimalNumber $cost,
        Carrier $carrier,
        ?int $addressId
    ): ShippingCostResult {
        $precision = $this->context->getContext()->getComputingPrecision();

        $taxExcluded = $cost;
        $taxIncluded = $cost;

        if (Configuration::get('PS_TAX') && $addressId) {
            $address = Address::initialize($addressId);
            $carrierTax = 0;

            if (Configuration::get('PS_ATCP_SHIPWRAP')) {
                $taxIncluded = $cost;
                $taxExcluded = $cost;
            } else {
                $carrierTax = $carrier->getTaxesRate($address);
                $taxIncluded = $cost->times(
                    new DecimalNumber((string) (1 + ($carrierTax / 100)))
                );
            }
        }

        $taxExcludedRounded = new DecimalNumber(
            (string) $this->tools->round((float) (string) $taxExcluded, $precision)
        );
        $taxIncludedRounded = new DecimalNumber(
            (string) $this->tools->round((float) (string) $taxIncluded, $precision)
        );

        return new ShippingCostResult(
            $taxExcludedRounded,
            $taxIncludedRounded,
            $carrier->id,
            $precision
        );
    }

    private function setFreeShippingCost(int $carrierId): ShippingCostResult
    {
        $precision = $this->context->getContext()->getComputingPrecision();
        $zero = new DecimalNumber('0');

        return new ShippingCostResult(
            $zero,
            $zero,
            $carrierId,
            $precision
        );
    }
}
