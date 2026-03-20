<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Pricing\Product;

use PrestaShop\PrestaShop\Core\Pricing\ValueObject\TaxablePrice;
use PrestaShop\PrestaShop\Core\Pricing\ValueObject\TaxablePriceInterface;

/**
 * Lightweight ProductPrice DTO with no tracking overhead. Setters simply assign values.
 */
class ProductPrice implements ProductPriceInterface
{
    protected TaxablePriceInterface $unitPrice;
    protected TaxablePriceInterface $originalPrice;

    protected function __construct(
        protected readonly int $productId,
        protected readonly int $combinationId,
        protected readonly int $quantity,
    ) {
        $this->unitPrice = TaxablePrice::zero();
        $this->originalPrice = TaxablePrice::zero();
    }

    public static function create(int $productId, int $combinationId, int $quantity = 1): self
    {
        return new self($productId, $combinationId, $quantity);
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getCombinationId(): int
    {
        return $this->combinationId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): TaxablePriceInterface
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(TaxablePriceInterface $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getOriginalPrice(): TaxablePriceInterface
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(TaxablePriceInterface $originalPrice): void
    {
        $this->originalPrice = $originalPrice;
    }
}
