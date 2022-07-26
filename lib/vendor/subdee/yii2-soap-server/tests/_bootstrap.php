<?php
error_reporting(-1);
define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);

// This is global bootstrap for autoloading 
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

/**
 * Auoloaded for classes
 * @param string $class_name
 */
function autoload_class_dir($class_name)
{
    $fileName = 'Classes/' . $class_name . '.php';
    if (file_exists($fileName)) {
        /** @noinspection PhpIncludeInspection */
        require_once $fileName;
    }
}

spl_autoload_register('autoload_class_dir');
