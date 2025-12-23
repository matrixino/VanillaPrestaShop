<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\PrestaShop\Core\Domain\Discount\Command;

use DateTimeImmutable;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;
use PrestaShop\PrestaShop\Core\Domain\Currency\ValueObject\CurrencyId;
use PrestaShop\PrestaShop\Core\Domain\Customer\Group\ValueObject\GroupId;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShop\PrestaShop\Core\Domain\Discount\Exception\DiscountConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Discount\ProductRuleGroup;
use PrestaShop\PrestaShop\Core\Domain\Discount\ValueObject\DiscountType;
use PrestaShop\PrestaShop\Core\Domain\Exception\DomainConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Language\ValueObject\LanguageId;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationIdInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\NoCombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\ValueObject\Money;

class AddDiscountCommand
{
    private array $localizedNames = [];
    private int $priority = 1;
    private bool $active = false;
    private ?DateTimeImmutable $validFrom = null;
    private ?DateTimeImmutable $validTo = null;
    private ?int $totalQuantity = null;
    private ?int $quantityPerUser = 1;
    private string $description = '';
    private string $code = '';
    private ?CustomerId $customerId = null;
    private bool $highlightInCart = false;
    private bool $allowPartialUse = true;
    private DiscountType $type;
    private ?DecimalNumber $percentDiscount = null;
    private ?Money $amountDiscount = null;
    private ?ProductId $giftProductId = null;
    private ?CombinationIdInterface $giftCombinationId = null;
    private int $reductionProduct = 0;
    private ?int $minimumProductsQuantity = null;

    private ?array $productConditions = null;

    private ?Money $minimumAmount = null;

    private ?bool $minimumAmountShippingIncluded = null;

    /**
     * @var CarrierId[]|null
     */
    private ?array $carrierIds = null;

    private ?array $countryIds = null;

    /**
     * @var GroupId[]|null
     */
    private ?array $customerGroupIds = null;
    /**
     * @var int[]|null
     */
    private ?array $compatibleDiscountTypeIds = null;

    public function __construct(
        string $type,
        array $localizedNames,
    ) {
        $this->type = new DiscountType($type);
        $this->localizedNames = $localizedNames;
    }

    /**
     * @param array<int, string> $localizedNames
     */
    public function setLocalizedNames(array $localizedNames): self
    {
        foreach ($localizedNames as $languageId => $name) {
            $this->localizedNames[(new LanguageId($languageId))->getValue()] = $name;
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getLocalizedNames(): array
    {
        return $this->localizedNames;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setValidityDateRange(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        $this->assertDateRangeIsValid($from, $to);
        $this->validFrom = $from;
        $this->validTo = $to;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getDiscountType(): DiscountType
    {
        return $this->type;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setPriority(int $priority): self
    {
        if (0 >= $priority) {
            throw new DiscountConstraintException(
                sprintf('Invalid discount priority "%s". Must be a positive integer.', $priority),
                DiscountConstraintException::INVALID_PRIORITY
            );
        }

        $this->priority = $priority;

        return $this;
    }

    public function getTotalQuantity(): ?int
    {
        return $this->totalQuantity;
    }

    public function isHighlightInCart(): bool
    {
        return $this->highlightInCart;
    }

    public function setHighlightInCart(bool $highlightInCart): void
    {
        $this->highlightInCart = $highlightInCart;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setTotalQuantity(?int $quantity): self
    {
        if ($quantity !== null && 0 > $quantity) {
            throw new DiscountConstraintException(sprintf('Quantity cannot be lower than zero, %d given', $quantity), DiscountConstraintException::INVALID_QUANTITY);
        }

        $this->totalQuantity = $quantity;

        return $this;
    }

    public function getQuantityPerUser(): ?int
    {
        return $this->quantityPerUser;
    }

    /**
     * @throws DiscountConstraintException
     */
    public function setQuantityPerUser(?int $quantity): self
    {
        if ($quantity !== null && 0 > $quantity) {
            throw new DiscountConstraintException(sprintf('Quantity per user cannot be lower than zero, %d given', $quantity), DiscountConstraintException::INVALID_QUANTITY_PER_USER);
        }

        $this->quantityPerUser = $quantity;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function allowPartialUse(): bool
    {
        return $this->allowPartialUse;
    }

    public function setAllowPartialUse(bool $allow): self
    {
        $this->allowPartialUse = $allow;

        return $this;
    }

    public function getCustomerId(): ?CustomerId
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): self
    {
        $this->customerId = new CustomerId($customerId);

        return $this;
    }

    public function getPercentDiscount(): ?DecimalNumber
    {
        return $this->percentDiscount;
    }

    public function setPercentDiscount(DecimalNumber $percentDiscount): self
    {
        $this->percentDiscount = $percentDiscount;

        return $this;
    }

    public function getAmountDiscount(): ?Money
    {
        return $this->amountDiscount;
    }

    /**
     * @throws DomainConstraintException
     */
    public function setAmountDiscount(DecimalNumber $amountDiscount, int $currencyId, bool $taxIncluded): self
    {
        if ($amountDiscount->isLowerThanZero()) {
            throw new DiscountConstraintException(sprintf('Money amount cannot be lower than zero, %s given', $amountDiscount), DiscountConstraintException::INVALID_DISCOUNT_VALUE_CANNOT_BE_NEGATIVE);
        }

        $this->amountDiscount = new Money($amountDiscount, new CurrencyId($currencyId), $taxIncluded);

        return $this;
    }

    public function getGiftProductId(): ?ProductId
    {
        return $this->giftProductId;
    }

    public function setGiftProductId(int $giftProductId): self
    {
        $this->giftProductId = new ProductId($giftProductId);

        return $this;
    }

    public function getGiftCombinationId(): ?CombinationIdInterface
    {
        return $this->giftCombinationId;
    }

    public function setGiftCombinationId(int $giftCombinationId): self
    {
        if (NoCombinationId::NO_COMBINATION_ID === $giftCombinationId) {
            $this->giftCombinationId = new NoCombinationId();
        } else {
            $this->giftCombinationId = new CombinationId($giftCombinationId);
        }

        return $this;
    }

    public function getReductionProduct(): int
    {
        return $this->reductionProduct;
    }

    /**
     * @param int $reductionProduct
     *
     * @return $this
     *
     * This can have several values
     *  0 => The discount is not a Product discount
     * -1 => The discounted product is the cheapest of the cart
     * -2 => The discount is applied on a selection of product // this case is not yet handled.
     * >0 => The productId of the discounted product
     */
    public function setReductionProduct(int $reductionProduct): self
    {
        $this->reductionProduct = $reductionProduct;

        return $this;
    }

    public function getMinimumProductsQuantity(): ?int
    {
        return $this->minimumProductsQuantity;
    }

    public function setMinimumProductsQuantity(int $minimumProductsQuantity): self
    {
        if ($minimumProductsQuantity < 0) {
            throw new DiscountConstraintException('Minimum products quantity must be greater than 0', DiscountConstraintException::INVALID_MINIMUM_PRODUCT_QUANTITY);
        }

        $this->minimumProductsQuantity = $minimumProductsQuantity;

        return $this;
    }

    public function getMinimumAmount(): ?Money
    {
        return $this->minimumAmount;
    }

    public function getMinimumAmountShippingIncluded(): ?bool
    {
        return $this->minimumAmountShippingIncluded;
    }

    public function setMinimumAmount(DecimalNumber $amountDiscount, int $currencyId, bool $taxIncluded, bool $minimumAmountShippingIncluded): self
    {
        if ($amountDiscount->isLowerThanZero()) {
            throw new DiscountConstraintException(sprintf('Money amount cannot be lower than zero, %s given', $amountDiscount), DiscountConstraintException::INVALID_DISCOUNT_VALUE_CANNOT_BE_NEGATIVE);
        }

        $this->minimumAmount = new Money($amountDiscount, new CurrencyId($currencyId), $taxIncluded);
        $this->minimumAmountShippingIncluded = $minimumAmountShippingIncluded;

        return $this;
    }

    public function getProductConditions(): ?array
    {
        return $this->productConditions;
    }

    /**
     * @param ProductRuleGroup[] $productConditions
     *
     * @return self
     *
     * @throws DiscountConstraintException
     */
    public function setProductConditions(array $productConditions): self
    {
        foreach ($productConditions as $productCondition) {
            if (!$productCondition instanceof ProductRuleGroup) {
                throw new DiscountConstraintException(sprintf('Product conditions must be an array of %s', ProductRuleGroup::class), DiscountConstraintException::INVALID_PRODUCTS_CONDITIONS);
            }
            if (empty($productCondition->getRules())) {
                throw new DiscountConstraintException(sprintf('Product conditions rules cannot be empty'), DiscountConstraintException::INVALID_PRODUCTS_CONDITIONS);
            }

            foreach ($productCondition->getRules() as $rule) {
                if (empty($rule->getItemIds())) {
                    throw new DiscountConstraintException(sprintf('Product conditions rule items cannot be empty'), DiscountConstraintException::INVALID_PRODUCTS_CONDITIONS);
                }

                foreach ($rule->getItemIds() as $itemId) {
                    if (!is_int($itemId)) {
                        throw new DiscountConstraintException(sprintf('Product conditions rule item ID must be an integer'), DiscountConstraintException::INVALID_PRODUCTS_CONDITIONS);
                    }
                    if ((int) $itemId <= 0) {
                        throw new DiscountConstraintException(sprintf('Product conditions rule item ID must be strictly positive'), DiscountConstraintException::INVALID_PRODUCTS_CONDITIONS);
                    }
                }
            }
        }

        $this->productConditions = $productConditions;

        return $this;
    }

    /**
     * @return CarrierId[]|null
     */
    public function getCarrierIds(): ?array
    {
        return $this->carrierIds;
    }

    /**
     * @param int[]|null $carrierIds
     *
     * @return $this
     */
    public function setCarrierIds(?array $carrierIds): self
    {
        $this->carrierIds = array_map(fn (int $carrierId) => new CarrierId($carrierId), $carrierIds);

        return $this;
    }

    public function getCountryIds(): ?array
    {
        return $this->countryIds;
    }

    public function setCountryIds(?array $countryIds): self
    {
        $this->countryIds = array_map(fn (int $countryId) => new CountryId($countryId), $countryIds);

        return $this;
    }

    /**
     * @return GroupId[]|null
     */
    public function getCustomerGroupIds(): ?array
    {
        return $this->customerGroupIds;
    }

    /**
     * @return $this
     */
    public function setCustomerGroupIds(?array $customerGroupIds): self
    {
        $this->customerGroupIds = $customerGroupIds ? array_map(fn (int $groupId) => new GroupId($groupId), $customerGroupIds) : null;

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getCompatibleDiscountTypeIds(): ?array
    {
        return $this->compatibleDiscountTypeIds;
    }

    /**
     * @param int[]|null $compatibleDiscountTypeIds
     */
    public function setCompatibleDiscountTypeIds(?array $compatibleDiscountTypeIds): self
    {
        foreach ($compatibleDiscountTypeIds as $compatibleDiscountTypeId) {
            if (!is_int($compatibleDiscountTypeId) || $compatibleDiscountTypeId <= 0) {
                throw new DiscountConstraintException('Compatible discount type ID must be positive integer', DiscountConstraintException::INVALID_COMPATIBLE_TYPE_IDS);
            }
        }
        $uniqueValues = array_unique($compatibleDiscountTypeIds);
        if ($uniqueValues !== $compatibleDiscountTypeIds) {
            throw new DiscountConstraintException('Provided compatible discount type ID must be unique', DiscountConstraintException::INVALID_COMPATIBLE_TYPE_IDS);
        }

        $this->compatibleDiscountTypeIds = $compatibleDiscountTypeIds;

        return $this;
    }

    /**
     * @throws DiscountConstraintException
     */
    private function assertDateRangeIsValid(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): void
    {
        if ($dateFrom > $dateTo) {
            throw new DiscountConstraintException('Date from cannot be greater than date to.', DiscountConstraintException::DATE_FROM_GREATER_THAN_DATE_TO);
        }
    }
}
