<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Unit\Core\Pricing\Product\Calculator;

use PHPUnit\Framework\TestCase;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Pricing\Product\Calculator\RoundingCalculator;
use PrestaShop\PrestaShop\Core\Pricing\Product\ProductPrice;
use PrestaShop\PrestaShop\Core\Pricing\Rounding\RoundingService;
use PrestaShop\PrestaShop\Core\Pricing\ValueObject\TaxablePrice;
use PrestaShop\PrestaShop\Core\Pricing\ValueObject\TaxRate;

class RoundingCalculatorTest extends TestCase
{
    public function testRoundsToIntegerInPhase1(): void
    {
        $roundingService = new RoundingService(0);
        $calculator = new RoundingCalculator($roundingService);

        $productPrice = ProductPrice::create(1, 0);
        $productPrice->setUnitPrice(TaxablePrice::fromTaxExcluded(new DecimalNumber('29.99'), TaxRate::zero()));
        $productPrice->setOriginalPrice(TaxablePrice::fromTaxExcluded(new DecimalNumber('29.99'), TaxRate::zero()));

        $calculator->compute($productPrice);

        // 29.99 rounds to 30 with ROUND_HALF_UP at precision 0
        $this->assertTrue(
            $productPrice->getUnitPrice()->getTaxExcluded()->equals(new DecimalNumber('30'))
        );
        $this->assertTrue(
            $productPrice->getOriginalPrice()->getTaxExcluded()->equals(new DecimalNumber('30'))
        );
    }

    public function testRoundsDownWhenBelow5(): void
    {
        $roundingService = new RoundingService(0);
        $calculator = new RoundingCalculator($roundingService);

        $productPrice = ProductPrice::create(1, 0);
        $productPrice->setUnitPrice(TaxablePrice::fromTaxExcluded(new DecimalNumber('29.49'), TaxRate::zero()));
        $productPrice->setOriginalPrice(TaxablePrice::fromTaxExcluded(new DecimalNumber('29.49'), TaxRate::zero()));

        $calculator->compute($productPrice);

        $this->assertTrue(
            $productPrice->getUnitPrice()->getTaxExcluded()->equals(new DecimalNumber('29'))
        );
    }
}
