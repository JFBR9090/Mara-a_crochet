<?php
// admin/logout.php - Cerrar sesión administrativa
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

adminLogout();
setFlash('info', 'Sesión Finalizada', 'Has cerrado la sesión administrativa del taller correctamente.');
$redirectUrl = getBaseUrl() . '/admin/login.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cerrando sesión...</title>
  <script>
    try {
      sessionStorage.removeItem('marana_auth_dismissed');
    } catch(e) {}
    window.location.replace("<?= $redirectUrl ?>");
  </script>
</head>
<body>
</body>
</html>
<?php
exit();


