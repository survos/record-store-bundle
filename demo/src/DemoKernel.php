<?php

declare(strict_types=1);

namespace Survos\RecordStore\Demo;

use Survos\Kit\SurvosKitBundle;
use Survos\RecordStoreBundle\SurvosRecordStoreBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

final class DemoKernel extends Kernel
{
    /** @return \Generator<int, FrameworkBundle|SurvosKitBundle|SurvosRecordStoreBundle> */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SurvosKitBundle();
        yield new SurvosRecordStoreBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', [
                'secret' => 'record-store-demo',
                'http_client' => [],
            ]);

            $documentId = self::environment('GRIST_DEMO_DOC_ID');
            $applications = '' === $documentId ? [] : [
                'demo' => [
                    'connection' => 'grist',
                    'id' => $documentId,
                    'tables' => [
                        'people' => [
                            'id' => 'People',
                            'fields' => [
                                'name' => 'Name',
                                'email' => 'Email',
                            ],
                        ],
                    ],
                ],
            ];

            $container->loadFromExtension('survos_record_store', [
                'connections' => [
                    'grist' => [
                        'driver' => 'grist',
                        'options' => [
                            'base_uri' => self::environment('GRIST_BASE_URI', 'http://127.0.0.1:8485/api/'),
                            'token' => self::environment('GRIST_API_KEY'),
                        ],
                    ],
                ],
                'applications' => $applications,
            ]);
        });
    }

    public function getCacheDir(): string
    {
        $configuration = implode("\0", [
            self::environment('GRIST_BASE_URI', 'http://127.0.0.1:8485/api/'),
            self::environment('GRIST_API_KEY'),
            self::environment('GRIST_DEMO_DOC_ID'),
        ]);

        return sys_get_temp_dir().'/survos-record-store-demo/cache-'.substr(hash('sha256', $configuration), 0, 12);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/survos-record-store-demo/log';
    }

    private static function environment(string $name, string $default = ''): string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        return is_string($value) ? $value : $default;
    }
}
