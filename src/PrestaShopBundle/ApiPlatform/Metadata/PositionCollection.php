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

namespace PrestaShopBundle\ApiPlatform\Metadata;

use Attribute;
use Symfony\Component\Serializer\Attribute\Context;

/**
 * This attribute can be added on a property in an API resource class for array containing positions,
 * the field will be expected to be an array of this form:
 *
 * [
 *     [
 *         'rowId' => 3,
 *         'newPosition' => 2,
 *     ],
 *     [
 *         'rowId' => 6,
 *         'newPosition' => 1,
 *     ],
 * ]
 *
 *  You can use the $rowIdField parameter of this Attribute to adapt the expected format (with $rowIdField = attributeId):
 *
 *  [
 *      [
 *          'attributeId' => 3,
 *          'newPosition' => 2,
 *      ],
 *      [
 *          'attributeId' => 6,
 *          'newPosition' => 1,
 *      ],
 * ]
 *
 * Thanks to this attribute the normalization process is automatized so you don't need an extra mapping, and the Open API
 * format is also adapted accordingly.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PositionCollection extends Context
{
    public const DEFAULT_ROW_ID_FIELD = 'rowId';
    public const ROW_ID_FIELD = 'position_row_id_field';

    public function __construct(string $rowIdField = self::DEFAULT_ROW_ID_FIELD, array $context = [], array $normalizationContext = [], array $denormalizationContext = [], array|string $groups = [])
    {
        parent::__construct([self::ROW_ID_FIELD => $rowIdField] + $context, $normalizationContext, $denormalizationContext, $groups);
    }
}
