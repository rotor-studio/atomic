# Atomic Habit Tracker (PHP)

Aplicacion privada y minimalista para habitos, inspirada en Atomic Habits. Sin gamificacion, sin ruido, solo tres vistas claras.

## Requisitos
- PHP 8.2+
- Servidor Apache con mod_rewrite
- SQLite (incluido en PHP)

## Instalacion
1) Copia la carpeta en tu servidor (por ejemplo en `/tu-sitio/atomic`).
2) Asegura permisos de escritura para `data/`.
3) Verifica que Apache tenga mod_rewrite activo (usa `.htaccess`).
4) Accede a `/atomic` en el navegador.

La base SQLite se crea automaticamente en `data/app.sqlite`.
Usuario demo (se crea al iniciar por primera vez):
- Correo: `demo@atomic.local`
- Contrasena: `password123`

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

## Seguridad basica
- Hash seguro con `password_hash`.
- Consultas preparadas (PDO).
- CSRF tokens en formularios.
 - Validacion basica de imagenes para avatar.
