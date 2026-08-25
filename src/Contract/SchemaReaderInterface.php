<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Contract;

use Survos\RecordStoreBundle\Model\ApplicationReference;
use Survos\RecordStoreBundle\Model\ApplicationSchema;

interface SchemaReaderInterface
{
    public function schema(ApplicationReference $application): ApplicationSchema;
}
