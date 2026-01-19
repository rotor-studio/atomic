<div class="stack">
  <div class="stack panel">
    <div class="label">semana de <?php echo e($week_start); ?></div>
    <div class="title">semana</div>
  </div>

  <?php if (!$habits): ?>
    <div class="muted panel">anade habitos para ver el patron semanal.</div>
  <?php endif; ?>

  <?php foreach ($habits as $habit): ?>
    <?php
      $values = [];
      foreach ($dates as $d) {
          $values[] = (int) ($entries[$habit['id']][$d]['value'] ?? 0);
      }
      $target = $habit['frequency_type'] === 'DAILY' ? 7 : (int) $habit['frequency_target'];
      $total = 0;
      foreach ($values as $v) {
          $total += score_from_value($v);
      }
    ?>
    <div class="panel week-card">
      <div>
        <div class="title habit-week-title" style="font-size:18px;"><?php echo e($habit['name']); ?></div>
        <div class="muted week-meta">
          <?php echo $habit['frequency_type'] === 'DAILY' ? 'diario' : e($habit['frequency_target']) . '/semana'; ?>
        </div>
        <div class="muted week-total"><?php echo number_format($total, 1); ?> / <?php echo e($target); ?> esta semana</div>
      </div>
      <div class="row">
        <?php foreach ($values as $v): ?>
          <?php
            $dot = $v === 2 ? '🙂' : ($v === 1 ? '😐' : '😕');
          ?>
          <span class="dot"><?php echo $dot; ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
