<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

logoutCustomer();
setFlash('info', 'Has cerrado sesión correctamente.', 'Esperamos verte pronto tejiendo magia.');
$baseUrl = getBaseUrl();
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
    window.location.replace("<?= $baseUrl ?>/index.php");
  </script>
</head>
<body>
</body>
</html>
<?php
exit();

