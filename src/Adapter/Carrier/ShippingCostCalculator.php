<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier;

use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\ShippingCalculationRequest;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\ShippingCostResult;

/**
 * Entry point for shipping cost calculation.
 * Creates the context from the request, runs it through the pipeline,
 * and returns the final ShippingCostResult.
 */
class ShippingCostCalculator
{
    public function __construct(
        private readonly ShippingCostCalculatorInterface $pipeline,
    ) {
    }

    public function calculate(ShippingCalculationRequest $request): ?ShippingCostResult
    {
        $context = ShippingCostContext::createFromRequest($request);

        if ($context->isEmpty()) {
            return null;
        }

        $this->pipeline->compute($context);

        $taxExcluded = $context->getTaxExcluded();
        $taxIncluded = $context->getTaxIncluded();
        $selectedCarrierId = $context->getSelectedCarrierId();
        $precision = $context->getPrecision();

        if ($taxExcluded === null || $selectedCarrierId === null || $precision === null) {
            return null;
        }

        return new ShippingCostResult(
            $taxExcluded,
            $taxIncluded ?? $taxExcluded,
            $selectedCarrierId,
            $precision,
        );
    }
}
