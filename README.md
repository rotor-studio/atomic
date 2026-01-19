# Atomic Habit Tracker (PHP)

Aplicacion privada y minimalista para habitos, inspirada en Atomic Habits. Sin gamificacion, sin ruido, solo tres vistas claras.

## Requisitos
- PHP 8.2+
- Servidor Apache con mod_rewrite
- SQLite (incluido en PHP)

## Instalacion
1) Copia la carpeta en tu servidor (por ejemplo en `/tu-sitio/atomic`).
2) Asegura permisos de escritura para `data/` y `uploads/`.
3) Verifica que Apache tenga mod_rewrite activo (usa `.htaccess`).
4) Ajusta `RewriteBase` en `.htaccess` si la carpeta no es `/atomic`.
5) Accede a la URL del proyecto en el navegador.

La base SQLite se crea automaticamente en `data/app.sqlite`.
El primer usuario se marca como admin. Si existe `hola@romantorre.net`, se fuerza como admin.

## Estructura
- `index.php`: router y acciones.
- `lib/`: base de datos, auth, helpers.
- `views/`: vistas HTML.
- `assets/style.css`: estilo tipografico minimal.
- `data/app.sqlite`: base de datos.
- `uploads/`: avatares (no se versiona).

## Acciones clave
- Habitos: crear, editar, pausar, borrar.
- Entradas diarias: No / Parcial / Hecho.
- Ajustes: cerrar sesion, resetear datos, borrar cuenta.
- Administracion (solo admin): crear usuarios, roles, borrar usuarios, reset de contrasena.

## Seguridad basica
- Hash seguro con `password_hash`.
- Consultas preparadas (PDO).
- CSRF tokens en formularios.
- Rate limit basico para login.
- Cookies de sesion endurecidas.
- Validacion basica de imagenes para avatar.
- `.htaccess` en `data/` y `uploads/` para bloquear acceso directo.
