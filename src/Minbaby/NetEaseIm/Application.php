<?php

namespace Minbaby\NetEaseIm;

use Minbaby\NetEaseIm\Manager\SmsManager;
use Minbaby\NetEaseIm\Manager\UserManager;
use Minbaby\NetEaseIm\Manager\HistoryManager;
use Minbaby\NetEaseIm\Manager\MessageManager;
use Minbaby\NetEaseIm\Manager\ChatRoomManager;
use Minbaby\NetEaseIm\Manager\UserGroupManager;

/**
 * http://dev.netease.im/docs?doc=server
 *
 * Class Application
 *
 * @method UserManager getUserManager()
 * @method MessageManager getMessageManager()
 * @method ChatRoomManager getChatRoomManager()
 * @method HistoryManager getHistoryManager()
 * @method SmsManager getSmsManager()
 * @method UserGroupManager getUserGroupManager()
 */
class Application
{
    private static $instance;

    private $appKey;

    private $appSecret;

    private $mangers = [];

    private $debug = false;

    private $https_api_netease_im = 'https://api.netease.im';

    private function __construct($appKey, $appSecret)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
    }

    /**
     * @param $appKey
     * @param $appSecret
     *
     * @return Application
     */
    public static function getInstance($appKey, $appSecret)
    {
        if (empty(static::$instance)) {
            static::$instance = new self($appKey, $appSecret);
        }

        return static::$instance;
    }

    public static function setDefaultLog()
    {
        Logger::getInstance()->setLogPath(getenv('LOG_PATH'))->setDefaultHandler();
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (! isset($this->mangers[$name])) {
            $className = '\Minbaby\NetEaseIm\Manager\\' . substr($name, 3, strlen($name));
            $this->mangers[$name] = new $className($this->appKey, $this->appSecret, $this->https_api_netease_im);
        }

        $this->mangers[$name]->setDebug($this->debug);

        return $this->mangers[$name];
    }

    /**
     * @param mixed $debug
     *
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        Logger::getInstance()->setLogLevel($this->debug ? 'DEBUG' : 'INFO');

        return $this;
    }
}
