<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SOLEDIS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SOLEDIS GROUP is strictly forbidden.
 * ___ ___ _ ___ ___ ___ ___
 * / __|/ _ \| | | __| \_ _/ __|
 * \__ \ (_) | |__| _|| |) | |\__ \
 * |___/\___/|____|___|___/___|___/
 *
 * @author    SOLEDIS <prestashop@groupe-soledis.com>
 * @copyright 2026 SOLEDIS
 * @license   All Rights Reserved
 *
 * @developer HERVOUET Clément
 */

namespace PrestaShop\PrestaShop\Core\Feature\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ShopModeEnum: string implements TranslatableInterface
{
    case SHOP_MODE_B2C_ONLY = 'b2c_only';
    case SHOP_MODE_B2B_ONLY = 'b2b_only';
    case SHOP_MODE_B2B_AND_B2C = 'b2b_and_b2c';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::SHOP_MODE_B2C_ONLY => $translator->trans('B2C only', [], 'Admin.Shopparameters.Feature', $locale),
            self::SHOP_MODE_B2B_ONLY => $translator->trans('B2B only', [], 'Admin.Shopparameters.Feature', $locale),
            self::SHOP_MODE_B2B_AND_B2C => $translator->trans('B2B and B2C', [], 'Admin.Shopparameters.Feature', $locale),
        };
    }
}
