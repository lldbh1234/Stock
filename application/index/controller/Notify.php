<?php
namespace app\index\controller;

use app\common\payment\authRbPay;
use app\common\payment\huifuPay;
use app\common\payment\paymentFuiou;
use app\common\payment\paymentLLpay;
use app\common\payment\xyFuiouPay;
use app\index\logic\AdminWithdrawLogic;
use app\index\logic\WithdrawLogic;
use think\Controller;
use app\index\logic\RechargeLogic;
use app\common\payment\authLlpay;


class Notify extends Controller
{
    public function authLLpay()
    {
        //计算得出通知验证结果
        $payment = new authLlpay();
        $llpayNotify = $payment->verifyNotify();
        @file_put_contents("./pay.log", json_encode($llpayNotify->notifyResp)."\r\n", FILE_APPEND);
        if ($llpayNotify->result) { //验证成功
            //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
            $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
            $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
            $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
            if($result_pay == "SUCCESS"){
                //请在这里加上商户的业务逻辑程序代(更新订单状态、入账业务)
                //——请根据您的业务逻辑来编写程序——
                //payAfter($llpayNotify->notifyResp);
                $_rechargeLogic = new RechargeLogic();
                $order = $_rechargeLogic->orderByTradeNo($no_order, 0);
                if($order){
                    // 有该笔充值订单
                    $res = $_rechargeLogic->rechargeComplete($no_order, $order['amount'], $order['user_id'], $oid_paybill);
                    if(!$res){
                        @file_put_contents("./pay.log", "failed1\r\n", FILE_APPEND);
                        die("{'ret_code':'9999','ret_msg':'订单状态修改失败'}");
                    }
                }
            }
            //file_put_contents("log.txt", "异步通知 验证成功\n", FILE_APPEND);
            @file_put_contents("./pay.log", "success\r\n", FILE_APPEND);
            die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
        } else {
            //file_put_contents("log.txt", "异步通知 验证失败\n", FILE_APPEND);
            @file_put_contents("./pay.log", "failed2\r\n", FILE_APPEND);
            die("{'ret_code':'9999','ret_msg':'验签失败'}");
        }
    }

    public function payment()
    {
        $payment = new paymentLLpay();
        $llpayNotify = $payment->verifyNotify();
        @file_put_contents("./pay.log", json_encode($llpayNotify->notifyResp)."\r\n", FILE_APPEND);
        if ($llpayNotify->result) { //验证成功
            //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
            $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
            $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
            $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
            if($result_pay == "SUCCESS"){
                //请在这里加上商户的业务逻辑程序代(更新订单状态、入账业务)
                //——请根据您的业务逻辑来编写程序——
                //payAfter($llpayNotify->notifyResp);
                $_withdrawLogic = new WithdrawLogic();
                $order = $_withdrawLogic->orderByTradeNo($no_order, 1);
                if($order){
                    $data = ["state" => 2];
                    $res = $_withdrawLogic->updateByTradeNo($no_order, $data);
                    if(!$res){
                        @file_put_contents("./pay.log", "failed1\r\n", FILE_APPEND);
                        die("{'ret_code':'9999','ret_msg':'订单状态修改失败'}");
                    }
                }
            }
            //file_put_contents("log.txt", "异步通知 验证成功\n", FILE_APPEND);
            @file_put_contents("./pay.log", "success\r\n", FILE_APPEND);
            die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //file_put_contents("log.txt", "异步通知 验证失败\n", FILE_APPEND);
            //验证失败
            die("{'ret_code':'9999','ret_msg':'验签失败'}");
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

    public function proxyPayment()
    {
        $payment = new paymentLLpay();
        $llpayNotify = $payment->verifyNotify();
        @file_put_contents("./proxy_pay.log", json_encode($llpayNotify->notifyResp)."\r\n", FILE_APPEND);
        if ($llpayNotify->result) { //验证成功
            //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
            $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
            $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
            $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
            if($result_pay == "SUCCESS"){
                //请在这里加上商户的业务逻辑程序代(更新订单状态、入账业务)
                //——请根据您的业务逻辑来编写程序——
                //payAfter($llpayNotify->notifyResp);
                $_withdrawLogic = new AdminWithdrawLogic();
                $order = $_withdrawLogic->orderByTradeNo($no_order, 1);
                if($order){
                    $data = ["state" => 2];
                    $res = $_withdrawLogic->updateByTradeNo($no_order, $data);
                    if(!$res){
                        @file_put_contents("./proxy_pay.log", "failed1\r\n", FILE_APPEND);
                        die("{'ret_code':'9999','ret_msg':'订单状态修改失败'}");
                    }
                }
            }
            //file_put_contents("log.txt", "异步通知 验证成功\n", FILE_APPEND);
            @file_put_contents("./proxy_pay.log", "success\r\n", FILE_APPEND);
            die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //file_put_contents("log.txt", "异步通知 验证失败\n", FILE_APPEND);
            //验证失败
            die("{'ret_code':'9999','ret_msg':'验签失败'}");
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

    public function huifuNotify()
    {
        if(request()->isPost()){
            @file_put_contents("./pay.log", "POST:" . json_encode($_POST)."\r\n", FILE_APPEND);
            $reData = [
                "resp_code" => $_POST['resp_code'],
                "resp_desc" => $_POST['resp_desc'],
                "cust_id" => $_POST['cust_id'],
                "ord_id" => $_POST['ord_id'],
                "platform_seq_id" => $_POST['platform_seq_id'],
                "trans_amt" => $_POST['trans_amt'],
                "mer_priv" => $_POST['mer_priv'],
                "extension" => $_POST['extension']
            ];
            $huifu = new huifuPay();
            $validate = $huifu->validateCheckValue($reData, $_POST['check_value']);
            if($reData['resp_code'] == "000" && $validate){
                $tradeNo = $reData["ord_id"];
                $_rechargeLogic = new RechargeLogic();
                $order = $_rechargeLogic->orderByTradeNo($tradeNo, 0);
                if($order){
                    // 有该笔充值订单
                    $outTradeNo = $reData['platform_seq_id'];
                    $res = $_rechargeLogic->rechargeComplete($tradeNo, $order['amount'], $order['user_id'], $outTradeNo);
                    if(!$res){
                        @file_put_contents("./pay.log", "failed1\r\n", FILE_APPEND);
                    }
                }
                @file_put_contents("./pay.log", "success\r\n", FILE_APPEND);
                exit("ECHO_SEQ_ID={$tradeNo}");
            }else{
                @file_put_contents("./pay.log", "SIGN ERROR\r\n", FILE_APPEND);
                exit("SIGN ERROR");
            }
        }else{
            exit("ERROR");
        }
    }
    public function authRbPay()
    {
        //计算得出通知验证结果
        $payment = new authRbPay();
//        @file_put_contents("./pay.log", json_encode($_REQUEST).PHP_EOL, FILE_APPEND);
        $response = $payment->notify($_REQUEST);
        if($response)
        {
            $_rechargeLogic = new RechargeLogic();
            $order = $_rechargeLogic->orderByTradeNo($response['order_no'], 0);
            if($order){
                // 有该笔充值订单
                $res = $_rechargeLogic->rechargeComplete($response['order_no'], $order['amount'], $order['user_id'], $response['trade_no']);
                if(!$res){
                    $response['date123'] = date('Y-m-d H:i:s');
                    @file_put_contents("./pay_rongbao.log", json_decode($response).PHP_EOL, FILE_APPEND);
                }else{
                    die("success");
                }
            }
        } else {
            $response['date123'] = date('Y-m-d H:i:s');
            @file_put_contents("./pay_rongbao.log", json_decode($response).PHP_EOL, FILE_APPEND);
            die("fail");
        }
    }
    // 富友协议支付回调
    public function fuiouXyNotify()
    {
        header("Content-Type:text/html;charset=utf-8");
        $html = file_get_contents('php://input');
        file_put_contents("./fuiou.log", $html . "\r\n", FILE_APPEND);
        parse_str($html, $response);
        if($response){
            $obj = new xyFuiouPay();
            $sign = $obj->checkSignature($response);
            if($sign){
                if($response['RESPONSECODE'] == "0000"){
                    $_rechargeLogic = new RechargeLogic();
                    $order = $_rechargeLogic->orderByTradeNo($response['MCHNTORDERID'], 0);
                    if($order){
                        // 有该笔充值订单
                        $res = $_rechargeLogic->rechargeComplete($response['MCHNTORDERID'], $order['amount'], $order['user_id'], $response['ORDERID']);
                        if(!$res){
                            json([])->code(200);
                            @file_put_contents("./fuiou.log", "SUCCESS\r\n", FILE_APPEND);
                        }else{
                            json([])->code(500);
                            @file_put_contents("./fuiou.log", "ORDER CHANGE ERROR\r\n", FILE_APPEND);
                        }
                    }else{
                        json([])->code(404);
                        @file_put_contents("./fuiou.log", "ORDER NOT EXIST\r\n", FILE_APPEND);
                    }
                }else{
                    json([])->code(401);
                    @file_put_contents("./fuiou.log", "PAYMENT FAILED\r\n", FILE_APPEND);
                }
            }else{
                json([])->code(401);
                @file_put_contents("./fuiou.log", "SIGN ERROR\r\n", FILE_APPEND);
            }
        }else{
            json([])->code(204);
            @file_put_contents("./fuiou.log", "DATA EMPTY\r\n", FILE_APPEND);
        }
    }

    public function fuiouPayment()
    {
        header("Content-Type:text/html;charset=utf-8");
        $html = file_get_contents('php://input');
        parse_str($html, $response);
        @file_put_contents("./fuiou_payment.log", json_encode($response) . "\r\n", FILE_APPEND);
        if($response){
            $payment = new paymentFuiou();
            $sign = $payment->checkSignature($response);
            if($sign){
                if($response['state'] == '1'){
                    // 支付成功
                    $orderIndex = substr($response['orderno'], -1);
                    $orderSn = substr($response['orderno'], 0, -1);
                    if($orderIndex == "A"){
                        @file_put_contents("./fuiou_payment.log", "USER ORDER\r\n", FILE_APPEND);
                        // 用户出金
                        $_withdrawLogic = new WithdrawLogic();
                        $order = $_withdrawLogic->orderByTradeNo($orderSn, 1);
                        if($order){
                            $data = ["state" => 2];
                            $res = $_withdrawLogic->updateByTradeNo($orderSn, $data);
                            if(!$res){
                                @file_put_contents("./fuiou_payment.log", "CHANGE FAILED\r\n", FILE_APPEND);
                                die("1");
                            }
                            @file_put_contents("./fuiou_payment.log", "SUCCESS\r\n", FILE_APPEND);
                        }
                        die("1");
                    }elseif($orderIndex == "B"){
                        @file_put_contents("./fuiou_payment.log", "PROXY ORDER\r\n", FILE_APPEND);
                        // 代理商出金
                        $_withdrawLogic = new AdminWithdrawLogic();
                        $order = $_withdrawLogic->orderByTradeNo($orderSn, 1);
                        if($order){
                            $data = ["state" => 2];
                            $res = $_withdrawLogic->updateByTradeNo($orderSn, $data);
                            if(!$res){
                                @file_put_contents("./fuiou_payment.log", "CHANGE FAILED\r\n", FILE_APPEND);
                                die("1");
                            }
                            @file_put_contents("./fuiou_payment.log", "SUCCESS\r\n", FILE_APPEND);
                        }
                        die("1");
                    }else{
                        @file_put_contents("./fuiou_payment.log", "ORDER ERROR\r\n", FILE_APPEND);
                        die("0");
                    }
                }else{
                    @file_put_contents("./fuiou_payment.log", "PAYMENT FAILED\r\n", FILE_APPEND);
                    die("1");
                }
            }else{
                @file_put_contents("./fuiou_payment.log", "SIGN ERROR\r\n", FILE_APPEND);
                die("0");
            }
        }else{
            @file_put_contents("./fuiou_payment.log", "DATA EMPTY\r\n", FILE_APPEND);
            die("0");
        }
    }
    /**
     * sdk回调
     */
    public function sdkLLpay()
    {
        //计算得出通知验证结果
        $payment = new authLlpay();
        $llpayNotify = $payment->verifyNotify();
        @file_put_contents("./pay-sdk.log", json_encode($llpayNotify->notifyResp)."\r\n", FILE_APPEND);
        if ($llpayNotify->result) { //验证成功
            //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
            $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
            $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
            $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
            if($result_pay == "SUCCESS"){
                //请在这里加上商户的业务逻辑程序代(更新订单状态、入账业务)
                //——请根据您的业务逻辑来编写程序——
                //payAfter($llpayNotify->notifyResp);
                $_rechargeLogic = new RechargeLogic();
                $order = $_rechargeLogic->orderByTradeNo($no_order, 0);
                if($order){
                    // 有该笔充值订单
                    $res = $_rechargeLogic->rechargeComplete($no_order, $order['amount'], $order['user_id'], $oid_paybill);
                    if(!$res){
                        @file_put_contents("./pay-sdk.log", "failed1\r\n", FILE_APPEND);
                        die("{'ret_code':'9999','ret_msg':'订单状态修改失败'}");
                    }
                }
            }
            //file_put_contents("log.txt", "异步通知 验证成功\n", FILE_APPEND);
            @file_put_contents("./pay-sdk.log", "success\r\n", FILE_APPEND);
            die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
        } else {
            //file_put_contents("log.txt", "异步通知 验证失败\n", FILE_APPEND);
            @file_put_contents("./pay-sdk.log", "failed2\r\n", FILE_APPEND);
            die("{'ret_code':'9999','ret_msg':'验签失败'}");
        }
    }
}