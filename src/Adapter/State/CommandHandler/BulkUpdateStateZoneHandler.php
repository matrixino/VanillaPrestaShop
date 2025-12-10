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

namespace PrestaShop\PrestaShop\Adapter\State\CommandHandler;

use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\State\Command\BulkUpdateStateZoneCommand;
use PrestaShop\PrestaShop\Core\Domain\State\CommandHandler\BulkUpdateStateZoneHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\State\Exception\CannotUpdateStateException;
use PrestaShop\PrestaShop\Core\Domain\Zone\Exception\ZoneNotFoundException;
use PrestaShopException;
use State;
use Zone;

/**
 * Handles command which updates zone for multiple states
 */
#[AsCommandHandler]
class BulkUpdateStateZoneHandler implements BulkUpdateStateZoneHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(BulkUpdateStateZoneCommand $command): void
    {
        $zoneId = $command->getNewZoneId();
        $stateIds = $command->getStateIds();

        try {
            $zone = new Zone($zoneId);
            if (!$zone->id) {
                throw new ZoneNotFoundException(sprintf('Zone with id "%d" was not found.', $zoneId));
            }
        } catch (PrestaShopException $e) {
            throw new ZoneNotFoundException(sprintf('Zone with id "%d" was not found.', $zoneId));
        }

        try {
            $state = new State();
            $result = $state->affectZoneToSelection($stateIds, $zoneId);

            if (!$result) {
                throw new CannotUpdateStateException(
                    sprintf('Failed to update zone for states: %s', implode(', ', $stateIds))
                );
            }
        } catch (PrestaShopException $e) {
            throw new CannotUpdateStateException(
                sprintf('An error occurred when updating zone for states: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
}
