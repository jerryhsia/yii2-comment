<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', dirname(__DIR__));

new yii\console\Application(yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/config/config.php'),
    []
));
