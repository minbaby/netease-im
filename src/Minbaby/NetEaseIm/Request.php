<?php

namespace Minbaby\NetEaseIm;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Minbaby\NetEaseIm\Exception\NetEaseImException;

class Request
{
    private $appKey;

    private $appSecret;

    private $debug = false;

    private $baseUrl;

    private $client;
    /**
     * @var SignCheck
     */
    private $signCheck;

    const MAX_RETRY_TIMES = 3;

    const MAX_RETRY_WAITING_TIME = 500; // ms

    /**
     * AbstractManager constructor.
     *
     * @param $appKey
     * @param $appSecret
     * @param $baseUrl
     * @param SignCheck $signCheck
     */
    public function __construct($appKey, $appSecret, $baseUrl, SignCheck $signCheck)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->baseUrl = $baseUrl;
        $this->signCheck = $signCheck;
    }

    /**
     * TODO: retry
     *
     * @param $url
     * @param array $data
     *
     * @throws NetEaseImException
     *
     * @return array
     */
    public function post($url, array $data)
    {
        $options = [
            RequestOptions::TIMEOUT         => 3,
            RequestOptions::CONNECT_TIMEOUT => 3,
            RequestOptions::DEBUG           => $this->debug,
            RequestOptions::HEADERS         => $this->getHeaders(),
            RequestOptions::BODY            => http_build_query($data),
        ];

        Logger::getInstance()->getLogger()->info(sprintf('%s::Request', __METHOD__), [
            'url' => $this->baseUrl . $url, 'options' => $options]);

        $content = '';

        retry(static::MAX_RETRY_TIMES, function () use (&$content, $url, $options) {
            $content = $this->getClient()->post($url, $options)->getBody()->getContents();
        }, static::MAX_RETRY_WAITING_TIME);

        Logger::getInstance()->getLogger()->info(sprintf('%s::Response', __METHOD__), ['response' => $content]);

        $json = json_decode($content, true);
        if (empty($json)) {
            throw new NetEaseImException('json decode err: ' . $content);
        }

        return $json;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    private function getClient()
    {
        if (empty($this->client)) {
            $this->client = new Client(['base_uri' => $this->baseUrl]);
        }

        return $this->client;
    }

    private function getHeaders()
    {
        $nonce = $this->signCheck->buildNonce();
        $curTime = strval(time());
        $checkSum = $this->signCheck->buildCheckSum($this->appSecret, $nonce, $curTime);

        return [
            'AppKey'   => $this->appKey, //开发者平台分配的AppKey
            'Nonce'    => $nonce,        //随机数（最大长度128个字符）
            'CurTime'  => $curTime,      //当前UTC时间戳，从1970年1月1日0点0 分0 秒开始到现在的秒数(String)
            'CheckSum' => $checkSum,     //SHA1(AppSecret + Nonce + CurTime),
                                         //三个参数拼接的字符串，进行SHA1哈希计算，
                                         //转化成16进制字符(String，小写)
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
}
