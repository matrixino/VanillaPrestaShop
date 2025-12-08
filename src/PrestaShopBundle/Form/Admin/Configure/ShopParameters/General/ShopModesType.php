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
 * @copyright 2025 SOLEDIS
 * @license   All Rights Reserved
 * @developer HERVOUET Clément
 */

namespace PrestaShopBundle\Form\Admin\Configure\ShopParameters\General;

use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagSettings;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagStateCheckerInterface;
use PrestaShop\PrestaShop\Core\Security\Permission;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShopModesType extends TranslatorAwareType
{
    public const ENABLE_IMPROVED_B2B_FEATURE = 'enable_improved_b2b_feature';
    public const ENABLE_B2C_FEATURE = 'enable_b2c_feature';

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly RequestStack $requestStack,
    )
    {
        parent::__construct($translator, $locales);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentLegacyController = $this->requestStack->getCurrentRequest()->attributes->get('_legacy_controller');
        $currentEmployeeHasNecessaryRights = $this->authorizationChecker->isGranted(
            Permission::UPDATE,
            $currentLegacyController
        );

        $builder
            ->add(self::ENABLE_B2C_FEATURE, SwitchType::class, [
                'disabled' => !$currentEmployeeHasNecessaryRights,
                'label' => $this->trans('Enable b2c mode (by default)', 'Admin.Shopparameters.Feature'),
                'help' => $this->trans(
                    'The B2c model allows a clientto order for themselves',
                    'Admin.Shopparameters.Help'
                ),
            ])
            ->add(self::ENABLE_IMPROVED_B2B_FEATURE, SwitchType::class, [
                'disabled' => !$currentEmployeeHasNecessaryRights,
                'label' => $this->trans('Enable improved b2b mode', 'Admin.Shopparameters.Feature'),
                'help' => $this->trans(
                    'The B2B model allows a client to order for different business entity.',
                    'Admin.Shopparameters.Help'
                ),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'Admin.Shopparameters.Feature',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'shopModes';
    }
}
