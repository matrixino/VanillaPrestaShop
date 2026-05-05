<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier\ShippingCost\Calculator;

use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Provider\CarrierDataProviderInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;

/**
 * Loads carrier configuration into the context.
 * When the carrier is not found, falls back to free shipping.
 * When the carrier shipping method is FREE, marks the context as free shipping.
 */
class CarrierDataCalculator implements ShippingCostCalculatorInterface
{
    public function __construct(
        private readonly CarrierDataProviderInterface $carrierDataProvider,
    ) {
    }

    public function compute(ShippingCostContext $context): void
    {
        $carrierData = $this->carrierDataProvider->getCarrierShippingData($context->getCarrierId());

        if ($carrierData === null) {
            $context->setSelectedCarrierId($context->getCarrierId());
            $context->setFreeShipping(true);

            return;
        }

        $context->setCarrierData($carrierData);
        $context->setSelectedCarrierId($carrierData->getCarrierId());

        if ($carrierData->isFreeShippingMethod()) {
            $context->setFreeShipping(true);
        }
    }
}
