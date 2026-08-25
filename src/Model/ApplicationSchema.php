<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class ApplicationSchema
{
    /** @param list<TableSchema> $tables */
    public function __construct(
        public string $id,
        public string $name,
        public array $tables,
    ) {
    }
}
