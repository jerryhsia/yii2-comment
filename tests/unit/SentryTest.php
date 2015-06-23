<?php
use Codeception\TestCase\Test;

class SentryTest extends Test
{
    public function testSentry()
    {
        /* @var $sentry jerryhsia\sentry\Sentry */
        $sentry = Yii::$app->errorHandler;
        $sentry->captureException(new Exception('2222'));
    }
}
