<?php

namespace Minbaby\NetEaseIm\Manager;

use Minbaby\NetEaseIm\Utils;
use Minbaby\NetEaseIm\AbstractManager;
use Minbaby\NetEaseIm\Exception\NetEaseImException;

class UserManager extends AbstractManager
{
    /**
     * 添加好友类型
     */
    const ADD_FRIEND_DIRECT = 1;           // 1直接加好友
    const ADD_FRIEND_TYPE_REQUEST = 2;     // 2请求加好友
    const ADD_FRIEND_TYPE_AGREE = 3;      // 3同意加好友
    const ADD_FRIEND_TYPE_REJECT = 4;     // 4拒绝加好友

    /**
     * 好友操作类型
     */
    const SPECIAL_RELATION_TYPE_BLACK_LIST = 1; // 黑名单
    const SPECIAL_RELATION_TYPE_MUTE = 2; // 静音

    /**
     * 消息类型
     */
    const MSG_OPE_POINT_TO_POINT = 0;  // 点对点消息
    const MSG_OPE_CHAT_ROOM = 1;    // 群消息

    private $nimserver_user_create_action = '/nimserver/user/create.action';
    private $nimserver_user_update_action = '/nimserver/user/update.action';
    private $nimserver_user_refresh_token_action = '/nimserver/user/refreshToken.action';
    private $nimserver_user_block_action = '/nimserver/user/block.action';
    private $nimserver_user_unblock_action = '/nimserver/user/unblock.action';
    private $nimserver_user_update_uinfo_action = '/nimserver/user/updateUinfo.action';
    private $nimserver_user_get_uinfos_action = '/nimserver/user/getUinfos.action';
    private $nimserver_user_set_donnop_action = '/nimserver/user/setDonnop.action';
    private $nimserver_friend_add_action = '/nimserver/friend/add.action';
    private $nimserver_friend_update_action = '/nimserver/friend/update.action';
    private $nimserver_friend_delete_action = '/nimserver/friend/delete.action';
    private $nimserver_friend_get_action = '/nimserver/friend/get.action';
    private $nimserver_user_set_special_relation_action = '/nimserver/user/setSpecialRelation.action';
    private $nimserver_user_list_black_and_mute_list_action = '/nimserver/user/listBlackAndMuteList.action';
    private $nimserer_msg_send_msg = 'https://api.netease.im/nimserver/msg/sendMsg.action';

    /**
     * 创建云信ID
     * 1.第三方帐号导入到云信平台；
     * 2.注意accid，name长度以及考虑管理秘钥token
     *
     * @param string $accid  [云信ID，最大长度32字节，必须保证一个APP内唯一（只允许字母、数字、半角下划线_、@、半角点以及半角-组成，不区分大小写，会统一小写处理）]
     * @param string $name   [云信ID昵称，最大长度64字节，用来PUSH推送时显示的昵称]
     * @param array  $props  [json属性，第三方可选填，最大长度1024字节]
     * @param string $icon   [云信ID头像URL，第三方可选填，最大长度1024]
     * @param string $token  [云信ID可以指定登录token值，最大长度128字节，并更新，如果未指定，会自动生成token，并在创建成功后返回]
     * @param string $sign   用户签名，最大长度256字符
     * @param string $email  用户email，最大长度64字符
     * @param string $birth  用户生日，最大长度16字符
     * @param string $mobile 用户mobile，最大长度32字符，只支持国内号码
     * @param int    $gender 用户性别，0表示未知，1表示男，2女表示女，其它会报参数错误
     * @param string $ex     用户名片扩展字段，最大长度1024字符，用户可自行扩展，建议封装成JSON字符串
     *
     * @return array $result    [返回array数组对象]
     */
    public function createUserId(
        $accid,
        $name = '',
        $props = [],
        $icon = '',
        $token = '',
        $sign = '',
        $email = '',
        $birth = '',
        $mobile = '',
        $gender = 0,
        $ex = ''
    ) {
        $data = [
            'accid'  => $accid,
            'name'   => $name,
            'props'  => json_encode($props),
            'icon'   => $icon,
            'token'  => $token,
            'sign'   => $sign,
            'email'  => $email,
            'birth'  => $birth,
            'mobile' => $mobile,
            'gender' => $gender,
            'ex'     => $ex,
        ];
        $ret = $this->post($this->nimserver_user_create_action, $data);

        return Utils::arrayGet($ret, 'info');
    }

    /**
     * 更新云信ID
     *
     * @param string $accid [云信ID，最大长度32字节，必须保证一个APP内唯一（只允许字母、数字、半角下划线_、@、半角点以及半角-组成，不区分大小写，会统一小写处理）]
     * @param string $name  [云信ID昵称，最大长度64字节，用来PUSH推送时显示的昵称]
     * @param array  $props [json属性，第三方可选填，最大长度1024字节]
     * @param string $token [云信ID可以指定登录token值，最大长度128字节，并更新，如果未指定，会自动生成token，并在创建成功后返回]
     *
     * @return $result [返回array数组对象]
     */
    public function updateUserId($accid, $name = '', $props = [], $token = '')
    {
        $data = ['accid' => $accid, 'name' => $name, 'props' => json_encode($props), 'token' => $token];

        return $this->post($this->nimserver_user_update_action, $data);
    }

    /**
     * 更新并获取新token
     *
     * @param  $accid [云信ID，最大长度32字节，必须保证一个APP内唯一（只允许字母、数字、半角下划线_、@、半角点以及半角-组成，不区分大小写，会统一小写处理）]
     *
     * @return $result [返回array数组对象]
     */
    public function refreshToken($accid)
    {
        $data = ['accid' => $accid];
        $ret = $this->post($this->nimserver_user_refresh_token_action, $data);

        return Utils::arrayGet($ret, 'info');
    }

    /**
     * @param string $accId
     * @param bool   $needKick 是否踢掉被禁用户, 默认 false
     *
     * @return bool
     */
    public function blockUser($accId, $needKick = false)
    {
        $data = ['accid' => $accId, 'needkick' => $needKick];
        $ret = $this->post($this->nimserver_user_block_action, $data);

        return true;
    }

    /**
     * @param string $accId
     * @param bool   $needKick 是否踢掉被禁用户, 默认 false
     *
     * @return bool
     */
    public function unBlockUser($accId)
    {
        $data = ['accid' => $accId];
        $ret = $this->post($this->nimserver_user_unblock_action, $data);

        return true;
    }

    /**
     * @param string $accId
     * @param string $name   用户昵称，最大长度64字符
     * @param string $icon   用户icon，最大长度1024字符
     * @param string $sign   用户签名，最大长度256字符
     * @param string $email  用户email，最大长度64字符
     * @param string $birth  用户生日，最大长度16字符
     * @param string $mobile 用户mobile，最大长度32字符，只支持国内号码
     * @param string $gender 用户性别，0表示未知，1表示男，2女表示女，其它会报参数错误
     * @param string $ex     用户名片扩展字段，最大长度1024字符，用户可自行扩展，建议封装成JSON字符串
     *
     * @return bool
     */
    public function updateUserInfo(
        $accId,
        $name = null,
        $icon = null,
        $sign = null,
        $email = null,
        $birth = null,
        $mobile = null,
        $gender = null,
        $ex = null
    ) {
        $data = ['accid' => $accId];
        $params = ['name', 'icon', 'sign', 'email', 'birth', 'mobile', 'gender', 'ex'];
        foreach ($params as $param) {
            if ( ! is_null($$param)) {
                $data[$param] = $$param;
            }
        }
        $this->post($this->nimserver_user_update_uinfo_action, $data);

        return true;
    }

    /**
     * @param array $accIds 最多可以200个
     *
     * @return array
     */
    public function getUserInfo(array $accIds)
    {
        $data = ['accids' => json_encode($accIds)];
        $ret = $this->post($this->nimserver_user_get_uinfos_action, $data);

        return Utils::arrayGet($ret, 'uinfos');
    }

    /**
     * 设置桌面端在线时，移动端是否需要推送
     *
     * @param string $accId
     * @param bool   $donnopOpen 桌面端在线时，移动端是否不推送：true:移动端不需要推送，false:移动端需要推送
     *
     * @return bool
     */
    public function setDonnop($accId, $donnopOpen = false)
    {
        $data = ['accid' => $accId, 'donnopOpen' => Utils::boolConvertToString($donnopOpen)];
        $this->post($this->nimserver_user_set_donnop_action, $data);

        return true;
    }

    /**
     * @param string $accId
     * @param string $fAccId
     * @param string $type
     * @param string $msg
     *
     * @throws NetEaseImException
     *
     * @return bool
     */
    public function addFriend($accId, $fAccId, $type, $msg = '')
    {
        $support = [
            static::ADD_FRIEND_DIRECT,
            static::ADD_FRIEND_TYPE_REQUEST,
            static::ADD_FRIEND_TYPE_AGREE,
            static::ADD_FRIEND_TYPE_REJECT
        ];

        if ( ! in_array($type, $support)) {
            throw new NetEaseImException('type must in:' . implode(', ', $support));
        }
        $data = ['accid' => $accId, 'faccid' => $fAccId, 'type' => $type, 'msg' => $msg];
        $this->post($this->nimserver_friend_add_action, $data);

        return true;
    }

    /**
     * @param $accId
     * @param $fAccId
     * @param $alias
     *
     * @return bool
     */
    public function updateFriend($accId, $fAccId, $alias)
    {
        $data = ['accid' => $accId, 'faccid' => $fAccId, 'alias' => $alias];
        $this->post($this->nimserver_friend_update_action, $data);

        return true;
    }

    /**
     * @param $accId
     * @param $fAccId
     *
     * @return bool
     */
    public function deleteFriend($accId, $fAccId)
    {
        $data = ['accid' => $accId, 'faccid' => $fAccId];
        $this->post($this->nimserver_friend_delete_action, $data);

        return true;
    }

    /**
     * @param string $accId
     * @param int    $createTime
     *
     * @return bool
     */
    public function getFriend($accId, $createTime = 0)
    {
        $data = ['accid' => $accId, 'createtime' => $createTime];
        $ret = $this->post($this->nimserver_friend_get_action, $data);
        unset($ret['code']);

        return $ret;
    }

    /**
     * @param $accId
     * @param $targetAcc
     * @param $relationType
     * @param $value
     *
     * @throws \Minbaby\NetEaseIm\Exception\NetEaseImException
     *
     * @return bool
     */
    public function setSpecialRelation($accId, $targetAcc, $relationType, $value)
    {
        if ( ! in_array($relationType, [static::SPECIAL_RELATION_TYPE_BLACK_LIST, static::SPECIAL_RELATION_TYPE_MUTE])) {
            throw new NetEaseImException('check relationType');
        }

        $data = ['accid' => $accId, 'targetAcc' => $targetAcc, 'relationType' => $relationType, 'value' => $value];
        $this->post($this->nimserver_user_set_special_relation_action, $data);

        return true;
    }

    public function listBlackAndMuteList($accId)
    {
        $data = ['accid' => $accId];
        $ret = $this->post($this->nimserver_user_list_black_and_mute_list_action, $data);
        unset($ret['code']);

        return $ret;
    }
}
