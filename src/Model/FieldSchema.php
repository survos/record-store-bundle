<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class FieldSchema
{
    /** @param array<string, mixed> $providerMetadata */
    public function __construct(
        public int|string $id,
        public string $name,
        public string $label,
        public FieldType $type,
        public array $providerMetadata = [],
    ) {
    }
}
