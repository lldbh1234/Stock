<?php
namespace app\common\payment;

use llpay\wap\api\LLpaySubmit as apiLLpaySubmit;
use llpay\wap\auth\LLpaySubmit as authLLpaySubmit;

class authLlpay
{
    protected $config;
    protected $notifyUrl;
    protected $returnUrl;
    public function __construct()
    {
        $this->config = config("llpay_wap_config");
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
            "card_no" => "6217004220033901731",
            "acct_name" => "梁健",
            "id_no" => "142222199008101512",
            "no_agree" => "",
            "risk_item" => '{\"frms_ware_category\":\"2026\",\"user_info_mercht_userno\":\"1\",\"user_info_dt_register\":\"20180226113000\",\"user_info_full_name\":\"梁健\",\"user_info_id_no\":\"142222199008101512\",\"user_info_identify_type\":\"1\",\"user_info_identify_state\":\"1\"}'
        ];
        $llpaySubmit = new authLLpaySubmit($this->config);
        return $llpaySubmit->buildRequestForm($parameter, "post", "确认");
    }

    public function bankBindList()
    {
        //订单查询接口地址
        $llpay_gateway_new = 'https://queryapi.lianlianpay.com/bankcardbindlist.htm';
        $parameter = [
            "oid_partner" => trim($this->config['oid_partner']),
            "user_id" => "22222222",
            "pat_type" => "D",
            "offset" => "0",
            "sign_type" => trim($this->config['sign_type']),
        ];

        $llpaySubmit = new apiLLpaySubmit($this->config);
        $html_text = $llpaySubmit->buildRequestJSON($parameter, $llpay_gateway_new);
        echo $html_text;
    }
}