<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
	$widgets[] = array('name' => 'CumulativeDiscount', 'title' => TEXT_ACCUMULATIVE_DISCOUNT, 'description' => '', 'type' => 'general', 'class' => '');
}