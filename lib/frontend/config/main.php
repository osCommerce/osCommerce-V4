<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [],
    'controllerNamespace' => 'frontend\controllers',
    
    'name' => 'Trueloaded New',
    'defaultRoute' => 'index/index',

    'modules' => [
        'suppliers-area' => [
            'class' => 'suppliersarea\SupplierModule',
            'components' => [
                'user' => [
                    'class' => 'suppliersarea\components\User',
                    'config' => [
                        'identityClass' => 'suppliersarea\components\SupplierIdentity',
                        'enableAutoLogin' => true,
                        'idParam' => '__sid',
                        'loginUrl'=>['/index/login'],
                        ],
                ],
            ]
        ]
    ],
    'components' => [
        'storage' => [
            'class' => '\common\services\storages\SessionStorage'
        ],
        'request' => [
            'class' => '\frontend\components\Request'
        ],
        'urlManager' => [
            'class' => 'app\components\TlUrlManager',
            'hostInfo' => HTTP_SERVER,
            'baseUrl' => rtrim(DIR_WS_HTTP_CATALOG, '/'),

            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'app\components\TlUrlRule', /* 'controller' => 'site' */],
                '<controller:[\w-]+>'=>'<controller>/index',
                '/' => 'index',
            ],
        ],

        'view' => [
            'class' => 'common\components\View',
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
/*                
    '@app/views' => [
        '@app/themes/christmas', // <-- @app/themes/christmas/site/index.php or @app/themes/basic/site/index.php, depending on which themed file exists.
        '@app/themes/basic',
    ],
*/
                    '@app/views' => '@app/themes/basic',
                    '@app/modules' => '@app/themes/basic/modules', // <-- It will allow you to theme @app/modules/blog/views/comment/index.php into @app/themes/basic/modules/blog/views/comment/index.php.
                    '@app/widgets' => '@app/themes/basic/widgets', // <-- This will allow you to theme @app/widgets/currency/views/index.php into @app/themes/basic/widgets/currency/index.php.
                ],
            ],

        ],
        
        'user' => [
            'identityClass' => 'common\components\Customer',
          // remember me start
            /*
            'enableAutoLogin' => true,
            'class' => 'common\components\RememberMe',
            'autoLoginDuration' => 3600*24*7, // for a week
            'autoRenewCookie' => false, //don't use with current implementation
          */
            // remember me end
            'loginUrl'=>['/account/login'],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:404', 'sql_error'],
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

        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js'=>[]
                ],
            ],
        ],
    ],
    'params' => $params,
];
