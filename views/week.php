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
    <div class="stack panel">
      <div>
        <div class="title" style="font-size:18px;"><?php echo e($habit['name']); ?></div>
        <div class="muted"><?php echo number_format($total, 1); ?> / <?php echo e($target); ?> esta semana</div>
      </div>
      <div class="row">
        <?php foreach ($values as $v): ?>
          <?php
            $dot = $v === 2 ? '●' : ($v === 1 ? '◐' : '○');
          ?>
          <span class="dot"><?php echo $dot; ?></span>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($suggestions[$habit['id']])): ?>
        <div class="muted"><?php echo e($suggestions[$habit['id']]); ?></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
