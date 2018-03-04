<?php
namespace app\index\logic;

use app\index\model\UserNotice;

class UserNoticeLogic
{
    public function create($data)
    {
        $res = model("UserNotice")->save($data);
        return $res ? model("UserNotice")->getLastInsID() : 0;
    }
    public function getAllBy($where=[])
    {
        $lists = UserNotice::where($where)->order(['read' => 'asc', 'id' => 'desc'])->select();
        return collection($lists)->toArray();
    }
    public function getAllByUid($uid)
    {
        $lists = UserNotice::where(['user_id' => $uid])->order(['read' => 'asc', 'id' => 'desc'])->select();
        return collection($lists)->toArray();
    }
    public function getContentById($id)
    {
        $content = UserNotice::find($id);
        return $content->toArray();
    }
    public function updateBy($map)
    {
        return UserNotice::update($map);
    }
    public function add($data)
    {
        return model('UserNotice')->saveAll($data);
    }

}