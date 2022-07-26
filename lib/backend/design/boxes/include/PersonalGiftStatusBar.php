<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
    $widgets[] = array(
        'name' => 'PersonalGiftStatusBar',
        'title' => "Personal Gift Status Bars",
        'description' => '',
        'type' => 'general',
        'class' => ''
    );
}