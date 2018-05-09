<?php
namespace app\common\payment;


use huifu\wap\fast\HuifuSubmit;

class huifuPay
{
    protected $config;
    protected $notifyUrl;
    protected $returnUrl;
    public function __construct()
    {
        $this->config = config("huifu");
        $this->notifyUrl = url("index/Notify/huifuNotify", "", true, true);
        $this->returnUrl = url("index/Index/index", "", true, true);
    }

    public function validateCheckValue($parameter, $outValue)
    {
        $values = array_values($parameter);
        $string = implode('', $values);
        $myValue = strtoupper(md5("{$string}{$this->config['mac_key']}"));
        return $myValue == $outValue;
    }

    public function getCode($tradeNo, $amount)
    {
        $parameter = [
            "version" => $this->config['version'],
            "cust_id" => $this->config['cust_id'],
            "ord_id" => trim($tradeNo),
            "subject" => urlencode("58好策略余额充值"),
            "gate_id" => $this->config['gate_id'],
            "trans_amt" => $amount,
            "card_name" => "",
            "mobile_no" => "",
            "ret_url" => urlencode($this->returnUrl),
            "bg_ret_url" => urlencode($this->notifyUrl),
            "mer_priv" => "",
            "extension" => "",
        ];
        $huifuSubmit = new HuifuSubmit($this->config);
        return $huifuSubmit->buildRequestForm($parameter);
    }
}