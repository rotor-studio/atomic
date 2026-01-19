<?php
$isEdit = $mode === 'edit';
$habit = $habit ?: [
    'id' => 0,
    'name' => '',
    'frequency_type' => 'DAILY',
    'frequency_target' => 3,
    'two_minute_version' => '',
    'plan_b' => '',
    'friction_note' => '',
    'notification_enabled' => 0,
    'notification_time_local' => '',
    'is_active' => 1,
];
?>

<div class="stack">
  <div class="stack panel compact">
    <div class="label"><?php echo $isEdit ? 'editar habito' : 'nuevo habito'; ?></div>
    <div class="title"><?php echo $isEdit ? e($habit['name']) : 'anadir habito'; ?></div>
  </div>

  <form class="stack panel form-card" method="post" action="<?php echo e(url('habitos/guardar')); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" name="id" value="<?php echo e($habit['id']); ?>">

    <div class="section" data-section="basico">
      <div class="section-toggle" data-toggle>
        <div class="section-title">basico</div>
        <span class="chevron">⌄</span>
      </div>
      <div class="section-body">
        <label class="field">
          <span class="label">nombre</span>
          <input class="input" type="text" name="name" value="<?php echo e($habit['name']); ?>" required>
        </label>
        <div class="toggle-group">
          <label class="toggle-option">
            <input type="radio" name="frequency_type" value="DAILY" <?php echo $habit['frequency_type'] === 'DAILY' ? 'checked' : ''; ?>>
            diario
          </label>
          <label class="toggle-option">
            <input type="radio" name="frequency_type" value="WEEKLY" <?php echo $habit['frequency_type'] === 'WEEKLY' ? 'checked' : ''; ?>>
            objetivo semanal
          </label>
        </div>
        <label class="field">
          <span class="label">veces por semana</span>
          <input class="input" type="number" min="1" max="7" name="frequency_target" value="<?php echo e($habit['frequency_target']); ?>">
        </label>
      </div>
    </div>

    <div class="section collapsed" data-section="plan">
      <div class="section-toggle" data-toggle>
        <div class="section-title">plan</div>
        <span class="chevron">⌄</span>
      </div>
      <div class="section-body">
        <label class="field">
          <span class="label">version de 2 minutos</span>
          <input class="input" type="text" name="two_minute_version" value="<?php echo e($habit['two_minute_version']); ?>" required>
        </label>
        <label class="field">
          <span class="label">plan b</span>
          <input class="input" type="text" name="plan_b" value="<?php echo e($habit['plan_b']); ?>" required>
        </label>
        <label class="field">
          <span class="label">nota de friccion (opcional)</span>
          <input class="input" type="text" name="friction_note" value="<?php echo e($habit['friction_note']); ?>">
        </label>
      </div>
    </div>

    <div class="section collapsed" data-section="recordatorio">
      <div class="section-toggle" data-toggle>
        <div class="section-title">recordatorio</div>
        <span class="chevron">⌄</span>
      </div>
      <div class="section-body">
        <label class="toggle-option">
          <input type="checkbox" name="notification_enabled" <?php echo $habit['notification_enabled'] ? 'checked' : ''; ?>>
          activar recordatorio
        </label>
      <label class="field">
        <span class="label">horas del recordatorio</span>
        <input class="input" type="text" name="notification_time_local" value="<?php echo e($habit['notification_time_local']); ?>" placeholder="08:00, 14:30">
      </label>
      </div>
    </div>

    <div class="section collapsed" data-section="estado">
      <div class="section-toggle" data-toggle>
        <div class="section-title">estado</div>
        <span class="chevron">⌄</span>
      </div>
      <div class="section-body">
        <label class="toggle-option">
          <input type="checkbox" name="is_active" <?php echo $habit['is_active'] ? 'checked' : ''; ?>>
          activo
        </label>
      </div>
    </div>

    <div class="button-row">
      <button class="btn primary-action" type="submit"><?php echo $isEdit ? 'guardar cambios' : 'crear habito'; ?></button>
      <a class="btn" href="<?php echo e(url('habitos')); ?>">volver a habitos</a>
    </div>
  </form>
  <script src="<?php echo e(url('assets/form.js')); ?>"></script>
</div>
