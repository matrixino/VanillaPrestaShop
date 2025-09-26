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

declare(strict_types=1);

namespace Tests\Unit\PrestaShopBundle\Command;

use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\Command\RegenerateThumbnailsCommand as DomainRegenerateThumbnailsCommand;
use PrestaShop\PrestaShop\Core\Domain\ImageSettings\ImageDomain;
use PrestaShopBundle\Command\RegenerateThumbnailsCommand;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RegenerateThumbnailsCommandTest extends TestCase
{
    private CommandBusInterface&MockObject $commandBus;
    private RegenerateThumbnailsCommand $command;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->command = new RegenerateThumbnailsCommand($this->commandBus);
        $this->tester = new CommandTester($this->command);
    }

    public function testItDispatchesDomainCommand(): void
    {
        $imageType = 5;
        $this->commandBus->expects($this->once())
            ->method('handle')
            ->with(
                $this->callback(function (DomainRegenerateThumbnailsCommand $command) use ($imageType) {
                    return $command->getImage() === ImageDomain::PRODUCTS->value
                        && $command->getImageTypeId() === $imageType
                        && $command->erasePreviousImages() === true;
                }));

        $exitCode = $this->tester->execute([
            'image' => 'products',
            'image-type' => $imageType,
            '--delete' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function testItDispatchesDomainCommandWithShortcutErase(): void
    {
        $imageType = 5;
        $this->commandBus->expects($this->once())
            ->method('handle')
            ->with(
                $this->callback(function (DomainRegenerateThumbnailsCommand $command) use ($imageType) {
                    return $command->getImage() === ImageDomain::PRODUCTS->value
                        && $command->getImageTypeId() === $imageType
                        && $command->erasePreviousImages() === true;
                }));

        $exitCode = $this->tester->execute([
            'image' => 'products',
            'image-type' => $imageType,
            '-d' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @dataProvider validImageDomainsProvider
     */
    public function testAllValidImageDomains(ImageDomain $imageCategory): void
    {
        $this->commandBus->expects($this->once())->method('handle');

        $exitCode = $this->tester->execute([
            'image' => $imageCategory->value,
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    public function testInvalidImageDomain(): void
    {
        $this->commandBus->expects($this->never())->method('handle');

        $exitCode = $this->tester->execute([
            'image' => 'invalid',
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
    }

    /**
     * @dataProvider invalidImageDomainsProvider
     */
    public function testInvalidImageTypes($imageType): void
    {
        $this->commandBus->expects($this->never())->method('handle');

        $exitCode = $this->tester->execute([
            'image' => 'products',
            'image-type' => $imageType,
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
    }

    public function testWithoutEraseOption(): void
    {
        $this->commandBus->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (DomainRegenerateThumbnailsCommand $command) {
                return $command->erasePreviousImages() === false;
            }));

        $exitCode = $this->tester->execute([
            'image' => 'products',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @dataProvider invalidImageCasingProvider
     */
    public function testInvalidCasingOrWhitespaceForImage(string $image): void
    {
        $this->commandBus->expects($this->never())->method('handle');

        $exitCode = $this->tester->execute(['image' => $image]);

        $this->assertSame(Command::FAILURE, $exitCode);
    }

    public function testBusFailureIsReported(): void
    {
        $this->commandBus->method('handle')->willThrowException(new RuntimeException('boom'));

        $exitCode = $this->tester->execute(['image' => 'products']);
        $this->assertSame(Command::FAILURE, $exitCode);
    }

    public static function numericStringImageTypesProvider(): array
    {
        return [
            ['5', 5],
            ['05', 5],
            ['000', 0],
        ];
    }

    public static function validImageDomainsProvider(): Generator
    {
        yield from array_map(static fn (ImageDomain $imageCategory) => [$imageCategory->name => $imageCategory], ImageDomain::cases());
    }

    public static function invalidImageDomainsProvider(): array
    {
        return [
            'non-numeric string' => ['foo'],
            'negative integer' => [-1],
            'empty string' => [''],
        ];
    }

    public static function invalidImageCasingProvider(): array
    {
        return [
            ['Products'],
            [' products '],
        ];
    }
}
