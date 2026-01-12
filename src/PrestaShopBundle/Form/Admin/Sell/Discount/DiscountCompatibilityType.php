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

namespace PrestaShopBundle\Form\Admin\Sell\Discount;

use PrestaShop\PrestaShop\Core\Domain\Discount\ValueObject\DiscountType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountCompatibilityType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableTypes = $options['available_types'] ?? [];

        foreach ($availableTypes as $type) {
            // Skip "On total order" discount type
            // (Disabled temporarily, because of infinite loop issue with this kind of discount. See issue #39419)
            if ($type['discount_type'] === DiscountType::ORDER_LEVEL) {
                continue;
            }

            $builder->add('compatible_type_' . $type['id_cart_rule_type'], CheckboxType::class, [
                'label' => $type['name'],
                'label_help_box' => $type['description'],
                'required' => false,
                'attr' => [
                    'data-type-id' => $type['id_cart_rule_type'],
                    'data-type-name' => $type['name'],
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => $this->trans('Compatible with discounts', 'Admin.Catalog.Feature'),
            'label_help_box' => $this->trans('Select which discount types this discount is compatible with.', 'Admin.Catalog.Help'),
            'available_types' => [],
            'required' => false,
        ]);
        $resolver->setAllowedTypes('available_types', ['array']);
    }
}
