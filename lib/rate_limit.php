<?php

require_once __DIR__ . '/db.php';

function rate_limit_check($ip)
{
    $stmt = db()->prepare('SELECT attempts, last_attempt FROM login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);
    $row = $stmt->fetch();

    if (!$row) {
        return true;
    }

    $last = strtotime($row['last_attempt']);
    if (time() - $last > 600) {
        return true;
    }

    return (int) $row['attempts'] < 5;
}

function rate_limit_fail($ip)
{
    $stmt = db()->prepare('SELECT attempts FROM login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);
    $row = $stmt->fetch();

    if ($row) {
        $stmt = db()->prepare('UPDATE login_attempts SET attempts = attempts + 1, last_attempt = :last WHERE ip = :ip');
        $stmt->execute([':last' => date('c'), ':ip' => $ip]);
        return;
    }

    $stmt = db()->prepare('INSERT INTO login_attempts (ip, attempts, last_attempt) VALUES (:ip, :attempts, :last)');
    $stmt->execute([':ip' => $ip, ':attempts' => 1, ':last' => date('c')]);
}

function rate_limit_success($ip)
{
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);
}
