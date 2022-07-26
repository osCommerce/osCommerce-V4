<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'IqdADG0qMX8Lf9H0fn6wlBJgQsj-XM0H',
        ],
    ],
];

if (false && !YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    if (!defined('TEP_DB_TRACK_TIME')) define('TEP_DB_TRACK_TIME',true);
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        //'class' => 'common\modules\debug\Module',
        'class' => 'yii\debug\Module',
        'panels' => [
            'oldDb' => ['class' => 'common\modules\debug\panels\OldDbPanel'],
        ],
        'allowedIPs' => ['172.18.0.*','127.0.0.1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
