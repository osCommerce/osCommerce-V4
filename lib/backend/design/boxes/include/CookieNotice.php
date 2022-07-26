<?php
if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
    $widgets[] = array(
        'name' => 'CookieNotice',
        'title' => 'Cookie Notice',
        'description' => '',
        'type' => 'general',
        'class' => ''
    );
}