<?php

if ($type == 'email'){
  $widgets[] = array('name' => 'Html_box', 'title' => 'html', 'description' => '', 'type' => 'email', 'class' => 'html');
}

if ($type == 'invoice'){
  $widgets[] = array('name' => 'Html_box', 'title' => 'html', 'description' => '', 'type' => 'invoice', 'class' => 'html');
}

if ($type == 'packingslip'){
  $widgets[] = array('name' => 'Html_box', 'title' => 'html', 'description' => '', 'type' => 'packingslip', 'class' => 'html');
}

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
  $widgets[] = array('name' => 'Html_box', 'title' => 'html', 'description' => '', 'type' => 'general', 'class' => 'html');
}