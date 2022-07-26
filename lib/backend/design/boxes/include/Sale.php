<?php

$params['type'] = $params['type'] ?? null;
if ($params['type'] != 'email' && $params['type'] != 'invoice' && $params['type'] != 'packingslip' && $params['type'] != 'pdf' && $params['type'] != 'orders') {
  $widgets[] = array('name' => 'Sale', 'title' => TEXT_SALE, 'description' => '', 'type' => 'general', 'class' => 'specials-products');
}
