<div class="stack">
  <div class="stack panel">
    <div class="label">ajustes</div>
    <div class="title">cuenta</div>
  </div>

  <div class="stack panel">
    <div class="label">mi perfil</div>

    <form class="stack" method="post" action="/ajustes/password">
      <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
      <div class="label">cambiar contrasena</div>
      <label class="field">
        <span class="label">contrasena actual</span>
        <input class="input" type="password" name="current_password" minlength="8" required>
      </label>
      <label class="field">
        <span class="label">nueva contrasena</span>
        <input class="input" type="password" name="new_password" minlength="8" required>
      </label>
      <label class="field">
        <span class="label">confirmar contrasena</span>
        <input class="input" type="password" name="confirm_password" minlength="8" required>
      </label>
      <button class="btn primary-action" type="submit">guardar contrasena</button>
    </form>

    <form class="stack" method="post" action="/ajustes/avatar" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
      <div class="label">avatar</div>
      <label class="field">
        <span class="label">imagen</span>
        <input class="input" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" required>
      </label>
      <button class="btn" type="submit">subir avatar</button>
    </form>

    <div class="button-row">
      <form method="post" action="/logout">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <button class="btn" type="submit">cerrar sesion</button>
      </form>
      <form method="post" action="/ajustes/reset">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <button class="btn" type="submit">resetear datos</button>
      </form>
      <form method="post" action="/ajustes/borrar">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <button class="btn" type="submit">borrar cuenta</button>
      </form>
    </div>
  </div>

    <?php if (!empty($user['is_superuser'])): ?>
      <div class="stack panel">
        <div class="label">administracion del sistema</div>

        <form class="stack panel" method="post" action="/ajustes/create-user">
          <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
          <div class="label">crear usuario</div>
          <label class="field">
            <span class="label">correo</span>
            <input class="input" type="email" name="email" required>
          </label>
          <label class="field">
            <span class="label">contrasena</span>
            <input class="input" type="password" name="password" minlength="8" required>
          </label>
          <label class="toggle-option">
            <input type="checkbox" name="is_superuser">
            admin
          </label>
          <button class="btn primary-action" type="submit">crear usuario</button>
        </form>

      <?php
        $stmt = db()->prepare('SELECT id, email, is_superuser FROM users ORDER BY created_at ASC');
        $stmt->execute();
        $users = $stmt->fetchAll();
      ?>
        <div class="stack">
          <div class="label">usuarios</div>
          <?php foreach ($users as $row): ?>
            <form class="toggle-option" method="post" action="/ajustes/role">
              <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
              <input type="hidden" name="user_id" value="<?php echo e($row['id']); ?>">
              <div class="muted"><?php echo e($row['email']); ?></div>
              <div style="margin-left:auto;">
                <label class="row">
                  <input type="checkbox" name="is_superuser" <?php echo $row['is_superuser'] ? 'checked' : ''; ?> <?php echo (int) $row['id'] === (int) $user['id'] ? 'disabled' : ''; ?>>
                  admin
                </label>
              </div>
              <button class="btn" type="submit" <?php echo (int) $row['id'] === (int) $user['id'] ? 'disabled' : ''; ?>>guardar</button>
            </form>
            <?php if ((int) $row['id'] !== (int) $user['id']): ?>
              <form class="toggle-option" method="post" action="/ajustes/user-password">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="user_id" value="<?php echo e($row['id']); ?>">
                <label class="field" style="margin:0;">
                  <span class="label">nueva contrasena</span>
                  <input class="input" type="password" name="new_password" minlength="8" required>
                </label>
                <button class="btn" type="submit">reiniciar</button>
              </form>
            <?php endif; ?>
            <?php if ((int) $row['id'] !== (int) $user['id']): ?>
              <form class="toggle-option" method="post" action="/ajustes/user-delete" onsubmit="return confirm('borrar usuario?');">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="user_id" value="<?php echo e($row['id']); ?>">
                <div class="muted">borrar usuario</div>
                <button class="btn" type="submit">borrar</button>
              </form>
            <?php endif; ?>
          <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
