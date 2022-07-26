<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
  $widgets[] = array('name' => 'PromoCode', 'title' => TEXT_PROMO_CODE, 'description' => '', 'type' => 'general');
}