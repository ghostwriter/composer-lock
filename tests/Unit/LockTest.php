<?php

declare(strict_types=1);

namespace Ghostwriter\ComposerLock\Tests\Unit;

use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Ghostwriter\ComposerLock\Lock;
use PHPUnit\Framework\TestCase;

use function class_exists;

/**
 * @coversDefaultClass Lock
 */
final class LockTest extends TestCase
{
    private Lock $lock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lock = new Lock();
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
}
