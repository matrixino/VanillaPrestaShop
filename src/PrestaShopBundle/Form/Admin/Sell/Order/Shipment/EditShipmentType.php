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

namespace PrestaShopBundle\Form\Admin\Sell\Order\Shipment;

use PrestaShop\PrestaShop\Adapter\Form\ChoiceProvider\AvailableCarriersForShipmentChoiceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditShipmentType extends AbstractType
{
    public function __construct(
        private readonly AvailableCarriersForShipmentChoiceProvider $availableCarriersForShipmentChoiceProvider,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('carrier', ChoiceType::class, [
                'choices' => $this->availableCarriersForShipmentChoiceProvider->getChoices([
                    'selectedProducts' => $options['data']['selectedProducts'],
                    'shipment_id' => $options['data']['shipment_id'],
                ]),
                'placeholder' => $this->translator->trans('Select a carrier', [], 'Admin.Orderscustomers.Feature'),
                'required' => true,
            ])
            ->add('current_order_carrier_id', HiddenType::class)
            ->add('tracking_number', TextType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'order_id',
            'shipment_id',
        ])
            ->setAllowedTypes('order_id', 'int')
            ->setAllowedTypes('shipment_id', 'int');
    }
}
