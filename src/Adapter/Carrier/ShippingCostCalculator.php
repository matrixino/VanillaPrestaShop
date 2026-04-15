<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier;

use Carrier;
use Exception;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Adapter\Address\Repository\AddressRepository;
use PrestaShop\PrestaShop\Adapter\Carrier\Repository\CarrierRepository;
use PrestaShop\PrestaShop\Adapter\Configuration as AdapterConfiguration;
use PrestaShop\PrestaShop\Adapter\Currency\Repository\CurrencyRepository;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Adapter\Tools;
use PrestaShop\PrestaShop\Core\Context\CurrencyContext;
use PrestaShop\PrestaShop\Core\Domain\Address\Exception\AddressNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\ShippingCalculationRequest;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\ShippingCostResult;
use PrestaShop\PrestaShop\Core\Domain\Currency\ValueObject\CurrencyId;

class ShippingCostCalculator
{
    /**
     * @var Carrier[]
     */
    private array $carrierCache = [];

    public function __construct(
        private readonly LegacyContext $context,
        private readonly Tools $tools,
        private readonly CurrencyRepository $currencyRepository,
        private readonly CurrencyContext $currencyContext,
        private readonly AdapterConfiguration $configurationAdapter,
        private readonly CarrierRepository $carrierRepository,
        private readonly AddressRepository $addressRepository,
    ) {
    }

    public function calculate(ShippingCalculationRequest $request): ?ShippingCostResult
    {
        $physicalProducts = $this->filterPhysicalProducts($request->getProducts());

        if (empty($physicalProducts)) {
            return null;
        }

        $zoneId = $this->resolveZoneId($request);
        $carrier = $this->getCarrier($request->getCarrierId());

        if (empty($carrier)) {
            return $this->setFreeShippingCost($request->getCarrierId());
        }

        if ($carrier->getShippingMethod() === Carrier::SHIPPING_METHOD_FREE) {
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

        if ($carrier->shipping_handling && $this->configurationAdapter->get('PS_SHIPPING_HANDLING')) {
            $handlingCost = new DecimalNumber((string) $this->configurationAdapter->get('PS_SHIPPING_HANDLING'));
            $baseCost = $baseCost->plus($handlingCost);
        }

        $baseCost = $this->addProductShippingCosts($baseCost, $physicalProducts);
        $baseCost = $this->convertCurrency($baseCost, $request->getCurrencyId());

        return $this->applyTaxAndRound($baseCost, $carrier, $request->getAddressId());
    }

    /**
     * @return array<array{
     *     id_product: int,
     *     id_product_attribute: int,
     *     quantity: int,
     *     weight: float,
     *     weight_attribute: float|null,
     *     is_virtual: bool,
     *     additional_shipping_cost: float,
     *     price_wt: float
     * }>
     */
    private function filterPhysicalProducts(array $products): array
    {
        return array_filter($products, fn ($p) => $p['is_virtual'] === false);
    }

    private function resolveZoneId(ShippingCalculationRequest $request): int
    {
        if ($request->getZoneId() !== null) {
            return $request->getZoneId();
        }

        if ($request->getAddressId()) {
            try {
                return $this->addressRepository->getZoneId(
                    new AddressId($request->getAddressId())
                );
            } catch (Exception $e) {
                throw new AddressNotFoundException(sprintf('An error occured while retrieving address with %s', $request->getAddressId()), 400);
            }
        }

        return $request->getCountryZoneId();
    }

    private function getCarrier(int $carrierId): ?Carrier
    {
        if (!isset($this->carrierCache[$carrierId])) {
            try {
                $carrier = $this->carrierRepository->get(new CarrierId($carrierId));
                $this->carrierCache[$carrierId] = $carrier;
            } catch (Exception $e) {
                return null;
            }
        }

        return $this->carrierCache[$carrierId];
    }

    private function calculateTotalWeight(array $products): float
    {
        $totalWeight = 0;

        foreach ($products as $product) {
            if ($product['is_virtual'] === true) {
                continue;
            }

            $weight = $product['weight_attribute'] ?? $product['weight'] ?? 0;
            $totalWeight += $weight * $product['quantity'];
        }

        return $totalWeight;
    }

    private function qualifiesForFreeShipping(float $orderTotal, float $totalWeight, int $currencyId): bool
    {
        $shippingFreePrice = $this->configurationAdapter->get('PS_SHIPPING_FREE_PRICE');
        $shippingFreeWeight = $this->configurationAdapter->get('PS_SHIPPING_FREE_WEIGHT');

        if (isset($shippingFreePrice) && (float) $shippingFreePrice > 0) {
            $freeShippingPrice = $this->tools->convertPrice(
                (float) $shippingFreePrice,
                $this->currencyRepository->get(new CurrencyId($currencyId))
            );

            if ($orderTotal >= $freeShippingPrice) {
                return true;
            }
        }

        if (isset($shippingFreeWeight) && (float) $shippingFreeWeight > 0) {
            if ($totalWeight >= (float) $shippingFreeWeight) {
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
            if ($shippingMethod === Carrier::SHIPPING_METHOD_WEIGHT) {
                if (Carrier::checkDeliveryPriceByWeight($carrier->id, $totalWeight, $zoneId) === false) {
                    return new DecimalNumber('0');
                }
            } elseif ($shippingMethod === Carrier::SHIPPING_METHOD_PRICE) {
                if (Carrier::checkDeliveryPriceByPrice($carrier->id, $orderTotal, $zoneId, $currencyId) === false) {
                    return new DecimalNumber('0');
                }
            }
        }

        if ($shippingMethod === Carrier::SHIPPING_METHOD_WEIGHT) {
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
            if ($product['is_virtual'] === true) {
                continue;
            }

            if (isset($product['additional_shipping_cost']) && $product['additional_shipping_cost'] > 0) {
                $productShippingCost = (float) $product['additional_shipping_cost'];
                $productQuantity = (int) $product['quantity'];

                $productCost = new DecimalNumber(
                    (string) ($productShippingCost * $productQuantity)
                );
                $additionalCost = $additionalCost->plus($productCost);
            }
        }

        return $baseCost->plus($additionalCost);
    }

    private function convertCurrency(DecimalNumber $amount, int $currencyId): DecimalNumber
    {
        $currency = $this->currencyRepository->get(new CurrencyId($currencyId));

        $converted = $this->tools->convertPrice(
            (float) (string) $amount,
            $currency
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

        if ($this->configurationAdapter->get('PS_TAX') && $addressId && !$this->configurationAdapter->get('PS_ATCP_SHIPWRAP')) {
            $address = $this->addressRepository->get(new AddressId($addressId));
            $carrierTax = $carrier->getTaxesRate($address);
            $taxIncluded = $cost->times(
                new DecimalNumber((string) (1 + ($carrierTax / 100)))
            );
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
        $precision = $this->currencyContext->getPrecision();
        $zero = new DecimalNumber('0');

        return new ShippingCostResult(
            $zero,
            $zero,
            $carrierId,
            $precision
        );
    }
}
