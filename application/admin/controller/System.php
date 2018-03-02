<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/1
 * Time: 下午6:36
 */

namespace app\admin\controller;

use app\admin\logic\SystemLogic;
use think\Db;
use think\Request;
class System extends Base
{
    public $systemLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->systemLogic = new SystemLogic();
    }

    public function lists()
    {
        $lists = $this->systemLogic->getAll();
        $lists = $this->systemLogic->getAliasList($lists);
        $this->assign('lists', $lists);
        return view();

    }

    public function modify()
    {
        if(request()->isPost()) {
            $data = input('post.');
            $addArr = [];
            foreach ($data as $k => $v) {
                $addArr[] = [
                    'alias' => $k,
                    'val' => $v
                ];
            }
            $postLength = count($data);
            $dataLength = count(array_unique(array_keys($data)));
            if($postLength != $dataLength) return $this->fail("系统提示：别名已存在！");

            if ($this->systemLogic->updateAll($addArr)) {
                return $this->ok();
            } else {
                return $this->fail("修改失败！");
            }
        }

    }

}