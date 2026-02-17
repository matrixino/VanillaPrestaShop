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

namespace PrestaShop\PrestaShop\Core\Domain\Shipping\ValueObject;

use Country;

final class ShippingCalculationRequest
{
    private array $products;
    private ?int $carrierId;
    private ?int $zoneId;
    private ?int $addressId;
    private Country $country;
    private int $currencyId;
    private ?int $customerId;
    private float $orderTotal;

    /**
     * @param array $products Array of products with required fields:
     *                        - id_product (int): Product ID
     *                        - id_product_attribute (int): Product attribute/combination ID
     *                        - quantity (int): Quantity in cart
     *                        - weight (float): Product base weight
     *                        - weight_attribute (float|null): Product attribute weight (optional)
     *                        - is_virtual (bool): Whether product is virtual (0 = physical, 1 = virtual)
     *                        - additional_shipping_cost (float): Additional shipping cost per item
     * @param int|null $carrierId Specific carrier to use, null for auto-selection
     * @param int|null $zoneId Zone ID for shipping, null for auto-detection from address/country
     * @param int|null $addressId Address ID for tax calculation and zone resolution
     * @param Country $country Country for shipping (fallback for zone resolution)
     * @param int $currencyId Currency ID for price calculations
     * @param int|null $customerId Customer ID for carrier filtering by customer groups (optional)
     * @param float $orderTotal Order total for price-based shipping calculations
     *
     * @throws \InvalidArgumentException If products array is invalid or missing required fields
     */
    public function __construct(
        array $products,
        int $carrierId,
        ?int $zoneId,
        ?int $addressId,
        Country $country,
        int $currencyId,
        ?int $customerId,
        float $orderTotal
    ) {
        foreach ($products as $product) {
            $this->validateProduct($product);
        }

        $this->products = $products;
        $this->carrierId = $carrierId;
        $this->zoneId = $zoneId;
        $this->addressId = $addressId;
        $this->country = $country;
        $this->currencyId = $currencyId;
        $this->customerId = $customerId;
        $this->orderTotal = $orderTotal;
    }

    private function validateProduct(array $product): void
    {
        $required = ['id_product', 'quantity', 'is_virtual'];
        foreach ($required as $field) {
            if (!isset($product[$field])) {
                throw new \InvalidArgumentException("Product missing required field: {$field}");
            }
        }
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getCarrierId(): ?int
    {
        return $this->carrierId;
    }

    public function getZoneId(): ?int
    {
        return $this->zoneId;
    }

    public function getAddressId(): ?int
    {
        return $this->addressId;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function getOrderTotal(): float
    {
        return $this->orderTotal;
    }
}
