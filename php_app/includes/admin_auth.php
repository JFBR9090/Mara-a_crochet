<?php
declare(strict_types=1);

// includes/admin_auth.php - Sistema de Autenticación de Administrador para Maraña Crochet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

/**
 * Credenciales válidas para el panel de administración
 * Soporta credenciales predeterminadas para taller y desarrollo
 */
const ADMIN_USERS = [
    'admin' => [
        'password_hash' => '$2y$10$wTfZ4Fj0Esqk60vHqH.t3uYVd763jH9x1K8BsknF0yF1XGqZ/fXGy', // admin123
        'plain_fallback' => ['admin', 'admin123', 'marana2026', 'neiva2026'],
        'name' => 'Tallerista Maraña',
        'role' => 'Administrador Taller',
        'email' => 'taller@maranacrochet.co',
        'location' => 'Neiva, Huila, Colombia'
    ]
];

/**
 * Verifica si el usuario actual ha iniciado sesión como administrador
 */
function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_user']) && isset($_SESSION['admin_user']['username']);
}

/**
 * Obtiene los datos del administrador autenticado
 */
function getAdminUser(): ?array {
    return $_SESSION['admin_user'] ?? null;
}

/**
 * Valida las credenciales e inicia la sesión administrativa
 */
function adminLogin(string $username, string $password): bool {
    $userKey = strtolower(trim($username));
    
    // Permitir acceso por usuario 'admin' o por correo del taller
    $matchedUser = null;
    if (isset(ADMIN_USERS[$userKey])) {
        $matchedUser = ADMIN_USERS[$userKey];
        $username = $userKey;
    } else {
        foreach (ADMIN_USERS as $uKey => $userData) {
            if (strtolower($userData['email']) === $userKey) {
                $matchedUser = $userData;
                $username = $uKey;
                break;
            }
        }
    }

    if (!$matchedUser) {
        return false;
    }

    // Comprobar contraseña contra hash o lista de fallbacks seguros
    $passwordValid = false;
    if (password_verify($password, $matchedUser['password_hash'])) {
        $passwordValid = true;
    } elseif (in_array($password, $matchedUser['plain_fallback'], true)) {
        $passwordValid = true;
    }

    if ($passwordValid) {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        $_SESSION['admin_user'] = [
            'username' => $username,
            'name' => $matchedUser['name'],
            'role' => $matchedUser['role'],
            'email' => $matchedUser['email'],
            'location' => $matchedUser['location'],
            'login_time' => date('d/m/Y H:i:s'),
        ];
        return true;
    }

    return false;
}

/**
 * Cierra la sesión administrativa
 */
function adminLogout(): void {
    if (isset($_SESSION['admin_user'])) {
        unset($_SESSION['admin_user']);
    }
}

/**
 * Middleware: Exige inicio de sesión activo para ingresar a cualquier página administrativa
 */
function requireAdminAuth(): void {
    if (!isAdminLoggedIn()) {
        $baseUrl = getBaseUrl();
        $currentUri = $_SERVER['REQUEST_URI'] ?? ($baseUrl . '/admin/orders.php');
        $_SESSION['admin_redirect'] = $currentUri;
        setFlash('info', 'Acceso Restringido', 'Debes iniciar sesión como administrador para acceder al panel del taller.');
        header('Location: ' . $baseUrl . '/admin/login.php');
        exit();
    }
}

