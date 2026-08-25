<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Contract;

use Survos\RecordStoreBundle\Model\ProviderCapability;

interface RecordStoreAdapterInterface extends SchemaReaderInterface, RecordReaderInterface, RecordWriterInterface
{
    public function provider(): string;

    /** @return list<ProviderCapability> */
    public function capabilities(): array;
}
