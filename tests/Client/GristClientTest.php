<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Tests\Client;

use PHPUnit\Framework\TestCase;
use Survos\RecordStoreBundle\Adapter\Grist\GristClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GristClientTest extends TestCase
{
    public function testReadsTablesAndColumns(): void
    {
        $responses = [
            new MockResponse('{"tables":[{"id":"People","fields":{"tableRef":1}}]}'),
            new MockResponse('{"columns":[{"id":"Name","fields":{"label":"Full name","type":"Text"}}]}'),
        ];
        $client = new GristClient(new MockHttpClient($responses, 'https://grist.test/api/'));

        self::assertSame('People', $client->tables('doc-1')[0]['id']);
        self::assertSame('Name', $client->columns('doc-1', 'People')[0]['id']);
        self::assertSame('https://grist.test/api/docs/doc-1/tables', $responses[0]->getRequestUrl());
        self::assertSame('https://grist.test/api/docs/doc-1/tables/People/columns', $responses[1]->getRequestUrl());
    }

    public function testQueriesRecordsWithFiltersAndSorts(): void
    {
        $response = new MockResponse('{"records":[{"id":7,"fields":{"Name":"Ada","Status":"Active"}}]}');
        $client = new GristClient(new MockHttpClient($response, 'https://grist.test/api/'));

        $records = $client->queryRecords('doc-1', 'People', ['Status' => ['Active']], ['-Name'], 25);

        self::assertSame(7, $records[0]['id']);
        self::assertSame('Ada', $records[0]['fields']['Name']);
        self::assertStringContainsString('filter=', $response->getRequestUrl());
        self::assertStringContainsString('sort=-Name', $response->getRequestUrl());
        self::assertStringContainsString('limit=25', $response->getRequestUrl());
    }

    public function testAddsAndUpsertsRecords(): void
    {
        $responses = [
            new MockResponse('{"records":[{"id":8}]}'),
            new MockResponse('{"records":[{"id":8}]}'),
        ];
        $client = new GristClient(new MockHttpClient($responses, 'https://grist.test/api/'));

        self::assertSame([8], $client->addRecords('doc-1', 'People', [['Name' => 'Ada']]));
        self::assertSame([8], $client->upsertRecords('doc-1', 'People', [[
            'require' => ['Email' => 'ada@example.test'],
            'fields' => ['Name' => 'Ada'],
        ]]));
        self::assertSame('POST', $responses[0]->getRequestMethod());
        self::assertSame('PUT', $responses[1]->getRequestMethod());
        $addBody = $responses[0]->getRequestOptions()['body'];
        $upsertBody = $responses[1]->getRequestOptions()['body'];
        self::assertIsString($addBody);
        self::assertIsString($upsertBody);
        self::assertJsonStringEqualsJsonString('{"records":[{"fields":{"Name":"Ada"}}]}', $addBody);
        self::assertJsonStringEqualsJsonString('{"records":[{"require":{"Email":"ada@example.test"},"fields":{"Name":"Ada"}}]}', $upsertBody);
    }
}
