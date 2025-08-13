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

namespace Core\Module\Parser;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Module\Parser\ModuleParser;
use PrestaShop\PrestaShop\Core\Version;

class ModuleParserTest extends TestCase
{
    /**
     * @dataProvider getModules
     */
    public function testParseModule(string $moduleClassPath, array $expectedInfos): void
    {
        $parser = new ModuleParser();
        $moduleInfos = $parser->parseModule($moduleClassPath);
        $this->assertEquals($expectedInfos, $moduleInfos);
    }

    public static function getModules(): iterable
    {
        $parsedModulesFolder = __DIR__ . '/../../../Resources/parsed-modules/';

        yield 'all hard-coded in constructor' => [
            $parsedModulesFolder . 'all-hard-coded.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => '8.2.0',
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'core version from _PS_VERSION_ defined const' => [
            $parsedModulesFolder . 'defined-const.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => _PS_VERSION_,
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'core version from FQCN \PrestaShop\PrestaShop\Core\Version::VERSION' => [
            $parsedModulesFolder . 'fqcn-const.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => Version::VERSION,
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'core version from FQCN PrestaShop\PrestaShop\Core\Version::VERSION, no initial backslash' => [
            $parsedModulesFolder . 'cs-fixed-fqcn-const.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => Version::VERSION,
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'core version from Version object with use statement' => [
            $parsedModulesFolder . 'use-statement-const.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => Version::VERSION,
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'module values from module const accessed via self' => [
            $parsedModulesFolder . 'module-self-const.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.1.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => '8.2.0',
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'module values from module const accessed via static' => [
            $parsedModulesFolder . 'module-static-const.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.1.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => '8.2.0',
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'translated values' => [
            $parsedModulesFolder . 'translated-values.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => '8.2.0',
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];

        yield 'module with const arrays' => [
            $parsedModulesFolder . 'module-const-array.php',
            [
                'name' => 'bankwire',
                'tab' => 'payments_gateways',
                'version' => '2.0.0',
                'ps_versions_compliancy' => [
                    'min' => '1.7',
                    'max' => '8.2.0',
                ],
                'author' => 'PrestaShop',
                'displayName' => 'Bank wire',
                'description' => 'Accept payments for your products via bank wire transfer.',
                'hooks' => [
                    'paymentReturn',
                    'paymentOptions',
                    'displayHome',
                ],
            ],
        ];
    }
}
