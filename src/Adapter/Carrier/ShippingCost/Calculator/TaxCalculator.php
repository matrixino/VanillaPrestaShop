<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier\ShippingCost\Calculator;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Adapter\Configuration as AdapterConfiguration;
use PrestaShop\PrestaShop\Adapter\Tools;
use PrestaShop\PrestaShop\Core\Context\CurrencyContext;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Provider\ShippingTaxRateProviderInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;

/**
 * Applies the carrier tax rate to produce final tax-excluded and tax-included amounts.
 * Also handles the free shipping case by writing zeros.
 * Must be the last calculator in the pipeline.
 */
class TaxCalculator implements ShippingCostCalculatorInterface
{
    public function __construct(
        private readonly AdapterConfiguration $configuration,
        private readonly ShippingTaxRateProviderInterface $taxRateProvider,
        private readonly Tools $tools,
        private readonly CurrencyContext $currencyContext,
    ) {
    }

    public function compute(ShippingCostContext $context): void
    {
        $precision = $this->currencyContext->getPrecision();
        $context->setPrecision($precision);

        if ($context->isFreeShipping()) {
            $zero = new DecimalNumber('0');
            $context->setTaxExcluded($zero);
            $context->setTaxIncluded($zero);

            return;
        }

        $cost = $context->getCost();

        $addressId = $context->getAddressId();
        $carrierId = $context->getSelectedCarrierId() ?? $context->getCarrierId();
        $taxIncluded = $cost;

        if (
            $this->configuration->get('PS_TAX')
            && $addressId !== null
            && !$this->configuration->get('PS_ATCP_SHIPWRAP')
        ) {
            $taxRate = $this->taxRateProvider->getTaxRate($carrierId, $addressId);
            $taxIncluded = $cost->times(new DecimalNumber((string) (1 + ($taxRate / 100))));
        }

        $context->setTaxExcluded(
            new DecimalNumber((string) $this->tools->round($cost->__toString(), $precision))
        );
        $context->setTaxIncluded(
            new DecimalNumber((string) $this->tools->round($taxIncluded->__toString(), $precision))
        );
    }
}
