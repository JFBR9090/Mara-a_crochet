<?php
declare(strict_types=1);

namespace Marana\Http;

use JsonSerializable;

/**
 * Gestor estandarizado de respuestas HTTP JSON en PHP 8.2+
 */
class Response {
    public static function json(mixed $data, int $statusCode = 200, array $headers = []): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Powered-By: Maraña PHP 8.2 Engine');

            foreach ($headers as $key => $value) {
                header("{$key}: {$value}");
            }
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit();
    }

    public static function success(mixed $data, string $message = '', int $statusCode = 200): void {
        $payload = ['success' => true];
        if ($message !== '') {
            $payload['message'] = $message;
        }

        if (is_array($data)) {
            $payload = array_merge($payload, $data);
        } else {
            $payload['data'] = $data;
        }

        self::json($payload, $statusCode);
    }

    public static function error(string $message, int $statusCode = 400, array $details = []): void {
        $payload = [
            'success' => false,
            'error'   => $message,
        ];

        if (!empty($details)) {
            $payload['details'] = $details;
        }

        self::json($payload, $statusCode);
    }
}
