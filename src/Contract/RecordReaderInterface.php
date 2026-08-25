<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Contract;

use Survos\RecordStoreBundle\Model\RecordPage;
use Survos\RecordStoreBundle\Model\RecordQuery;
use Survos\RecordStoreBundle\Model\TableReference;

interface RecordReaderInterface
{
    public function query(TableReference $table, RecordQuery $query): RecordPage;
}
