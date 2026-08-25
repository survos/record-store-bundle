<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class WriteResult
{
    /**
     * @param list<int|string> $createdIds
     * @param list<int|string> $updatedIds
     * @param list<int|string> $unchangedIds
     * @param list<int|string> $affectedIds IDs returned when the provider cannot distinguish create from update.
     */
    public function __construct(
        public array $createdIds = [],
        public array $updatedIds = [],
        public array $unchangedIds = [],
        public array $affectedIds = [],
    ) {
    }
}
