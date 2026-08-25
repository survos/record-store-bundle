<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Survos\RecordStoreBundle\Adapter\Grist\GristAdapter;
use Survos\RecordStoreBundle\Contract\GristClientInterface;
use Survos\RecordStoreBundle\Exception\UnsupportedRecordStoreOperation;
use Survos\RecordStoreBundle\Model\ApplicationReference;
use Survos\RecordStoreBundle\Model\FieldType;
use Survos\RecordStoreBundle\Model\Record;
use Survos\RecordStoreBundle\Model\RecordQuery;
use Survos\RecordStoreBundle\Model\RecordSort;
use Survos\RecordStoreBundle\Model\SortDirection;
use Survos\RecordStoreBundle\Model\TableReference;
use Survos\RecordStoreBundle\Model\UpsertRequest;

final class GristAdapterTest extends TestCase
{
    public function testNormalizesSchemaAndLogicalRecordFields(): void
    {
        $client = $this->createMock(GristClientInterface::class);
        $client->expects($this->once())->method('tables')->with('doc-1')->willReturn([
            ['id' => 'People', 'fields' => ['tableRef' => 1]],
        ]);
        $client->expects($this->once())->method('columns')->with('doc-1', 'People')->willReturn([
            ['id' => 'Name', 'fields' => ['label' => 'Full name', 'type' => 'Text']],
            ['id' => 'Organization', 'fields' => ['type' => 'Ref:Organizations']],
        ]);
        $client->expects($this->once())->method('queryRecords')->with(
            'doc-1',
            'People',
            ['Status' => ['Active']],
            ['-Name'],
            10,
        )->willReturn([['id' => 7, 'fields' => ['Name' => 'Ada', 'Status' => 'Active']]]);
        $adapter = new GristAdapter($client);
        $table = self::table();

        $schema = $adapter->schema(new ApplicationReference('contacts', 'internal', 'doc-1'));
        self::assertSame(FieldType::Text, $schema->tables[0]->fields[0]->type);
        self::assertSame(FieldType::Reference, $schema->tables[0]->fields[1]->type);

        $page = $adapter->query($table, new RecordQuery(
            ['name'],
            ['status' => ['Active']],
            [new RecordSort('name', SortDirection::Descending)],
            10,
        ));
        self::assertSame(['name' => 'Ada'], $page->records[0]->fields);
    }

    public function testMapsLogicalFieldsForUpsert(): void
    {
        $client = $this->createMock(GristClientInterface::class);
        $client->expects($this->once())->method('upsertRecords')->with('doc-1', 'People', [[
            'require' => ['Email' => 'ada@example.test'],
            'fields' => ['Name' => 'Ada', 'Email' => 'ada@example.test'],
        ]])->willReturn([7]);
        $adapter = new GristAdapter($client);

        $result = $adapter->upsert(self::table(), new UpsertRequest([
            new Record(['name' => 'Ada', 'email' => 'ada@example.test']),
        ], ['email']));

        self::assertSame([7], $result->affectedIds);
    }

    public function testRejectsPortableOffsets(): void
    {
        $adapter = new GristAdapter($this->createStub(GristClientInterface::class));

        $this->expectException(UnsupportedRecordStoreOperation::class);
        $adapter->query(self::table(), new RecordQuery(offset: 1));
    }

    private static function table(): TableReference
    {
        return new TableReference('contacts', 'doc-1', 'internal', 'people', 'People', [
            'name' => 'Name',
            'email' => 'Email',
            'status' => 'Status',
        ]);
    }
}
