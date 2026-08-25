<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class TableSchema
{
    /** @param list<FieldSchema> $fields */
    public function __construct(
        public string $id,
        public string $name,
        public string $label,
        public array $fields = [],
    ) {
    }
}
