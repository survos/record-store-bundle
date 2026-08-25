<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Exception;

final class GristApiException extends \RuntimeException
{
    /** @param array<array-key, mixed> $response */
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly array $response = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
