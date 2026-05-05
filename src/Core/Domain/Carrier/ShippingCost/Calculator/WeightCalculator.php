<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;

class WeightCalculator implements ShippingCostCalculatorInterface
{
    public function compute(ShippingCostContext $context): void
    {
        if ($context->isFreeShipping()) {
            return;
        }

        $totalWeight = new DecimalNumber('0');
        foreach ($context->getPhysicalProducts() as $product) {
            $weight = new DecimalNumber((string) ($product['weight_attribute'] ?? $product['weight'] ?? 0));
            $quantity = new DecimalNumber((string) $product['quantity']);
            $totalWeight = $totalWeight->plus($weight->times($quantity));
        }

        $context->setTotalWeight($totalWeight);
    }
}
