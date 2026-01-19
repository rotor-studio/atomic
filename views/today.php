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
    <?php
      $value = $entry['value'] ?? 0;
      $statusEmoji = $value === 2 ? '🙂' : ($value === 1 ? '😐' : '😕');
    ?>
    <details class="stack panel habit-details">
      <summary class="habit-summary" style="list-style:none; cursor:pointer;">
        <div>
          <div class="habit-title"><?php echo e($habit['name']); ?></div>
          <div class="habit-meta">
            <?php echo $habit['frequency_type'] === 'DAILY' ? 'diario' : e($habit['frequency_target']) . '/semana'; ?>
          </div>
        </div>
        <div class="habit-preview">
          <span class="dot"><?php echo $statusEmoji; ?></span>
        </div>
      </summary>
      <form class="row habit-actions" method="post" action="<?php echo e(url('entrada/guardar')); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="habit_id" value="<?php echo e($habit['id']); ?>">
        <input type="hidden" name="entry_date" value="<?php echo e($date); ?>">
        <button class="btn" type="submit" name="value" value="0">no</button>
        <button class="btn" type="submit" name="value" value="1">parcial</button>
        <button class="btn" type="submit" name="value" value="2">hecho</button>
      </form>
      <div class="habit-detail-lines">
        <div><span class="detail-label">2 minutos:</span> <?php echo e($habit['two_minute_version']); ?></div>
        <div><span class="detail-label">plan b:</span> <?php echo e($habit['plan_b']); ?></div>
      </div>
      <form class="stack" method="post" action="<?php echo e(url('entrada/guardar')); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="habit_id" value="<?php echo e($habit['id']); ?>">
        <input type="hidden" name="entry_date" value="<?php echo e($date); ?>">
        <input type="hidden" name="value" value="<?php echo e($value); ?>">
        <label class="stack note-stack">
          <span class="label note-label"><span class="detail-label">nota</span></span>
          <textarea class="input note-field" name="note" rows="2" placeholder="opcional .."><?php echo e($note); ?></textarea>
        </label>
        <div class="button-row split">
          <button class="btn" type="submit">guardar nota</button>
          <div class="button-row right">
            <a class="btn" href="<?php echo e(url('habitos/editar')); ?>?id=<?php echo e($habit['id']); ?>">editar</a>
          </div>
        </div>
      </form>
    </details>
  <?php endforeach; ?>
</div>
