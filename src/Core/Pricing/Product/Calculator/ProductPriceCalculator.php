<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Pricing\Product\Calculator;

use PrestaShop\PrestaShop\Core\Pricing\Product\ProductPriceInterface;

/**
 * Main entry point for computing a product price. Named ProductPriceCalculator rather than
 * ProductCalculatorOrchestrator because callers simply want to calculate a product price —
 * the fact that it delegates to a priority-sorted pipeline of ProductCalculatorInterface
 * steps is an internal implementation detail.
 */
class ProductPriceCalculator implements ProductCalculatorInterface
{
    /**
     * @param iterable<ProductCalculatorInterface> $calculators Tagged iterator, priority-sorted
     */
    public function __construct(
        protected readonly iterable $calculators,
    ) {
    }

    public function compute(ProductPriceInterface $productPrice): void
    {
        foreach ($this->calculators as $calculator) {
            $calculator->compute($productPrice);
        }
    }
}
