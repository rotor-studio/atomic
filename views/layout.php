<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Atomic</title>
  <link rel="stylesheet" href="<?php echo e(url('assets/style.css')); ?>">
</head>
<body>
  <div class="container">
    <?php if ($user): ?>
      <div class="nav">
        <div class="row">
          <a href="<?php echo e(url('hoy')); ?>">hoy</a>
          <a href="<?php echo e(url('semana')); ?>">semana</a>
          <a href="<?php echo e(url('mes')); ?>">mes</a>
          <a href="<?php echo e(url('guia')); ?>">guia</a>
        </div>
        <div class="row">
          <?php if (!empty($user['avatar_path'])): ?>
            <img class="avatar" src="<?php echo e(url(ltrim($user['avatar_path'], '/'))); ?>" alt="avatar">
          <?php endif; ?>
          <a href="<?php echo e(url('habitos/nuevo')); ?>">+ anadir habito</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($flash): ?>
      <div class="panel">
        <p class="muted"><?php echo e($flash); ?></p>
      </div>
    <?php endif; ?>

    <?php include __DIR__ . '/' . $view . '.php'; ?>

    <?php if ($user): ?>
      <div class="footer">
        <a href="<?php echo e(url('ajustes')); ?>">ajustes</a> ·
        creado por <a href="https://romantorre.net" target="_blank" rel="noopener">romantorre.net</a> 2026
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
