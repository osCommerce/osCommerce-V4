<?php

if (($type ?? null) == 'product') {
    $widgets[] = array(
        'name' => 'product\BonusPoints',
        'title' => TEXT_BONUS_POINTS,
        'description' => '',
        'type' => 'product'
    );
}
if ($type !== 'email' && $type !== 'invoice' && $type !== 'packingslip' && $type !== 'pdf' && $type !== 'orders') {
    $widgets[] = [
        'name' => 'account\BonusPointsConverter',
        'title' => BONUS_POINTS_CONVERTER,
        'description' => '',
        'type' => 'general',
        'class' => ''
    ];
}
