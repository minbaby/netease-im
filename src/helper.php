<?php

function getVersion()
{
    return NETEASE_IM_VERSION;
}

if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int      $times
     * @param callable $callback
     * @param int      $sleep
     *
     * @throws \Exception
     *
     * @return mixed
     */
    function retry($times, callable $callback, $sleep = 0)
    {
        $times--;
        beginning:
        try {
            return $callback();
        } catch (Exception $e) {
            if (! $times) {
                throw $e;
            }
            $times--;
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}

if (! function_exists('throwExceptionIfTrue')) {
    /**
     * @param $bool
     * @param $msg
     * @param int $code
     *
     * @throws \Minbaby\NetEaseIm\Exception\NetEaseImException
     */
    function throwExceptionIfTrue($bool, $msg, $code = 0)
    {
        if ($bool) {
            throw new \Minbaby\NetEaseIm\Exception\NetEaseImException($msg, $code);
        }
    }
}
