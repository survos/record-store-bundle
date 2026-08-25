<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class RecordSort
{
    public function __construct(
        public string $field,
        public SortDirection $direction = SortDirection::Ascending,
    ) {
        if ('' === trim($this->field)) {
            throw new \InvalidArgumentException('A record sort field cannot be empty.');
        }
    }
}
