<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\FeatureFlag\B2b;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Feature\ShopModeFeature;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagSettings;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagStateCheckerInterface;
use Symfony\Contracts\Service\ResetInterface;

final class ImprovedB2bTabsToggler
{
    private const TAB_CLASS_NAMES = [
        'AdminBusinessEntity',
        'AdminBusinessEntities',
        'AdminCustomersB2B',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly FeatureFlagStateCheckerInterface $featureFlagChecker,
        private readonly ShopModeFeature $shopModeFeature,
        private readonly string $dbPrefix,
    ) {
    }

    public function sync(): void
    {
        if ($this->featureFlagChecker instanceof ResetInterface) {
            $this->featureFlagChecker->reset();
        }

        $shouldEnable =
            $this->featureFlagChecker->isEnabled(FeatureFlagSettings::FEATURE_FLAG_IMPROVED_B2B)
            && $this->shopModeFeature->isB2BShopModeEnable();

        $this->setTabsActive($shouldEnable);
    }

    private function setTabsActive(bool $active): void
    {
        $sql = sprintf(
            'UPDATE %stab SET active = :active WHERE class_name IN (:tabs)',
            $this->dbPrefix
        );

        $this->connection->executeStatement(
            $sql,
            [
                'active' => $active ? 1 : 0,
                'tabs' => self::TAB_CLASS_NAMES,
            ],
            [
                'tabs' => ArrayParameterType::STRING,
            ]
        );
    }
}
