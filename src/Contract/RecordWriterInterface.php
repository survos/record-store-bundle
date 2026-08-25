<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Contract;

use Survos\RecordStoreBundle\Model\TableReference;
use Survos\RecordStoreBundle\Model\UpsertRequest;
use Survos\RecordStoreBundle\Model\WriteResult;

interface RecordWriterInterface
{
    public function upsert(TableReference $table, UpsertRequest $request): WriteResult;
}
