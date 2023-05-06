<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'common\components\SessionFlowConsole'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'urlManager' => [
            'class' => 'app\components\AdminUrlManager',
            //'class' => 'yii\web\UrlManager',
            'hostInfo' => HTTP_SERVER,
            'baseUrl' => rtrim(DIR_WS_HTTP_CATALOG, '/'),
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
        ],
        'session' => [
            'class' => 'yii\web\Session',
        ],
        'storage' => [
            'class' => '\common\services\storages\ObjectStorage'
        ],
        'user' => [
            'class' => 'yii\web\User',
            'enableSession' => false,
            'identityClass' => 'common\components\Customer',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'categories' => ['sql_error'],
                    'logFile' => '@app/runtime/logs/sql_error.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => ['_SERVER'],
                ],
                'datasource' => [
                    'prefix' => function ($message) {
                        return "[-][-][".YII_BEGIN_TIME."]";
                    }
                ],
            ],
        ],
        'errorHandler' => [
            'class' => '\common\classes\TlErrorHandlerConsole',
        ],
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'templateFile' => '@app/views/migration.php',
        ],
    ],
    'params' => $params,
];
