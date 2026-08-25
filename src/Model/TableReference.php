<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class TableReference
{
    /** @param array<string, int|string> $fields */
    public function __construct(
        public string $application,
        public string $applicationId,
        public string $connection,
        public string $name,
        public string $id,
        public array $fields = [],
    ) {
        foreach ([$this->application, $this->applicationId, $this->connection, $this->name, $this->id] as $value) {
            if ('' === trim($value)) {
                throw new \InvalidArgumentException('Record-store table references require non-empty application, connection, name, and ID values.');
            }
        }

        foreach ($this->fields as $logicalName => $remoteField) {
            if ('' === trim($logicalName)) {
                throw new \InvalidArgumentException('Logical field names cannot be empty.');
            }

            if ((\is_int($remoteField) && $remoteField < 1) || (\is_string($remoteField) && '' === trim($remoteField))) {
                throw new \InvalidArgumentException(sprintf('Provider field mapping for "%s" must be a positive integer or non-empty string.', $logicalName));
            }
        }
    }

    public function remoteField(string $logicalName): int|string
    {
        if ('' === trim($logicalName)) {
            throw new \InvalidArgumentException('A logical field name cannot be empty.');
        }

        return $this->fields[$logicalName] ?? $logicalName;
    }
}
