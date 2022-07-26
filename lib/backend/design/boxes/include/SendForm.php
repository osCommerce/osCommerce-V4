<?php

$params['type'] = $params['type'] ?? null;
if ($params['type'] != 'email' && $params['type'] != 'invoice' && $params['type'] != 'packingslip' && $params['type'] != 'pdf' && $params['type'] != 'orders') {
    $widgets[] = array('name' => 'SendForm', 'title' => SEND_FORM, 'description' => '', 'type' => 'general', 'class' => 'contact-form');
}