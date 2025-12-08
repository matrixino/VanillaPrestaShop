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

use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\LegacyLogger;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Feature\B2cFeature;
use PrestaShop\PrestaShop\Core\Feature\ImprovedB2bFeature;
use PrestaShopLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShopModesConfiguration implements DataConfigurationInterface
{
    public function __construct(
        private readonly Configuration $configuration,
        #[Autowire('@prestashop.adapter.legacy.logger')]
        private readonly LegacyLogger $logger,
        private readonly TranslatorInterface $translator,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        return [
            ShopModesType::ENABLE_IMPROVED_B2B_FEATURE => $this->configuration->get(ImprovedB2bFeature::CONFIGURATION_NAME),
            ShopModesType::ENABLE_B2C_FEATURE => $this->configuration->get(B2cFeature::CONFIGURATION_NAME),
        ];
    }

    /**
     * @inheritDoc
     */
    public function updateConfiguration(array $configuration): array
    {
        if (!$this->validateAtLeastOneShopModeActivated($configuration)) {
            return [
                [
                    'key' => 'At least one shop mode (B2C or advanced B2B) must be activated.',
                    'domain' => 'Admin.Advparameters.Notification',
                    'parameters' => [],
                ],
            ];
        }

        $oldConfiguration = $this->getConfiguration();

        if ($oldConfiguration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE] !== $configuration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE]) {
            $this->configuration->set(
                ImprovedB2bFeature::CONFIGURATION_NAME,
                $configuration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE]
            );
            $this->logger->info(
                $this->translator->trans(
                    'Improved B2B shop mode configuration updated. Old value: %old_value%, new value: %new_value%',
                    [
                        '%old_value%' => (bool) $oldConfiguration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE],
                        '%new_value%' => (bool) $configuration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE],
                    ],
                    'Admin.Advparameters.Notification'
                ),
            );
        }

        if ($oldConfiguration[ShopModesType::ENABLE_B2C_FEATURE] !== $configuration[ShopModesType::ENABLE_B2C_FEATURE]) {
            $this->configuration->set(
                B2cFeature::CONFIGURATION_NAME,
                $configuration[ShopModesType::ENABLE_B2C_FEATURE]
            );
            $this->logger->info(
                $this->translator->trans(
                    'B2C shop mode configuration updated. Old value: %old_value%, new value: %new_value%',
                    [
                        '%old_value%' => (bool) $oldConfiguration[ShopModesType::ENABLE_B2C_FEATURE],
                        '%new_value%' => (bool) $configuration[ShopModesType::ENABLE_B2C_FEATURE],
                    ],
                    'Admin.Advparameters.Notification'
                ),
            );
        }

        return [];
    }

    protected function validateAtLeastOneShopModeActivated(array $configuration): bool
    {
        return $configuration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE] || $configuration[ShopModesType::ENABLE_B2C_FEATURE];
    }


    public function validateConfiguration(array $configuration): bool
    {
        return isset(
            $configuration[ShopModesType::ENABLE_IMPROVED_B2B_FEATURE],
            $configuration[ShopModesType::ENABLE_B2C_FEATURE],
        );
    }
}
