<?php

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function today_local()
{
    return date('Y-m-d');
}

function week_start($date)
{
    $dt = new DateTime($date);
    $day = (int) $dt->format('N');
    $dt->modify('-' . ($day - 1) . ' days');
    return $dt->format('Y-m-d');
}

function score_from_value($value)
{
    if ($value === 2) {
        return 1.0;
    }
    if ($value === 1) {
        return 0.5;
    }
    return 0.0;
}

function weighted_completion($values)
{
    if (!$values) {
        return 0;
    }
    $total = 0;
    foreach ($values as $value) {
        $total += score_from_value((int) $value);
    }
    return $total / count($values);
}

function trend_label($previous, $current)
{
    $delta = $current - $previous;
    if (abs($delta) < 0.03) {
        return 'estable';
    }
    return $delta > 0 ? 'sube' : 'baja';
}
