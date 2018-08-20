<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\AdminLogic;

class Team extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new AdminLogic();
    }

    public function settle()
    {
        $_res = $this->_logic->pageTeamLists("settle", input(""));
        $pageFee = array_sum(collection($_res['lists']['data'])->column("total_fee"));
        $pageIncome = array_sum(collection($_res['lists']['data'])->column("total_income"));
        $isAdmin = $this->_logic->isAdmin($this->adminId);
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalFee", $_res['totalFee']);
        $this->assign("totalIncome", $_res['totalIncome']);
        $this->assign("pageFee", $pageFee);
        $this->assign("pageIncome", $pageIncome);
        $this->assign("search", input(""));
        $this->assign("isAdmin", $isAdmin);
        return view();
    }

    public function operate()
    {
        $_res = $this->_logic->pageTeamLists("operate", input(""));
        $pageFee = array_sum(collection($_res['lists']['data'])->column("total_fee"));
        $pageIncome = array_sum(collection($_res['lists']['data'])->column("total_income"));
        $isAdmin = $this->_logic->isAdmin($this->adminId);
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalFee", $_res['totalFee']);
        $this->assign("totalIncome", $_res['totalIncome']);
        $this->assign("pageFee", $pageFee);
        $this->assign("pageIncome", $pageIncome);
        $this->assign("search", input(""));
        $this->assign("isAdmin", $isAdmin);
        return view();
    }

    public function member()
    {
        //$_res = $this->_logic->pageTeamLists("member", input(""));
        $_res = $this->_logic->pageMemberLists(input(""));
        $pageFee = array_sum(collection($_res['lists']['data'])->column("total_fee"));
        $pageIncome = array_sum(collection($_res['lists']['data'])->column("total_income"));
        $tableCols = $this->_logic->tableColumnShow();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalFee", $_res['totalFee']);
        $this->assign("totalIncome", $_res['totalIncome']);
        $this->assign("pageFee", $pageFee);
        $this->assign("pageIncome", $pageIncome);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    public function ring()
    {
        $_res = $this->_logic->pageRingLists(input(""));
        $pageFee = array_sum(collection($_res['lists']['data'])->column("total_fee"));
        $pageIncome = array_sum(collection($_res['lists']['data'])->column("total_income"));
        $tableCols = $this->_logic->tableColumnShow();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalFee", $_res['totalFee']);
        $this->assign("totalIncome", $_res['totalIncome']);
        $this->assign("pageFee", $pageFee);
        $this->assign("pageIncome", $pageIncome);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    public function createSettle()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
                $data['role'] = \app\admin\model\Admin::SETTLE_ROLE_ID;
                $data['pid'] = $this->_logic->getAdminPid();
                $data['code'] = $this->_logic->getAdminCode($data['role']);
                $adminId = $this->_logic->adminCreate($data);
                if(0 < $adminId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modifySettle($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['username']);
                if(empty($data['password'])){
                    unset($data['password']);
                }
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "settle");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function giveSettle($admin_id = null)
    {
        $isAdmin = $this->_logic->isAdmin($this->adminId);
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                if($isAdmin){
                    $adminId = input("post.id/d");
                    $money = input("post.money/f");
                    $remark = input("post.remark/s");
                    if($this->_logic->giveProxy($adminId, $money, $remark)){
                        return $this->ok();
                    } else {
                        return $this->fail("操作失败！");
                    }
                }else{
                    return $this->fail("系统提示：非法操作！");
                }
            }
        }
        $proxy = $this->_logic->teamAdminById($admin_id, "settle");
        if($proxy && $isAdmin){
            $this->assign("proxy", $proxy);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function createOperate()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('createTeam')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
                $data['role'] = \app\admin\model\Admin::OPERATE_ROLE_ID;
                $data['code'] = $this->_logic->getAdminCode($data['role']);
                $adminId = $this->_logic->adminCreate($data);
                if(0 < $adminId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        $parent = $this->_logic->teamAdminsByRole("settle");
        $this->assign("parent", $parent);
        return view();
    }

    public function modifyOperate($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('modifyTeam')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['username']);
                if(empty($data['password'])){
                    unset($data['password']);
                }
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "operate");
        if($admin){
            $parent = $this->_logic->teamAdminsByRole("settle");
            $this->assign("admin", $admin);
            $this->assign("parent", $parent);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function giveOperate($admin_id = null)
    {
        $isAdmin = $this->_logic->isAdmin($this->adminId);
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                if($isAdmin){
                    $adminId = input("post.id/d");
                    $money = input("post.money/f");
                    $remark = input("post.remark/s");
                    if($this->_logic->giveProxy($adminId, $money, $remark)){
                        return $this->ok();
                    } else {
                        return $this->fail("操作失败！");
                    }
                }else{
                    return $this->fail("系统提示：非法操作！");
                }
            }
        }
        $proxy = $this->_logic->teamAdminById($admin_id, "operate");
        if($proxy && $isAdmin){
            $this->assign("proxy", $proxy);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function createMember()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('createTeam')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
                $data['role'] = \app\admin\model\Admin::MEMBER_ROLE_ID;
                $data['code'] = $this->_logic->getAdminCode($data['role']);
                $adminId = $this->_logic->adminCreate($data);
                if(0 < $adminId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        $parent = $this->_logic->teamAdminsByRole("operate");
        $this->assign("parent", $parent);
        return view();
    }

    public function modifyMember($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('modifyTeam')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['username']);
                if(empty($data['password'])){
                    unset($data['password']);
                }
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "member");
        if($admin){
            $parent = $this->_logic->teamAdminsByRole("operate");
            $this->assign("admin", $admin);
            $this->assign("parent", $parent);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function memberWechat($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('wechat')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $data['create_by'] = manager()['admin_id'];
                $adminId = $data['id'];
                unset($data['id']);
                $res = $this->_logic->saveRingWechat($adminId, $data);
                if($res !== false){
                    return $this->ok();
                } else {
                    return $this->fail("配置失败！");
                }
            }
        }
        $member = $this->_logic->memberWechat($id);
        $this->assign("member", $member);
        return view("wechat");
    }

    public function createRing()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('createTeam')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
                $data['role'] = \app\admin\model\Admin::RING_ROLE_ID;
                $data['code'] = $this->_logic->getAdminCode($data['role']);
                $adminId = $this->_logic->adminCreate($data);
                if(0 < $adminId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        $parent = $this->_logic->teamAdminsByRole("member");
        $this->assign("parent", $parent);
        return view();
    }

    public function modifyRing($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('modifyTeam')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['username']);
                if(empty($data['password'])){
                    unset($data['password']);
                }
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "ring");
        if($admin){
            $parent = $this->_logic->teamAdminsByRole("member");
            $this->assign("admin", $admin);
            $this->assign("parent", $parent);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function recharge()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('recharge')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $adminId = input("post.id/d");
                $money = input("post.number/f");
                $res = $this->_logic->depositRecharge($adminId, $money);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("充值失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }

    public function settlePoint($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('point')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "admin_id" => input("post.id/d"),
                    "point" => input("post.point/f"),
                    "jiancang_point" => input("post.jiancang_point/f"),
                    "defer_point" => input("post.defer_point/f")
                ];
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "settle");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function operatePoint($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('point')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "admin_id" => input("post.id/d"),
                    "point" => input("post.point/f"),
                    "jiancang_point" => input("post.jiancang_point/f"),
                    "defer_point" => input("post.defer_point/f")
                ];
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "operate");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function memberPoint($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('point')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "admin_id" => input("post.id/d"),
                    "point" => input("post.point/f"),
                    "jiancang_point" => input("post.jiancang_point/f"),
                    "defer_point" => input("post.defer_point/f")
                ];
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "member");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function ringPoint($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('point')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "admin_id" => input("post.id/d"),
                    "point" => input("post.point/f"),
                    "jiancang_point" => input("post.jiancang_point/f"),
                    "defer_point" => input("post.defer_point/f")
                ];
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "ring");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }

    // 推广链接
    public function ringShare($id = null)
    {
        $admin = $this->_logic->teamAdminById($id, "ring");
        if($admin){
            $shareUrl = url('index/Home/register', ['_c' => base64_encode($admin['code'])], true, "m.sxxishang.com");
            $this->assign("admin", $admin);
            $this->assign("shareUrl", $shareUrl);
            return view();
        }else{
            return "非法操作！";
        }
    }

    /*public function rebate()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('rebate')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "admin_id" => input("post.id/d"),
                    "point" => input("post.point/f")
                ];
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }*/

    public function giveLog()
    {
        $_res = $this->_logic->pageAdminGiveLogs(input(''));
        $roles = $this->_logic->allTeamRoles();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        $this->assign("roles", $roles);
        return view();
    }
    public function exportTeamGiveLog()
    {
        $param = input("");
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        require ROOT_PATH.'vendor/PHPExcel/Classes/PHPExcel.php';
        //获取数据
        $roles = $this->_logic->allTeamRoles();
        $title = '代理商赠金列表';
        dump($roles);die();
        if(input('role'))
        {
            $title = $roles['role'].'-赠金列表';
        }

        $data = $this->_logic->adminGiveLogs($param);

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
        $Excel->setActiveSheetIndex(0)->setCellValue('A2','代理商ID');
        $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('B2','登录名');
        $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('C2','代理商类型');
        $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('D2','赠金金额');
        $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('E2','赠金时间');
        $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('F2','赠金人');
        $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $Excel->setActiveSheetIndex(0)->setCellValue('G2','备注');

        $filePath = ROOT_PATH. 'excel/';

        //内容
        foreach ($data as $val) {
            $Excel->setActiveSheetIndex(0)->setCellValue('A'.$n, $val['admin_id']);
            $Excel->setActiveSheetIndex(0)->setCellValue('B'.$n, $val['belongs_to_admin']['nickname']);
            $Excel->setActiveSheetIndex(0)->setCellValue('C'.$n, $val['belongs_to_admin']['has_one_role']['name']);
            $Excel->setActiveSheetIndex(0)->setCellValue('D'.$n, $val['amount']);
            $Excel->setActiveSheetIndex(0)->setCellValue('E'.$n, date('Y-m-d H:i:s', $val['create_at']));
            $Excel->setActiveSheetIndex(0)->setCellValue('F'.$n, $val['has_one_operator']['username']);
            $Excel->setActiveSheetIndex(0)->setCellValue('G'.$n, $val['remark']);
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
}