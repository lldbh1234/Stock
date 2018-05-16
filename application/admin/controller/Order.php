<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use think\Request;
use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;

class Order extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new OrderLogic();
    }

    // 委托
    public function index()
    {
        $_res = $this->_logic->pageEntrustOrders(input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    // 委托详情
    public function entrustDetail($id = null)
    {
        $order = $this->_logic->orderIncUserById($id, $state = 4);
        if($order){
            $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
            $order['state_text'] = $state[$order['state']];
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 委托返点
    public function entrustRebate($id = null)
    {
        $order = $this->_logic->orderIncRecordById($id, $state = 4);
        if($order){
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 历史
    public function history()
    {
        if(input('out_put') == 1) return $this->downExcel(input(""), date('Y-m-d H:i:s') .'平仓单-订单信息导出记录', 2);
        $_res = $this->_logic->pageHistoryOrders(input(""));
        $tableCols = (new AdminLogic())->tableColumnShow();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalProfit", $_res['totalProfit']);
        $this->assign("totalJiancang", $_res['totalJiancang']);
        $this->assign("totalDefer", $_res['totalDefer']);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    // 平仓详情
    public function historyDetail($id = null)
    {
        $order = $this->_logic->orderIncUserById($id, $state = 2);
        if($order){
            $holiday = explode(',', cf('holiday', ''));
            $workDay = workDay($order['original_free'], $order['free_time'], $holiday);
            $forceType = [0 => '委托平仓', 1 => '爆仓', 2 => '止盈止损', 3 => '非自动递延', 4 => '余额不足'];
            $order['force_type_text'] = $forceType[$order['force_type']];
            $order['total_defer'] = $workDay * $order['defer'];
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 平仓返点
    public function historyRebate($id = null)
    {
        $order = $this->_logic->orderIncRecordById($id, $state = 2);
        if($order){
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 持仓
    public function position()
    {
        if(input('out_put') == 1) return $this->downExcel(input(""), date('Y-m-d H:i:s') .'持仓单-订单信息导出记录');
        $_res = $this->_logic->pagePositionOrders(input(""));
        $tableCols = (new AdminLogic())->tableColumnShow();
        if($_res['lists']['data']){
            $codes = array_column($_res['lists']['data'], "code");
            $quotation = (new StockLogic())->stockQuotationBySina($codes);
            array_filter($_res['lists']['data'], function(&$item) use ($quotation){
                $item['last_px'] = isset($quotation[$item['code']]['last_px']) ? number_format($quotation[$item['code']]['last_px'], 2) : '-';
                $item['pl'] = isset($quotation[$item['code']]['last_px']) ? number_format(($item['last_px'] - $item['price']) * $item['hand'], 2) : "-";
            });
        }
        $pageDeposit = array_sum(collection($_res['lists']['data'])->column("deposit"));
        $pageJiancang = array_sum(collection($_res['lists']['data'])->column("jiancang_fee"));
        $pageDefer = array_sum(collection($_res['lists']['data'])->column("defer_total"));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalDeposit", $_res['totalDeposit']);
        $this->assign("totalJiancang", $_res['totalJiancang']);
        $this->assign("totalDefer", $_res['totalDefer']);
        $this->assign("pageDeposit", $pageDeposit);
        $this->assign("pageJiancang", $pageJiancang);
        $this->assign("pageDefer", $pageDefer);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    // 强制平仓
    public function force()
    {
        $_res = $this->_logic->pageForceOrders(input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    // 持仓详情
    public function positionDetail($id = null)
    {
        $order = $this->_logic->orderIncUserById($id, $state = 3);
        if($order){
            $hedging = [1 => '是', 0 => '否'];
            $holiday = explode(',', cf('holiday', ''));
            $workDay = workDay($order['original_free'], $order['free_time'], $holiday);
            $quotation = (new StockLogic())->stockQuotationBySina($order['code']);
            $order['is_hedging_text'] = $hedging[$order['is_hedging']];
            $order['last_px'] = isset($quotation[$order['code']]['last_px']) ? number_format($quotation[$order['code']]['last_px'], 2) : '-';
            $order['pl'] = isset($quotation[$order['code']]['last_px']) ? number_format(($order['last_px'] - $order['price']) * $order['hand'], 2) : "-";
            $order['total_defer'] = $workDay * $order['defer'];
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 持仓返点
    public function positionRebate($id = null)
    {
        $order = $this->_logic->orderIncRecordById($id, $state = 3);
        if($order){
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    /*public function buyOk()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('buyOk')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "price" => input("post.price/f"),
                    "state" => 3
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }*/

    // 持仓订单对冲
    public function hedging()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('hedging')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "price" => input("post.price/f"),
                    "state" => 3,
                    "is_hedging" => 1,
                    "update_by" => isLogin()
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    /*public function buyFail()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('buyFail')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->buyFail(input("post.id/d"));
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }*/

    public function sellOk()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('sell')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->sellOk(input("post.id/d"));
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    public function sellFail()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('sell')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "sell_price" => 0,
                    "sell_hand" => 0,
                    "sell_deposit" => 0,
                    "profit"    => 0,
                    "state"     => 3
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 强制平仓
    public function forceSell()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('force')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $price = input("post.sell_price/f");
                $res = $this->_logic->forceSell($orderId, $price);
                if($res){
                    return $this->ok("平仓成功！");
                }else{
                    return $this->fail("强制平仓失败，请稍后重试！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 送股
    public function give($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $price = input("post.price/f");
                $order = $this->_logic->orderById($id);
                $data = [
                    "order_id"  => $orderId,
                    "price"     => $price,
                    "hand"      => input("post.hand/d"),
                    "stop_profit_price" => input("post.profit/f"),
                    "stop_profit_point" => round((input("post.profit/f") - $price) / $price * 100, 2),
                    "stop_loss_price" => input("post.loss/f"),
                    "stop_loss_point" => round(($price - input("post.loss/f")) / $price * 100, 2),
                    "sell_hand" => input("post.hand/d"),
                    "sell_deposit" => $order['sell_price'] * input("post.hand/d"),
                    "profit"    => ($order['sell_price'] - $price) * input("post.hand/d"),
                ];
                $res = $this->_logic->orderGive($data);
                if($res){
                    return $this->ok("操作成功！");
                }else{
                    return $this->fail("操作失败，请稍后重试！");
                }
            }
        }else{
            $order = $this->_logic->orderById($id, $state = 6);
            if($order){
                $this->assign("order", $order);
                return view();
            }else{
                return "非法操作！";
            }
        }
    }

    // 穿仓
    public function ware()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('ware')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $price = input("post.sell/f");
                $res = $this->_logic->orderWare($orderId, $price);
                if($res){
                    return $this->ok("操作成功！");
                }else{
                    return $this->fail("操作失败，请稍后重试！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 订单转持仓
    public function toPosition()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('toPosition')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $res = $this->_logic->orderToPosition($orderId);
                if($res){
                    return $this->ok("操作成功！");
                }else{
                    return $this->fail("操作失败，请稍后重试！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }
    public function downExcel($param=[], $title = '持仓单-订单信息统计表', $type=1)
    {
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");

        require ROOT_PATH.'vendor/PHPExcel/Classes/PHPExcel.php';
        //获取数据
        if(1 == $type)
        {
            $data = $this->_logic->positionOrders($param);
            if($data && isset($data['lists']))
            {
                $data = $data['lists'];
            }else{
                return $this->error('暂时没有导出的数据');
            }

            $codes = array_column($data, "code");
            $quotation = (new StockLogic())->stockQuotationBySina($codes);
            array_filter($data, function(&$item) use ($quotation){
                $item['last_px'] = isset($quotation[$item['code']]['last_px']) ? number_format($quotation[$item['code']]['last_px'], 2) : '-';
                $item['pl'] = isset($quotation[$item['code']]['last_px']) ? number_format(($item['last_px'] - $item['price']) * $item['hand'], 2) : "-";
            });
        }elseif (2 == $type) {
            $data = $this->_logic->historyOrders($param);
            if($data && isset($data['lists']))
            {
                $data = $data['lists'];
            }else{
                return $this->error('暂时没有导出的数据');
            }

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
        $Excel->getActiveSheet()->setCellValue('A1',$title);
        //表头
        $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
        $Excel->setActiveSheetIndex(0)->setCellValue('A2','策略ID');
        $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
        $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
        $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('D2','股票代码');
        $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('E2','股票名称');
        if(1 == $type)
        {
            $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('F2','委托价');
            $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('G2','委托数量');
            $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('H2','现价');
            $Excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('I2','保证金');
            $Excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('J2','盈亏');
            $Excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('K2','市值');
            $Excel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('L2','止盈');
            $Excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('M2','止损');
            $Excel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('N2','建仓费');
            $Excel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('O2','递延费/天');
            $Excel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('P2','递延费合计');
            $Excel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('Q2','下单时间');
            $Excel->getActiveSheet()->getColumnDimension('Q')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('R2','交易模式');
            $Excel->getActiveSheet()->getColumnDimension('R')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('S2','免息截止日期');

        }elseif (2 == $type) {
            $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('F2','买入价');
            $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('G2','卖出价	');
            $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('H2','卖出数量');
            $Excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('I2','保证金');
            $Excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('J2','盈亏');
            $Excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('K2','穿仓金额');
            $Excel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('L2','买入时间');
            $Excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('M2','交易模式');
            $Excel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
            $Excel->setActiveSheetIndex(0)->setCellValue('N2','卖出时间');

        }


        $filePath = ROOT_PATH. 'excel/';
        $num = 1;
        $m = 1;

        //内容
        foreach ($data as $val) {
            $Excel->setActiveSheetIndex(0)->setCellValue('A'.$n, $val['order_id']);
            $Excel->setActiveSheetIndex(0)->setCellValue('B'.$n, $val['has_one_user']['nickname']?:$val['has_one_user']['username']);
            $Excel->setActiveSheetIndex(0)->setCellValue('C'.$n, $val['has_one_user']['mobile']);
            $Excel->setActiveSheetIndex(0)->setCellValue('D'.$n, $val['code']);
            $Excel->setActiveSheetIndex(0)->setCellValue('E'.$n, $val['name']);
            if(1 == $type)
            {
                $Excel->setActiveSheetIndex(0)->setCellValue('F'.$n, $val['price']);
                $Excel->setActiveSheetIndex(0)->setCellValue('G'.$n, $val['hand']);
                $Excel->setActiveSheetIndex(0)->setCellValue('H'.$n, @$val['last_px']);
                $Excel->setActiveSheetIndex(0)->setCellValue('I'.$n, $val['deposit']);
                $Excel->setActiveSheetIndex(0)->setCellValue('J'.$n, @$val['pl']);
                $Excel->setActiveSheetIndex(0)->setCellValue('K'.$n, $val['price']*$val['hand']);
                $Excel->setActiveSheetIndex(0)->setCellValue('L'.$n, $val['stop_profit_price']);
                $Excel->setActiveSheetIndex(0)->setCellValue('M'.$n, $val['stop_loss_price']);
                $Excel->setActiveSheetIndex(0)->setCellValue('N'.$n, $val['jiancang_fee']);
                $Excel->setActiveSheetIndex(0)->setCellValue('O'.$n, $val['defer']);
                $Excel->setActiveSheetIndex(0)->setCellValue('P'.$n, $val['defer_total']);
                $Excel->setActiveSheetIndex(0)->setCellValue('Q'.$n, date('Y-m-d H:i', $val['create_at']));
                $Excel->setActiveSheetIndex(0)->setCellValue('R'.$n, $val['belongs_to_mode']['name']?:'-');
                $Excel->setActiveSheetIndex(0)->setCellValue('S'.$n, date('Y-m-d H:i', $val['original_free']));
            }elseif (2 == $type){
                $Excel->setActiveSheetIndex(0)->setCellValue('F'.$n, $val['price']);
                $Excel->setActiveSheetIndex(0)->setCellValue('G'.$n, $val['sell_price']);
                $Excel->setActiveSheetIndex(0)->setCellValue('H'.$n, $val['sell_hand']);
                $Excel->setActiveSheetIndex(0)->setCellValue('I'.$n, $val['deposit']);
                $Excel->setActiveSheetIndex(0)->setCellValue('J'.$n, $val['profit']);
                $Excel->setActiveSheetIndex(0)->setCellValue('K'.$n, $val['deposit']+$val['profit']);
                $Excel->setActiveSheetIndex(0)->setCellValue('L'.$n, date('Y-m-d H:i', $val['create_at']));
                $Excel->setActiveSheetIndex(0)->setCellValue('M'.$n, $val['belongs_to_mode']['name']?:'-');
                $Excel->setActiveSheetIndex(0)->setCellValue('N'.$n, $val['create_at'] ? date('Y-m-d H:i', $val['create_at']) : '-');
            }

            $n++;
            $Excel->getActiveSheet()->getRowDimension($n+1)->setRowHeight(18);
            if ($m != 0 && $m % 1000 == 0) {
                //保存到服务器
                $filename = $filePath . $num . '.xls';
                $fp = fopen($filename, 'w+');
                if (!is_writable($filename) ){
                    die('文件:' . $filename . '不可写，请检查！');
                }
                $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
                $objWriter->save($filename);
                fclose($fp);
                $num++;
                $n = 3;
                $Excel = new \PHPExcel();
                $Excel->setActiveSheetIndex(0);
                //编辑表格    标题
                $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
                $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
                $Excel->getActiveSheet()->setCellValue('A1',$title);
                //表头
                $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
                $Excel->setActiveSheetIndex(0)->setCellValue('A2','策略ID');
                $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
                $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
                $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('D2','股票代码');
                $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                $Excel->setActiveSheetIndex(0)->setCellValue('E2','股票名称');
                if(1 == $type) {
                    $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('F2', '委托价');
                    $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('G2', '委托数量');
                    $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('H2', '现价');
                    $Excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('I2', '保证金');
                    $Excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('J2', '盈亏');
                    $Excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('K2', '市值');
                    $Excel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('L2', '止盈');
                    $Excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('M2', '止损');
                    $Excel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('N2', '建仓费');
                    $Excel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('O2', '递延费/天');
                    $Excel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('P2', '递延费合计');
                    $Excel->getActiveSheet()->getColumnDimension('P')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('Q2', '下单时间');
                    $Excel->getActiveSheet()->getColumnDimension('Q')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('R2', '交易模式');
                    $Excel->getActiveSheet()->getColumnDimension('R')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('S2', '免息截止日期');
                }elseif (2 == $type) {
                    $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('F2','买入价');
                    $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('G2','卖出价	');
                    $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('H2','卖出数量');
                    $Excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('I2','保证金');
                    $Excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('J2','盈亏');
                    $Excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('K2','穿仓金额');
                    $Excel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('L2','买入时间');
                    $Excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('M2','交易模式');
                    $Excel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                    $Excel->setActiveSheetIndex(0)->setCellValue('N2','卖出时间');
                }
            }
            $m++;
        }
        $filename = $filePath . $title . '.xls';
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
        $archive->ZipAndDownload($filePath, $title);
    }
}