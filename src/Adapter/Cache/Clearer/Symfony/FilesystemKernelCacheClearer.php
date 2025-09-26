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

namespace PrestaShop\PrestaShop\Adapter\Cache\Clearer\Symfony;

use AppKernel;
use PrestaShop\PrestaShop\Adapter\Cache\Clearer\SafeLoggerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * This clearer uses a manual removal of files directly on the file system to clear
 * the kernel cache.
 *
 * It is the least favored method to clear because:
 *  - in the past this simple technique created some side effects
 *  - it doesn't initialize the future container like the cache:clear command does
 *
 * Note: we don't add too many try/catch because the SymfonyCacheClearer already wraps this service,
 * it allows keeping the code simpler in this service.
 */
#[AutoconfigureTag('prestashop.kernel.cache_clearer')]
#[AsTaggedItem(priority: -1)]
class FilesystemKernelCacheClearer implements KernelCacheClearerInterface
{
    use SafeLoggerTrait;

    public const MANUAL_REMOVAL_TRIALS = 5;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly Filesystem $filesystem,
    ) {
    }

    public function clearKernelCache(AppKernel $kernel, string $environment): bool
    {
        $cacheDir = $kernel->getCacheDir();
        for ($i = 0; $i < self::MANUAL_REMOVAL_TRIALS; ++$i) {
            try {
                $this->logDebug(sprintf(
                    'FilesystemKernelCacheClearer: Trying manual removal on trial %d/%d of cache folder %s',
                    $i + 1,
                    self::MANUAL_REMOVAL_TRIALS, $cacheDir
                ));
                $this->filesystem->remove($cacheDir);
                // The whole folder was removed
                if (!is_dir($cacheDir)) {
                    break;
                }
            } catch (Throwable $e) {
                $this->logError(sprintf(
                    'FilesystemKernelCacheClearer: Error while trying removing cache folder on trial %d/%d: %s',
                    $e->getMessage(),
                    $i + 1,
                    self::MANUAL_REMOVAL_TRIALS
                ));
            }
        }

        // The folder is still present
        if (is_dir($cacheDir)) {
            $this->logError(sprintf('FilesystemKernelCacheClearer: Folder cache %s still present even after %d manual removals', $cacheDir, self::MANUAL_REMOVAL_TRIALS));

            return false;
        }

        $this->logInfo(sprintf('FilesystemKernelCacheClearer: Cache folder %s successfully cleared manually', $cacheDir));

        return true;
    }
}
