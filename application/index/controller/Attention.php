<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\UserLogic;

class Attention extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
    }

    public function index(){
        $res = $this->_logic->userIncAttention($this->user_id);
        if($res['has_many_attention'])
        {
            foreach($res['has_many_attention'] as $k => $v)
            {
                if(!empty($v['belongs_to_attention']))
                {
                    $res['has_many_attention'][$k]['belongs_to_attention']
                        = array_merge($v['belongs_to_attention'], $this->_logic->userDetail($v['follow_id'], ['state' => 2]));//抛出
                }
            }

        }
        $this->assign('res', $res);
        return view();
    }
}