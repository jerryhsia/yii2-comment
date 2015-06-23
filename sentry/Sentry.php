<?php
namespace jerryhsia\sentry;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\web\Application;
use yii\web\ErrorHandler;

class Sentry extends ErrorHandler
{
    const MAX_MESSAGE_LENGTH = 2048;
    const MAX_TAG_KEY_LENGTH = 32;
    const MAX_TAG_VALUE_LENGTH = 200;
    const MAX_CULPRIT_LENGTH = 200;

    public $dsn;

    public $environment = 'dev';

    public $enabledEnvironments = ['production', 'test', 'dev', 'staging'];

    /**
     * @var array options to pass to the Raven client with the following structure:
     *   logger: (string) name of the logger
     *   auto_log_stacks: (bool) whether to automatically log stacktraces
     *   name: (string) name of the server
     *   site: (string) name of the installation
     *   tags: (array) key/value pairs that describe the event
     *   trace: (bool) whether to send stacktraces
     *   timeout: (int) timeout when connecting to Sentry (in seconds)
     *   exclude: (array) class names of exceptions to exclude
     *   shift_vars: (bool) whether to shift variables when creating a backtrace
     *   processors: (array) list of data processors
     */
    public $options = [];

    /**
     * @var array extra variables to send with exceptions to Sentry.
     */
    public $extraVariables = [];

    /**
     * Holds all event ids that were reported during this request
     */
    protected $_loggedEventIds = [];

    /**
     * @param mixed $loggedEventIds
     */
    public function setLoggedEventIds($loggedEventIds)
    {
        $this->_loggedEventIds = $loggedEventIds;
    }

    /**
     * @return mixed
     */
    public function getLoggedEventIds()
    {
        return $this->_loggedEventIds;
    }

    /** @var \Raven_Client */
    private $_client;

    /**
     * Initializes the error handler.
     */
    public function init()
    {
        $this->_client = $this->createClient();
        Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'onShutdown']);
    }

    /**
     * Logs an exception to Sentry.
     * @param Exception $exception exception to log.
     * @param array $options capture options that can contain the following structure:
     *   culprit: (string) function call that caused the event
     *   extra: (array) additional metadata to store with the event
     * @param string $logger name of the logger.
     * @param mixed $context exception context.
     * @return string event id (or null if not captured).
     * @throws Exception if logging the exception fails.
     */
    public function captureException($exception, $options = [], $logger = '', $context = null)
    {
        if (!$this->isEnvironmentEnabled()) {
            return null;
        }
        $this->processOptions($options);
        $eventId = $this->_client->getIdent(
            $this->_client->captureException($exception, $options, $logger, $context)
        );
        try {

        } catch (\Exception $e) {
            if (YII_DEBUG) {
                throw new Exception('SentryClient failed to log exception: ' . $exception->getMessage(), (int)$exception->getCode());
            } else {
                $this->log($e->getMessage(), Logger::LEVEL_ERROR);
                throw new Exception('SentryClient failed to log exception.', (int)$e->getCode());
            }
        }
        $this->_loggedEventIds[] = $eventId;
        $this->log(sprintf('Exception logged to Sentry with event id: %d', $eventId), Logger::LEVEL_INFO);
        return $eventId;
    }

    /**
     * Logs a message to Sentry.
     * @param string $message message to log.
     * @param array $params message parameters.
     * @param array $options capture options that can contain the following structure:
     *   culprit: (string) function call that caused the event
     *   extra: (array) additional metadata to store with the event
     * @param bool $stack whether to send the stack trace.
     * @param mixed $context message context.
     * @return string event id (or null if not captured).
     * @throws Exception if logging the message fails.
     */
    public function captureMessage($message, $params = [], $options = [], $stack = false, $context = null)
    {
        if (strlen($message) > self::MAX_MESSAGE_LENGTH) {
            throw new InvalidParamException(sprintf(
                'SentryClient cannot send messages that contain more than %d characters.',
                self::MAX_MESSAGE_LENGTH
            ));
        }
        if (!$this->isEnvironmentEnabled()) {
            return null;
        }
        $this->processOptions($options);
        try {
            $eventId = $this->_client->getIdent(
                $this->_client->captureMessage($message, $params, $options, $stack, $context)
            );
        } catch (\Exception $e) {
            if (YII_DEBUG) {
                throw new Exception('SentryClient failed to log message: ' . $e->getMessage(), (int)$e->getCode());
            } else {
                $this->log($e->getMessage(), Logger::LEVEL_ERROR);
                throw new Exception('SentryClient failed to log message.', (int)$e->getCode());
            }
        }
        $this->_loggedEventIds[] = $eventId;
        $this->log(sprintf('Message logged to Sentry with event id: %d', $eventId), Logger::LEVEL_INFO);
        return $eventId;
    }

    /**
     * Logs a query to Sentry.
     * @param string $query query to log.
     * @param integer $level log level.
     * @param string $engine name of the sql driver.
     * @return string event id (or null if not captured).
     * @throws Exception if logging the query fails.
     */
    public function captureQuery($query, $level = Logger::LEVEL_INFO, $engine = '')
    {
        if (!$this->isEnvironmentEnabled()) {
            return null;
        }
        try {
            $eventId = $this->_client->getIdent(
                $this->_client->captureQuery($query, $level, $engine)
            );
        } catch (\Exception $e) {
            if (YII_DEBUG) {
                throw new Exception('SentryClient failed to log query: ' . $e->getMessage(), (int)$e->getCode());
            } else {
                $this->log($e->getMessage(), Logger::LEVEL_ERROR);
                throw new Exception('SentryClient failed to log query.', (int)$e->getCode());
            }
        }
        $this->_loggedEventIds[] = $eventId;
        $this->log(sprintf('Query logged to Sentry with event id: %d', $eventId), Logger::LEVEL_INFO);
        return $eventId;
    }

    /**
     * Returns whether the active environment is enabled.
     * @return bool the result.
     */
    protected function isEnvironmentEnabled()
    {
        return in_array($this->environment, $this->enabledEnvironments);
    }

    /**
     * Processes the given options.
     * @param array $options the options to process.
     */
    protected function processOptions(&$options)
    {
        if (!isset($options['extra'])) {
            $options['extra'] = [];
        }

        $options['extra'] = ArrayHelper::merge($this->extraVariables, $options['extra']);
    }

    /**
     * Writes a message to the log.
     * @param string $message message to log.
     * @param string $level log level.
     */
    protected function log($message, $level)
    {
        Yii::getLogger()->log($message, $level, 'ecom.sentry');
    }

    /**
     * Creates a Raven client
     * @return Raven_Client client instance.
     * @throws Exception if the client could not be created.
     */
    protected function createClient()
    {
        $options = ArrayHelper::merge(
            [
                'logger' => 'yii',
                'tags' => [
                    'environment' => $this->environment,
                    'php_version' => phpversion(),
                ],
            ],
            $this->options
        );

        try {
            $this->checkTags($options['tags']);
            return new \Raven_Client($this->dsn, $options);
        } catch (\Exception $e) {
            if (YII_DEBUG) {
                throw new Exception('SentryClient failed to create client: ' . $e->getMessage(), (int)$e->getCode());
            } else {
                $this->log($e->getMessage(), Logger::LEVEL_ERROR);
                throw new Exception('SentryClient failed to create client.', (int)$e->getCode());
            }
        }
    }

    /**
     * Checks that the given tags are valid.
     * @param array $tags tags to check.
     * @throws Exception if a tag is invalid.
     */
    protected function checkTags($tags)
    {
        foreach ($tags as $key => $value) {
            if (strlen($key) > self::MAX_TAG_KEY_LENGTH) {
                throw new InvalidConfigException(sprintf(
                    'SentryClient does not allow tag keys that contain more than %d characters.',
                    self::MAX_TAG_KEY_LENGTH
                ));
            }

            if (strlen($value) > self::MAX_TAG_VALUE_LENGTH) {
                throw new InvalidConfigException(sprintf(
                    'SentryClient does not allow tag values that contain more than %d characters.',
                    self::MAX_TAG_VALUE_LENGTH
                ));
            }
        }
    }

    /**
     * Invoked on shutdown to attempt to capture any unhandled errors.
     */
    public function onShutdown()
    {
        $error = error_get_last();
        if ($error !== null) {
            $errors = array(
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING,
                E_STRICT
            );
            if (in_array($error['type'], $errors)) {
                $this->captureException(
                    $this->createErrorException($error['message'], $error['type'], $error['file'], $error['line'])
                );
            }
        }
    }

    public function handleError($code, $message, $file, $line)
    {
        if (error_reporting() & $code) {
            $this->captureException(
                $this->createErrorException($message, $code, $file, $line)
            );
        }

        parent::handleError($code, $message, $file, $line);
    }

    public function handleException($exception)
    {
        $this->captureException($exception);
        parent::handleException($exception);
    }

    protected function createErrorException($message, $code, $file, $line)
    {
        return new \ErrorException($message, $code, 0/* will be resolved */, $file, $line);
    }
}
