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

namespace PrestaShopBundle\ApiPlatform;

use PrestaShopBundle\ApiPlatform\Metadata\PositionCollection;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

/**
 * Used to adapt the data from array of positions, so far all it needs to do is transform
 * ['attributeId' => 5] into ['rowId' => 5]. To do that it detects the array properties with
 * the PositionCollection attribute.
 */
class PositionCollectionUpdater
{
    public function __construct(
        protected ClassMetadataFactoryInterface $classMetadataFactory,
    ) {
    }

    public function normalizePositionCollection(array $normalizedData, string $type): array
    {
        if (!$this->classMetadataFactory->hasMetadataFor($type)) {
            return $normalizedData;
        }

        $metadata = $this->classMetadataFactory->getMetadataFor($type);
        foreach ($metadata->getAttributesMetadata() as $attributeMetadata) {
            $attributeContext = $attributeMetadata->getDenormalizationContexts()['*'] ?? [];
            // This is not an attribute with PositionCollection attribute
            if (!isset($attributeContext[PositionCollection::ROW_ID_FIELD])) {
                continue;
            }

            // The associated data is not set or is not an array
            if (!isset($normalizedData[$attributeMetadata->getName()]) || !is_array($normalizedData[$attributeMetadata->getName()])) {
                continue;
            }

            $rowIdField = $attributeContext[PositionCollection::ROW_ID_FIELD];
            // Default name no need to adapt
            if ($rowIdField === PositionCollection::DEFAULT_ROW_ID_FIELD) {
                continue;
            }

            // Loop through all position updates, and replace the ID field with homogenized rowId expected by the GridPositionUpdater
            // example ['attributeId' => 5] becomes ['rowId' => 5]
            foreach (array_keys($normalizedData[$attributeMetadata->getName()]) as $positionIndex) {
                $normalizedData[$attributeMetadata->getName()][$positionIndex][PositionCollection::DEFAULT_ROW_ID_FIELD] = $normalizedData[$attributeMetadata->getName()][$positionIndex][$rowIdField];
                unset($normalizedData[$attributeMetadata->getName()][$positionIndex][$rowIdField]);
            }
        }

        return $normalizedData;
    }
}
