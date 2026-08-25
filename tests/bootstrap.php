<?php

declare(strict_types=1);

$packageRoot = dirname(__DIR__);
$autoload = $packageRoot.'/vendor/autoload.php';
if (!is_file($autoload)) {
    $autoload = dirname($packageRoot, 2).'/vendor/autoload.php';
}
require $autoload;

spl_autoload_register(static function (string $class) use ($packageRoot): void {
    $prefixes = [
        'Survos\\RecordStoreBundle\\Tests\\' => $packageRoot.'/tests/',
        'Survos\\RecordStoreBundle\\' => $packageRoot.'/src/',
    ];
    foreach ($prefixes as $prefix => $directory) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $file = $directory.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
