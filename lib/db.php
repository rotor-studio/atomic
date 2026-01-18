<?php

function db()
{
    static $pdo = null;

    if ($pdo === null) {
        $path = __DIR__ . '/../data/app.sqlite';
        $isNew = !file_exists($path);
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($isNew) {
            init_db($pdo);
        } else {
            ensure_schema($pdo);
        }
    }

    return $pdo;
}

function init_db(PDO $pdo)
{
    $pdo->exec('
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            avatar_path TEXT,
            is_superuser INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL
        );
    ');

    $stmt = $pdo->prepare('
        INSERT INTO users (email, password_hash, created_at)
        VALUES (:email, :hash, :created_at)
    ');
    $stmt->execute([
        ':email' => 'demo@atomic.local',
        ':hash' => password_hash('password123', PASSWORD_DEFAULT),
        ':created_at' => date('c'),
    ]);

    $pdo->exec('UPDATE users SET is_superuser = 1 WHERE email = "demo@atomic.local"');

    $pdo->exec('
        CREATE TABLE habits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            frequency_type TEXT NOT NULL,
            frequency_target INTEGER,
            two_minute_version TEXT NOT NULL,
            plan_b TEXT NOT NULL,
            friction_note TEXT,
            notification_enabled INTEGER NOT NULL DEFAULT 0,
            notification_time_local TEXT,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ');

    $pdo->exec('
        CREATE TABLE entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            habit_id INTEGER NOT NULL,
            entry_date TEXT NOT NULL,
            value INTEGER NOT NULL,
            note TEXT,
            UNIQUE (habit_id, entry_date),
            FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE
        );
    ');

    $pdo->exec('
        CREATE TABLE suggestions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            habit_id INTEGER NOT NULL,
            week_start TEXT NOT NULL,
            mechanism_id TEXT NOT NULL,
            text TEXT NOT NULL,
            UNIQUE (habit_id, week_start),
            FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE
        );
    ');

    $pdo->exec('
        CREATE TABLE login_attempts (
            ip TEXT PRIMARY KEY,
            attempts INTEGER NOT NULL DEFAULT 0,
            last_attempt TEXT NOT NULL
        );
    ');
}

function ensure_schema(PDO $pdo)
{
    $result = $pdo->query("PRAGMA table_info(users)")->fetchAll();
    $columns = array_map(fn($row) => $row['name'], $result);
    if (!in_array('avatar_path', $columns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN avatar_path TEXT');
    }
    if (!in_array('is_superuser', $columns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN is_superuser INTEGER NOT NULL DEFAULT 0');
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_superuser = 1')->fetchColumn();
    if ($count === 0) {
        $pdo->exec('UPDATE users SET is_superuser = 1 WHERE id = (SELECT id FROM users ORDER BY created_at ASC LIMIT 1)');
    }

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS login_attempts (
            ip TEXT PRIMARY KEY,
            attempts INTEGER NOT NULL DEFAULT 0,
            last_attempt TEXT NOT NULL
        );
    ');
}
