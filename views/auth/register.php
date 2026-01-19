<div class="stack">
  <div class="stack panel">
    <div class="label">comenzar</div>
    <div class="title">crear cuenta</div>
    <div class="muted">seguimiento privado sin ruido social.</div>
  </div>
  <form class="stack panel" method="post" action="<?php echo e(url('registro')); ?>">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <label class="stack">
      <span class="label">correo</span>
      <input class="input" type="email" name="email" required>
    </label>
    <label class="stack">
      <span class="label">contrasena</span>
      <input class="input" type="password" name="password" minlength="8" required>
    </label>
    <button class="btn" type="submit">crear cuenta</button>
  </form>
  <div class="muted">
    ya tienes cuenta? <a href="<?php echo e(url('login')); ?>">iniciar sesion</a>.
  </div>
</div>
