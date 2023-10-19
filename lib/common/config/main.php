<?php

return [
    'timeZone' => date_default_timezone_get(),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'bootstrap' => require __DIR__ . '/load-bootstrap-classes.php',
    'components' => [
      'cache' => [
          'class' => 'yii\caching\FileCache',
          'cachePath' => '@frontend/runtime/cache'
          /*
          'class' => 'yii\redis\Cache',
          'keyPrefix' => '<YOURSITE CODE>_', // change prefix!!! a unique key prefix required
          'redis' => [
              'hostname' => 'localhost',
              'port' => 6379,
              'database' => 0,
          ]
          */
      ],
      'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host='.DB_SERVER.';dbname='.DB_DATABASE,
        'username' => DB_SERVER_USERNAME,
        'password' => DB_SERVER_PASSWORD,
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCache' => 'cache',
        'enableLogging' => false,
        'enableProfiling' => false,
        'attributes' => (
            ( defined('DB_SSL_CERT') && DB_SSL_CERT!='' && is_file(DB_SSL_CERT) )?
                [
                    \PDO::MYSQL_ATTR_SSL_KEY =>DB_SSL_CERT,
                    \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,

                ]:
                []
        ),
        'on afterOpen' => function($event) {
          /*
          *hot fix for mysql mode. remove when active record complete
          */
          $event->sender->createCommand("SET SESSION sql_mode = '';")->execute();
          /*
          *end of hot fix
          */
          $event->sender->createCommand("SET SESSION time_zone = '".date('P')."'")->execute();
        },
      ],
        'mutex' => [
            'class' => 'yii\mutex\FileMutex',
        ], 
        /*'cache' => [
            'class' => 'yii\caching\FileCache',
        ],*/
        /*'cache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 60,
                ],
            ],
        ],*/
        /*'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],*/
        'platform' => [
            'class' => 'common\classes\platform',
        ],

        /*'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['user'],
        ],*/
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            // all Auth clients will use this configuration for HTTP client:
            /*'httpClient' => [
                'transport' => 'yii\httpclient\CurlTransport',
            ],*/
            'clients' => [],
        ],
        'PropsHelper'=>[
            'class' => '\common\helpers\Props'
        ],
        'log' => [
            'flushInterval' => YII_DEBUG ? 1 : 100,
            'targets' => [
                [
                    'class' =>  '\common\classes\TlMainFileLogWriter', //'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:404', 'sql_error', 'dbg*'],
                ],
                [
                    'categories' => ['sql_error'],
                    'logFile' => '@app/runtime/logs/sql_error.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => ['_SERVER'],
                ],
                'list' => [
                    'logFile' => '@app/runtime/logs/list.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['yii\web\HttpException:404', 'sql_error', 'dbg*'],
                    'logVars' => [],
                ],
                'datasource' => [
                    'categories' => ['datasource'],
                    'logFile' => '@app/runtime/logs/datasource.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => [],
                ],
                'dbg' => [
                    'categories' => ['dbg*'],
                    'logFile' => '@app/runtime/logs/dbg.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => [],
                    'exportInterval' => 1,
                ],
                'supplier' => [
                    'categories' => ['supplier*'],
                    'logFile' => '@app/runtime/logs/supplier.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => [],
                    'exportInterval' => 1,
                ],
                [
                    'categories' => ['yii\web\HttpException:404'],
                    'logFile' => '@app/runtime/logs/404_error.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET'],
                ],
            ],
        ],
      'mediaManager' => [
          'class' => 'common\classes\MediaManager',
      ],
      'settings' => [
          'class' => 'common\components\Settings', 
          'sessionKey' => 'primary-settings',
      ],
    ],
    'modules' => [
        
    ]
];
