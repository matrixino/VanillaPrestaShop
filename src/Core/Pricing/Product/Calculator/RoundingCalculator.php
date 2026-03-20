<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Pricing\Product\Calculator;

use PrestaShop\PrestaShop\Core\Pricing\Product\ProductPriceInterface;
use PrestaShop\PrestaShop\Core\Pricing\Rounding\RoundingServiceInterface;
use PrestaShop\PrestaShop\Core\Pricing\ValueObject\ImmutableTaxablePrice;
use PrestaShop\PrestaShop\Core\Pricing\ValueObject\TaxablePriceInterface;

/**
 * Last calculator in the pipeline: applies final rounding to all price fields.
 * This is the only place where rounding occurs — all prior calculators work at full precision.
 * Produces ImmutableTaxablePrice instances where both tax-excluded and tax-included are
 * independently rounded and will not be recomputed from one another.
 */
class RoundingCalculator implements ProductCalculatorInterface
{
    public function __construct(
        protected readonly RoundingServiceInterface $roundingService,
    ) {
    }

    public function compute(ProductPriceInterface $productPrice): void
    {
        $productPrice->setUnitPrice($this->roundPrice($productPrice->getUnitPrice()));
        $productPrice->setOriginalPrice($this->roundPrice($productPrice->getOriginalPrice()));
    }

    protected function roundPrice(TaxablePriceInterface $price): ImmutableTaxablePrice
    {
        $roundedExcl = $this->roundingService->round($price->getTaxExcluded());
        $roundedIncl = $this->roundingService->round($price->getTaxIncluded());

        return new ImmutableTaxablePrice(
            $roundedExcl,
            $roundedIncl,
            $roundedIncl->minus($roundedExcl),
            $price->getTaxRate(),
        );
    }
}
