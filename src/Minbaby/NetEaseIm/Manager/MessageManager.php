<?php

namespace  Minbaby\NetEaseIm\Manager;

use  Minbaby\NetEaseIm\AbstractManager;
use Minbaby\NetEaseIm\Utils;

/**
 * Class MessageManager
 * @package NetEaseIm\Manager
 *
 * TODO !!!! 好恶心, 不想写
 */
class MessageManager extends AbstractManager
{


    /**
     * 消息类型
     *  0 表示文本消息,
     *  1 表示图片，
     *  2 表示语音，
     *  3 表示视频，
     *  4 表示地理位置信息，
     *  6 表示文件，
     *  100 自定义消息类型
     */
    const MSG_TYPE_TXT = 0;
    const MSG_TYPE_IMG = 1;
    const MSG_TYPE_AUDIO = 2;
    const MSG_TYPE_VIDEO = 3;
    const MSG_TYPE_LOC = 4;
    const MSG_TYPE_FILE = 5;
    const MSG_TYPE_CUSTOM = 100;

    const MSG_OPE_POINT_TO_POINT = 0;


    private $nimserver_msg_send_msg = '/nimserver/msg/sendMsg.action';

    /**
     * http://dev.netease.im/docs?doc=server&#发送普通消息
     *
     * @param string $from
     * @param string $ope               0：点对点个人消息，1：群消息，其他返回414
     * @param string $to                ope==0是表示accid即用户id，ope==1表示tid即群id
     * @param string $type              0 表示文本消息,
     *                                  1 表示图片，
     *                                  2 表示语音，
     *                                  3 表示视频，
     *                                  4 表示地理位置信息，
     *                                  6 表示文件，
     *                                  100 自定义消息类型
     * @param string $body
     * @param array  $option            发消息时特殊指定的行为选项,Json格式，可用于指定消息的漫游，存云端历史，发送方多端同步，推送，消息抄送等特殊行为;option中字段不填时表示默认值
     *                                  option示例:
     *                                  {"push":false,"roam":true,"history":false,"sendersync":true,"route":false,"badge":false,"needPushNick":true}
     *
     *                       字段说明：
     *                       1. roam: 该消息是否需要漫游，默认true（需要app开通漫游消息功能）；
     *                       2. history: 该消息是否存云端历史，默认true；
     *                       3. sendersync: 该消息是否需要发送方多端同步，默认true；
     *                       4. push: 该消息是否需要APNS推送或安卓系统通知栏推送，默认true；
     *                       5. route: 该消息是否需要抄送第三方；默认true (需要app开通消息抄送功能);
     *                       6. badge:该消息是否需要计入到未读计数中，默认true;
     *                       7. needPushNick: 推送文案是否需要带上昵称，不设置该参数时默认true;
     *
     * @param string $pushContent       ios推送内容，不超过150字符，option选项中允许推送（push=true），此字段可以指定推送内容
     * @param array  $payload           ios 推送对应的payload,必须是JSON,不能超过2k字符
     * @param array  $ext               开发者扩展字段，长度限制1024字符
     * @param array  $forcePushList     发送群消息时的强推（@操作）用户列表
     *                                  格式为JSONArray，如["accid1","accid2"]。若forcepushall为true，则forcepushlist为除发送者外的所有有效群成员
     * @param string $forcePushContent  发送群消息时，针对强推（@操作）列表forcepushlist中的用户，强制推送的内容
     * @param bool   $forcePushAll      发送群消息时，强推（@操作）列表是否为群里除发送者外的所有有效成员，true或false，默认为false
     *
     * @return bool
     */
    public function sendMsg(
        $from,
        $ope,
        $to,
        $type,
        array $body,
        array $option = [],
        $pushContent = '',
        array $payload = [],
        array $ext = [],
        array $forcePushList = [],
        $forcePushContent = '',
        $forcePushAll = false
    ) {

        $optionDefault = [
            "push" => true,
            "roam" => true,
            "history" => true,
            "sendersync" => true,
            "route" => true,
            "badge" => true,
            "needPushNick" => true
        ];

        if (empty($option)) {
            $option = $optionDefault;
        } else {
            $option = array_merge($optionDefault, $option);
        }

        $data = [
            'from' => $from,
            'ope' => $ope,
            'to' => $to,
            'type' => $type,
            'body' => json_encode($body),
            'option' => json_encode($option),
            'pushcontent' => $pushContent,
            'ext' => json_encode($ext),
            'forcepush' => json_encode($forcePushList),
            'forcepushcontent' => $forcePushContent,
            'forcepushall' => $forcePushAll
        ];

        Utils::arrCheckAndPush($data, 'payload', $payload);

        $this->post($this->nimserver_msg_send_msg, $data);
        return true;
    }

    public function sendTxtMsgToUser($from, $to, $msg, $pushContent)
    {
        $this->sendMsg(
            $from,
            static::MSG_OPE_POINT_TO_POINT,
            $to,
            static::MSG_TYPE_TXT,
            ['msg' => $msg],
            [],
            $pushContent
        );
    }

    public function sendCustomMsgToUser($from, $to, $msg, $pushContent)
    {
        $this->sendMsg(
            $from,
            static::MSG_OPE_POINT_TO_POINT,
            $to,
            static::MSG_TYPE_CUSTOM,
            ['msg' => $msg],
            [],
            $pushContent
        );
    }
}
