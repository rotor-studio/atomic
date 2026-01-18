<div class="stack">
  <div class="stack panel">
    <div class="label"><?php echo e($date); ?></div>
    <div class="title">hoy</div>
  </div>

  <?php if (!$habits): ?>
    <div class="muted panel">anade hasta tres habitos para enfocar tu dia.</div>
  <?php endif; ?>

  <?php foreach ($habits as $habit): ?>
    <?php
      $entry = $entries[$habit['id']] ?? null;
      $value = $entry['value'] ?? 0;
      $note = $entry['note'] ?? '';
      $nowTime = date('H:i');
      $reminderTimes = array_filter(array_map('trim', explode(',', (string) $habit['notification_time_local'])));
      $reminder = false;
      if ($habit['notification_enabled'] && $reminderTimes && !$entry) {
          foreach ($reminderTimes as $time) {
              if ($time <= $nowTime) {
                  $reminder = true;
                  break;
              }
          }
      }
    ?>
    <?php if ($reminder): ?>
      <div class="muted">recordatorio: <?php echo e($habit['name']); ?></div>
    <?php endif; ?>
    <div class="stack panel">
      <div>
        <div class="title" style="font-size:18px;"><?php echo e($habit['name']); ?></div>
        <div class="muted">
          <?php echo $habit['frequency_type'] === 'DAILY' ? 'diario' : e($habit['frequency_target']) . '/semana'; ?>
        </div>
      </div>
      <form class="row" method="post" action="/entrada/guardar">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="habit_id" value="<?php echo e($habit['id']); ?>">
        <input type="hidden" name="entry_date" value="<?php echo e($date); ?>">
        <button class="btn" type="submit" name="value" value="0">no</button>
        <button class="btn" type="submit" name="value" value="1">parcial</button>
        <button class="btn" type="submit" name="value" value="2">hecho</button>
      </form>
      <div class="muted">
        2 minutos: <?php echo e($habit['two_minute_version']); ?><br>
        plan b: <?php echo e($habit['plan_b']); ?>
      </div>
      <form class="stack" method="post" action="/entrada/guardar">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="habit_id" value="<?php echo e($habit['id']); ?>">
        <input type="hidden" name="entry_date" value="<?php echo e($date); ?>">
        <input type="hidden" name="value" value="<?php echo e($value); ?>">
        <label class="stack">
          <span class="label">nota</span>
          <input class="input" type="text" name="note" value="<?php echo e($note); ?>" placeholder="nota opcional">
        </label>
        <button class="btn" type="submit">guardar nota</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>
