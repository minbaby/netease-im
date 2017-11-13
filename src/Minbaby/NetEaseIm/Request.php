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
     * AbstractManager constructor.
     *
     * @param $appKey
     * @param $appSecret
     * @param $baseUrl
     */
    public function __construct($appKey, $appSecret, $baseUrl)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->baseUrl = $baseUrl;
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

        $content = $this->getClient()->post($url, $options)->getBody()->getContents();

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
        $nonce = $this->buildNonce();
        $curTime = strval(time());

        return [
            'AppKey'   => $this->appKey,                              //开发者平台分配的AppKey
            'Nonce'    => $nonce,                                     //随机数（最大长度128个字符）
            'CurTime'  => $curTime,                                  //当前UTC时间戳，从1970年1月1日0点0 分0 秒开始到现在的秒数(String)
            'CheckSum' => $this->buildCheckSum($nonce, $curTime),   //SHA1(AppSecret + Nonce + CurTime),
                                                                    //三个参数拼接的字符串，进行SHA1哈希计算，
                                                                    //转化成16进制字符(String，小写)
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];
    }

    private function buildNonce()
    {
        $hexDigits = '0123456789abcdef';
        $ret = '';
        for ($i = 0; $i < 128; $i++) {            //随机字符串最大128个字符，也可以小于该数
            $ret .= $hexDigits[mt_rand(0, 15)];
        }

        return $ret;
    }

    private function buildCheckSum($nonce, $curTime)
    {
        return sha1($this->appSecret . $nonce . $curTime);
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
}
