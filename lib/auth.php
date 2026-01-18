<?php

require_once __DIR__ . '/db.php';

function current_user()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, email, avatar_path, is_superuser FROM users WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login()
{
    if (!current_user()) {
        header('Location: /login');
        exit;
    }
}

function login_user($userId)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user()
{
    unset($_SESSION['user_id']);
}
