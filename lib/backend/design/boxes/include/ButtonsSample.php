<?php

if (($params['type'] ?? null) == 'product') {
    $widgets[] = array(
        'name' => 'product\ButtonsSample',
        'title' => \common\helpers\Php8::getConst('REQUEST_FOR_SAMPLE_BUTTON'),
        'description' => '',
        'type' => 'product',
        'class' => ''
    );
}
