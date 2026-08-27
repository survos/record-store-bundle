<?php

declare(strict_types=1);

namespace Survos\RecordStore\Tests\Registry;

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
}
