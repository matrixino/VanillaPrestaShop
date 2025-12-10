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

namespace PrestaShop\PrestaShop\Core\Domain\State\Command;

use PrestaShop\PrestaShop\Core\Domain\State\Exception\StateException;

/**
 * Updates zone for given states.
 */
class BulkUpdateStateZoneCommand
{
    /**
     * @var int[]
     */
    private $stateIds;

    /**
     * @var int
     */
    private $newZoneId;

    /**
     * @param int[] $stateIds
     * @param int $newZoneId
     */
    public function __construct(array $stateIds, int $newZoneId)
    {
        if ($newZoneId <= 0) {
            throw new StateException(sprintf('Zone Id must be integer greater than 0, but %s given.', var_export($newZoneId, true)));
        }

        $this->newZoneId = $newZoneId;
        $this->setStateIds($stateIds);
    }

    /**
     * @return int[]
     */
    public function getStateIds(): array
    {
        return $this->stateIds;
    }

    /**
     * @return int
     */
    public function getNewZoneId(): int
    {
        return $this->newZoneId;
    }

    /**
     * @param int[] $stateIds
     */
    private function setStateIds(array $stateIds): void
    {
        if (empty($stateIds)) {
            throw new StateException('You must select at least one state.');
        }

        foreach ($stateIds as $stateId) {
            if (!is_int($stateId) || $stateId <= 0) {
                throw new StateException(sprintf('Invalid state ID: %s', var_export($stateId, true)));
            }
        }

        $this->stateIds = $stateIds;
    }
}
