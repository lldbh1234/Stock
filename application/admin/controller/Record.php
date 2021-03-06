<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use app\admin\logic\StockLogic;
use think\Request;
use app\admin\logic\RecordLogic;

class Record extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new RecordLogic();
    }

    // 代理商个人返点记录
    public function my()
    {
        $res = $this->_logic->pageSelfRecordById($this->adminId, input(""));
        $type = [0 => "盈利分成", 1 => "建仓费分成", 2=> "递延费分成", 3=>"系统赠金"];
        array_filter($res['lists']['data'], function (&$item) use ($type){
            $item["type_text"] = $type[$item["type"]];
        });
        $pageMoney = array_sum(collection($res['lists']['data'])->column("money"));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("pageMoney", $pageMoney);
        $this->assign("totalMoney", $res['totalMoney']);
        $this->assign("search", input(""));
        return view();
    }

    // 充值记录
    public function recharge()
    {
        $res = $this->_logic->pageUserRechargeList(input(""));
        $way = config('recharge_way');
        array_filter($res['lists']['data'], function (&$_item) use ($way){
            $_item['type_text'] = $way[$_item['type']];
        });
        $tableCols = (new AdminLogic())->tableColumnShow($this->adminId);
        $pageAmount = array_sum(collection($res['lists']['data'])->column("amount"));
        $pageActual = array_sum(collection($res['lists']['data'])->column("actual"));
        $pagePoundage = array_sum(collection($res['lists']['data'])->column("poundage"));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("totalAmount", $res['totalAmount']);
        $this->assign("totalActual", $res['totalActual']);
        $this->assign("totalPoundage", $res['totalPoundage']);
        $this->assign("pageAmount", $pageAmount);
        $this->assign("pageActual", $pageActual);
        $this->assign("pagePoundage", $pagePoundage);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    // 牛人返点
    public function niuren()
    {
        $res = $this->_logic->pageNiurenRecord(input(""));
        $type = [0 => "跟单分成", 1 => "建仓费分成", 2=> "递延费分成"];
        array_filter($res['lists']['data'], function (&$item) use ($type){
            $item["type_text"] = $type[$item["type"]];
        });
        $pageMoney = array_sum(collection($res['lists']['data'])->column("money"));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("pageMoney", $pageMoney);
        $this->assign("totalMoney", $res['totalMoney']);
        $this->assign("search", input(""));
        return view();
    }

    // 经纪人返点
    public function manager()
    {
        $res = $this->_logic->pageManagerRecord(input(""));
        $type = [0 => "盈利分成", 1 => "建仓费分成", 2=> "递延费分成"];
        array_filter($res['lists']['data'], function (&$item) use ($type){
            $item["type_text"] = $type[$item["type"]];
        });
        $pageMoney = array_sum(collection($res['lists']['data'])->column("money"));
        $tableCols = (new AdminLogic())->tableColumnShow();
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("pageMoney", $pageMoney);
        $this->assign("totalMoney", $res['totalMoney']);
        $this->assign("search", input(""));
        $this->assign("tableCols", $tableCols);
        return view();
    }

    // 代理商返点
    public function proxy()
    {
        $res = $this->_logic->pageAdminRecord(input(""));
        $roles = (new AdminLogic())->allTeamRoles();
        $type = [0 => "盈利分成", 1 => "建仓费分成", 2=> "递延费分成", 3=> "平台赠送"];
        array_filter($res['lists']['data'], function (&$item) use ($type, $roles){
            $item["role_text"] = $roles[$item["belongs_to_admin"]["role"]];
            $item["type_text"] = $type[$item["type"]];
        });
        $pageMoney = array_sum(collection($res['lists']['data'])->column("money"));
        $tableCols = (new AdminLogic())->tableColumnShow();
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("pageMoney", $pageMoney);
        $this->assign("totalMoney", $res['totalMoney']);
        $this->assign("roles", $roles);
        $this->assign("search", input(""));
        $this->assign("tableCols", $tableCols);
        return view();
    }

    //代理商出金列表
    public function proxyWithdrawLists()
    {
        $_res = $this->_logic->pageProxyWithdrawLists(input(''));
        $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
        $pageActual = array_sum(collection($_res['lists']['data'])->column("actual"));
        $pagePoundage = array_sum(collection($_res['lists']['data'])->column("poundage"));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalAmount", $_res['totalAmount']);
        $this->assign("totalActual", $_res['totalActual']);
        $this->assign("totalPoundage", $_res['totalPoundage']);
        $this->assign("pageAmount", $pageAmount);
        $this->assign("pageActual", $pageActual);
        $this->assign("pagePoundage", $pagePoundage);
        $this->assign("search", input(""));
        return view();
    }

    //代理商出金详情
    public function proxyWithdrawDetail($id = null, $type = 1)
    {
        $withdraw = $this->_logic->proxyWithdrawById($id);
        if($withdraw){
            switch ($type){
                case "1":
                    // 银行卡信息
                    $this->assign("withdraw", $withdraw);
                    return view();
                    break;
                case "2":
                    // 代理信息
                    $_adminLogic = new AdminLogic();
                    $proxy = $_adminLogic->proxyFamily($withdraw['admin_id']);
                    $familyShow = $_adminLogic->proxyFamilyShow($withdraw['admin_id']);
                    $this->assign("proxy", $proxy);
                    $this->assign("familyShow", $familyShow);
                    return view("proxyWithdrawDetail2");
                    break;
                default:
                    return "非法操作！";
                    break;
            }
        }else{
            return "非法操作！";
        }
    }

    // 代理商出金操作
    public function doProxyWithdraw()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('AdminWithDraw');
            if(!$validate->scene('do')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $id = input('post.id/d');
                $state = input('post.state/d');
                list($res, $msg) = $this->_logic->doProxyWithdraw($id, $state);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail($msg);
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }

    // 递延费扣除记录
    public function defer()
    {
        $res = $this->_logic->pageDeferRecord(input(""));
        $type = [0 => "余额扣除", 1 => "保证金扣除"];
        array_filter($res['lists']['data'], function (&$item) use ($type){
            $item["type_text"] = $type[$item["type"]];
        });
        $pageMoney = array_sum(collection($res['lists']['data'])->column("money"));
        $tableCols = (new AdminLogic())->tableColumnShow($this->adminId);
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("pageMoney", $pageMoney);
        $this->assign("totalMoney", $res['totalMoney']);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }
}