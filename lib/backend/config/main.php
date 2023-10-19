<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerNamespace' => 'backend\controllers',
    
    'name' => 'Backend',
    'defaultRoute' => 'index/index',
    
    'modules' => [],
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js'=>[]
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'class' => 'app\components\AdminUrlManager',
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
//                ['class' => 'yii\web\UrlRule'],
                '403' => 'index/error-403'
            ]
/*
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => 'site'],
            ],
*/
        ],
        'storage' => [
            'class' => '\common\services\storages\DbStorage'
        ],
        'view' => [
            'class' => 'yii\web\View',
            'defaultExtension' => 'tpl',
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                    'options' => [
                        'error_reporting' => E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_NOTICE,
                    ],
                ],
            ],

            'theme' => [
                'basePath' => '@app/themes/basic',
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                
    '@app/views' => [
        '@app/themes/basic',
        '@app/themes/basic',
    ],

                    '@app/modules' => '@app/themes/basic/modules', // <-- It will allow you to theme @app/modules/blog/views/comment/index.php into @app/themes/basic/modules/blog/views/comment/index.php.
                    '@app/widgets' => '@app/themes/basic/widgets', // <-- This will allow you to theme @app/widgets/currency/views/index.php into @app/themes/basic/widgets/currency/index.php.
                ],
            ],

        ],
        
        'user' => [
            'identityClass' => 'common\components\Customer',
            'enableAutoLogin' => true,
        ],
        'platform' => [
          'class' => 'common\classes\platform',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
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
                    'logVars' => ['_GET','_POST'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'index/error',
            'class' => '\common\classes\TlErrorHandler',
        ],
    ],
    'params' => $params,
];
