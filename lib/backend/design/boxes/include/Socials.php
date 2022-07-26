<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
	$widgets[] = array('name' => 'Socials', 'title' => BOX_HEADING_SOCIALS, 'description' => '', 'type' => 'general', 'class' => '');
}