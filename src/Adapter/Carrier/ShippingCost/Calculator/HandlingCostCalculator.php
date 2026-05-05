<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier\ShippingCost\Calculator;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Adapter\Configuration as AdapterConfiguration;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;

/**
 * Adds the global handling cost (PS_SHIPPING_HANDLING) to the running cost
 * when the carrier has shipping_handling enabled.
 */
class HandlingCostCalculator implements ShippingCostCalculatorInterface
{
    public function __construct(
        private readonly AdapterConfiguration $configuration,
    ) {
    }

    public function compute(ShippingCostContext $context): void
    {
        if ($context->isFreeShipping()) {
            return;
        }

        $carrierData = $context->getCarrierData();
        if ($carrierData === null || !$carrierData->hasShippingHandling()) {
            return;
        }

        $handlingCost = $this->configuration->get('PS_SHIPPING_HANDLING');
        if (!$handlingCost) {
            return;
        }

        $context->setCost($context->getCost()->plus(new DecimalNumber((string) $handlingCost)));
    }
}
