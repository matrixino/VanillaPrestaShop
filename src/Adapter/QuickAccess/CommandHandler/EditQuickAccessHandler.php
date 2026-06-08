<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\QuickAccess\CommandHandler;

use PrestaShop\PrestaShop\Adapter\QuickAccess\Repository\QuickAccessRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\QuickAccess\Command\EditQuickAccessCommand;
use PrestaShop\PrestaShop\Core\Domain\QuickAccess\CommandHandler\EditQuickAccessHandlerInterface;

#[AsCommandHandler]
class EditQuickAccessHandler implements EditQuickAccessHandlerInterface
{
    public function __construct(private readonly QuickAccessRepository $repository)
    {
    }

    public function handle(EditQuickAccessCommand $command): void
    {
        $quickAccess = $this->repository->get($command->getQuickAccessId());

        if (null !== $command->getLocalizedNames()) {
            // @phpstan-ignore-next-line (ObjectModel multilingual field accepts array at runtime)
            $quickAccess->name = $command->getLocalizedNames();
        }

        if (null !== $command->getLink()) {
            $quickAccess->link = $command->getLink();
        }

        if (null !== $command->getNewWindow()) {
            $quickAccess->new_window = $command->getNewWindow();
        }

        $this->repository->update($quickAccess);
    }
}
