<?php

namespace Minbaby\NetEaseIm\Manager;

use Minbaby\NetEaseIm\Utils;
use Minbaby\NetEaseIm\AbstractManager;

class UserGroupManager extends AbstractManager
{
    private $nimserver_team_create = '/nimserver/team/create.action';

    private $nimserver_team_join_teams = '/nimserver/team/joinTeams.action';

    private $nimserver_team_add = '/nimserver/team/add.action';

    private $nimserver_team_query = '/nimserver/team/query.action';

    private $nimserver_team_update = '/nimserver/team/update.action';

    private $nimserver_team_remove = '/nimserver/team/remove.action';

    private $nimserver_team_kick = '/nimserver/team/kick.action';

    private $nimserver_team_change_owner = '/nimserver/team/changeOwner.action';

    private $nimserver_team_add_manager = '/nimserver/team/addManager.action';

    private $nimserver_team_remove_manager = '/nimserver/team/removeManager.action';

    private $nimserver_team_update_team_nick = '/nimserver/team/updateTeamNick.action';

    private $nimserver_team_mute_team = '/nimserver/team/muteTeam.action';

    private $nimserver_team_mute_tlist = '/nimserver/team/muteTlist.action';

    private $nimserver_team_leave = '/nimserver/team/leave.action';

    private $nimserver_team_mute_tlist_all = '/nimserver/team/muteTlistAll.action';

    private $nimserver_team_list_team_mute = '/nimserver/team/listTeamMute.action';

    /**
     * 管理后台建群时，
     * 0不需要被邀请人同意加入群，
     * 1需要被邀请人同意才可以加入群。
     * 其它会返回414
     */
    const CREATE_AGREE_NO_NEED_AGREE = 0;
    const CREATE_AGRENT_NEED_AGREE = 1;

    /**
     * 群建好后，sdk操作时，
     * 0不用验证，
     * 1需要验证,
     * 2不允许任何人加入。
     * 其它返回414
     */
    const CREATE_JOIN_MODE_NO_NEED_VERIFY = 0;
    const CREATE_JOIN_MODE_NEED_VERIFY = 1;
    const CREATE_JOIN_MODE_NO_ALLOW = 2;

    /**
     * 被邀请人同意方式，
     *
     * 0-需要同意(默认)
     * 1-不需要同意。其它返回414
     */
    const CREATE_BE_INVITED_MODE_NEED_ALLOW = 0;
    const CREATE_BE_INVITED_MODE_NO_NEED_ALLOW = 1;

    /**
     * 0-管理员(默认),
     * 1-所有人
     * 其它返回414
     */
    const PERMISSION_MODE_ADMIN = 0;
    const PERMISSION_MODE_EVERYBODY = 1;

    /**
     * 1表示带上群成员列表，
     * 0表示不带群成员列表，只返回群信息
     */
    const QUERY_OPE_NO_MEMBER_LIST = 0;
    const QUERY_OPE_MEMBER_LIST = 1;

    /**
     * 1:群主解除群主后离开群，
     * 2：群主解除群主后成为普通成员。
     * 其它414
     */
    const CHANGE_OWNER_LEAVE_GROUP = 1;
    const CHANGE_OWNER_NOT_LEAVE_GROUP = 2;

    /**
     * 1-禁言
     * 0-解禁
     */
    const MUTE_YES = 1;
    const MUTE_NO = 0;

    /**
     * 创建群组
     *
     * @param string $tname        群名称，最大长度64字符
     * @param string $owner        群主用户帐号，最大长度32字符
     * @param string $icon         群头像，最大长度1024字符
     * @param array  $members      ["aaa","bbb"](JSONArray对应的accid，如果解析出错会报414)，一次最多拉200个成员
     * @param string $custom       自定义高级群扩展属性，第三方可以跟据此属性自定义扩展自己的群属性。（建议为json）,最大长度1024字符
     * @param string $msg          邀请发送的文字，最大长度150字符
     * @param string $announcement 群公告，最大长度1024字符
     * @param string $intro        群描述，最大长度512字符
     * @param int    $mAgree       管理后台建群时，0不需要被邀请人同意加入群，1需要被邀请人同意才可以加入群。其它会返回414
     * @param int    $joinMode     群建好后，sdk操作时，0不用验证，1需要验证,2不允许任何人加入。其它返回414
     * @param int    $beInviteMode 被邀请人同意方式，0-需要同意(默认),1-不需要同意。其它返回414
     * @param int    $inviteMode   谁可以邀请他人入群，0-管理员(默认),1-所有人。其它返回414
     * @param int    $uptInfoMode  谁可以修改群资料，0-管理员(默认),1-所有人。其它返回414
     * @param int    $upCustomMode 谁可以更新群自定义属性，0-管理员(默认),1-所有人。其它返回414
     *
     * @return array
     */
    public function create(
        $tname,
        $owner,
        $icon,
        array $members,
        $custom = '',
        $msg = 'msg',
        $announcement = '',
        $intro = '',
        $mAgree = self::CREATE_AGREE_NO_NEED_AGREE,
        $joinMode = self::CREATE_JOIN_MODE_NO_NEED_VERIFY,
        $beInviteMode = self::CREATE_BE_INVITED_MODE_NO_NEED_ALLOW,
        $inviteMode = self::PERMISSION_MODE_EVERYBODY,
        $uptInfoMode = self::PERMISSION_MODE_ADMIN,
        $upCustomMode = self::PERMISSION_MODE_EVERYBODY
    ) {
        $data = [
            'tname'        => $tname,
            'owner'        => $owner,
            'members'      => json_encode($members),
            'announcement' => $announcement,
            'intro'        => $intro,
            'msg'          => $msg,
            'magree'       => $mAgree,
            'joinmode'     => $joinMode,
            'custom'       => $custom,
            'icon'         => $icon,
            'beinvitemode' => $beInviteMode,
            'invitemode'   => $inviteMode,
            'uptinfomode'  => $uptInfoMode,
            'upcustommode' => $upCustomMode,
        ];

        return $this->post($this->nimserver_team_create, $data);
    }

    /**
     * 获取某个用户所加入高级群的群信息
     *
     * @param string $accid
     *
     * @return array
     */
    public function joinTeams($accid)
    {
        $data = [
            'accid' => $accid
        ];

        return $this->post($this->nimserver_team_join_teams, $data);
    }

    /**
     * 拉人入群
     *
     * @param string $tid     网易云通信服务器产生，群唯一标识，创建群时会返回，最大长度128字符
     * @param string $owner   群主用户帐号，最大长度32字符
     * @param array  $members ["aaa","bbb"](JSONArray对应的accid，如果解析出错会报414)，一次最多拉200个成员
     * @param string $msg     邀请发送的文字，最大长度150字符
     * @param int    $mAgree  管理后台建群时，0不需要被邀请人同意加入群，1需要被邀请人同意才可以加入群。其它会返回414
     * @param string $attach  自定义扩展字段，最大长度512
     *
     * @return array
     */
    public function add($tid, $owner, array $members, $msg, $mAgree = self::CREATE_AGREE_NO_NEED_AGREE, $attach = '')
    {
        $data = [
            'tid'     => $tid,
            'owner'   => $owner,
            'members' => json_encode($members),
            'magree'  => $mAgree,
            'msg'     => $msg,
        ];

        $keys = [
            'attach',
        ];
        foreach ($keys as $key) {
            $data = Utils::arrCheckAndPush($data, $key, $$key);
        }

        return $this->post($this->nimserver_team_add, $data);
    }

    /**
     * 群信息与成员列表查询
     *
     * @param array $tids 群id列表，如["3083","3084"]
     * @param int   $ope  1表示带上群成员列表，0表示不带群成员列表，只返回群信息
     *
     * @return array
     */
    public function query(array $tids, $ope = self::QUERY_OPE_MEMBER_LIST)
    {
        $data = [
            'tids' => json_encode($tids),
            'ope'  => $ope
        ];

        return $this->post($this->nimserver_team_query, $data);
    }

    /**
     * 更新群组
     *
     * @param string $tid          易云通信服务器产生，群唯一标识，创建群时会返回
     * @param string $tname        群名称，最大长度64字符
     * @param string $owner        群主用户帐号，最大长度32字符
     * @param string $icon         群头像，最大长度1024字符
     * @param string $custom       自定义高级群扩展属性，第三方可以跟据此属性自定义扩展自己的群属性。（建议为json）,最大长度1024字符
     * @param string $announcement 群公告，最大长度1024字符
     * @param string $intro        群描述，最大长度512字符
     * @param int    $joinMode     群建好后，sdk操作时，0不用验证，1需要验证,2不允许任何人加入。其它返回414
     * @param int    $beInviteMode 被邀请人同意方式，0-需要同意(默认),1-不需要同意。其它返回414
     * @param int    $inviteMode   谁可以邀请他人入群，0-管理员(默认),1-所有人。其它返回414
     * @param int    $uptInfoMode  谁可以修改群资料，0-管理员(默认),1-所有人。其它返回414
     * @param int    $upCustomMode 谁可以更新群自定义属性，0-管理员(默认),1-所有人。其它返回414
     *
     *  @return array
     */
    public function update(
        $tid,
        $tname,
        $owner,
        $icon = '',
        $custom = '',
        $announcement = '',
        $intro = '',
        $joinMode = self::CREATE_JOIN_MODE_NO_NEED_VERIFY,
        $beInviteMode = self::CREATE_BE_INVITED_MODE_NO_NEED_ALLOW,
        $inviteMode = self::PERMISSION_MODE_EVERYBODY,
        $uptInfoMode = self::PERMISSION_MODE_ADMIN,
        $upCustomMode = self::PERMISSION_MODE_EVERYBODY
    ) {
        $data = [
            'tid'   => $tid,
            'tname' => $tname,
            'owner' => $owner,
        ];
        $keys = [
            'announcement',
            'intro',
            'joinMode',
            'custom',
            'icon',
            'beInviteMode',
            'inviteMode',
            'uptInfoMode',
            'upCustomMode'
        ];
        foreach ($keys as $key) {
            $data = Utils::arrCheckAndPush($data, $key, $$key);
        }

        return $this->post($this->nimserver_team_update, $data);
    }

    /**
     * 解散群
     *
     * @param string $tid   网易云通信服务器产生，群唯一标识，创建群时会返回，最大长度128字符
     * @param string $owner 群主用户帐号，最大长度32字符
     * @param mixed  $owner
     *
     *  @return array
     */
    public function remove($tid, $owner)
    {
        $data = [
            'tid'   => $tid,
            'owner' => $owner
        ];

        return $this->post($this->nimserver_team_remove, $data);
    }

    /**
     * 踢人出群
     *
     * @param string $tid    网易云通信服务器产生，群唯一标识，创建群时会返回，最大长度128字符
     * @param string $owner  群主用户帐号，最大长度32字符
     * @param string $member 被移除人的accid，用户账号，最大长度字符
     * @param string $attach 自定义扩展字段，最大长度512
     *
     *  @return array
     */
    public function kick($tid, $owner, $member, $attach)
    {
        $data = [
            'tid'    => $tid,
            'owner'  => $owner,
            'member' => $member,
            'attach' => $attach
        ];

        return $this->post($this->nimserver_team_kick, $data);
    }

    /**
     * 移交群主
     *
     * @param string $tid      网易云通信服务器产生，群唯一标识，创建群时会返回，最大长度128字符
     * @param string $owner    群主用户帐号，最大长度32字符
     * @param string $newOwner 新群主帐号，最大长度32字符
     * @param int    $leave    1:群主解除群主后离开群，2：群主解除群主后成为普通成员。其它414
     * @param mixed  $owner
     *
     *  @return array
     */
    public function changeOwner($tid, $owner, $newOwner, $leave = self::CHANGE_OWNER_LEAVE_GROUP)
    {
        $data = [
            'tid'      => $tid,
            'owner'    => $owner,
            'newowner' => $newOwner,
            'leave'    => $leave
        ];

        return $this->post($this->nimserver_team_change_owner, $data);
    }

    /**
     * 任命管理员
     *
     * @param string $tid
     * @param string $owner
     * @param array  $members
     *
     * @return array
     */
    public function addManager($tid, $owner, array $members)
    {
        $data = [
            'tid'     => $tid,
            'owner'   => $owner,
            'members' => json_encode($members)
        ];

        return $this->post($this->nimserver_team_add_manager, $data);
    }

    /**
     * 移除管理员
     *
     * @param string $tid     群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $owner   群主 accid
     * @param array  $members
     *
     *  @return array
     */
    public function removeManager($tid, $owner, array $members)
    {
        $data = [
            'tid'     => $tid,
            'owner'   => $owner,
            'members' => json_encode($members)
        ];

        return $this->post($this->nimserver_team_remove_manager, $data);
    }

    /**
     * 修改群昵称
     *
     * @param string $tid    群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $owner  群主 accid
     * @param string $accid  要修改群昵称的群成员 accid
     * @param string $nick   accid 对应的群昵称，最大长度32字符
     * @param string $custom 自定义扩展字段，最大长度1024字节
     *
     *  @return array
     */
    public function updateTeamNick($tid, $owner, $accid, $nick, $custom = '')
    {
        $data = [
            'tid'    => $tid,
            'owner'  => $owner,
            'accid'  => $accid,
            'nick'   => $nick,
            'custom' => $custom
        ];

        return $this->post($this->nimserver_team_update_team_nick, $data);
    }

    /**
     * 修改消息提醒开关
     *
     * @param string $tid   群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $accid 要操作的群成员accid
     * @param int    $ope
     *
     *  @return array
     */
    public function muteTeam($tid, $accid, $ope)
    {
        $data = [
            'tid'   => $tid,
            'accid' => $accid,
            'ope'   => $ope
        ];

        return $this->post($this->nimserver_team_mute_team, $data);
    }

    /**
     * 禁言群成员
     *
     * @param string $tid   群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $owner 要操作的群成员accid
     * @param string $accid 禁言对象的accid
     * @param int    $mute  1-禁言，0-解禁
     *
     *  @return array
     */
    public function muteTlist($tid, $owner, $accid, $mute = self::MUTE_NO)
    {
        $data = [
            'tid'   => $tid,
            'owner' => $owner,
            'accid' => $accid,
            'mute'  => $mute
        ];

        return $this->post($this->nimserver_team_mute_tlist, $data);
    }

    /**
     * 主动退群
     *
     * @param string $tid   群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $accid 禁言对象的accid
     *
     *  @return array
     */
    public function leave($tid, $accid)
    {
        $data = [
            'tid'   => $tid,
            'accid' => $accid,
        ];

        return $this->post($this->nimserver_team_leave, $data);
    }

    /**
     * 将群组整体禁言
     *
     * @param string $tid   群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $owner 要操作的群成员accid
     * @param int    $mute  1-禁言，0-解禁
     *
     *  @return array
     */
    public function muteTlistAll($tid, $owner, $mute = self::MUTE_NO)
    {
        $data = [
            'tid'   => $tid,
            'owner' => $owner,
            'mute'  => $mute
        ];

        return $this->post($this->nimserver_team_mute_tlist_all, $data);
    }

    /**
     * 获取群组禁言列表
     *
     * @param string $tid   群唯一标识，创建群时网易云通信服务器产生并返回
     * @param string $owner 要操作的群成员accid
     *
     *  @return array
     */
    public function listTeamMute($tid, $owner)
    {
        $data = [
            'tid'   => $tid,
            'owner' => $owner,
        ];

        return $this->post($this->nimserver_team_list_team_mute, $data);
    }
}
