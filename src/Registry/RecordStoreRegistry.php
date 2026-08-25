<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Registry;

use Survos\RecordStoreBundle\Contract\AdapterFactoryInterface;
use Survos\RecordStoreBundle\Contract\RecordStoreAdapterInterface;
use Survos\RecordStoreBundle\Exception\RecordStoreConfigurationException;
use Survos\RecordStoreBundle\Model\ApplicationReference;
use Survos\RecordStoreBundle\Model\ConnectionConfiguration;
use Survos\RecordStoreBundle\Model\TableReference;

final class RecordStoreRegistry
{
    /** @var list<AdapterFactoryInterface> */
    private array $factories;

    /** @var array<string, RecordStoreAdapterInterface> */
    private array $adapters = [];

    /**
     * @param array<string, array{driver: string, options?: array<string, mixed>}> $connections
     * @param array<string, array{connection: string, id: string, tables?: array<string, array{id: string, fields?: array<string, int|string>}>}> $applications
     * @param iterable<AdapterFactoryInterface> $factories
     */
    public function __construct(
        private readonly array $connections,
        private readonly array $applications,
        iterable $factories,
    ) {
        $this->factories = array_values(iterator_to_array($factories));
    }

    /** @return list<string> */
    public function applicationNames(): array
    {
        return array_keys($this->applications);
    }

    public function application(string $name): ApplicationReference
    {
        $configuration = $this->applications[$name]
            ?? throw new RecordStoreConfigurationException(sprintf('Unknown record-store application "%s".', $name));
        $connection = $configuration['connection'];
        $this->connection($connection);

        $tables = [];
        foreach ($configuration['tables'] ?? [] as $tableName => $table) {
            $tables[$tableName] = new TableReference(
                $name,
                $configuration['id'],
                $connection,
                $tableName,
                $table['id'],
                $table['fields'] ?? [],
            );
        }

        return new ApplicationReference($name, $connection, $configuration['id'], $tables);
    }

    public function table(string $name): TableReference
    {
        $parts = explode('.', $name, 2);
        if (2 !== count($parts)) {
            throw new \InvalidArgumentException(sprintf('Record-store table "%s" must use application.table notation.', $name));
        }

        return $this->application($parts[0])->table($parts[1]);
    }

    public function adapter(string $connectionName): RecordStoreAdapterInterface
    {
        if (isset($this->adapters[$connectionName])) {
            return $this->adapters[$connectionName];
        }

        $connection = $this->connection($connectionName);
        foreach ($this->factories as $factory) {
            if ($factory->supports($connection->driver)) {
                return $this->adapters[$connectionName] = $factory->create($connection);
            }
        }

        throw new RecordStoreConfigurationException(sprintf(
            'No record-store adapter factory supports driver "%s" for connection "%s".',
            $connection->driver,
            $connectionName,
        ));
    }

    public function adapterFor(ApplicationReference|TableReference $reference): RecordStoreAdapterInterface
    {
        return $this->adapter($reference->connection);
    }

    private function connection(string $name): ConnectionConfiguration
    {
        $configuration = $this->connections[$name]
            ?? throw new RecordStoreConfigurationException(sprintf('Unknown record-store connection "%s".', $name));

        return new ConnectionConfiguration($name, $configuration['driver'], $configuration['options'] ?? []);
    }
}
