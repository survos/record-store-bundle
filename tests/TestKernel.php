<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle\Tests;

use Survos\Kit\SurvosKitBundle;
use Survos\RecordStoreBundle\SurvosRecordStoreBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    /** @return \Generator<int, BundleInterface> */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SurvosKitBundle();
        yield new SurvosRecordStoreBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', ['secret' => 'test', 'test' => true, 'http_client' => []]);
            $container->loadFromExtension('survos_record_store', [
                'connections' => [
                    'internal' => [
                        'driver' => 'grist',
                        'options' => ['base_uri' => 'https://grist.test/api/', 'token' => 'test-token'],
                    ],
                ],
                'applications' => [
                    'contacts' => [
                        'connection' => 'internal',
                        'id' => 'doc-1',
                        'tables' => [
                            'contacts' => [
                                'id' => 'Contacts',
                                'fields' => ['name' => 'Name', 'email' => 'Email'],
                            ],
                        ],
                    ],
                ],
            ]);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/record-store-bundle-tests/cache/'.spl_object_hash($this);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/record-store-bundle-tests/log';
    }
}
