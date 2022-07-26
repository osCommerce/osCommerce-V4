<?php

if (($params['type'] ?? null) == 'product') {
    $widgets[] = array(
        'name' => 'product\ButtonsQuote',
        'title' => REQUEST_FOR_QUOTE_BUTTON,
        'description' => '',
        'type' => 'product',
        'class' => ''
    );
}
