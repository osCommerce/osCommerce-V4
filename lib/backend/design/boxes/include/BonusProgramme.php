<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
  $widgets[] = array('name' => 'BonusProgramme', 'title' => TEXT_PROMO_BONUS_PROGRAMME, 'description' => '', 'type' => 'general');
}