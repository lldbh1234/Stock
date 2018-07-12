<?php
// wap认证支付-融宝
namespace app\common\payment;

use rbpay\wap\fast\rbPayaySubmit;

class authRbPay
{
    protected $rbpay_config;
    protected $notifyUrl;
    protected $returnUrl;
    protected $rbPaySubmit;

    public function __construct()
    {
        $this->rbpay_config = config("rbpay.rbpay_wap_config");
        $this->notifyUrl    = url("index/Notify/authRbPay", "", true, true);
        $this->returnUrl    = url("index/Index/index", "", true, true);
        $this->rbPaySubmit  = new rbPayaySubmit();
    }



    /**
     * 解绑卡
     * @param array $data
     */
    public function unbindBank($data = [])
    {
        //参数数组
        $paramArr = [
            'merchant_id'   => $this->rbpay_config['merchant_id'],//商户在融宝的账户ID，
            'member_id'     => $data['userId'],
            'bind_id'       => $data['bind_id'],
        ];

        $response = $this->rbPaySubmit->unbindBank($paramArr);
        if($response && isset($response['result_code']) && $response['result_code'] == 0000)
        {
            return ['code' => 0, 'message' => $response['result_msg'], 'data' => $response];

        }else{
            return ['code' => 1, 'message' => $response['result_msg'], 'data' => $response];
        }
    }

    /**
     * 查询绑卡
     * @return string
     */
    public function findCard($data = [])
    {
        //参数数组
        $paramArr = [
            'merchant_id'       => $this->rbpay_config['merchant_id'],
            'member_id'         => $data['userId'],
            'bank_card_type'    => "0",
        ];

        //访问储蓄卡签约服务
        $response = $this->rbPaySubmit->findCard($paramArr);
        if($response && isset($response['bind_card_list']))
        {
            return ['code' => 0, 'message' => "获取成功", 'data' => $response['bind_card_list']];

        }else{
            return ['code' => 1, 'message' => $response['result_msg'], 'data' => $response];
        }
    }

    public function notify($data=[])
    {
        if(isset($data['data']) && isset($data['encryptkey']))
        {
            return $this->rbPaySubmit->notify($data);
        }
        return false;
    }
    public function payForm($data = [])
    {
        $parameter = [
            'seller_email'  => $this->rbpay_config['seller_email'],
            'merchant_id'   => $this->rbpay_config['merchant_id'],
            'notify_url'    => $this->notifyUrl,
            'return_url'    => $this->returnUrl,
            'transtime'     => date("Y-m-d H:i:s"),
            'currency'      => '156',
            'member_id'     => $data['userId'],
            'member_ip'     => $_SERVER["REMOTE_ADDR"],
            'terminal_type' => 'mobile',
            'terminal_info' => 'terminal_info',
            'sign_type'     => 'md5',
            "order_no"      => $data['order_no'],
            'total_fee'     => $data['total_fee'] * 100,
            'title'         => '中建58好策略余额充值',
            'body'          => '中建58好策略余额充值',
        ];
        ////构造函数，生成请求URL
        return $this->rbPaySubmit->buildForm($parameter);
    }

}