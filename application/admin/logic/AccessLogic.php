<?php
namespace app\admin\logic;

use app\admin\model\Access;

class AccessLogic
{

    public function getRoleBy($where=[])
    {
        $filter = [];
        if(!empty($where) && is_array($where))
        {
            foreach ($where as $k => $v)
            {
                $filter[$k] = $v;
            }

        }
        return Access::where($filter)->column('node_id');

    }

}