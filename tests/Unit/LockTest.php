<?php

declare(strict_types=1);

namespace Ghostwriter\ComposerLock\Tests\Unit;

use Composer\Console\Application;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Generator;
use Ghostwriter\ComposerLock\Lock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

use function class_exists;
use function sprintf;
use function substr;
use function trim;

/**
 * @coversDefaultClass Lock
 */
final class LockTest extends TestCase
{
    private CommandTester $commandTester;
    private Lock $lock;
    protected function setUp(): void
    {
        $this->lock = new Lock();

        $this->commandTester = new CommandTester(
            (static function (Application $application, Lock $lock): Command {
                $application->setAutoExit(false);
                $application->add($lock->getCommands()[0]);

                return $application->find(Lock::PACKAGE_COMMAND);
            })(
                new Application(),
                $this->lock
            )
        );
    }

    public function testPluginExists(): void
    {
        self::assertTrue(class_exists(Lock::class));
    }

    public function testImplementsPluginInterface(): void
    {
        self::assertInstanceOf(PluginInterface::class, $this->lock);
    }

    public function testImplementsCommandProviderInterface(): void
    {
        self::assertInstanceOf(CommandProvider::class, $this->lock);
    }

    public function testImplementsCapableInterface(): void
    {
        self::assertInstanceOf(Capable::class, $this->lock);
    }

    /**
     * @dataProvider supportedCommandsProvider
     * @param array<"--dry-run"|"--php"|"-d"|"-p"|"command", bool|string> $input
     * @param array<"capture_stderr_separately"|"verbosity", bool> $options
     */
    public function testCommandOptionsProvider(
        array $input,
        array $options,
        string $expectedOutput = '',
        string $expectedErrorOutput = '',
        int $expectedStatusCode = 0
    ): void {
        $this->commandTester->execute($input, $options);

        self::assertSame($this->commandTester->getDisplay(true), $expectedOutput);
        self::assertSame($this->commandTester->getErrorOutput(true), $expectedErrorOutput);
        self::assertSame($this->commandTester->getStatusCode(), $expectedStatusCode);
    }

    /**
     * @doesNotPerformAssertions
     * phpcs:disable
     * @return Generator<string, array{0: array<"--dry-run"|"--php"|"-d"|"-p"|"command", bool|string>, 1: array{capture_stderr_separately: true, verbosity: true}, 2: '', 3: '', 4: 0}, mixed, void>
     * phpcs:enable
     */
    public function supportedCommandsProvider(): Generator
    {
        foreach ($this->phpCommandOptions() as $phpCommandOption) {
            foreach ($this->supportedPhpVersions() as $phpVersion) {
                $phpVersionKey = substr($phpVersion, 0, 3);
                foreach ($this->packageFixtures() as $version => $packageFixture) {
                    if ($version !== $phpVersionKey) {
                        continue;
                    }
                    foreach ($this->dryRunCommandOptions() as $dryRun) {
                        yield  trim(sprintf(
                            'composer lock %s %s -d %s %s',
                            $phpCommandOption,
                            $phpVersion,
                            $packageFixture,
                            $dryRun ? '--dry-run' : ''
                        )) => [
                            [
                                'command'         => 'lock',
                                $phpCommandOption => $phpVersion,
                                '-d'              => $packageFixture,
                                '--dry-run'       => $dryRun,
                            ],
                            ['capture_stderr_separately' => true, 'verbosity' => true],
                            '',
                            '',
                            0,
                        ];
                    }
                }
            }
        }
    }

    /**
     * @return Generator<int, bool, mixed, void>
     */
    private function dryRunCommandOptions(): Generator
    {
        yield from [true, false];
    }

    /**
     * @return Generator<int, '--php'|'-p', mixed, void>
     */
    private function phpCommandOptions(): Generator
    {
        yield from ['--php', '-p'];
    }

    /**
     * @return Generator<int, string, mixed, void>
     */
    private function supportedPhpVersions(): Generator
    {
        yield from ['7.4', '8.0', '8.1', '7.4.999', '8.0.999', '8.1.999'];
    }

    /**
     * @return Generator<'7.4'|'8.0'|'8.1', string, mixed, void>
     */
    private function packageFixtures(): Generator
    {
        yield from [
            '7.4' => __DIR__ . '/../fixtures/package-74',
            '8.0' => __DIR__ . '/../fixtures/package-80',
            '8.1' => __DIR__ . '/../fixtures/package-81',
        ];
    }
}
