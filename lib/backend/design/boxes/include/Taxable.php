<?php

$params['type'] = $params['type'] ?? null;
if ($params['type'] != 'email' && $params['type'] != 'invoice' && $params['type'] != 'packingslip' && $params['type'] != 'pdf' && $params['type'] != 'orders') {
  $widgets[] = array('name' => 'Taxable', 'title' => TEXT_INC_VAT, 'description' => '', 'type' => 'general', 'class' => '');
}
