<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class UpsertRequest
{
    /**
     * @param list<Record> $records
     * @param list<string> $keyFields
     */
    public function __construct(
        public array $records,
        public array $keyFields = [],
    ) {
        if ([] === $this->records) {
            throw new \InvalidArgumentException('An upsert request requires at least one record.');
        }
    }
}
