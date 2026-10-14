<?php
declare(strict_types=1);

namespace Marana\Support;

/**
 * Diagnóstico de capacidades y nivel del motor PHP para Maraña Crochet
 */
class Environment {
    public static function getDiagnostics(): array {
        $opcacheStatus = function_exists('opcache_get_status') ? @opcache_get_status(false) : null;
        $jitEnabled = is_array($opcacheStatus) && !empty($opcacheStatus['jit']['enabled']);

        return [
            'engine' => [
                'php_version'        => PHP_VERSION,
                'php_version_id'     => PHP_VERSION_ID,
                'is_php_8_2_or_high' => PHP_VERSION_ID >= 80200,
                'sapi'               => PHP_SAPI,
                'architecture'       => (PHP_INT_SIZE * 8) . '-bit',
                'operating_system'   => PHP_OS_FAMILY,
            ],
            'modern_features' => [
                'strict_types'           => true,
                'backed_enums'           => enum_exists(\Marana\Enums\ProductStatus::class),
                'readonly_classes'       => class_exists(\Marana\DTO\ProductDTO::class),
                'dnf_types_support'      => PHP_VERSION_ID >= 80200,
                'fibers_supported'       => class_exists(\Fiber::class),
                'jit_compiler_available' => $jitEnabled,
            ],
            'performance' => [
                'memory_limit'      => ini_get('memory_limit'),
                'memory_usage_mb'   => round(memory_get_usage(true) / 1024 / 1024, 2),
                'memory_peak_mb'    => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'max_execution_sec' => ini_get('max_execution_time'),
                'opcache_enabled'   => is_array($opcacheStatus),
            ],
            'loaded_extensions' => [
                'pdo'       => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mbstring'  => extension_loaded('mbstring'),
                'openssl'   => extension_loaded('openssl'),
                'json'      => extension_loaded('json'),
            ],
        ];
    }
}
