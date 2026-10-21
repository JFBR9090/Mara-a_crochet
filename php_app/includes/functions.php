<?php
declare(strict_types=1);

// includes/functions.php - Funciones utilitarias y manejo de estado de sesión en PHP 8.2+
$autoloadCandidate = __DIR__ . '/../../api/autoload.php';
if (file_exists($autoloadCandidate)) {
    require_once $autoloadCandidate;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializar sesión de carrito y favoritos si no existen
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = ['lulu-aranita'];
}

/**
 * Retorna la URL base del proyecto para enlaces absolutos limpios
 */
function getBaseUrl(): string {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Normalizar separadores y eliminar trailing slashes innecesarios
    $path = str_replace('\\', '/', $scriptDir);
    // Si estamos en un subdirectorio como /admin o /api, obtener la raíz de la app
    if (basename($path) === 'admin' || basename($path) === 'api') {
        $path = dirname($path);
    }
    return rtrim($path, '/');
}

/**
 * Escapar contenido seguro para HTML
 */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear número a moneda colombiana (COP)
 */
function formatCOP(float|int $amount): string {
    return '$' . number_format($amount, 0, ',', '.') . ' COP';
}

/**
 * Alias de compatibilidad formatCLP -> formatCOP
 */
function formatCLP(float|int $amount): string {
    return formatCOP($amount);
}

/**
 * Manejo de mensajes Flash / Toasts
 */
function setFlash(string $type, string $message, string $subMessage = ''): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'info', 'error'
        'message' => $message,
        'subMessage' => $subMessage,
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Carrito de Compras en Sesión
 */
function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function getCartCount(): int {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += (int)$item['quantity'];
    }
    return $count;
}

function getCartSubtotal(): int {
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += ((int)$item['price']) * ((int)$item['quantity']);
    }
    return $subtotal;
}

function addToCart(array $product, int $quantity = 1): void {
    $id = $product['id'];
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $firstImage = '';
        if (!empty($product['images'])) {
            $imgs = is_array($product['images']) ? $product['images'] : json_decode($product['images'], true);
            $firstImage = $imgs[0]['url'] ?? '';
        }
        $_SESSION['cart'][$id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => (int)$product['price'],
            'sku' => $product['sku'],
            'image' => $firstImage,
            'quantity' => $quantity,
        ];
    }
}

function updateCartQuantity(string $productId, int $delta): void {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $delta;
        if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
            unset($_SESSION['cart'][$productId]);
        }
    }
}

function removeFromCart(string $productId): void {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

function clearCart(): void {
    $_SESSION['cart'] = [];
}

/**
 * Favoritos en Sesión
 */
function getFavorites(): array {
    return $_SESSION['favorites'] ?? [];
}

function toggleFavorite(string $productId): bool {
    if (!isset($_SESSION['favorites'])) {
        $_SESSION['favorites'] = [];
    }
    $index = array_search($productId, $_SESSION['favorites']);
    if ($index !== false) {
        array_splice($_SESSION['favorites'], $index, 1);
        return false; // Removido
    } else {
        $_SESSION['favorites'][] = $productId;
        return true; // Agregado
    }
}

/**
 * Gestión de Sesión de Clientes (Maraña Accounts)
 */
function isCustomerLoggedIn(): bool {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

function getCustomer(): ?array {
    if (!isCustomerLoggedIn()) {
        return null;
    }
    return [
        'id' => (int)$_SESSION['customer_id'],
        'name' => $_SESSION['customer_name'] ?? '',
        'email' => $_SESSION['customer_email'] ?? '',
        'phone' => $_SESSION['customer_phone'] ?? '',
        'address' => $_SESSION['customer_address'] ?? '',
        'city' => $_SESSION['customer_city'] ?? '',
        'department' => $_SESSION['customer_department'] ?? 'Cundinamarca',
        'avatar' => $_SESSION['customer_avatar'] ?? null,
        'theme_preference' => $_SESSION['customer_theme'] ?? 'light',
    ];
}

function loginCustomer(array $customer): void {
    $_SESSION['customer_id'] = (int)$customer['id'];
    $_SESSION['customer_name'] = $customer['name'];
    $_SESSION['customer_email'] = $customer['email'];
    $_SESSION['customer_phone'] = $customer['phone'] ?? '';
    $_SESSION['customer_address'] = $customer['address'] ?? '';
    $_SESSION['customer_city'] = $customer['city'] ?? '';
    $_SESSION['customer_department'] = $customer['department'] ?? 'Cundinamarca';
    $_SESSION['customer_avatar'] = $customer['avatar'] ?? null;
    $_SESSION['customer_theme'] = $customer['theme_preference'] ?? 'light';
}

function logoutCustomer(): void {
    unset(
        $_SESSION['customer_id'],
        $_SESSION['customer_name'],
        $_SESSION['customer_email'],
        $_SESSION['customer_phone'],
        $_SESSION['customer_address'],
        $_SESSION['customer_city'],
        $_SESSION['customer_department'],
        $_SESSION['customer_avatar'],
        $_SESSION['customer_theme']
    );
}

/**
 * Renderiza el Avatar de un cliente (Foto personalizada, Preset amigurumi o Iniciales)
 */
function renderCustomerAvatarHtml(?string $avatar, string $name, string $size = 'md', string $extraClass = ''): string {
    $baseUrl = getBaseUrl();
    $initial = mb_strtoupper(mb_substr(trim($name ?: 'M'), 0, 1));
    
    // Dimensiones según tamaño
    $dim = match ($size) {
        'sm' => 'width: 1.85rem; height: 1.85rem; font-size: 0.85rem;',
        'md' => 'width: 2.75rem; height: 2.75rem; font-size: 1.25rem;',
        'lg' => 'width: 4.5rem; height: 4.5rem; font-size: 1.8rem;',
        'xl' => 'width: 6.5rem; height: 6.5rem; font-size: 2.5rem;',
        default => 'width: 2.5rem; height: 2.5rem; font-size: 1rem;'
    };

    $presets = [
        'preset:rana' => ['emoji' => '🐸', 'bg' => '#E8F5E9', 'name' => 'Ranita Cósmica'],
        'preset:aranita' => ['emoji' => '🕷️', 'bg' => '#F3E5F5', 'name' => 'Lulu Arañita'],
        'preset:murcielago' => ['emoji' => '🦇', 'bg' => '#EDE7F6', 'name' => 'Murciélago Lunar'],
        'preset:pulpo' => ['emoji' => '🐙', 'bg' => '#E0F7FA', 'name' => 'Pulpo de Apego'],
        'preset:ovillo' => ['emoji' => '🧶', 'bg' => '#FFF3E0', 'name' => 'Ovillo Mágico'],
        'preset:abeja' => ['emoji' => '🐝', 'bg' => '#FFFDE7', 'name' => 'Abejita Silvestre'],
    ];

    // Caso 1: Preset artesanal de crochet
    if ($avatar && isset($presets[$avatar])) {
        $preset = $presets[$avatar];
        return '<div class="customer-avatar-preset ' . h($extraClass) . '" style="' . $dim . ' border-radius: 50%; background: ' . $preset['bg'] . '; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.12); border: 2px solid var(--color-primary-light); user-select: none;" title="' . h($preset['name']) . '">'
            . '<span style="line-height: 1;">' . $preset['emoji'] . '</span>'
            . '</div>';
    }

    // Caso 2: Imagen personalizada subida
    if ($avatar && !str_starts_with($avatar, 'preset:')) {
        $imgUrl = str_starts_with($avatar, 'http') ? $avatar : $baseUrl . '/' . ltrim($avatar, '/');
        return '<div class="customer-avatar-wrapper ' . h($extraClass) . '" style="' . $dim . ' border-radius: 50%; position: relative; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; border: 2.5px solid var(--color-primary); box-shadow: 0 4px 12px rgba(123,44,191,0.25); flex-shrink: 0; background: var(--color-neutral-bg);">'
            . '<img src="' . h($imgUrl) . '" alt="' . h($name) . '" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">'
            . '<div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; background: var(--color-primary); color: #fff; font-weight: 800;">' . h($initial) . '</div>'
            . '</div>';
    }

    // Caso 3: Fallback a Iniciales estilizadas
    return '<div class="customer-avatar-initial ' . h($extraClass) . '" style="' . $dim . ' border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); color: #ffffff; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(123,44,191,0.25); border: 2px solid #ffffff; flex-shrink: 0; user-select: none;">'
        . h($initial)
        . '</div>';
}



