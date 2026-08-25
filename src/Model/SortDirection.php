<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Model;

enum SortDirection: string
{
    case Ascending = 'ASC';
    case Descending = 'DESC';
}
