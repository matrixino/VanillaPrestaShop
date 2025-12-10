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

namespace PrestaShop\PrestaShop\Core\Grid\Position;

use PrestaShop\PrestaShop\Core\Grid\Position\Exception\PositionUpdateException;
use PrestaShop\PrestaShop\Core\Grid\Position\UpdateHandler\PositionUpdateHandlerInterface;

/**
 * Class GridPositionUpdater, this class is responsible for updating the position of items
 * of a grid using the information from a PositionUpdateInterface object.
 */
final class GridPositionUpdater implements GridPositionUpdaterInterface
{
    /**
     * @var PositionUpdateHandlerInterface
     */
    private $updateHandler;

    /**
     * @param PositionUpdateHandlerInterface $updateHandler
     */
    public function __construct(PositionUpdateHandlerInterface $updateHandler)
    {
        $this->updateHandler = $updateHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function update(PositionUpdateInterface $positionUpdate)
    {
        $newPositions = $this->getNewPositions($positionUpdate);
        $this->updateHandler->updatePositions($positionUpdate->getPositionDefinition(), $newPositions, $positionUpdate->getParentId());
    }

    /**
     * @param PositionUpdateInterface $positionUpdate
     *
     * @return array
     */
    private function getNewPositions(PositionUpdateInterface $positionUpdate)
    {
        // Positions are returned ascending
        $currentPositions = $this->updateHandler->getCurrentPositions($positionUpdate->getPositionDefinition(), $positionUpdate->getParentId());

        // Build positions data [rowId, currentPosition, newPosition]
        $rowPositions = [];

        // First make sure current position are correctly indexed (based on defined first position) and consecutive
        $firstPosition = $positionUpdate->getPositionDefinition()->getFirstPosition();
        $positionIndex = $firstPosition;
        $allRowIds = array_keys($currentPositions);
        foreach ($allRowIds as $rowId) {
            $rowPositions[$rowId] = [
                'rowId' => $rowId,
                'currentPosition' => $positionIndex,
                'newPosition' => null,
            ];
            ++$positionIndex;
        }

        // Then define new positions (keep track of new position values, doublons are not accepted)
        $newPositionIndexes = [];
        /** @var PositionModificationInterface $rowModification */
        foreach ($positionUpdate->getPositionModificationCollection() as $rowModification) {
            if (!in_array($rowModification->getId(), $allRowIds)) {
                throw new PositionUpdateException('No row identified by %rowId% in the list', 'Admin.Catalog.Notification', ['%rowId%' => $rowModification->getId()]);
            }
            if (in_array($rowModification->getNewPosition(), $newPositionIndexes)) {
                throw new PositionUpdateException('You cannot set the same new position for two elements', 'Admin.Catalog.Notification');
            }

            $newPosition = $rowModification->getNewPosition();
            if ($newPosition < $firstPosition) {
                $newPosition = $firstPosition;
            } elseif ($newPosition > count($allRowIds) - 1 + $firstPosition) {
                $newPosition = count($allRowIds) - 1 + $firstPosition;
            }

            $rowPositions[$rowModification->getId()]['newPosition'] = $newPosition;
            $newPositionIndexes[] = $rowModification->getNewPosition();
        }

        usort($rowPositions, static function (array $rowPositionA, array $rowPositionB): int {
            // Using ?? is important here (?: would ignore a value 0 and wrongly use the fallback, ?? uses fallback
            // only if newPosition is not set)
            $positionA = $rowPositionA['newPosition'] ?? $rowPositionA['currentPosition'];
            $positionB = $rowPositionB['newPosition'] ?? $rowPositionB['currentPosition'];

            // In case both position are equal we prioritize by valuing the expected modification
            if ($positionA === $positionB) {
                // We compute relative positions based on:
                // - the row is being modified, since newPosition is present
                // - is it aiming to be higher or lower (we compute the difference between current and new to reflect this)
                if (null !== $rowPositionA['newPosition']) {
                    $positionA = $positionA + $rowPositionA['newPosition'] - $rowPositionA['currentPosition'];
                }
                if (null !== $rowPositionB['newPosition']) {
                    $positionB = $positionB + $rowPositionB['newPosition'] - $rowPositionB['currentPosition'];
                }
            }

            return $positionA < $positionB ? -1 : 1;
        });

        // Now that rows are order we recompute the new positions array, making sure it is indexed bas on first position,
        // and not missing a position
        $newPositions = [];
        $positionIndex = $firstPosition;
        foreach ($rowPositions as $rowPosition) {
            $newPositions[$rowPosition['rowId']] = $positionIndex;
            ++$positionIndex;
        }

        return $newPositions;
    }
}
