<?php

namespace Minbaby\NetEaseIm;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\AbstractProcessingHandler;

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

    private $name;

    private function __construct($name)
    {
        $this->name = $name;
    }

    public static function getInstance($name = 'NetEaseIm')
    {
        if (empty(static::$instance)) {
            static::$instance = new self($name);
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
        if (empty($this->logger)) {
            $this->logger = new MonoLogger($this->name);
            $this->setDefaultHandler();
        }

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
        /** @var AbstractProcessingHandler[] $handlers */
        $handlers = $this->getLogger()->getHandlers();
        foreach ($handlers as $handler) {
            $handler->setLevel(MonoLogger::toMonologLevel($level));
        }

        return $this;
    }

    /**
     * @param MonoLogger $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
