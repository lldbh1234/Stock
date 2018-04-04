<?php
namespace app\common\payment;

use llpay\wap\fast\LLpaySubmit;
use llpay\wap\fast\LLpayNotify;

class llpay
{
    protected $config;
    protected $notifyUrl;
    protected $returnUrl;
    public function __construct()
    {
        $this->config = config("llpay.llpay_wap_config");
        $this->notifyUrl = url("index/Notify/llpay", "", true, true);
        $this->returnUrl = url("index/Index/index", "", true, true);
    }

    public function getCode($userId, $tradeNo, $amount)
    {
        $parameter = [
            "oid_partner" => trim($this->config['oid_partner']),
            "app_request" => trim($this->config['app_request']),
            "sign_type" => trim($this->config['sign_type']),
            "valid_order" => trim($this->config['valid_order']),
            "bg_color"  => trim($this->config['bg_color']),
            "user_id" => $userId,
            "busi_partner" => trim($this->config['busi_partner']),
            "no_order" => $tradeNo,
            "dt_order" => date('YmdHis'),
            "name_goods" => "58好策略余额充值",
            "info_order" => "58好策略余额充值",
            "money_order" => $amount,
            "notify_url" => $this->notifyUrl,
            "url_return" => $this->returnUrl,
            "card_no" => "",
            "acct_name" => "",
            "id_no" => "",
            "no_agree" => "",
            "risk_item" => "",
        ];
        $llpaySubmit = new LLpaySubmit($this->config);
        return $llpaySubmit->buildRequestForm($parameter, "post", "确认");
    }

    public function verifyNotify()
    {
        $llpayNotify = new LLpayNotify($this->config);
        $llpayNotify->verifyNotify();
        return $llpayNotify;
    }
}