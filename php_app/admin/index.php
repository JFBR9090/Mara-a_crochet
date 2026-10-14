<?php
// admin/index.php - Redirección al panel de pedidos
require_once __DIR__ . '/../includes/functions.php';
header('Location: ' . getBaseUrl() . '/admin/orders.php');
exit();
