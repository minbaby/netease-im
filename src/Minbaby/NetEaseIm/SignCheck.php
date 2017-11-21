<?php

namespace Minbaby\NetEaseIm;

class SignCheck
{
    public function buildNonce()
    {
        $hexDigits = '0123456789abcdef';
        $ret = '';
        for ($i = 0; $i < 128; $i++) {            //随机字符串最大128个字符，也可以小于该数
            $ret .= $hexDigits[mt_rand(0, 15)];
        }

        return $ret;
    }

    public function buildCheckSum($appSecret, $nonce, $curTime)
    {
        return sha1($appSecret . $nonce . $curTime);
    }
}
