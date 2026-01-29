<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Adapter\Preferences;

use DateTimeImmutable;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Context\EmployeeContext;
use PrestaShop\PrestaShop\Core\Feature\B2BModeFeature;
use PrestaShop\PrestaShop\Core\Feature\B2CModeFeature;
use PrestaShop\PrestaShop\Core\Http\CookieOptions;
use PrestaShopBundle\Form\Admin\Configure\ShopParameters\General\PreferencesType;
use PrestaShopLogger;

/**
 * This class will provide Shop Preferences configuration.
 */
class PreferencesConfiguration implements DataConfigurationInterface
{
    /**
     * @var Configuration
     */
    //    private $configuration;
    //
    //    /**
    //     * @var EmployeeContext
    //     */
    //    private $employeeContext;

    public function __construct(
        Configuration $configuration,
        EmployeeContext $employeeContext
    ) {
        $this->configuration = $configuration;
        $this->employeeContext = $employeeContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return [
            'enable_ssl' => $this->configuration->getBoolean('PS_SSL_ENABLED'),
            'enable_token' => $this->configuration->getBoolean('PS_TOKEN_ENABLE'),
            PreferencesType::ENABLE_B2C_MODE => $this->configuration->getBoolean(B2CModeFeature::CONFIGURATION_NAME),
            PreferencesType::ENABLE_B2B_MODE => $this->configuration->getBoolean(B2BModeFeature::CONFIGURATION_NAME),
            'allow_html_iframes' => $this->configuration->getBoolean('PS_ALLOW_HTML_IFRAME'),
            'use_htmlpurifier' => $this->configuration->getBoolean('PS_USE_HTMLPURIFIER'),
            'price_round_mode' => $this->configuration->get('PS_PRICE_ROUND_MODE'),
            'price_round_type' => $this->configuration->get('PS_ROUND_TYPE'),
            'display_suppliers' => $this->configuration->getBoolean('PS_DISPLAY_SUPPLIERS'),
            'display_manufacturers' => $this->configuration->getBoolean('PS_DISPLAY_MANUFACTURERS'),
            'display_best_sellers' => $this->configuration->getBoolean('PS_DISPLAY_BEST_SELLERS'),
            'multishop_feature_active' => $this->configuration->getBoolean('PS_MULTISHOP_FEATURE_ACTIVE'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration)
    {
        if (false === $this->validateConfiguration($configuration)) {
            return [
                [
                    'key' => 'Invalid configuration',
                    'domain' => 'Admin.Notifications.Warning',
                    'parameters' => [],
                ],
            ];
        }

        if ($this->validateSameSiteConfiguration($configuration)) {
            return [
                [
                    'key' => 'Cannot disable SSL configuration due to the Cookie SameSite=None.',
                    'domain' => 'Admin.Advparameters.Notification',
                    'parameters' => [],
                ],
            ];
        }

        $newB2c = (bool) $configuration[PreferencesType::ENABLE_B2C_MODE];
        $newB2b = (bool) $configuration[PreferencesType::ENABLE_B2B_MODE];

        if (!$newB2c && !$newB2b) {
            return [[
                'key' => 'At least one mode must be enabled (B2C or B2B).',
                'domain' => 'Admin.Notifications.Warning',
                'parameters' => [],
            ]];
        }

        $oldB2c = $this->configuration->getBoolean(PreferencesType::ENABLE_B2C_MODE);
        $oldB2b = $this->configuration->getBoolean(PreferencesType::ENABLE_B2B_MODE);

        $b2cChanged = ($oldB2c !== $newB2c);
        $b2bChanged = ($oldB2b !== $newB2b);

        if ($b2cChanged || $b2bChanged) {
            $employee = $this->employeeContext->getEmployee();
            $employeeId = $employee ? (int) $employee->getId() : 0;

            $payload = [
                'employee_id' => $employeeId,
                'datetime' => (new DateTimeImmutable())->format(DATE_ATOM),
                'changes' => [],
            ];

            if ($b2cChanged) {
                $payload['changes'][PreferencesType::ENABLE_B2C_MODE] = ['old' => $oldB2c ? 1 : 0, 'new' => $newB2c ? 1 : 0];
            }
            if ($b2bChanged) {
                $payload['changes'][PreferencesType::ENABLE_B2B_MODE] = ['old' => $oldB2b ? 1 : 0, 'new' => $newB2b ? 1 : 0];
            }

            PrestaShopLogger::addLog(
                'B2C/B2B modes updated: ' . json_encode($payload, JSON_UNESCAPED_UNICODE),
                1,
                null,
                'Configuration',
                0,
                true
            );
        }

        $this->configuration->set('PS_SSL_ENABLED', $configuration['enable_ssl']);
        $this->configuration->set('PS_TOKEN_ENABLE', $configuration['enable_token']);
        $this->configuration->set(B2CModeFeature::CONFIGURATION_NAME, $configuration[PreferencesType::ENABLE_B2C_MODE]);
        $this->configuration->set(B2BModeFeature::CONFIGURATION_NAME, $configuration[PreferencesType::ENABLE_B2B_MODE]);
        $this->configuration->set('PS_ALLOW_HTML_IFRAME', $configuration['allow_html_iframes']);
        $this->configuration->set('PS_USE_HTMLPURIFIER', $configuration['use_htmlpurifier']);
        $this->configuration->set('PS_PRICE_ROUND_MODE', $configuration['price_round_mode']);
        $this->configuration->set('PS_ROUND_TYPE', $configuration['price_round_type']);
        $this->configuration->set('PS_DISPLAY_SUPPLIERS', $configuration['display_suppliers']);
        $this->configuration->set('PS_DISPLAY_MANUFACTURERS', $configuration['display_manufacturers']);
        $this->configuration->set('PS_DISPLAY_BEST_SELLERS', $configuration['display_best_sellers']);
        $this->configuration->set('PS_MULTISHOP_FEATURE_ACTIVE', $configuration['multishop_feature_active']);

        return [];
    }

    /**
     * Validate the SSL configuration can be disabled if the SameSite Cookie
     * is not settled to None
     *
     * @param array $configuration
     *
     * @return bool
     */
    protected function validateSameSiteConfiguration(array $configuration): bool
    {
        return $configuration['enable_ssl'] === false && $this->configuration->get('PS_COOKIE_SAMESITE') === CookieOptions::SAMESITE_NONE;
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration)
    {
        return isset(
            $configuration['enable_ssl'],
            $configuration['enable_token'],
            $configuration[PreferencesType::ENABLE_B2C_MODE],
            $configuration[PreferencesType::ENABLE_B2B_MODE],
            $configuration['allow_html_iframes'],
            $configuration['use_htmlpurifier'],
            $configuration['price_round_mode'],
            $configuration['price_round_type'],
            $configuration['display_suppliers'],
            $configuration['display_manufacturers'],
            $configuration['display_best_sellers'],
            $configuration['multishop_feature_active']
        );
    }
}
