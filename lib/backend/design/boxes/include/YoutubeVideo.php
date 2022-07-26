<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
	$widgets[] = array('name' => 'YoutubeVideo', 'title' => TEXT_YOUTUBE_VIDEO, 'description' => '', 'type' => 'general', 'class' => '');
}