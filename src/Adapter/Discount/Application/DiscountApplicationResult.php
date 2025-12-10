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

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Discount\Application;

/**
 * Result of discount application validation and priority ordering
 *
 * This is the single source of truth for which discounts should be applied to a cart
 * and in what order they should be applied.
 */
class DiscountApplicationResult
{
    /**
     * @param array<int> $discountsToApply List of discount IDs to apply, ordered by priority
     * @param array<int> $discountsToRemove List of discount IDs that should be removed from cart
     */
    public function __construct(
        private readonly bool $canApply,
        private readonly array $discountsToApply,
        private readonly array $discountsToRemove = [],
        private readonly ?string $rejectionReason = null
    ) {
    }

    /**
     * Check if the new discount can be applied to the cart
     */
    public function canApply(): bool
    {
        return $this->canApply;
    }

    /**
     * Get the ordered list of discount IDs that should be applied to the cart
     * The order reflects the priority of application (first = applied first)
     *
     * @return array<int>
     */
    public function getDiscountsToApply(): array
    {
        return $this->discountsToApply;
    }

    /**
     * Get the list of discount IDs that need to be removed from the cart
     *
     * @return array<int>
     */
    public function getDiscountsToRemove(): array
    {
        return $this->discountsToRemove;
    }

    /**
     * Get the reason why the discount was rejected (if applicable)
     */
    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    /**
     * Check if any discounts need to be removed
     */
    public function hasDiscountsToRemove(): bool
    {
        return !empty($this->discountsToRemove);
    }
}
