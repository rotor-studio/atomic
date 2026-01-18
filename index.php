<?php

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_start();

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/suggestions.php';
require_once __DIR__ . '/lib/rate_limit.php';

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if ($path === '') {
    header('Location: /hoy');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post($path);
}

switch ($path) {
    case 'login':
        render('auth/login');
        break;
    case 'registro':
        render('auth/register');
        break;
    case 'logout':
        header('Location: /ajustes');
        exit;
    case 'hoy':
        require_login();
        render('today', get_today_data());
        break;
    case 'semana':
        require_login();
        render('week', get_week_data());
        break;
    case 'mes':
        require_login();
        render('month', get_month_data());
        break;
    case 'habitos':
        require_login();
        render('habits', get_habits_data());
        break;
    case 'habitos/nuevo':
        require_login();
        render('habit_form', ['mode' => 'new', 'habit' => null]);
        break;
    case 'habitos/editar':
        require_login();
        $habit = get_habit((int) ($_GET['id'] ?? 0));
        if (!$habit) {
            header('Location: /habitos');
            exit;
        }
        render('habit_form', ['mode' => 'edit', 'habit' => $habit]);
        break;
    case 'ajustes':
        require_login();
        render('settings');
        break;
    case 'guia':
        require_login();
        render('guide');
        break;
    default:
        http_response_code(404);
        echo 'No encontrado.';
        break;
}

function render($view, $data = [])
{
    $user = current_user();
    extract($data);
    include __DIR__ . '/views/layout.php';
}

function handle_post($path)
{
    verify_csrf();

    switch ($path) {
        case 'login':
            handle_login();
            break;
        case 'registro':
            handle_register();
            break;
        case 'logout':
            logout_user();
            header('Location: /login');
            exit;
        case 'habitos/guardar':
            require_login();
            save_habit();
            break;
        case 'habitos/borrar':
            require_login();
            delete_habit();
            break;
        case 'habitos/activar':
            require_login();
            toggle_habit();
            break;
        case 'entrada/guardar':
            require_login();
            save_entry();
            break;
        case 'ajustes/reset':
            require_login();
            reset_account();
            break;
        case 'ajustes/borrar':
            require_login();
            delete_account();
            break;
        case 'ajustes/avatar':
            require_login();
            upload_avatar();
            break;
        case 'ajustes/password':
            require_login();
            change_password();
            break;
        case 'ajustes/role':
            require_login();
            update_role();
            break;
        case 'ajustes/user-delete':
            require_login();
            delete_user();
            break;
        case 'ajustes/create-user':
            require_login();
            create_user();
            break;
        case 'ajustes/user-password':
            require_login();
            reset_user_password();
            break;
    }
}

function handle_login()
{
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if (!rate_limit_check($ip)) {
        $_SESSION['flash'] = 'demasiados intentos. espera unos minutos.';
        header('Location: /login');
        exit;
    }

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        rate_limit_fail($ip);
        $_SESSION['flash'] = 'Correo o contrasena incorrectos.';
        header('Location: /login');
        exit;
    }

    rate_limit_success($ip);
    login_user($user['id']);
    header('Location: /hoy');
    exit;
}

function handle_register()
{
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password || strlen($password) < 8) {
        $_SESSION['flash'] = 'Datos no validos.';
        header('Location: /registro');
        exit;
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['flash'] = 'No se pudo crear la cuenta.';
        header('Location: /registro');
        exit;
    }

    $stmt = db()->prepare('INSERT INTO users (email, password_hash, created_at) VALUES (:email, :hash, :created)');
    $stmt->execute([
        ':email' => $email,
        ':hash' => password_hash($password, PASSWORD_DEFAULT),
        ':created' => date('c'),
    ]);

    login_user((int) db()->lastInsertId());
    header('Location: /hoy');
    exit;
}

function get_today_data()
{
    $user = current_user();
    $date = today_local();
    $stmt = db()->prepare('
        SELECT * FROM habits WHERE user_id = :user_id AND is_active = 1 ORDER BY created_at ASC LIMIT 3
    ');
    $stmt->execute([':user_id' => $user['id']]);
    $habits = $stmt->fetchAll();

    $entries = [];
    if ($habits) {
        $ids = array_column($habits, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM entries WHERE entry_date = ? AND habit_id IN ($placeholders)");
        $stmt->execute(array_merge([$date], $ids));
        foreach ($stmt->fetchAll() as $entry) {
            $entries[$entry['habit_id']] = $entry;
        }
    }

    return ['habits' => $habits, 'entries' => $entries, 'date' => $date];
}

function get_week_data()
{
    $user = current_user();
    $date = today_local();
    $weekStart = week_start($date);
    $dates = [];
    for ($i = 0; $i < 7; $i++) {
        $dates[] = date('Y-m-d', strtotime($weekStart . ' +' . $i . ' days'));
    }

    $stmt = db()->prepare('SELECT * FROM habits WHERE user_id = :user_id AND is_active = 1 ORDER BY created_at ASC');
    $stmt->execute([':user_id' => $user['id']]);
    $habits = $stmt->fetchAll();

    $entries = [];
    if ($habits) {
        $ids = array_column($habits, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM entries WHERE entry_date BETWEEN ? AND ? AND habit_id IN ($placeholders)");
        $stmt->execute(array_merge([$dates[0], $dates[6]], $ids));
        foreach ($stmt->fetchAll() as $entry) {
            $entries[$entry['habit_id']][$entry['entry_date']] = $entry;
        }
    }

    $suggestions = [];
    foreach ($habits as $habit) {
        $values = [];
        foreach ($dates as $d) {
            $value = $entries[$habit['id']][$d]['value'] ?? 0;
            $values[] = (int) $value;
        }

        $stmt = db()->prepare('SELECT * FROM suggestions WHERE habit_id = :habit AND week_start = :week_start');
        $stmt->execute([':habit' => $habit['id'], ':week_start' => $weekStart]);
        $existing = $stmt->fetch();
        if (!$existing) {
            $suggestion = generate_suggestion(
                $habit['name'],
                $habit['two_minute_version'],
                $values,
                (int) $habit['notification_enabled'] === 1
            );
            $stmt = db()->prepare('
                INSERT INTO suggestions (habit_id, week_start, mechanism_id, text)
                VALUES (:habit, :week_start, :mechanism_id, :text)
            ');
            $stmt->execute([
                ':habit' => $habit['id'],
                ':week_start' => $weekStart,
                ':mechanism_id' => $suggestion['mechanism_id'],
                ':text' => $suggestion['text'],
            ]);
            $suggestions[$habit['id']] = $suggestion['text'];
        } else {
            $suggestions[$habit['id']] = $existing['text'];
        }
    }

    return [
        'habits' => $habits,
        'entries' => $entries,
        'dates' => $dates,
        'week_start' => $weekStart,
        'suggestions' => $suggestions,
    ];
}

function get_month_data()
{
    $user = current_user();
    $end = today_local();
    $start = date('Y-m-d', strtotime($end . ' -29 days'));
    $previousStart = date('Y-m-d', strtotime($end . ' -59 days'));

    $stmt = db()->prepare('SELECT * FROM habits WHERE user_id = :user_id AND is_active = 1 ORDER BY created_at ASC');
    $stmt->execute([':user_id' => $user['id']]);
    $habits = $stmt->fetchAll();

    $entries = [];
    if ($habits) {
        $ids = array_column($habits, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT * FROM entries WHERE entry_date BETWEEN ? AND ? AND habit_id IN ($placeholders)");
        $stmt->execute(array_merge([$previousStart, $end], $ids));
        foreach ($stmt->fetchAll() as $entry) {
            $entries[$entry['habit_id']][] = $entry;
        }
    }

    return [
        'habits' => $habits,
        'entries' => $entries,
        'start' => $start,
        'end' => $end,
    ];
}

function get_habits_data()
{
    $user = current_user();
    $stmt = db()->prepare('SELECT * FROM habits WHERE user_id = :user_id ORDER BY created_at ASC');
    $stmt->execute([':user_id' => $user['id']]);
    return ['habits' => $stmt->fetchAll()];
}

function get_habit($id)
{
    $user = current_user();
    $stmt = db()->prepare('SELECT * FROM habits WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $id, ':user_id' => $user['id']]);
    return $stmt->fetch();
}

function save_habit()
{
    $user = current_user();
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $frequencyType = $_POST['frequency_type'] ?? 'DAILY';
    $frequencyTarget = (int) ($_POST['frequency_target'] ?? 0);
    $twoMinute = trim($_POST['two_minute_version'] ?? '');
    $planB = trim($_POST['plan_b'] ?? '');
    $friction = trim($_POST['friction_note'] ?? '');
    $notificationEnabled = isset($_POST['notification_enabled']) ? 1 : 0;
    $notificationTime = trim($_POST['notification_time_local'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$twoMinute || !$planB) {
        $_SESSION['flash'] = 'Faltan datos obligatorios.';
        header('Location: ' . ($id ? '/habitos/editar?id=' . $id : '/habitos/nuevo'));
        exit;
    }

    if ($frequencyType === 'WEEKLY' && $frequencyTarget < 1) {
        $_SESSION['flash'] = 'Objetivo semanal invalido.';
        header('Location: ' . ($id ? '/habitos/editar?id=' . $id : '/habitos/nuevo'));
        exit;
    }

    if ($isActive === 1) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM habits WHERE user_id = :user_id AND is_active = 1' . ($id ? ' AND id != :id' : ''));
        $params = [':user_id' => $user['id']];
        if ($id) {
            $params[':id'] = $id;
        }
        $stmt->execute($params);
        if ((int) $stmt->fetchColumn() >= 3) {
            $_SESSION['flash'] = 'Solo puedes tener hasta 3 habitos activos.';
            header('Location: ' . ($id ? '/habitos/editar?id=' . $id : '/habitos/nuevo'));
            exit;
        }
    }

    if ($id) {
        $stmt = db()->prepare('
            UPDATE habits SET
                name = :name,
                frequency_type = :frequency_type,
                frequency_target = :frequency_target,
                two_minute_version = :two_minute_version,
                plan_b = :plan_b,
                friction_note = :friction_note,
                notification_enabled = :notification_enabled,
                notification_time_local = :notification_time_local,
                is_active = :is_active
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute([
            ':name' => $name,
            ':frequency_type' => $frequencyType,
            ':frequency_target' => $frequencyType === 'WEEKLY' ? $frequencyTarget : null,
            ':two_minute_version' => $twoMinute,
            ':plan_b' => $planB,
            ':friction_note' => $friction,
            ':notification_enabled' => $notificationEnabled,
            ':notification_time_local' => $notificationEnabled ? $notificationTime : null,
            ':is_active' => $isActive,
            ':id' => $id,
            ':user_id' => $user['id'],
        ]);
    } else {
        $stmt = db()->prepare('
            INSERT INTO habits
            (user_id, name, frequency_type, frequency_target, two_minute_version, plan_b, friction_note, notification_enabled, notification_time_local, is_active, created_at)
            VALUES
            (:user_id, :name, :frequency_type, :frequency_target, :two_minute_version, :plan_b, :friction_note, :notification_enabled, :notification_time_local, :is_active, :created_at)
        ');
        $stmt->execute([
            ':user_id' => $user['id'],
            ':name' => $name,
            ':frequency_type' => $frequencyType,
            ':frequency_target' => $frequencyType === 'WEEKLY' ? $frequencyTarget : null,
            ':two_minute_version' => $twoMinute,
            ':plan_b' => $planB,
            ':friction_note' => $friction,
            ':notification_enabled' => $notificationEnabled,
            ':notification_time_local' => $notificationEnabled ? $notificationTime : null,
            ':is_active' => $isActive,
            ':created_at' => date('c'),
        ]);
    }

    header('Location: /habitos');
    exit;
}

function delete_habit()
{
    $user = current_user();
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare('DELETE FROM habits WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $id, ':user_id' => $user['id']]);
    header('Location: /habitos');
    exit;
}

function toggle_habit()
{
    $user = current_user();
    $id = (int) ($_POST['id'] ?? 0);
    $isActive = (int) ($_POST['is_active'] ?? 0);

    if ($isActive === 1) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM habits WHERE user_id = :user_id AND is_active = 1 AND id != :id');
        $stmt->execute([':user_id' => $user['id'], ':id' => $id]);
        if ((int) $stmt->fetchColumn() >= 3) {
            $_SESSION['flash'] = 'Solo puedes tener hasta 3 habitos activos.';
            header('Location: /habitos');
            exit;
        }
    }

    $stmt = db()->prepare('UPDATE habits SET is_active = :active WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':active' => $isActive, ':id' => $id, ':user_id' => $user['id']]);
    header('Location: /habitos');
    exit;
}

function save_entry()
{
    $user = current_user();
    $habitId = (int) ($_POST['habit_id'] ?? 0);
    $date = $_POST['entry_date'] ?? today_local();
    $value = (int) ($_POST['value'] ?? 0);
    $note = trim($_POST['note'] ?? '');

    $stmt = db()->prepare('SELECT id FROM habits WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $habitId, ':user_id' => $user['id']]);
    if (!$stmt->fetch()) {
        header('Location: /hoy');
        exit;
    }

    $stmt = db()->prepare('
        INSERT INTO entries (habit_id, entry_date, value, note)
        VALUES (:habit_id, :entry_date, :value, :note)
        ON CONFLICT(habit_id, entry_date)
        DO UPDATE SET value = :value, note = :note
    ');
    $stmt->execute([
        ':habit_id' => $habitId,
        ':entry_date' => $date,
        ':value' => $value,
        ':note' => $note,
    ]);

    header('Location: /hoy');
    exit;
}

function reset_account()
{
    $user = current_user();
    $stmt = db()->prepare('DELETE FROM habits WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $user['id']]);
    header('Location: /hoy');
    exit;
}

function delete_account()
{
    $user = current_user();
    $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $user['id']]);
    logout_user();
    header('Location: /login');
    exit;
}

function change_password()
{
    $user = current_user();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || strlen($new) < 8 || $new !== $confirm) {
        $_SESSION['flash'] = 'contrasena invalida.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = :id');
    $stmt->execute([':id' => $user['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        $_SESSION['flash'] = 'contrasena actual incorrecta.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $stmt->execute([
        ':hash' => password_hash($new, PASSWORD_DEFAULT),
        ':id' => $user['id'],
    ]);

    $_SESSION['flash'] = 'contrasena actualizada.';
    header('Location: /ajustes');
    exit;
}

function update_role()
{
    $user = current_user();
    if (!$user['is_superuser']) {
        $_SESSION['flash'] = 'no autorizado.';
        header('Location: /ajustes');
        exit;
    }

    $targetId = (int) ($_POST['user_id'] ?? $user['id']);
    $isSuper = isset($_POST['is_superuser']) ? 1 : 0;

    if ($targetId === (int) $user['id'] && $isSuper === 0) {
        $_SESSION['flash'] = 'no puedes quitar tu propio rol de admin.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('UPDATE users SET is_superuser = :role WHERE id = :id');
    $stmt->execute([':role' => $isSuper, ':id' => $targetId]);

    $_SESSION['flash'] = 'rol actualizado.';
    header('Location: /ajustes');
    exit;
}

function delete_user()
{
    $user = current_user();
    if (!$user['is_superuser']) {
        $_SESSION['flash'] = 'no autorizado.';
        header('Location: /ajustes');
        exit;
    }

    $targetId = (int) ($_POST['user_id'] ?? 0);
    if ($targetId === (int) $user['id']) {
        $_SESSION['flash'] = 'no puedes borrar tu propia cuenta desde aqui.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $targetId]);

    $_SESSION['flash'] = 'usuario borrado.';
    header('Location: /ajustes');
    exit;
}

function create_user()
{
    $user = current_user();
    if (!$user['is_superuser']) {
        $_SESSION['flash'] = 'no autorizado.';
        header('Location: /ajustes');
        exit;
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $isSuper = isset($_POST['is_superuser']) ? 1 : 0;

    if (!$email || strlen($password) < 8) {
        $_SESSION['flash'] = 'datos no validos.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['flash'] = 'el correo ya existe.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('
        INSERT INTO users (email, password_hash, is_superuser, created_at)
        VALUES (:email, :hash, :role, :created_at)
    ');
    $stmt->execute([
        ':email' => $email,
        ':hash' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => $isSuper,
        ':created_at' => date('c'),
    ]);

    $_SESSION['flash'] = 'usuario creado.';
    header('Location: /ajustes');
    exit;
}

function reset_user_password()
{
    $user = current_user();
    if (!$user['is_superuser']) {
        $_SESSION['flash'] = 'no autorizado.';
        header('Location: /ajustes');
        exit;
    }

    $targetId = (int) ($_POST['user_id'] ?? 0);
    $new = $_POST['new_password'] ?? '';

    if ($targetId === (int) $user['id']) {
        $_SESSION['flash'] = 'usa el cambio de contrasena de tu perfil.';
        header('Location: /ajustes');
        exit;
    }

    if (strlen($new) < 8) {
        $_SESSION['flash'] = 'contrasena invalida.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $stmt->execute([
        ':hash' => password_hash($new, PASSWORD_DEFAULT),
        ':id' => $targetId,
    ]);

    $_SESSION['flash'] = 'contrasena reiniciada.';
    header('Location: /ajustes');
    exit;
}
function upload_avatar()
{
    $user = current_user();
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['flash'] = 'No se pudo subir la imagen.';
        header('Location: /ajustes');
        exit;
    }

    $file = $_FILES['avatar'];
    if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['flash'] = 'imagen demasiado grande.';
        header('Location: /ajustes');
        exit;
    }
    $info = getimagesize($file['tmp_name']);
    if (!$info) {
        $_SESSION['flash'] = 'Archivo invalido.';
        header('Location: /ajustes');
        exit;
    }

    if ($info[0] > 2000 || $info[1] > 2000) {
        $_SESSION['flash'] = 'imagen demasiado grande.';
        header('Location: /ajustes');
        exit;
    }

    $mime = $info['mime'];
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        $_SESSION['flash'] = 'Formato no permitido.';
        header('Location: /ajustes');
        exit;
    }

    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $name = 'avatar_' . $user['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $target = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        $_SESSION['flash'] = 'No se pudo guardar la imagen.';
        header('Location: /ajustes');
        exit;
    }

    $stmt = db()->prepare('UPDATE users SET avatar_path = :path WHERE id = :id');
    $stmt->execute([
        ':path' => '/uploads/' . $name,
        ':id' => $user['id'],
    ]);

    $_SESSION['flash'] = 'Avatar actualizado.';
    header('Location: /ajustes');
    exit;
}
