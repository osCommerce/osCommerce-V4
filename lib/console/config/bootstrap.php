<?php
Yii::setAlias('common', dirname(dirname(__DIR__)) . '/common');
Yii::setAlias('frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('console', dirname(__DIR__));
Yii::setAlias('webroot', dirname(dirname(dirname(__DIR__))));

\Yii::$container->setSingleton('currencies', '\common\classes\Currencies');

require_once(dirname(dirname(dirname(__DIR__))).'/includes/application_top_console.php');