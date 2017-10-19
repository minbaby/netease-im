<?php

namespace  Minbaby\NetEaseIm\Manager;

use NetEaseIm\AbstractManager;

class HistoryManager extends AbstractManager
{

    const ASC = 1;
    const DESC = 2;

    private $nimserver_history_query_session_msg = '/nimserver/history/querySessionMsg.action';
    private $nimserver_history_query_team_msg = '/nimserver/history/queryTeamMsg.action';
    private $nimserver_history_query_user_event = '/nimserver/history/queryUserEvent.action';
    private $nimserver_history_delete_media_file = '/nimserver/history/deleteMediaFile.action';

    public function querySessionMessage($from, $to, $beginTime, $endTime, $limit, $reverse = HistoryManager::DESC)
    {
        return $this->query(
            $this->nimserver_history_query_session_msg,
            $from,
            $to,
            $beginTime,
            $endTime,
            $limit,
            $reverse
        );
    }

    public function queryTeamMsg($from, $to, $beginTime, $endTime, $limit, $reverse = HistoryManager::DESC)
    {
        return $this->query(
            $this->nimserver_history_query_team_msg,
            $from,
            $to,
            $beginTime,
            $endTime,
            $limit,
            $reverse
        );
    }

    /**
     * @param        $url
     * @param string $from      发送者accid
     * @param string $to        接收者accid
     * @param int    $beginTime 开始时间，ms
     * @param int    $endTime   截止时间，ms
     * @param int    $limit     本次查询的消息条数上限(最多100条),小于等于0，或者大于100，会提示参数错误
     * @param int    $reverse   1按时间正序排列，2按时间降序排列。其它返回参数414错误.默认是按降序排列
     *
     * @return array
     */
    private function query($url, $from, $to, $beginTime, $endTime, $limit, $reverse = HistoryManager::DESC)
    {
        $data = [
            'from'      => $from,
            'to'        => $to,
            'begintime' => $beginTime,
            'endtime'   => $endTime,
            'limit'     => $limit,
            'reverse'   => $reverse
        ];
        $ret = $this->post($url, $data);
        unset($ret['code']);
        return $ret;
    }

    /**
     * @param     $accId
     * @param     $beginTime
     * @param     $endTime
     * @param     $limit
     * @param int $reverse
     *
     * @return mixed
     */
    public function queryUserEvent($accId, $beginTime, $endTime, $limit, $reverse = HistoryManager::DESC)
    {
        $data = [
            'accid'      => $accId,
            'begintime' => $beginTime,
            'endtime'   => $endTime,
            'limit'     => $limit,
            'reverse'   => $reverse
        ];
        $ret = $this->post($this->nimserver_history_query_user_event, $data);
        unset($ret['code']);
        return $ret;
    }

    /**
     * @param $channelId
     *
     * @return bool
     */
    public function deleteMediaFile($channelId)
    {
        $data = [
            'channelid' => $channelId,
        ];
        $this->post($this->nimserver_history_delete_media_file, $data);
        return true;
    }
}
