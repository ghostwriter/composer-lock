<?php

declare(strict_types=1);

namespace Ghostwriter\ComposerLock;

use Composer\Command\BaseCommand;
use Composer\Composer;
use Composer\InstalledVersions;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function implode;
use function sprintf;
use function substr;

use const PHP_EOL;

final class Lock implements PluginInterface, Capable, CommandProvider
{
    /**
     * @var string
     */
    public const PACKAGE_NAME = 'ghostwriter/composer-lock';

    /**
     * @var string
     */
    public const PACKAGE_AUTHOR = 'Nathanael Esayeas';

    /**
     * @var string
     */
    public const PACKAGE_COMMAND = 'lock';

    /**
     * @var string
     */
    public const PACKAGE_ATTRIBUTION = '<info>%s</info> (<comment>%s@%s</comment>) by <info>%s</info> and <comment>contributors</comment>.'; //phpcs:ignore

    /**
     * @var string
     */
    public const COMMAND_SUCCESS = '[<comment>%s</comment>]%s';

    /**
     * @var string
     */
    public const COMMAND_FAILURE = '[<comment>%s</comment>]<error>%s</error>';

    /**
     * @var int
     */
    public const SHORT_SHA_LENGTH = 7;

    private BaseCommand $lockCommand;

    public function __construct()
    {
        $this->lockCommand = new class extends BaseCommand
        {
            public function __construct()
            {
                parent::__construct(Lock::PACKAGE_COMMAND);
            }

            protected function configure(): void
            {
                $this->setName(Lock::PACKAGE_COMMAND)
                    ->setDescription('Manage composer.lock file for any PHP version.')
                    ->setHelp(
                        implode(PHP_EOL, [
                            'Use "--php|-p" to set the minimum supported PHP version.',
                            'e.g. "7.2", "7.3", "7.4", "8.0", "8.1", "8.2"',
                        ])
                    )
                    ->setDefinition([
                        new InputOption(
                            'php',
                            'p',
                            InputOption::VALUE_REQUIRED,
                            'Set the minimum supported PHP version.',
                            null
                        ),
                        new InputOption(
                            'dry-run',
                            null,
                            InputOption::VALUE_NONE,
                            'Display outputs without execute anything (implicitly enables --verbose).',
                        ),
                    ]);
            }

            public function error(string $message): void
            {
                $this->getIO()->writeError(sprintf(
                    Lock::COMMAND_FAILURE,
                    Lock::PACKAGE_NAME,
                    $message
                ));
            }

            public function success(string $message): void
            {
                $this->getIO()->write(sprintf(
                    Lock::COMMAND_SUCCESS,
                    Lock::PACKAGE_NAME,
                    $message
                ));
            }

            public function write(string $message): void
            {
                $this->getIO()->write($message);
            }

            /** @psalm-return 0|1 */
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $packageVersion   = (string) InstalledVersions::getPrettyVersion(Lock::PACKAGE_NAME);
                $packageReference = substr(
                    (string) InstalledVersions::getReference(Lock::PACKAGE_NAME),
                    0,
                    Lock::SHORT_SHA_LENGTH
                );

                $this->write(sprintf(
                    'Running ' . Lock::PACKAGE_ATTRIBUTION,
                    Lock::PACKAGE_NAME,
                    $packageVersion,
                    $packageReference,
                    Lock::PACKAGE_AUTHOR
                ));

                /** @var string|null $phpVersion */
                $phpVersion = $input->getOption('php');

                /** @var bool $dryRun */
                $dryRun = $input->getOption('dry-run');

                try {
                    $this->configCommand(
                        'Updating "config.platform.php" in composer.json',
                        'Failed to update "config.platform.php" in composer.json file',
                        new ArrayInput([
                            'setting-key'   => 'platform.php',
                            'setting-value' => [$phpVersion],
                        ]),
                        $output,
                        $dryRun,
                        $phpVersion
                    );

                    $this->updateCommand(
                        'Updating composer dependencies.',
                        'Failed to update composer dependencies',
                        new ArrayInput([
                            '--no-autoloader' => true,
                            '--no-cache'      => true,
                            '--no-scripts'    => true,
                            '--no-plugins'    => true,
                            '--no-progress'   => true,
                            '--quiet'         => true,
                        ]),
                        $output,
                        $dryRun
                    );

                    $this->configCommand(
                        'Removing "config.platform.php" in composer.json',
                        'Failed to remove "config.platform.php" in composer.json',
                        new ArrayInput([
                            '--unset'     => true,
                            'setting-key' => 'platform.php',
                        ]),
                        $output,
                        $dryRun,
                        $phpVersion
                    );

                    $this->updateCommand(
                        'Updating composer.lock',
                        'Failed to update composer.lock',
                        new ArrayInput([
                            '--no-cache'    => true,
                            '--no-scripts'  => true,
                            '--no-plugins'  => true,
                            '--no-progress' => true,
                            '--quiet'       => true,
                            '--no-install'  => true,
                            '--lock'        => true,
                        ]),
                        $output,
                        $dryRun
                    );
                } catch (Throwable $throwable) {
                    return 1;
                }

                $this->success('Successfully updated composer.lock');

                return 0;
            }

            private function configCommand(
                string $success,
                string $error,
                InputInterface $input,
                OutputInterface $output,
                bool $dryRun = false,
                ?string $phpVersion = null
            ): void {
                if ($phpVersion === null) {
                    return;
                }

                $this->success($success);

                if ($dryRun) {
                    return;
                }

                try {
                    $this->getApplication()->resetComposer();
                    $this->getApplication()->find('config')->run($input, $output);
                } catch (Throwable $throwable) {
                    $this->error($error . ', ' . $throwable->getMessage());

                    throw $throwable;
                }
            }

            private function updateCommand(
                string $success,
                string $error,
                InputInterface $input,
                OutputInterface $output,
                bool $dryRun = false
            ): void {
                try {
                    $this->success($success);

                    if ($dryRun) {
                        return;
                    }

                    $this->getApplication()->resetComposer();
                    $this->getApplication()->find('update')->run($input, $output);
                } catch (Throwable $throwable) {
                    $this->error($error . ', ' . $throwable->getMessage());

                    throw $throwable;
                }
            }
        };
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /** @return array<class-string<CommandProvider>, class-string<self>> */
    public function getCapabilities(): array
    {
        return [CommandProvider::class => self::class];
    }

    /** @return array{0: BaseCommand} */
    public function getCommands(): array
    {
        return [$this->lockCommand];
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
