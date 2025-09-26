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

namespace PrestaShop\PrestaShop\Core\Domain\Search\Command;

use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

/**
 * Triggers search indexation.
 */
class SearchIndexationCommand
{
    private bool $full;

    private ?ProductId $productId;

    private ShopConstraint $shopConstraint;

    public function __construct(
        bool $full = false,
        ?ShopConstraint $shopConstraint = null,
        ?ProductId $productId = null
    ) {
        $this->full = $full;
        $this->shopConstraint = $shopConstraint ?? ShopConstraint::allShops();
        $this->productId = $productId;
    }

    /**
     * Indicates if full reindex is requested.
     */
    public function isFull(): bool
    {
        return $this->full;
    }

    /**
     * Returns shop constraint for indexation.
     */
    public function getShopConstraint(): ShopConstraint
    {
        return $this->shopConstraint;
    }

    /**
     * Return product id for indexation
     */
    public function getProductId(): ?ProductId
    {
        return $this->productId;
    }
}
