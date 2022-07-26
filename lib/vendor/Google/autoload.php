<?php

/**
 * THIS FILE IS FOR BACKWARDS COMPATIBLITY ONLY
 *
 * If you were not already including this file in your project, please ignore it
 */
/*
$file = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($file)) {
  $exception = 'This library must be installed via composer or by downloading the full package.';
  $exception .= ' See the instructions at https://github.com/google/google-api-php-client#installation.';
  throw new Exception($exception);
}

$error = 'google-api-php-client\'s autoloader was moved to vendor/autoload.php in 2.0.0. This ';
$error .= 'redirect will be removed in 2.1. Please adjust your code to use the new location.';
trigger_error($error, E_USER_DEPRECATED);

require_once $file;
*/
$vendorDir = dirname(__FILE__);
spl_autoload_register(function($class) use ($vendorDir) {
    if (strpos($class, 'Google_') !== false){
        $ex = explode("\\", $class);
        unset($ex[0]);
        if (count($ex)){
            $ex = explode("_", $ex[1]);
            unset($ex[0]);
            if (count($ex)){
                $file = implode(DIRECTORY_SEPARATOR , $ex);
                if (file_exists($vendorDir . DIRECTORY_SEPARATOR . $file . '.php')){
                    require_once($vendorDir . DIRECTORY_SEPARATOR .$file . '.php');
                    $class = $file;
                }
            }
        }
    }
    /*if (strpos($class, 'GuzzleHttp') !== false){
        $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
        if (file_exists($vendorDir . DIRECTORY_SEPARATOR . $class .'.php')){
            require_once $vendorDir . DIRECTORY_SEPARATOR . $class .'.php';
        } else{
            $class = str_replace("GuzzleHttp", "", $class);
            if (file_exists($vendorDir . DIRECTORY_SEPARATOR . $class .'.php')){
                require_once $vendorDir . DIRECTORY_SEPARATOR . $class .'.php';
            }
        }
    }
    if (strpos($class, 'Psr\\') !== false){
        $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
        if (file_exists($vendorDir . DIRECTORY_SEPARATOR . $class .'.php')){
            require_once $vendorDir . DIRECTORY_SEPARATOR . $class .'.php';
        }
        //var_dump($vendorDir . DIRECTORY_SEPARATOR . $class .'.php');die;
    }*/
    if (strpos($class, 'Google\\') !== false){
        $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
        $file = $vendorDir . DIRECTORY_SEPARATOR . $class .'.php';
        $class = str_replace("Google/Google", 'Google', $file);
        if (file_exists($class)){
            require_once $class;
        }
        //var_dump($class);die;
    }
    
});
require_once($vendorDir . DIRECTORY_SEPARATOR . 'Logger.php');
//require_once($vendorDir . DIRECTORY_SEPARATOR . 'Psr7/functions.php');
//require_once($vendorDir . DIRECTORY_SEPARATOR . 'GuzzleHttp/functions.php');
//require_once($vendorDir . DIRECTORY_SEPARATOR . 'GuzzleHttp/Promise/functions.php');