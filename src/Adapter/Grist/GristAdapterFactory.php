<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Adapter\Grist;

use Survos\RecordStoreBundle\Contract\AdapterFactoryInterface;
use Survos\RecordStoreBundle\Contract\RecordStoreAdapterInterface;
use Survos\RecordStoreBundle\Model\ConnectionConfiguration;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GristAdapterFactory implements AdapterFactoryInterface
{
    public function __construct(private HttpClientInterface $http)
    {
    }

    public function supports(string $driver): bool
    {
        return 'grist' === strtolower($driver);
    }

    public function create(ConnectionConfiguration $connection): RecordStoreAdapterInterface
    {
        $baseUri = $connection->options['base_uri'] ?? null;
        if (!is_string($baseUri) || '' === trim($baseUri)) {
            throw new \InvalidArgumentException(sprintf('Grist connection "%s" requires a base_uri option.', $connection->name));
        }
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'survos/record-store-bundle',
        ];
        $token = $connection->options['token'] ?? null;
        if (is_string($token) && '' !== trim($token)) {
            $headers['Authorization'] = 'Bearer '.$token;
        }
        $timeout = $connection->options['timeout'] ?? 30.0;
        if (!is_int($timeout) && !is_float($timeout)) {
            throw new \InvalidArgumentException(sprintf('Grist connection "%s" timeout must be numeric.', $connection->name));
        }

        $client = new GristClient($this->http->withOptions([
            'base_uri' => rtrim($baseUri, '/').'/',
            'headers' => $headers,
            'timeout' => (float) $timeout,
        ]));

        return new GristAdapter($client);
    }
}
