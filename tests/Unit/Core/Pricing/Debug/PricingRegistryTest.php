<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Unit\Core\Pricing\Debug;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Pricing\Debug\PricingRegistry;
use PrestaShop\PrestaShop\Core\Pricing\Product\ProductPrice;

class PricingRegistryTest extends TestCase
{
    public function testRegisterAndRetrieve(): void
    {
        $registry = new PricingRegistry();
        $price1 = ProductPrice::create(1, 0);
        $price2 = ProductPrice::create(2, 0);

        $registry->registerProductPrice($price1);
        $registry->registerProductPrice($price2);

        $this->assertSame(2, $registry->count());
        $prices = $registry->getProductPrices();
        $this->assertSame($price1, $prices[0]);
        $this->assertSame($price2, $prices[1]);
    }

    public function testEmptyRegistry(): void
    {
        $registry = new PricingRegistry();

        $this->assertSame(0, $registry->count());
        $this->assertSame([], $registry->getProductPrices());
    }

    public function testClear(): void
    {
        $registry = new PricingRegistry();
        $registry->registerProductPrice(ProductPrice::create(1, 0));

        $registry->clear();

        $this->assertSame(0, $registry->count());
    }
}
