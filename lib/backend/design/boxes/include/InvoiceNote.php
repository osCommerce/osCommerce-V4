<?php
if ($type == 'invoice'){
  $widgets[] = array('name' => 'invoice\InvoiceNote', 'title' => 'Invoice Note', 'description' => '', 'type' => 'invoice', 'class' => 'invoice');
}
if ($type == 'packingslip'){
  $widgets[] = array('name' => 'invoice\InvoiceNote', 'title' => 'Invoice Note', 'description' => '', 'type' => 'packingslip', 'class' => 'packingslip');
}