<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Contract;

use Survos\RecordStoreBundle\Model\ConnectionConfiguration;

interface AdapterFactoryInterface
{
    public function supports(string $driver): bool;

    public function create(ConnectionConfiguration $connection): RecordStoreAdapterInterface;
}
