<div class="stack">
  <div class="stack panel">
    <div class="label">bienvenido</div>
    <div class="title">iniciar sesion</div>
    <div class="muted">espacio silencioso para sistemas pequenos.</div>
  </div>
  <form class="stack panel" method="post" action="/login">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <label class="stack">
      <span class="label">correo</span>
      <input class="input" type="email" name="email" required>
    </label>
    <label class="stack">
      <span class="label">contrasena</span>
      <input class="input" type="password" name="password" minlength="8" required>
    </label>
    <button class="btn" type="submit">entrar</button>
  </form>
  <div class="muted">
    nuevo por aqui? <a href="/registro">crear cuenta</a>.
  </div>
</div>
