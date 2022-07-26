<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
  $widgets[] = array('name' => 'HeaderStock', 'title' => TEXT_HEADER_STOCK, 'description' => '', 'type' => 'general', 'class' => 'headerStock');
}