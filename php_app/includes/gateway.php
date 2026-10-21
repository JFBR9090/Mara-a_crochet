<?php
declare(strict_types=1);

// includes/gateway.php - Gestión de pasarelas de pago colombianas y variables de entorno

/**
 * Cargar variables de entorno desde un archivo .env si existen
 */
function loadEnvFile(string $envPath): void {
    if (!file_exists($envPath)) {
        return;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remover comillas si existen
            $value = trim($value, '"\'');
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Cargar .env de la aplicación o de la raíz
loadEnvFile(__DIR__ . '/../.env');
loadEnvFile(__DIR__ . '/../../.env');

/**
 * Obtener variable de entorno con fallback
 */
function getEnvVar(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Generar firma de integridad para Wompi (Colombia)
 * Documentación: sha256(reference + amountInCents + currency + integrityKey)
 */
function generateWompiIntegritySignature(string $reference, int $amountInCOP): string {
    $amountInCents = $amountInCOP * 100;
    $currency = 'COP';
    $integrityKey = getEnvVar('WOMPI_INTEGRITY_KEY', 'test_integrity_k48a9s8f7d6a5s4df87a');
    $raw = "{$reference}{$amountInCents}{$currency}{$integrityKey}";
    return hash('sha256', $raw);
}

/**
 * Retorna las opciones de pago colombianas disponibles con sus especificaciones
 */
function getPaymentMethodsConfig(): array {
    return [
        'wompi_card' => [
            'id' => 'wompi_card',
            'name' => 'Tarjeta de Crédito / Débito (Wompi)',
            'icon' => 'credit_card',
            'badge' => 'Pasarela Segura',
            'description' => 'Aceptamos Visa, MasterCard y American Express a través de la pasarela oficial Wompi (Bancolombia).',
            'sandbox' => getEnvVar('WOMPI_ENVIRONMENT', 'sandbox') === 'sandbox',
            'publicKey' => getEnvVar('WOMPI_PUBLIC_KEY', 'pub_test_Q5yDA9xoKdePzhSGeVe9HAUr1jiFd1Jy')
        ],
        'pse' => [
            'id' => 'pse',
            'name' => 'PSE - Débito Bancario en Línea',
            'icon' => 'account_balance',
            'badge' => 'Todos los Bancos',
            'description' => 'Paga al instante debitando directamente desde cualquier cuenta bancaria colombiana.',
            'banks' => [
                'Bancolombia',
                'Banco de Bogotá',
                'Davivienda',
                'BBVA Colombia',
                'Banco de Occidente',
                'Banco Popular',
                'Scotiabank Colpatria',
                'Banco AV Villas',
                'Banco Caja Social',
                'Banco Falabella',
                'Nequi',
                'Daviplata',
                'Nu Colombia'
            ]
        ],
        'nequi_daviplata' => [
            'id' => 'nequi_daviplata',
            'name' => 'Nequi / Daviplata Directo',
            'icon' => 'smartphone',
            'badge' => 'Cero Comisiones',
            'description' => 'Transfiere al instante desde tu celular a nuestro número registrado o escanea el QR oficial.',
            'phone' => '312 456 7890',
            'holder' => 'Maraña Crochet Artesanal'
        ],
        'transferencia' => [
            'id' => 'transferencia',
            'name' => 'Transferencia Bancolombia',
            'icon' => 'receipt_long',
            'badge' => 'Cuenta del Taller',
            'description' => 'Transfiere a la Cuenta de Ahorros del taller y adjunta tu comprobante para despacho inmediato.',
            'account' => '482-901823-11',
            'type' => 'Cuenta de Ahorros',
            'bank' => 'Bancolombia'
        ],
        'contra_entrega' => [
            'id' => 'contra_entrega',
            'name' => 'Pago Contra Entrega',
            'icon' => 'local_shipping',
            'badge' => 'En tu Domicilio',
            'description' => 'Pagas en efectivo al mensajero de Inter Rapidísimo / Servientrega al recibir tu paquete.',
            'extraFee' => 0
        ]
    ];
}
