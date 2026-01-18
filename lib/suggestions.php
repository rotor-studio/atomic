<?php

function generate_suggestion($habitName, $twoMinuteVersion, $values, $notificationEnabled)
{
    $total = 0;
    foreach ($values as $value) {
        $total += score_from_value((int) $value);
    }
    $average = $values ? $total / count($values) : 0;

    if ($average < 0.5) {
        return [
            'mechanism_id' => 'MAKE_EASIER',
            'text' => 'Hazlo mas facil: deja visible la version de 2 minutos ("' . $twoMinuteVersion . '").',
        ];
    }

    if (!$notificationEnabled) {
        return [
            'mechanism_id' => 'MAKE_OBVIOUS',
            'text' => 'Hazlo mas obvio: coloca una senal antes de "' . $habitName . '".',
        ];
    }

    return [
        'mechanism_id' => 'MAKE_SATISFYING',
        'text' => 'Hazlo mas satisfactorio: cierra con un gesto breve despues de "' . $habitName . '".',
    ];
}
