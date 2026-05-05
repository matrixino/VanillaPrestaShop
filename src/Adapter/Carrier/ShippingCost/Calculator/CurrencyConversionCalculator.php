<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier\ShippingCost\Calculator;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Adapter\Currency\Repository\CurrencyRepository;
use PrestaShop\PrestaShop\Adapter\Tools;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Calculator\ShippingCostCalculatorInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\ShippingCostContext;
use PrestaShop\PrestaShop\Core\Domain\Currency\ValueObject\CurrencyId;

/**
 * Converts the accumulated cost to the order currency.
 */
class CurrencyConversionCalculator implements ShippingCostCalculatorInterface
{
    public function __construct(
        private readonly Tools $tools,
        private readonly CurrencyRepository $currencyRepository,
    ) {
    }

    public function compute(ShippingCostContext $context): void
    {
        if ($context->isFreeShipping()) {
            return;
        }

        $currency = $this->currencyRepository->get(new CurrencyId($context->getCurrencyId()));
        $converted = $this->tools->convertPrice($context->getCost()->__toString(), $currency);

        $context->setCost(new DecimalNumber((string) $converted));
    }
}
