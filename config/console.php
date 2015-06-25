<?php
$config = [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\commands'
];

$config = yii\helpers\ArrayHelper::merge(
    $config,
    require(__DIR__ . '/common.php'),
    require(__DIR__ . '/config.php')
);

return $config;
