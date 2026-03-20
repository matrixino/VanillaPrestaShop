<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Pricing\Product;

use PrestaShop\PrestaShop\Core\Pricing\ValueObject\TaxablePriceInterface;

/**
 * Mutable DTO carrying the computed prices for a single product (or combination).
 * Calculators receive this and mutate it in place.
 */
interface ProductPriceInterface
{
    public function getProductId(): int;

    public function getCombinationId(): int;

    public function getQuantity(): int;

    public function getUnitPrice(): TaxablePriceInterface;

    public function setUnitPrice(TaxablePriceInterface $unitPrice): void;

    public function getOriginalPrice(): TaxablePriceInterface;

    public function setOriginalPrice(TaxablePriceInterface $originalPrice): void;
}
