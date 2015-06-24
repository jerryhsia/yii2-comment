<?php

$config = [
    'id' => 'app',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'fuck the code',
        ],
        'errorHandler' => [
            'errorAction' => 'public/error',
        ],
    ]
];

$config = yii\helpers\ArrayHelper::merge(
    $config,
    require(__DIR__ . '/common.php'),
    require(__DIR__ . '/config.php')
);

return $config;
