<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class ApplicationReference
{
    /** @param array<string, TableReference> $tables */
    public function __construct(
        public string $name,
        public string $connection,
        public string $id,
        public array $tables = [],
    ) {
        if ('' === trim($this->name) || '' === trim($this->connection) || '' === trim($this->id)) {
            throw new \InvalidArgumentException('Record-store application references require non-empty name, connection, and ID values.');
        }
    }

    public function table(string $name): TableReference
    {
        return $this->tables[$name]
            ?? throw new \InvalidArgumentException(sprintf('Unknown table "%s" in record-store application "%s".', $name, $this->name));
    }
}
