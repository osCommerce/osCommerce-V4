<?php

spl_autoload_register (function($class){
    $dir = dirname(__FILE__)."/";
    
    if (file_exists($dir . str_replace('\\', "/", $class) .".php")){
     require_once($dir . str_replace('\\', "/", $class) .".php");
    }
});