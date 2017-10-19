<?php

namespace Minbaby\NeteaseIm\Manager;

use Minbaby\NeteaseIm\AbstractManager;
use Minbaby\NeteaseIm\Exception\NetEaseImException;
use Minbaby\NeteaseIm\Utils;

class ChatRoomManager extends AbstractManager
{

    const MEMBER_ROLE_OPT_ADMIN = 1; // 管理员
    const MEMBER_ROLE_OPT_CUSTOM = 2; // 普通用户
    const MEMBER_ROLE_OPT_BLACK_LIST = -1; // 黑名单
    const MEMBER_ROLE_OPT_BLOCK = -2; // 禁言

    const CLIENT_TYPE_WEB_LINK = 1;
    const CLIENT_TYPE_COMMON_LINK = 2;

    const MSG_TYPE_TXT = 0;         // 文本消息
    const MSG_TYPE_IMG = 1;         // 图片消息
    const MSG_TYPE_AUDIO = 2;       // 语音消息
    const MSG_TYPE_VIDEO = 3;       // 视频消息
    const MSG_TYPE_LOC = 4;         // 地理位置消息
    const MSG_TYPE_FILE = 6;        // 文件消息
    const MSG_TYPE_TIPS = 10;       // tips 消息
    const MSG_TYPE_CUSTOM = 100;    // 自定义消息

    private $nimserver_chatroom_create_action = '/nimserver/chatroom/create.action';
    private $nimserver_chatroom_get_action = '/nimserver/chatroom/get.action';
    private $nimserver_chatroom_update_action = '/nimserver/chatroom/update.action';
    private $nimserver_chatroom_toggle_close_stat_action = '/nimserver/chatroom/toggleCloseStat.action';
    private $nimserver_chatroom_set_member_role_action = '/nimserver/chatroom/setMemberRole.action';
    private $nimserver_chatroom_request_addr_action=  '/nimserver/chatroom/requestAddr.action';
    private $nimserver_chatroom_send_msg_action = '/nimserver/chatroom/sendMsg.action';
    private $nimserver_chatroom_add_robot_action = '/nimserver/chatroom/addRobot.action';
    private $nimserver_chatroom_remove_robot_action = '/nimserver/chatroom/removeRobot.action';
    private $nimserver_chatroom_temporary_mute = '/nimserver/chatroom/temporaryMute.action';
    private $nimserver_chatroom_queue_init = '/nimserver/chatroom/queueInit.action';
    private $nimserver_chatroom_queue_offer = '/nimserver/chatroom/queueOffer.action';
    private $nimserver_chatroom_queue_poll = '/nimserver/chatroom/queuePoll.action';
    private $nimserver_chatroom_queue_list = '/nimserver/chatroom/queueList.action';
    private $nimserver_chatroom_queue_drop = '/nimserver/chatroom/queueDrop.action';

    /**
     * @param string $creator      聊天室属主的账号accid
     * @param string $name         聊天室名称，长度限制128个字符
     * @param string $announcement 公告，长度限制4096个字符
     * @param string $broadcastUrl 直播地址，长度限制1024个字符
     * @param array  $ext          扩展字段，最长4096字符
     *
     * @return array
     */
    public function createChatRoom($creator, $name, $announcement, $broadcastUrl, $ext = [])
    {
        $data = [
            'creator' => $creator,
            'name' => $name,
            'announcement' => $announcement,
            'broadcasturl' => $broadcastUrl,
            'ext' => json_encode($ext)
        ];
        $ret =$this->post($this->nimserver_chatroom_create_action, $data);
        return Utils::arrayGet($ret, 'chatroom');
    }

    /**
     * @param int  $roomId 聊天室id
     * @param bool $needOnlineUserCount 是否需要返回在线人数，true或false，默认false
     *
     * @return array
     */
    public function getChatRoom($roomId, $needOnlineUserCount = false)
    {
        $data = ['roomid' => $roomId, 'needOnlineUserCount' => Utils::boolConvertToString($needOnlineUserCount)];
        $ret =$this->post($this->nimserver_chatroom_get_action, $data);
        return Utils::arrayGet($ret, 'chatroom');
    }

    /**
     * @param int    $roodId       聊天室id
     * @param string $name         聊天室名称    长度限制128个字符
     * @param string $announcement 公告，长度限制4096个字符
     * @param string $broadcastUrl 直播地址，长度限制1024个字符
     * @param array  $ext          扩展字段，     长度限制4096个字符
     * @param bool   $needNotify   true或false,是否需要发送更新通知事件，默认true
     * @param array  $notifyExt    通知事件扩展字段，长度限制2048
     *
     * @return array
     */
    public function updateChatRoom(
        $roodId,
        $name = '',
        $announcement = '',
        $broadcastUrl = '',
        $ext = [],
        $needNotify = true,
        $notifyExt = []
    ) {
        $init = ['roomid' => $roodId, 'needNotify' => Utils::boolConvertToString($needNotify)];

        $data = Utils::arrCheckAndPush($init, 'name', $name);
        $data = Utils::arrCheckAndPush($data, 'announcement', $announcement);
        $data = Utils::arrCheckAndPush($data, 'broadcasturl', $broadcastUrl);
        $data = Utils::arrCheckAndPush($data, 'ext', $ext);
        $data = Utils::arrCheckAndPush($data, 'notifyExt', $notifyExt);

        $ret = $this->post($this->nimserver_chatroom_update_action, $data);

        return Utils::arrayGet($ret, 'chatroom');
    }

    /**
     * @param string $roomId   聊天室id
     * @param string $operator 操作者账号，必须是创建者才可以操作
     * @param bool $valid    true或false，false:关闭聊天室；true:打开聊天室
     *
     * @return array
     */
    public function toggleCloseStatus($roomId, $operator, $valid)
    {
        $data = [
            'valid' => Utils::boolConvertToString($valid),
            'roomid' => $roomId,
            'operator' => $operator
        ];

        $ret = $this->post($this->nimserver_chatroom_toggle_close_stat_action, $data);

        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param string $roomId 聊天室id
     * @param string $operator 操作者账号accid
     * @param string $target 被操作者账号accid
     * @param string $opt 操作：
     *                              1: 设置为管理员，operator必须是创建者
     *                              2:设置普通等级用户，operator必须是创建者或管理员
     *                              -1:设为黑名单用户，operator必须是创建者或管理员
     *                              -2:设为禁言用户，operator必须是创建者或管理员
     * @param bool $optValue true或false，true:设置；false:取消设置
     * @param array $notifyExt 通知扩展字段，长度限制2048，请使用json格式
     *
     * 备注：
     * 返回的type字段可能为：
     * LIMITED,          //受限用户,黑名单+禁言
     * COMMON,           //普通固定成员
     * CREATOR,          //创建者
     * MANAGER,          //管理员
     * TEMPORARY,        //临时用户,非固定成员
     * @return array
     * @throws NetEaseImException
     */
    public function setMemberRole($roomId, $operator, $target, $opt, $optValue, $notifyExt = [])
    {
        $data = [
            'roomid' => $roomId,
            'operator' => $operator,
            'target' => $target,
            'opt' => $opt,
            'optvalue' => Utils::boolConvertToString($optValue),
        ];

        if (!in_array($opt, [
            static::MEMBER_ROLE_OPT_ADMIN,
            static::MEMBER_ROLE_OPT_CUSTOM,
            static::MEMBER_ROLE_OPT_BLACK_LIST,
            static::MEMBER_ROLE_OPT_BLOCK,
        ])) {
            throw new NetEaseImException("opt not support");
        }

        $data = Utils::arrCheckAndPush($data, 'notifyExt', $notifyExt);
        $ret = $this->post($this->nimserver_chatroom_set_member_role_action, $data);
        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param string $roomId     聊天室id
     * @param string $accId      进入聊天室的账号
     * @param int    $clientType 1:weblink; 2:commonlink, 默认1
     *
     * @return array
     */
    public function requestAddress($roomId, $accId, $clientType = ChatRoomManager::CLIENT_TYPE_WEB_LINK)
    {
        $data = [
            'roomid' => $roomId,
            'accid' => $accId,
            'clienttype' => $clientType
        ];
        $ret = $this->post($this->nimserver_chatroom_request_addr_action, $data);
        return Utils::arrayGet($ret, 'addr');
    }

    /**
     * TODO
     *
     * @param string $roomId      聊天室id
     * @param string $msgId       客户端消息id，使用uuid等随机串，msgId相同的消息会被客户端去重
     * @param string $fromAccId   消息发出者的账号accid
     * @param int    $msgType     消息类型：
     *                            0: 表示文本消息，
     *                            1: 表示图片，
     *                            2: 表示语音，
     *                            3: 表示视频，
     *                            4: 表示地理位置信息，
     *                            6: 表示文件，
     *                            10: 表示Tips消息，
     *                            100: 自定义消息类型
     * @param int    $resendFlag  重发消息标记，0：非重发消息，1：重发消息，如重发消息会按照msgid检查去重逻辑
     * @param array  $attach      消息内容，格式同消息格式示例中的body字段,长度限制2048字符
     * @param array  $ext         消息扩展字段，内容可自定义，请使用JSON格式，长度限制4096
     *
     * @return array
     */
    public function sendMessage(
        $roomId,
        $msgId,
        $fromAccId,
        $msgType = ChatRoomManager::MSG_TYPE_TXT,
        $resendFlag = 0,
        $attach = [],
        $ext = []
    ) {
        $data = [
            'roomid'        => $roomId,
            'msgId'         => $msgId,
            'fromAccid'     => $fromAccId,
            'msgType'       => $msgType,
            'resendFlag'    => $resendFlag,
            'attach'        => json_encode($attach),
            'ext'           => json_encode($ext),
        ];
        $ret = $this->post($this->nimserver_chatroom_send_msg_action, $data);
        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param       $roomId
     * @param       $msgId
     * @param       $fromAccId
     * @param       $msg
     * @param int   $resendFlag
     * @param array $ext
     *
     * @return array
     */
    public function sendTxtMessage(
        $roomId,
        $msgId,
        $fromAccId,
        $msg,
        $resendFlag = 0,
        $ext = []
    ) {
        return $this->sendMessage(
            $roomId,
            $msgId,
            $fromAccId,
            ChatRoomManager::MSG_TYPE_TXT,
            $resendFlag,
            ['msg' => $msg],
            $ext
        );
    }

    /**
     * @param string       $roomId    聊天室id
     * @param array        $accIds    机器人账号accid列表，必须是有效账号，账号数量上限100个
     * @param array        $roleExt   机器人信息扩展字段，请使用json格式，长度4096字符
     * @param array        $notifyExt 机器人进入聊天室通知的扩展字段，请使用json格式，长度2048字符
     *
     * @return array
     */
    public function addRobot($roomId, array $accIds, $roleExt = [], $notifyExt = [])
    {
        $data = [
            'roomid' => $roomId,
            'accids' => json_encode($accIds),
            'roleExt' => json_encode($roleExt),
            'notifyExt' => json_encode($notifyExt),
        ];
        $ret = $this->post($this->nimserver_chatroom_add_robot_action, $data);
        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param       $roomId
     * @param array $accIds 机器人账号accid列表，必须是有效账号，账号数量上限100个
     *
     * @return array
     */
    public function removeRobot($roomId, array $accIds)
    {
        $data = [
            'roomid' => $roomId,
            'accids' => json_encode($accIds),
        ];
        $ret = $this->post($this->nimserver_chatroom_remove_robot_action, $data);
        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param string $roomId
     * @param string $operator     操作者accid,必须是管理员或创建者
     * @param string $target       被禁言的目标账号accid
     * @param string $muteDuration 0:解除禁言;>0设置禁言的秒数，不能超过2592000秒(30天)
     * @param bool   $needNotify   操作完成后是否需要发广播，true或false，默认true
     * @param array  $notifyExt    通知广播事件中的扩展字段，长度限制2048字符
     *
     * @return mixed
     */
    public function temporaryMute($roomId, $operator, $target, $muteDuration, $needNotify = true, $notifyExt = [])
    {
        $data = [
            'roomid'        => $roomId,
            'operator'      => $operator,
            'target'        => $target,
            'muteDuration'  => $muteDuration,
            'needNotify'    => Utils::boolConvertToString($needNotify),
            'notifyExt'     => json_encode($notifyExt)
        ];
        $ret = $this->post($this->nimserver_chatroom_temporary_mute, $data);
        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param int $roomId
     * @param int $sizeLimit 队列长度限制，0~1000
     *
     * @return bool
     * @throws NetEaseImException
     */
    public function queueInit($roomId, $sizeLimit)
    {
        if ($sizeLimit < 0 || $sizeLimit > 1000) {
            throw new NetEaseImException("队列长度限制，0~1000");
        }

        $data = ['roomid' =>  $roomId, 'sizeLimit' => $sizeLimit];
        $this->post($this->nimserver_chatroom_queue_init, $data);
        return true;
    }

    /**
     * @param int    $roomId
     * @param string $key   elementKey,新元素的UniqKey,长度限制128字符
     * @param string $value elementValue,新元素内容，长度限制4096字符
     *
     * @return bool
     */
    public function queueOffer($roomId, $key, $value)
    {
        $data = [
            'roomid' => $roomId,
            'key' => $key,
            'value' => $value,
        ];
        $this->post($this->nimserver_chatroom_queue_offer, $data);
        return true;
    }

    /**
     * @param int    $roomId
     * @param string $key 目前元素的elementKey,长度限制128字符，不填表示取出头上的第一个
     *
     * @return array
     */
    public function queuePoll($roomId, $key)
    {
        $data = [
            'roomid' => $roomId,
            'key' => $key,
        ];
        $ret = $this->post($this->nimserver_chatroom_queue_poll, $data);
        return Utils::arrayGet($ret, 'desc');
    }

    /**
     * @param int    $roomId
     *
     * @return array
     */
    public function queueList($roomId)
    {
        $data = [
            'roomid' => $roomId,
        ];
        $ret = $this->post($this->nimserver_chatroom_queue_list, $data);
        return Utils::arrayGet($ret, 'desc.list');
    }

    public function queueDrop($roomId)
    {
        $data = [
            'roomid' => $roomId,
        ];
        $this->post($this->nimserver_chatroom_queue_drop, $data);
        return true;
    }
}
