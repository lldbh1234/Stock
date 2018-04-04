<?php
// wap认证支付
namespace app\common\payment;

use llpay\wap\api\LLpaySubmit as apiLLpaySubmit;
use llpay\wap\auth\LLpayNotify;
use llpay\wap\auth\LLpaySubmit as authLLpaySubmit;

class authLlpay
{
    protected $config;
    protected $notifyUrl;
    protected $returnUrl;
    public function __construct()
    {
        $this->config = config("llpay.llpay_wap_config");
        $this->notifyUrl = url("index/Notify/authLLpay", "", true, true);
        $this->returnUrl = url("index/Index/index", "", true, true);
    }

    public function getCode($userId, $tradeNo, $amount, $card, $risk)
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
            "card_no" => $card['bank_card'],
            "acct_name" => $card['bank_user'],
            "id_no" => $card['id_card'],
            "no_agree" => "",
            "risk_item" => addslashes(json_encode($risk, JSON_UNESCAPED_UNICODE))
        ];
        $llpaySubmit = new authLLpaySubmit($this->config);
        return $llpaySubmit->buildRequestForm($parameter, "post", "确认");
    }

    public function verifyNotify()
    {
        $llpayNotify = new LLpayNotify($this->config);
        $llpayNotify->verifyNotify();
        return $llpayNotify;
    }

    public function bankBindList($userId)
    {
        //订单查询接口地址
        $llpay_gateway_new = 'https://queryapi.lianlianpay.com/bankcardbindlist.htm';
        $parameter = [
            "oid_partner" => trim($this->config['oid_partner']),
            "user_id" => $userId,
            "pat_type" => "D",
            "offset" => "0",
            "sign_type" => trim($this->config['sign_type']),
        ];

        $llpaySubmit = new apiLLpaySubmit($this->config);
        $html_text = $llpaySubmit->buildRequestJSON($parameter, $llpay_gateway_new);
        $response = json_decode($html_text, true);
        if($response){
            if(isset($response['ret_code']) && $response['ret_code'] == '0000'){
                return $response['agreement_list'];
            }else{
                return [];
            }
        }
        return [];
    }

    public function unbindBank($userId, $noAgree)
    {
        $llpay_gateway_new = 'https://traderapi.lianlianpay.com/bankcardunbind.htm';
        $parameter = [
            "oid_partner" => trim($this->config['oid_partner']),
            "user_id" => $userId,
            "pat_type" => "D",
            "no_agree" => $noAgree,
            "sign_type" => trim($this->config['sign_type']),
        ];
        $llpaySubmit = new apiLLpaySubmit($this->config);
        $html_text = $llpaySubmit->buildRequestJSON($parameter, $llpay_gateway_new);
        $response = json_decode($html_text, true);
        if($response){
            if(isset($response['ret_code']) && $response['ret_code'] == '0000'){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }
}