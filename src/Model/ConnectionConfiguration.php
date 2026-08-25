<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

final readonly class ConnectionConfiguration
{
    /** @param array<string, mixed> $options */
    public function __construct(
        public string $name,
        public string $driver,
        public array $options = [],
    ) {
        if ('' === trim($this->name)) {
            throw new \InvalidArgumentException('A record-store connection name cannot be empty.');
        }
        if ('' === trim($this->driver)) {
            throw new \InvalidArgumentException(sprintf('Record-store connection "%s" requires a driver.', $this->name));
        }
    }
}
