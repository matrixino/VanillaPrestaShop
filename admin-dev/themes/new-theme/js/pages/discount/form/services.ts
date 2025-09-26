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
import Router from '@components/router';
import {Item, ItemGroup} from '@PSVue/components/grouped-item-collection/types';

const router = new Router();
const {$} = window;

interface AttributeGroup {
  id: number;
  name: string;
  attributes: Attribute[];
}

interface Attribute {
  id: number;
  name: string;
  color: string | null;
  texture: string | null;
}

interface FeatureGroup {
  id: number;
  name: string;
  feature_values: FeatureValue[];
}

interface FeatureValue {
  id: number;
  name: string;
}

/**
 * Fetch all attribute groups and convert them into ItemGroup/Item expected by the
 * GroupedItemCollection component.
 */
export const getAllAttributeGroups = async (): Promise<Array<ItemGroup>> => {
  const attributeGroups: AttributeGroup[] = await $.get(router.generate('admin_all_attribute_groups'));

  const itemGroups: ItemGroup[] = [];

  // Transform attribute groups into ItemGroup
  attributeGroups.forEach((attributeGroup: AttributeGroup) => {
    const itemGroup: ItemGroup = {
      id: attributeGroup.id,
      name: attributeGroup.name,
      items: [],
    };
    attributeGroup.attributes.forEach((attribute: Attribute) => {
      const item: Item = {
        id: attribute.id,
        name: attribute.name,
        selected: false,
        groupId: itemGroup.id,
        groupName: itemGroup.name,
        color: attribute.color,
        texture: attribute.texture,
      };
      itemGroup.items.push(item);
    });
    itemGroups.push(itemGroup);
  });

  return itemGroups;
};

/**
 * Fetch all feature groups and convert them into ItemGroup/Item expected by the
 * GroupedItemCollection component.
 */
export const getAllFeatureGroups = async (): Promise<Array<ItemGroup>> => {
  const featureGroups: FeatureGroup[] = await $.get(router.generate('admin_all_feature_groups'));

  const itemGroups: ItemGroup[] = [];

  // Transform feature groups into ItemGroup
  featureGroups.forEach((featureGroup: FeatureGroup) => {
    const itemGroup: ItemGroup = {
      id: featureGroup.id,
      name: featureGroup.name,
      items: [],
    };
    featureGroup.feature_values.forEach((featureValue: FeatureValue) => {
      const item: Item = {
        id: featureValue.id,
        name: featureValue.name,
        selected: false,
        groupId: itemGroup.id,
        groupName: itemGroup.name,
        color: null,
        texture: null,
      };
      itemGroup.items.push(item);
    });
    itemGroups.push(itemGroup);
  });

  return itemGroups;
};

export default {
  getAllAttributeGroups,
  getAllFeatureGroups,
};
