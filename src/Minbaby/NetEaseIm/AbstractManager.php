<?php
namespace  Minbaby\NetEaseIm;

use Minbaby\NetEaseIm\Exception\NetEaseImException;

abstract class AbstractManager
{
    private $appKey;

    private $appSecret;

    private $debug;

    private $baseUrl;

    /**
     * AbstractManager constructor.
     *
     * @param $AppKey
     * @param $AppSecret
     * @param $baseUrl
     */
    public function __construct($AppKey, $AppSecret, $baseUrl)
    {
        $this->appKey = $AppKey;
        $this->appSecret = $AppSecret;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param $debug
     *
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    protected function post($url, $data)
    {
        $request = new Request($this->appKey, $this->appSecret, $this->baseUrl);
        $request->setDebug($this->debug);
        $json = $request->post($url, $data);
        if ($json['code'] != '200') {
            throw new NetEaseImException($json['desc'], $json['code']);
        }
        return $json;
    }
}
