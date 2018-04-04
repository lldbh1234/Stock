<?php
/**
 * 连连支付代付
 */

namespace app\common\payment;

use llpay\payment\notify\LLpayNotify as paymentLLpayNotify;
use llpay\payment\pay\LLpaySubmit as paymentLLpaySubmit;

class paymentLLpay
{
    protected $config;
    protected $notifyUrl;
    public function __construct()
    {
        $this->config = config("llpay.llpay_wap_config");
        $this->notifyUrl = url("index/Notify/payment", "", true, true);
    }

    public function payment($withdraw)
    {
        $llpay_payment_url = 'https://instantpay.lianlianpay.com/paymentapi/payment.htm';
        $parameter = [
            "oid_partner" => trim($this->config['oid_partner']),
            "sign_type" => trim($this->config['sign_type']),
            "no_order" => $withdraw['tradeNo'],
            "dt_order" => date('YmdHis', $withdraw['createAt']),
            "money_order" => $withdraw['amount'],
            "acct_name" => $withdraw['name'],
            "card_no" => $withdraw['card'],
            "info_order" => "58好策略余额提现",
            "flag_card" => "0",
            "notify_url" => $this->notifyUrl,
            "platform" => "",
            "api_version" => "1.0"
        ];
        $llpaySubmit = new paymentLLpaySubmit($this->config);
        $sortPara = $llpaySubmit->buildRequestPara($parameter);
        $json = json_encode($sortPara);
        $parameterRequest = array (
            "oid_partner" => trim($this->config['oid_partner']),
            "pay_load" => $llpaySubmit->ll_encrypt($json) //请求参数加密
        );
        $html_text = $llpaySubmit->buildRequestJSON($parameterRequest, $llpay_payment_url);
        $response = json_decode($html_text, true);
        return $response ? $response : [];
    }

    public function verifyNotify()
    {
        $llpayNotify = new paymentLLpayNotify($this->config);
        $llpayNotify->verifyNotify();
        return $llpayNotify;
    }
}