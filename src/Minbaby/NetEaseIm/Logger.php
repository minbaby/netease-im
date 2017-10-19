<?php

namespace Minbaby\NeteaseIm;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    /**
     * @var Logger
     */
    private static $instance;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public $logPath;

    private $streamHandler;

    private function __construct($name)
    {
        $this->logger = new MonoLogger($name);
    }

    public static function getInstance($name = 'NetEaseIm')
    {
        if (empty(static::$instance)) {
            static::$instance = new Logger($name);
        }
        return static::$instance;
    }

    public function setLogPath($path)
    {
        $this->logPath = $path;
        return $this;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    private function getLogPath()
    {
        if (empty($this->logPath)) {
            $this->logPath = '/tmp';
        }
        return $this->logPath;
    }

    public function setHandler($handlers)
    {
        $this->getLogger()->setHandlers($handlers);
        return $this;
    }

    public function setDefaultHandler()
    {
        $this->setHandler([new StreamHandler($this->getLogPath() . '/NetEaseIm.log')]);
        return $this;
    }

    public function setLogLevel($level)
    {
        $handlers = $this->getLogger()->getHandlers();
        foreach ($handlers as $handler) {
            $handler->setLevel(MonoLogger::toMonologLevel($level));
        }
        return $this;
    }
}
