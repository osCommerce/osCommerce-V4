<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
  $widgets[] = array('name' => 'Contacts', 'title' => TEXT_CONTACTS, 'description' => '', 'type' => 'general', 'class' => '');
}