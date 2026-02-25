<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShopBundle\EventListener\Admin;

use Doctrine\ORM\Event\PostUpdateEventArgs;
use PrestaShop\PrestaShop\Core\FeatureFlag\B2b\ImprovedB2bTabsToggler;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagSettings;
use PrestaShopBundle\Entity\FeatureFlag;

final class ImprovedB2bFeatureFlagListener
{
    public function __construct(
        private readonly ImprovedB2bTabsToggler $toggler,
    ) {
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof FeatureFlag) {
            return;
        }

        if ($entity->getName() !== FeatureFlagSettings::FEATURE_FLAG_IMPROVED_B2B) {
            return;
        }

        $this->toggler->sync();
    }
}
