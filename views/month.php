<div class="stack">
  <div class="stack panel">
    <div class="label"><?php echo e($start); ?> a <?php echo e($end); ?></div>
    <div class="title">mes</div>
  </div>

  <?php if (!$habits): ?>
    <div class="muted panel">anade habitos para ver tu tendencia mensual.</div>
  <?php endif; ?>

  <?php foreach ($habits as $habit): ?>
    <?php
      $habitEntries = $entries[$habit['id']] ?? [];
      $currentValues = [];
      $previousValues = [];
      foreach ($habitEntries as $entry) {
          if ($entry['entry_date'] >= $start) {
              $currentValues[] = (int) $entry['value'];
          } else {
              $previousValues[] = (int) $entry['value'];
          }
      }
      $currentScore = weighted_completion($currentValues);
      $previousScore = weighted_completion($previousValues);
      $trend = trend_label($previousScore, $currentScore);
    ?>
    <div class="stack panel">
      <div class="title" style="font-size:18px;"><?php echo e($habit['name']); ?></div>
      <div class="muted"><?php echo number_format($currentScore * 100, 0); ?>% consistencia · <?php echo e($trend); ?></div>
    </div>
  <?php endforeach; ?>
</div>
