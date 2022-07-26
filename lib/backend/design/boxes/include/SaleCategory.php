<?php

$params['type'] = $params['type'] ?? null;
if ($params['type'] != 'email' && $params['type'] != 'invoice' && $params['type'] != 'packingslip' && $params['type'] != 'pdf' && $params['type'] != 'orders') {
  $widgets[] = array('name' => 'SaleCategory', 'title' => TEXT_SALE_CATEGORY, 'description' => '', 'type' => 'general', 'class' => 'specials-products');
}
