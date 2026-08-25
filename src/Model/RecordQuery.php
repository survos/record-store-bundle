<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class RecordQuery
{
    /**
     * @param list<string>                                              $select
     * @param array<string, list<int|float|string|bool|null>>           $filters
     * @param list<RecordSort>                                          $sorts
     */
    public function __construct(
        public array $select = [],
        public array $filters = [],
        public array $sorts = [],
        public int $limit = 100,
        public int $offset = 0,
    ) {
        if ($this->limit < 1) {
            throw new \InvalidArgumentException('A record query limit must be at least 1.');
        }
        if ($this->offset < 0) {
            throw new \InvalidArgumentException('A record query offset cannot be negative.');
        }
        foreach ($this->filters as $field => $values) {
            if ('' === trim($field) || [] === $values) {
                throw new \InvalidArgumentException('Record query filters require a field name and at least one allowed value.');
            }
        }
    }
}
