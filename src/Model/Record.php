<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class Record
{
    /** @param array<string, mixed> $fields */
    public function __construct(
        public array $fields,
        public int|string|null $id = null,
    ) {
    }
}
