<?php

    function stripGWInvalidChars($strToCheck) {
	$toReplace = array("<","&");
	$replaceWith = array("","&amp;");
	$cleanString = str_replace($toReplace, $replaceWith, $strToCheck);
	return $cleanString;
    }
