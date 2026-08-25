<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

enum FieldType: string
{
    case Text = 'text';
    case Integer = 'integer';
    case Decimal = 'decimal';
    case Boolean = 'boolean';
    case Date = 'date';
    case DateTime = 'datetime';
    case Choice = 'choice';
    case Reference = 'reference';
    case Attachment = 'attachment';
    case Unknown = 'unknown';
}
