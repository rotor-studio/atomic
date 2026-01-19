<div class="stack">
  <div class="stack panel">
    <div class="label"><?php echo count(array_filter($habits, fn($h) => $h['is_active'])); ?> habitos activos</div>
    <div class="title">habitos</div>
  </div>

  <?php if (!$habits): ?>
    <div class="muted panel">no hay habitos aun.</div>
  <?php endif; ?>

  <?php foreach ($habits as $habit): ?>
    <div class="stack panel">
      <div class="row" style="justify-content: space-between;">
        <div>
          <div class="title" style="font-size:18px;"><?php echo e($habit['name']); ?></div>
          <div class="muted">
            <?php echo $habit['frequency_type'] === 'DAILY' ? 'diario' : e($habit['frequency_target']) . '/semana'; ?>
          </div>
        </div>
        <div class="row">
          <form method="post" action="<?php echo e(url('habitos/activar')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?php echo e($habit['id']); ?>">
            <input type="hidden" name="is_active" value="<?php echo $habit['is_active'] ? 0 : 1; ?>">
            <button class="btn" type="submit"><?php echo $habit['is_active'] ? 'pausar' : 'reanudar'; ?></button>
          </form>
          <a class="btn" href="<?php echo e(url('habitos/editar')); ?>?id=<?php echo e($habit['id']); ?>">editar</a>
          <form method="post" action="<?php echo e(url('habitos/borrar')); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?php echo e($habit['id']); ?>">
            <button class="btn" type="submit">borrar</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <a class="btn" href="<?php echo e(url('habitos/nuevo')); ?>">+ anadir habito</a>
</div>
