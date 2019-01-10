<?php
// wap认证支付
namespace app\common\payment;

use llpay\wap\api\LLpaySubmit as apiLLpaySubmit;
use llpay\wap\auth\LLpayNotify;
use llpay\wap\auth\LLpaySubmit as authLLpaySubmit;

class sdkLlpay
{
    protected $config;
    protected $notifyUrl;
    protected $returnUrl;
    public function __construct()
    {
        $this->config = config("llpay.llpay_wap_new58_config");
        $this->notifyUrl = url("index/Notify/sdkLLpay", "", true, true);
        $this->returnUrl = url("index/Index/index", "", true, true);
    }

    /**
     * SDK Token获取
     * @param $userId
     * @param $tradeNo
     * @param $amount
     * @param $card
     * @param $risk
     * @return \llpay\wap\auth\要请求的参数数组
     */
    public function getSign($userId, $tradeNo, $amount, $card, $risk)
    {
        $parameter = [
            "api_version" => "1.0",
            "sign_type" => trim($this->config['sign_type']),
            "time_stamp" => date("YmdHis"),
            "platform" => "",
            "oid_partner" => trim($this->config['oid_partner']),
            "user_id" => $userId,
            "busi_partner" => '101001',
            "no_order" => $tradeNo,
            "dt_order" => date("YmdHis"),
            "name_goods" => "58好策略余额充值",
            "info_order" => "",
            "money_order" => $amount,
            "notify_url" => $this->notifyUrl,
            "url_return" => $this->returnUrl,
            "back_url" => $this->returnUrl,
            "risk_item" => addslashes(json_encode($risk, JSON_UNESCAPED_UNICODE)),
            "flag_pay_product" => "5",//支付产品标识。0， 快捷收款。1， 认证收款。2， 网银收款。5， 新认证收款。12， 手机网银收款。
            "flag_chnl" => "0",//self::phoneType(),//0:App-Android、1：App-iOS、2： Web。3：H5。
            "id_type" => "0",
            "id_no" => $card['id_card'],
            "acct_name" => $card['bank_user'],
            "card_no" => $card['bank_card'],
        ];

        $llpaySubmit = new authLLpaySubmit($this->config);
        return $llpaySubmit->buildRequestPara($parameter);
    }
    public function getSdkParam($data=[])
    {
        $url = 'https://payserverapi.lianlianpay.com/v1/paycreatebill';
        $headers = [
            "Content-type: application/json;charset='utf-8'",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时

        if(0 === strpos(strtolower($url), 'https')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
//                $data = array(0=>1,1=>2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        //CURLOPT_RETURNTRANSFER 不设置  curl_exec返回TRUE 设置  curl_exec返回json(此处) 失败都返回FALSE
        curl_close($ch);
        $response = json_decode($response, true);
        if($response['ret_code'] == '0000')
        {
            return ['code' => 0, 'ret' => $response];
        }
        return ['code' => 1, 'ret' => $response];
    }
    public function phoneType()
    {
        $flag_chnl = "-1";//0:App-Android、1：App-iOS、2： Web。3：H5。
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $flag_chnl = "1";
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            $flag_chnl = "0";
        }
        return $flag_chnl;
    }
}