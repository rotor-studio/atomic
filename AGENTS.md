# Repository Guidelines

## Project Structure
- `index.php`: router + acciones (login, habitos, entradas, ajustes).
- `lib/`: DB, auth, CSRF, helpers.
- `views/`: vistas HTML.
- `assets/`: estilos.
- `data/`: SQLite local.
- `uploads/`: avatares.

## Running Locally
- PHP built-in server: `php -S localhost:8000`
- Abrir `http://localhost:8000`

## Coding Style
- PHP clasico, sin framework.
- HTML limpio, sin tarjetas ni bordes.
- Mantener textos en espanol simple.

## Data Rules
- Maximo 3 habitos activos.
- Parcial cuenta como 0.5.
- Sin rachas infinitas.
- Admin solo visible para superusuario.
