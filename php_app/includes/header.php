<?php
// includes/header.php - Cabecera común y llamada a navegación
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Maraña Crochet | Amigurumis Cósmicos & Apego Artesanal';
$pageDesc = $pageDesc ?? 'Amigurumis hechos a mano con hilo de algodón hipoalergénico. Ediciones limitadas con empaque aromático de lavanda y certificado de adopción.';
$baseUrl = getBaseUrl();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?></title>
  <meta name="description" content="<?= h($pageDesc) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/img/favicon.png">
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body>

<?php require __DIR__ . '/navbar.php'; ?>

<!-- Flash Toast Notification Container -->
<div id="toast-container" class="toast-container">
  <?php if ($flash): ?>
    <div class="toast-card <?= h($flash['type']) ?>">
      <div class="toast-title"><?= h($flash['message']) ?></div>
      <?php if (!empty($flash['subMessage'])): ?>
        <div class="toast-desc"><?= h($flash['subMessage']) ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<main class="main-content">

