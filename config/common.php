<?php
$config = [
    'language' => 'zh-CN',
    'params' => require(__DIR__ . '/params.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ]
    ]
];

return $config;
