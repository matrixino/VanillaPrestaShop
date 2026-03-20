<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Integration\Core\Pricing;

use PrestaShop\PrestaShop\Core\Pricing\Debug\PricingHistoryDisplayer;
use PrestaShop\PrestaShop\Core\Pricing\Debug\PricingRegistry;
use PrestaShop\PrestaShop\Core\Pricing\Product\Calculator\ProductPriceCalculator;
use PrestaShop\PrestaShop\Core\Pricing\Product\Provider\CatalogProductProvider;
use PrestaShop\PrestaShop\Core\Pricing\Rounding\RoundingService;
use PrestaShop\PrestaShop\Core\Pricing\Rounding\RoundingServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PricingServiceWiringTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testRoundingServiceIsRegistered(): void
    {
        $service = self::getContainer()->get(RoundingService::class);
        $this->assertInstanceOf(RoundingService::class, $service);
    }

    public function testRoundingServiceInterfaceAlias(): void
    {
        $service = self::getContainer()->get(RoundingServiceInterface::class);
        $this->assertInstanceOf(RoundingService::class, $service);
    }

    public function testCatalogProductProviderIsRegistered(): void
    {
        $service = self::getContainer()->get(CatalogProductProvider::class);
        $this->assertInstanceOf(CatalogProductProvider::class, $service);
    }

    public function testCartOrchestratorIsRegistered(): void
    {
        $orchestrator = self::getContainer()->get('prestashop.pricing.cart.product_price_calculator');
        $this->assertInstanceOf(ProductPriceCalculator::class, $orchestrator);
    }

    public function testOrderOrchestratorIsRegistered(): void
    {
        $orchestrator = self::getContainer()->get('prestashop.pricing.order.product_price_calculator');
        $this->assertInstanceOf(ProductPriceCalculator::class, $orchestrator);
    }

    public function testPricingRegistryIsRegistered(): void
    {
        $registry = self::getContainer()->get(PricingRegistry::class);
        $this->assertInstanceOf(PricingRegistry::class, $registry);
    }

    public function testPricingHistoryDisplayerIsRegistered(): void
    {
        $displayer = self::getContainer()->get(PricingHistoryDisplayer::class);
        $this->assertInstanceOf(PricingHistoryDisplayer::class, $displayer);
    }
}
