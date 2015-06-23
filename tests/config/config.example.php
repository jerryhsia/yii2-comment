<?php

return [
    'id' => 'yii2-plugins',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection'
        ],
        'errorHandler' => [
            'class' => 'jerryhsia\sentry\Sentry',
            'environment' => 'test',
            'dsn' => ''
        ]
    ]
];
