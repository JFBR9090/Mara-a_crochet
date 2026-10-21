<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

logoutCustomer();
setFlash('info', 'Has cerrado sesión correctamente.', 'Esperamos verte pronto tejiendo magia.');
$baseUrl = getBaseUrl();
header('Location: ' . $baseUrl . '/index.php');
exit();
