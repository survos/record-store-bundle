<?php

declare(strict_types=1);

namespace Survos\RecordStore\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;

abstract class KernelTestCase extends SymfonyKernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
