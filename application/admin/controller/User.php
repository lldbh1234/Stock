<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/1
 * Time: 下午6:36
 */

namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use app\admin\logic\StockLogic;
use app\admin\logic\UserGiveLogic;
use app\admin\logic\UserLogic;
use app\admin\logic\UserWithdrawLogic;
use think\Db;
use think\Request;
class User extends Base
{
    public $userLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->userLogic = new UserLogic();
    }

    public function lists()
    {
        $_res = $this->userLogic->pageUserLists(input(''), 0);
        $pageAccount = array_sum(collection($_res['lists']['data'])->column("account"));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalAccount", $_res['totalAccount']);
        $this->assign("pageAccount", $pageAccount);
        $this->assign("search", input(""));
        return view();
    }

    public function modify()
    {
        if(request()->isPost()) {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['account']);
                if($this->userLogic->update($data)){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $id = input('user_id');
        $data = $this->userLogic->getOne($id);
        $this->assign('data', $data);
        $this->assign('id', $id);
        return view();
    }

    public function withdrawState()
    {
        if(request()->isPost()){
            $userId = input("post.user_id");
            $user = $this->userLogic->userById($userId);
            if($user){
                $data = [
                    "user_id" => $user['user_id'],
                    "withdraw_state" => ($user['withdraw_state'] + 1) % 2
                ];
                $res = $this->userLogic->update($data);
                if($res !== false){
                    return $this->ok();
                }
            }else{
                return $this->fail("系统提示：非法操作！");
            }
        }else{
            return "非法操作！";
        }
    }

    public function detail($id = null, $type = 1)
    {
        switch ($type){
            case '1':
                // 基本信息
                $user = $this->userLogic->userIncFamily($id);
                if($user){
                    $this->assign("user", $user);
                    return view("userDetail1");
                }else{
                    return "非法操作！";
                }
                break;
            case '2':
                //当前持仓
                $_res = $this->userLogic->pageUserOrderByUserId($id, 3, input(""), 10);
                if($_res){
                    if($_res['lists']['data']){
                        $codes = array_column($_res['lists']['data'], "code");
                        $quotation = (new StockLogic())->stockQuotationBySina($codes);
                        array_filter($_res['lists']['data'], function(&$item) use ($quotation){
                            $item['last_px'] = isset($quotation[$item['code']]['last_px']) ? number_format($quotation[$item['code']]['last_px'], 2) : '-';
                            $item['pl'] = isset($quotation[$item['code']]['last_px']) ? number_format(($item['last_px'] - $item['price']) * $item['hand'], 2) : "-";
                        });
                    }
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("totalProfit", $_res['totalProfit']);
                    $this->assign("totalDeposit", $_res['totalDeposit']);
                    $this->assign("totalJiancang", $_res['totalJiancang']);
                    $this->assign("totalDefer", $_res['totalDefer']);
                    $this->assign("search", input(""));
                    return view("userDetail2");
                }else{
                    return "非法操作！";
                }
                break;
            case '3':
                //历史交易
                $_res = $this->userLogic->pageUserOrderByUserId($id, 2, input(""), 10);
                if($_res){
                    $pageProfit = array_sum(collection($_res['lists']['data'])->column("profit"));
                    $pageDeposit = array_sum(collection($_res['lists']['data'])->column("deposit"));
                    $pageJiancang = array_sum(collection($_res['lists']['data'])->column("jiancang_fee"));
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("pageProfit", $pageProfit);
                    $this->assign("pageDeposit", $pageDeposit);
                    $this->assign("pageJiancang", $pageJiancang);
                    $this->assign("totalProfit", $_res['totalProfit']);
                    $this->assign("totalDeposit", $_res['totalDeposit']);
                    $this->assign("totalJiancang", $_res['totalJiancang']);
                    $this->assign("totalDefer", $_res['totalDefer']);
                    $this->assign("search", input(""));
                    return view("userDetail3");
                }else{
                    return "非法操作！";
                }
                break;
            case '4':
                //出金记录
                $_res = $this->userLogic->pageUserWithdrawByUserId($id, input(""), 10);
                if($_res){
                    $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
                    $pageActual = array_sum(collection($_res['lists']['data'])->column("actual"));
                    $pagePoundage = array_sum(collection($_res['lists']['data'])->column("poundage"));
                    $state = [0 => '待审核', '1' => '代付中', 2=> '已到账', -1 => '已拒绝'];
                    array_filter($_res['lists']['data'], function(&$_item) use ($state){
                        $_item['state_text'] = $state[$_item['state']];
                        $_item['remark'] = json_decode($_item['remark'], true);
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("pageAmount", $pageAmount);
                    $this->assign("pageActual", $pageActual);
                    $this->assign("pagePoundage", $pagePoundage);
                    $this->assign("totalAmount", $_res['totalAmount']);
                    $this->assign("totalActual", $_res['totalActual']);
                    $this->assign("totalPoundage", $_res['totalPoundage']);
                    $this->assign("search", input(""));
                    return view("userDetail4");
                }else{
                    return "非法操作！";
                }
                break;
            case '5':
                // 入金记录
                $_res = $this->userLogic->pageUserRechargeByUserId($id, input(""), 10);
                if($_res){
                    $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
                    $type = config('recharge_way');
                    array_filter($_res['lists']['data'], function(&$_item) use ($type){
                        $_item['type_text'] = $type[$_item['type']];
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("totalAmount", $_res['totalAmount']);
                    //$this->assign("totalActual", $_res['totalActual']);
                    //$this->assign("totalPoundage", $_res['totalPoundage']);
                    $this->assign("pageAmount", $pageAmount);
                    $this->assign("search", input(""));
                    return view("userDetail5");
                }else{
                    return "非法操作！";
                }
                break;
            case '6':
                // 资金记录
                $_res = $this->userLogic->pageUserRecordByUserId($id, input(""), 10);
                if($_res){
                    $capital = $this->_userCapital($id);
                    $type = config("user_record_type");
                    array_filter($_res['lists']['data'], function(&$_item) use ($type){
                        $_item['type_text'] = $type["{$_item['type']}_{$_item['direction']}"];
                        $_item['remark'] = json_decode($_item['remark'], true) ? json_decode($_item['remark'], true) : $_item['remark'];
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("capital", $capital);
                    $this->assign("search", input(""));
                    return view("userDetail6");
                }else{
                    return "非法操作！";
                }
                break;
            default:
                return "非法操作！";
                break;
        }
    }

    // 资金详情
    private function _userCapital($userId)
    {
        $capital = [
            "netAssets" => 0, //净资产
            "expendableFund" => 0, //可用资金
            "floatPL" => 0, //浮动盈亏
            "marketValue" => 0, //持仓市值
        ];
        $user = $this->userLogic->userIncOrder($userId, $state = 3);
        $codes = array_column($user["has_many_order"], "code");
        if($codes){
            $quotation = (new StockLogic())->stockQuotationBySina($codes);
            array_filter($user["has_many_order"], function($item) use ($quotation, &$capital){
                $floatPL = ($quotation[$item['code']]['last_px'] - $item['price']) * $item['hand'];
                $marketValue = $item['hand'] * $quotation[$item['code']]['last_px'];
                $capital['floatPL'] += $floatPL;
                $capital['marketValue'] += $marketValue;
            });
        }
        $capital['expendableFund'] = $user['account']; //可用资金
        $capital['netAssets'] = $capital['expendableFund'] + $user['blocked_account'] + $capital['floatPL']; //净资产
        return $capital;
    }

    public function modifyPwd()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify_pwd')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->userLogic->update(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
    }

    public function giveLists()
    {
        $_res = $this->userLogic->pageUserLists(input(''));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function giveAccount($user_id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $userId = input("post.user_id/d");
                $money = input("post.money/f");
                $remark = input("post.remark/s");
                if($this->userLogic->giveMoney($userId, $money, $remark)){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $user = $this->userLogic->userById($user_id);
        if($user){
            $this->assign("user", $user);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function giveLog()
    {
        $_res = (new UserGiveLogic())->pageUserGiveLists(input(''));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }
    public function exportUserGiveLog()
    {
        $param = input("");
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        require ROOT_PATH.'vendor/PHPExcel/Classes/PHPExcel.php';
        //获取数据
        $title = '会员赠金列表';

        $data = (new UserGiveLogic())->userGiveLists($param);

        if($data && isset($data['lists']))
        {
            $data = $data['lists'];
        }else{
            return $this->error('暂时没有导出的数据');
        }

        $n = 3;
        //加载PHPExcel插件
        $Excel = new \PHPExcel();
        $Excel->setActiveSheetIndex(0);
        //编辑表格    标题
        $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
        $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
        $Excel->getActiveSheet()->setCellValue('A1', $title);
        //表头
        $Excel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
        $Excel->setActiveSheetIndex(0)->setCellValue('A2','登录名');
        $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
        $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
        $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('D2','微圈');
        $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('E2','赠金金额');
        $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('F2','赠金时间');
        $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('G2','赠金人');
        $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('H2','备注');

        $filePath = ROOT_PATH. 'excel/';

        //内容
        foreach ($data as $val) {
            $Excel->setActiveSheetIndex(0)->setCellValue('A'.$n, $val['has_one_user']['username']);
            $Excel->setActiveSheetIndex(0)->setCellValue('B'.$n, $val['has_one_user']['nickname']);
            $Excel->setActiveSheetIndex(0)->setCellValue('C'.$n, $val['has_one_user']['mobile']);
            $Excel->setActiveSheetIndex(0)->setCellValue('D'.$n, $val['has_one_user']['has_one_admin']['username']);
            $Excel->setActiveSheetIndex(0)->setCellValue('E'.$n, $val['amount']);
            $Excel->setActiveSheetIndex(0)->setCellValue('F'.$n, date('Y-m-d H:i:s', $val['create_at']));
            $Excel->setActiveSheetIndex(0)->setCellValue('G'.$n, $val['create_by']);
            $Excel->setActiveSheetIndex(0)->setCellValue('H'.$n, $val['remark']);
            $n++;

        }
        $date = date("Y-m-d H:i:s");
        $filename = "{$filePath}{$date}.xls";
//        $filename = iconv('UTF-8', 'GBK', $filename);
        $fp = fopen($filename, 'w+');
        if (!is_writable($filename)) {
            die('文件:' . $filename . '不可写，请检查！');
        }
        $objWriter= \PHPExcel_IOFactory::createWriter($Excel, 'Excel5');
        $objWriter->save($filename);
        fclose($fp);
        //压缩下载
        require ROOT_PATH . 'vendor/PHPZip/PHPZip.php';
        $archive = new \PHPZip();
        $archive->ZipAndDownload($filePath, $title, $filename);
    }

    public function withdrawLists()
    {
        $_res = (new UserWithdrawLogic())->pageUserWithdrawLists(input(''));
        $isAdmin = (new AdminLogic())->isAdmin($this->adminId);
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalAmount", $_res['totalAmount']);
        $this->assign("totalActual", $_res['totalActual']);
        $this->assign("totalPoundage", $_res['totalPoundage']);
        $this->assign("search", input(""));
        $this->assign("isAdmin", $isAdmin);
        return view();
    }

    public function withdrawDetail($id = null, $type = 1)
    {
        $withdraw = (new UserWithdrawLogic())->getWithdrawById($id);
        if($withdraw) {
            switch ($type) {
                case '1':
                    //银行卡信息
                    $state = [0=>"待审核", 1=>"审核通过",-1=>"审核拒绝",2=>"已到账"];
                    $withdraw['remark'] = json_decode($withdraw['remark'], true);
                    $withdraw['state_text'] = $state[$withdraw['state']];
                    $this->assign("withdraw", $withdraw);
                    return view();
                    break;
                case '2':
                    // 代理信息
                    $user = $this->userLogic->userIncFamily($withdraw['user_id']);
                    if($user){
                        $this->assign("user", $user);
                        return view("withdrawDetail2");
                    }else{
                        return "非法操作！";
                    }
                    break;
                case '3':
                    // 当前持仓
                    $_res = $this->userLogic->pageUserOrderByUserId($withdraw['user_id'], 3, input(""), 10);
                    if($_res){
                        if($_res['lists']['data']){
                            $codes = array_column($_res['lists']['data'], "code");
                            $quotation = (new StockLogic())->stockQuotationBySina($codes);
                            array_filter($_res['lists']['data'], function(&$item) use ($quotation){
                                $item['last_px'] = isset($quotation[$item['code']]['last_px']) ? number_format($quotation[$item['code']]['last_px'], 2) : '-';
                                $item['pl'] = isset($quotation[$item['code']]['last_px']) ? number_format(($item['last_px'] - $item['price']) * $item['hand'], 2) : "-";
                            });
                        }
                        $this->assign("datas", $_res['lists']);
                        $this->assign("pages", $_res['pages']);
                        $this->assign("totalProfit", $_res['totalProfit']);
                        $this->assign("totalDeposit", $_res['totalDeposit']);
                        $this->assign("totalJiancang", $_res['totalJiancang']);
                        $this->assign("totalDefer", $_res['totalDefer']);
                        $this->assign("search", input(""));
                        return view("withdrawDetail3");
                    }else{
                        return "非法操作！";
                    }
                    break;
                case '4':
                    // 历史交易
                    $_res = $this->userLogic->pageUserOrderByUserId($withdraw['user_id'], 2, input(""), 10);
                    if($_res){
                        $pageProfit = array_sum(collection($_res['lists']['data'])->column("profit"));
                        $pageDeposit = array_sum(collection($_res['lists']['data'])->column("deposit"));
                        $pageJiancang = array_sum(collection($_res['lists']['data'])->column("jiancang_fee"));
                        $this->assign("datas", $_res['lists']);
                        $this->assign("pages", $_res['pages']);
                        $this->assign("pageProfit", $pageProfit);
                        $this->assign("pageDeposit", $pageDeposit);
                        $this->assign("pageJiancang", $pageJiancang);
                        $this->assign("totalProfit", $_res['totalProfit']);
                        $this->assign("totalDeposit", $_res['totalDeposit']);
                        $this->assign("totalJiancang", $_res['totalJiancang']);
                        $this->assign("totalDefer", $_res['totalDefer']);
                        $this->assign("search", input(""));
                        return view("withdrawDetail4");
                    }else{
                        return "非法操作！";
                    }
                    break;
                case '5':
                    // 入金记录
                    $_res = $this->userLogic->pageUserRechargeByUserId($withdraw['user_id'], input(""), 10);
                    if($_res){
                        $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
                        $type = config('recharge_way');
                        array_filter($_res['lists']['data'], function(&$_item) use ($type){
                            $_item['type_text'] = $type[$_item['type']];
                        });
                        $this->assign("datas", $_res['lists']);
                        $this->assign("pages", $_res['pages']);
                        $this->assign("totalAmount", $_res['totalAmount']);
                        //$this->assign("totalActual", $_res['totalActual']);
                        //$this->assign("totalPoundage", $_res['totalPoundage']);
                        $this->assign("pageAmount", $pageAmount);
                        $this->assign("search", input(""));
                        return view("withdrawDetail5");
                    }else{
                        return "非法操作！";
                    }
                    break;
                case '6':
                    // 出金记录
                    $_res = $this->userLogic->pageUserWithdrawByUserId($withdraw['user_id'], input(""), 10);
                    if($_res){
                        $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
                        $pageActual = array_sum(collection($_res['lists']['data'])->column("actual"));
                        $pagePoundage = array_sum(collection($_res['lists']['data'])->column("poundage"));
                        $state = [0 => '待审核', '1' => '代付中', 2=> '已到账', -1 => '已拒绝'];
                        array_filter($_res['lists']['data'], function(&$_item) use ($state){
                            $_item['state_text'] = $state[$_item['state']];
                            $_item['remark'] = json_decode($_item['remark'], true);
                        });
                        $this->assign("datas", $_res['lists']);
                        $this->assign("pages", $_res['pages']);
                        $this->assign("pageAmount", $pageAmount);
                        $this->assign("pageActual", $pageActual);
                        $this->assign("pagePoundage", $pagePoundage);
                        $this->assign("totalAmount", $_res['totalAmount']);
                        $this->assign("totalActual", $_res['totalActual']);
                        $this->assign("totalPoundage", $_res['totalPoundage']);
                        $this->assign("search", input(""));
                        return view("withdrawDetail6");
                    }else{
                        return "非法操作！";
                    }
                    break;
                default:
                    return '非法操作！';
            }
        }else{
            return "非法操作！";
        }
    }

    public function withdraw()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('UserWithDraw');
            if(!$validate->scene('user_withdraw')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $id = input('post.id/d');
                $state = input('post.state/d');
                list($res, $msg) = (new UserWithdrawLogic())->doWithdraw($id, $state);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail($msg);
                }
            }
        }
    }

    public function virtualLists()
    {
        $_res = $this->userLogic->pageUserLists(input(''), 1);
        $pageAccount = array_sum(collection($_res['lists']['data'])->column("account"));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalAccount", $_res['totalAccount']);
        $this->assign("pageAccount", $pageAccount);
        $this->assign("search", input(""));
        return view();
    }

    public function createVirtual()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('create_virtual')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $nickname = cf('nickname_prefix', config("nickname_prefix"));
                $virtual = input("post.");
                $virtual['username'] = $virtual['mobile'];
                $virtual['nickname'] = $nickname . substr($virtual["mobile"], -4);
                $virtual['face'] = config("default_face");
                $virtual['is_virtual'] = 1;
                $userId = $this->userLogic->createUser($virtual);
                if($userId > 0){
                    return $this->ok();
                }else{
                    return $this->fail("添加失败！");
                }
            }
        }
        $ring = (new AdminLogic())->teamAdminsByRole("ring");
        $this->assign("ring", $ring);
        return view();
    }

    public function modifyVirtual($user_id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify_virtual')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                if(empty($data['password'])){
                    unset($data['password']);
                }
                if($this->userLogic->update($data)){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $user = $this->userLogic->userById($user_id);
        if($user){
            $ring = (new AdminLogic())->teamAdminsByRole("ring");
            $this->assign("user", $user);
            $this->assign("ring", $ring);
            return view();
        }else{
            return "非法操作";
        }
    }

    public function virtualDetail($id = null, $type = 1)
    {
        switch ($type){
            case '1':
                // 基本信息
                $user = $this->userLogic->userIncFamily($id);
                if($user){
                    $this->assign("user", $user);
                    return view("virtualDetail1");
                }else{
                    return "非法操作！";
                }
                break;
            case '2':
                //当前持仓
                $_res = $this->userLogic->pageUserOrderByUserId($id, 3, input(""), 10);
                if($_res){
                    if($_res['lists']['data']){
                        $codes = array_column($_res['lists']['data'], "code");
                        $quotation = (new StockLogic())->stockQuotationBySina($codes);
                        array_filter($_res['lists']['data'], function(&$item) use ($quotation){
                            $item['last_px'] = isset($quotation[$item['code']]['last_px']) ? number_format($quotation[$item['code']]['last_px'], 2) : '-';
                            $item['pl'] = isset($quotation[$item['code']]['last_px']) ? number_format(($item['last_px'] - $item['price']) * $item['hand'], 2) : "-";
                        });
                    }
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("totalProfit", $_res['totalProfit']);
                    $this->assign("totalDeposit", $_res['totalDeposit']);
                    $this->assign("totalJiancang", $_res['totalJiancang']);
                    $this->assign("totalDefer", $_res['totalDefer']);
                    $this->assign("search", input(""));
                    return view("virtualDetail2");
                }else{
                    return "非法操作！";
                }
                break;
            case '3':
                //历史交易
                $_res = $this->userLogic->pageUserOrderByUserId($id, 2, input(""), 10);
                if($_res){
                    $pageProfit = array_sum(collection($_res['lists']['data'])->column("profit"));
                    $pageDeposit = array_sum(collection($_res['lists']['data'])->column("deposit"));
                    $pageJiancang = array_sum(collection($_res['lists']['data'])->column("jiancang_fee"));
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("pageProfit", $pageProfit);
                    $this->assign("pageDeposit", $pageDeposit);
                    $this->assign("pageJiancang", $pageJiancang);
                    $this->assign("totalProfit", $_res['totalProfit']);
                    $this->assign("totalDeposit", $_res['totalDeposit']);
                    $this->assign("totalJiancang", $_res['totalJiancang']);
                    $this->assign("totalDefer", $_res['totalDefer']);
                    $this->assign("search", input(""));
                    return view("virtualDetail3");
                }else{
                    return "非法操作！";
                }
                break;
            case '6':
                // 资金记录
                $_res = $this->userLogic->pageUserRecordByUserId($id, input(""), 10);
                if($_res){
                    $capital = $this->_userCapital($id);
                    $type = config("user_record_type");
                    array_filter($_res['lists']['data'], function(&$_item) use ($type){
                        $_item['type_text'] = $type["{$_item['type']}_{$_item['direction']}"];
                        $_item['remark'] = json_decode($_item['remark'], true) ? json_decode($_item['remark'], true) : $_item['remark'];
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("capital", $capital);
                    $this->assign("search", input(""));
                    return view("virtualDetail6");
                }else{
                    return "非法操作！";
                }
                break;
            default:
                return "非法操作！";
                break;
        }
    }
    public function unbindCard()
    {
        if(request()->isPost()) {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modifyCard')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $res = $this->userLogic->unbindCard($data['user_id']);
                if($res['code'] == 0){
                    return $this->ok($res['message']);
                }else{
                    return $this->fail($res['message']);
                }
            }
        }
        return $this->fail('非法操作');
    }
}