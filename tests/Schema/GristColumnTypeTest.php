<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Tests\Schema;

use PHPUnit\Framework\TestCase;
use Survos\Grist\Schema\GristColumnType;
use Survos\RecordStore\Model\FieldType;

final class GristColumnTypeTest extends TestCase
{
    public function testMapsNormalizedTypesToGristTypes(): void
    {
        self::assertSame('Text', GristColumnType::native(FieldType::Text));
        self::assertSame('Int', GristColumnType::native(FieldType::Integer));
        self::assertSame('Numeric', GristColumnType::native(FieldType::Decimal));
        self::assertSame('Bool', GristColumnType::native(FieldType::Boolean));
        self::assertSame('Date', GristColumnType::native(FieldType::Date));
        self::assertSame('Choice', GristColumnType::native(FieldType::Choice));
        self::assertSame('ChoiceList', GristColumnType::native(FieldType::Choice, list: true));
        self::assertSame('Attachments', GristColumnType::native(FieldType::Attachment));
    }

    /** A DateTime column carries its timezone in the type string itself. */
    public function testDateTimeCarriesTheTimezone(): void
    {
        self::assertSame('DateTime:UTC', GristColumnType::native(FieldType::DateTime));
        self::assertSame('DateTime:America/New_York', GristColumnType::native(FieldType::DateTime, timezone: 'America/New_York'));
    }

    public function testReferencesNameTheirTargetTable(): void
    {
        self::assertSame('Ref:People', GristColumnType::native(FieldType::Reference, 'People'));
        self::assertSame('RefList:People', GristColumnType::native(FieldType::Reference, 'People', list: true));
    }

    /** Rather than silently emitting a broken Ref: column, which Grist accepts and never resolves. */
    public function testAReferenceWithoutATargetIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GristColumnType::native(FieldType::Reference);
    }

    public function testUnmappableTypesBecomeTextRatherThanDisappearing(): void
    {
        self::assertSame('Text', GristColumnType::native(FieldType::Unknown));
        self::assertSame('Text', GristColumnType::native(FieldType::Time));
    }

    /** Grist stores widgetOptions as a JSON string; an object there is accepted and then ignored. */
    public function testChoiceDefinitionEncodesItsChoicesAsAJsonString(): void
    {
        $definition = GristColumnType::definition(FieldType::Choice, 'Status', choices: ['Open', 'Shut']);

        self::assertSame('Choice', $definition['type']);
        self::assertSame('Status', $definition['label']);
        self::assertSame('{"choices":["Open","Shut"],"choiceOptions":{}}', $definition['widgetOptions']);
    }

    public function testDatesCoerceToTheEpochSecondOfUtcMidnight(): void
    {
        self::assertSame(1615766400, GristColumnType::coerce('Date', '2021-03-15'));
        self::assertSame(1615766400, GristColumnType::coerce('Date', '2021-03-15T14:32:00Z'));
        self::assertSame(1615818720, GristColumnType::coerce('DateTime:UTC', '2021-03-15T14:32:00Z'));
    }

    /** A list cell is ['L', ...values], not a JSON array. */
    public function testListColumnsUseGristListEncoding(): void
    {
        self::assertSame(['L', 'a', 'b'], GristColumnType::coerce('ChoiceList', ['a', 'b']));
        self::assertSame(['L', 7], GristColumnType::coerce('Attachments', 7));
        self::assertSame(['L', 3, 4], GristColumnType::coerce('RefList:People', [3, 4]));
    }

    public function testEmptyValuesLeaveTheCellAloneRatherThanWritingNull(): void
    {
        self::assertNull(GristColumnType::coerce('Text', ''));
        self::assertNull(GristColumnType::coerce('Date', null));
        self::assertNull(GristColumnType::coerce('ChoiceList', []));
    }

    public function testScalarsCoerceToTheColumnType(): void
    {
        self::assertSame(7, GristColumnType::coerce('Ref:People', '7'));
        self::assertSame(42, GristColumnType::coerce('Int', '42'));
        self::assertSame(1.5, GristColumnType::coerce('Numeric', '1.5'));
        self::assertTrue(GristColumnType::coerce('Bool', 'yes'));
        self::assertFalse(GristColumnType::coerce('Bool', 'false'));
    }

    /** An unparseable date must not land in the column as text Grist will flag as invalid. */
    public function testUnparseableValuesBecomeNullRatherThanInvalidCells(): void
    {
        self::assertNull(GristColumnType::coerce('Date', 'sometime last spring'));
        self::assertNull(GristColumnType::coerce('Int', 'many'));
    }
}
