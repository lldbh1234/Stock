<?php
/**
 * 连连支付代付
 */

namespace app\common\payment;

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

    public function payment()
    {
        $llpay_payment_url = 'https://instantpay.lianlianpay.com/paymentapi/payment.htm';
        $parameter = [
            "oid_partner" => trim($this->config['oid_partner']),
            "sign_type" => trim($this->config['sign_type']),
            "no_order" => uniqid(),
            "dt_order" => date('YmdHis'),
            "money_order" => 0.1,
            "acct_name" => "梁健",
            "card_no" => "6217004220033901731",
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
        dump($html_text);
    }
}