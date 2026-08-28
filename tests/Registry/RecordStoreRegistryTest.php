<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Survos\RecordStore\Contract\AdapterFactoryInterface;
use Survos\RecordStore\Contract\RecordStoreAdapterInterface;
use Survos\RecordStore\Exception\RecordStoreConfigurationException;
use Survos\RecordStore\Model\ConnectionConfiguration;
use Survos\RecordStore\Registry\RecordStoreRegistry;

final class RecordStoreRegistryTest extends TestCase
{
    public function testResolvesLogicalApplicationsTablesAndAdapters(): void
    {
        $adapter = $this->createStub(RecordStoreAdapterInterface::class);
        $factory = new class($adapter) implements AdapterFactoryInterface {
            public function __construct(private readonly RecordStoreAdapterInterface $adapter)
            {
            }

            public function supports(string $driver): bool
            {
                return 'test' === $driver;
            }

            public function create(ConnectionConfiguration $connection): RecordStoreAdapterInterface
            {
                return $this->adapter;
            }
        };
        $registry = new RecordStoreRegistry(
            ['remote' => ['driver' => 'test']],
            ['contacts' => [
                'connection' => 'remote',
                'id' => 'doc-1',
                'tables' => ['people' => ['id' => 'People', 'fields' => ['email' => 'Email']]],
            ]],
            [$factory],
        );

        self::assertSame(['contacts'], $registry->applicationNames());
        $table = $registry->table('contacts.people');
        self::assertSame('People', $table->id);
        self::assertSame('Email', $table->remoteField('email'));
        self::assertSame($adapter, $registry->adapterFor($table));
        self::assertSame($adapter, $registry->adapter('remote'));
    }

    public function testRejectsAnUnknownDriver(): void
    {
        $registry = new RecordStoreRegistry(['remote' => ['driver' => 'missing']], [], []);

        $this->expectException(RecordStoreConfigurationException::class);
        $registry->adapter('remote');
    }

    public function testRejectsAnInvalidProviderFieldMapping(): void
    {
        $registry = new RecordStoreRegistry(
            ['remote' => ['driver' => 'test']],
            ['contacts' => [
                'connection' => 'remote',
                'id' => 'doc-1',
                'tables' => ['people' => ['id' => 'People', 'fields' => ['email' => '']]],
            ]],
            [],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider field mapping for "email"');
        $registry->table('contacts.people');
    }

    /**
     * A connection with no application pointing at it is still findable.
     *
     * That is the case for a tool that *creates* the remote resource: there is nothing to
     * configure an application against yet, so deriving the connection from the applications
     * finds nothing.
     */
    public function testFindsConnectionsByDriverWithoutAnApplication(): void
    {
        $registry = new RecordStoreRegistry(
            [
                'source' => ['driver' => 'quickbase'],
                'target' => ['driver' => 'grist'],
                'archive' => ['driver' => 'Grist'],
            ],
            [],
            [],
        );

        self::assertSame(['source', 'target', 'archive'], $registry->connectionNames());
        self::assertSame(['target', 'archive'], $registry->connectionsByDriver('grist'));
        self::assertSame(['source'], $registry->connectionsByDriver('QUICKBASE'));
        self::assertSame([], $registry->connectionsByDriver('baserow'));
        self::assertSame([], $registry->applicationNames());
    }
}
