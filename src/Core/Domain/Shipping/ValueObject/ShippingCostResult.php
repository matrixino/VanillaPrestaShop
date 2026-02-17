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

use PrestaShop\Decimal\DecimalNumber;

/**
 * Represents the result of a shipping cost calculation with both tax-included
 * and tax-excluded amounts.
 *
 * This value object is used by the standalone shipping cost calculator to return
 * calculated shipping costs without requiring a Cart object dependency.
 */
final class ShippingCostResult
{
    private DecimalNumber $taxExcluded;
    private DecimalNumber $taxIncluded;
    private int $selectedCarrierId;
    private int $precision;

    /**
     * @param DecimalNumber $taxExcluded Shipping cost without taxes (HT)
     * @param DecimalNumber $taxIncluded Shipping cost with taxes (TTC)
     * @param int $selectedCarrierId The carrier ID that was selected/used for calculation
     * @param int $precision Computing precision used for calculations
     *
     * @throws \InvalidArgumentException If amounts are negative or carrier ID is invalid
     */
    public function __construct(
        DecimalNumber $taxExcluded,
        DecimalNumber $taxIncluded,
        int $selectedCarrierId,
        int $precision
    ) {
        if ($taxExcluded->isNegative()) {
            throw new \InvalidArgumentException('Tax excluded amount cannot be negative');
        }
        if ($taxIncluded->isNegative()) {
            throw new \InvalidArgumentException('Tax included amount cannot be negative');
        }
        if ($selectedCarrierId <= 0) {
            throw new \InvalidArgumentException('Selected carrier ID must be positive');
        }

        $this->taxExcluded = $taxExcluded;
        $this->taxIncluded = $taxIncluded;
        $this->selectedCarrierId = $selectedCarrierId;
        $this->precision = $precision;
    }

    public function getTaxExcluded(): DecimalNumber
    {
        return $this->taxExcluded;
    }

    public function getTaxIncluded(): DecimalNumber
    {
        return $this->taxIncluded;
    }

    public function getSelectedCarrierId(): int
    {
        return $this->selectedCarrierId;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }


    public function getTaxExcludedAsFloat(): float
    {
        return (float) (string) $this->taxExcluded;
    }

    public function getTaxIncludedAsFloat(): float
    {
        return (float) (string) $this->taxIncluded;
    }
}
