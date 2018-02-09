<?php
namespace app\admin\controller;

use app\admin\model\Admin;
use think\Request;

class Team extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function settle()
    {
        //$list = Admin::where(["role" => Admin::RING_ROLE_ID])->manager()->select();
        $where = Admin::manager();
        $where['role'] = Admin::RING_ROLE_ID;
        $list = Admin::where($where)->select();
        dump(Admin::getLastSql());
        dump(collection($list)->toArray());
    }
}