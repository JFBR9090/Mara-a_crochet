<?php
declare(strict_types=1);

// api/autoload.php - Cargador PSR-4 híbrido (Composer + Fallback nativo PHP 8.2+)

// 1. Intentar cargar autoloader de Composer si existe
$composerAutoloaders = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

foreach ($composerAutoloaders as $path) {
    if (file_exists($path)) {
        require_once $path;
        return;
    }
}

// 2. Fallback de autoloader nativo PSR-4 para el namespace 'Marana\'
spl_autoload_register(function (string $class): void {
    $prefix = 'Marana\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
