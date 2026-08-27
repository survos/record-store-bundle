<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Tests;

use Survos\RecordStore\Registry\RecordStoreRegistry;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

final class BundleBootTest extends KernelTestCase
{
    public function testBundleRegistersRegistryAndCommands(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        self::assertInstanceOf(RecordStoreRegistry::class, $container->get(RecordStoreRegistry::class));
        $loader = $container->get('console.command_loader');
        self::assertInstanceOf(ContainerCommandLoader::class, $loader);
        $commands = array_flip($loader->getNames());
        self::assertArrayHasKey('record-store:applications', $commands);
        self::assertArrayHasKey('record-store:schema', $commands);
        self::assertArrayHasKey('record-store:query', $commands);
    }
}
