<?php
// admin/logout.php - Cerrar sesión administrativa
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

adminLogout();
setFlash('info', 'Sesión Finalizada', 'Has cerrado la sesión administrativa del taller correctamente.');
header('Location: ' . getBaseUrl() . '/admin/login.php');
exit();

