<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class RecordPage
{
    /** @param list<Record> $records */
    public function __construct(
        public array $records,
        public ?int $total = null,
        public ?int $nextOffset = null,
    ) {
    }
}
