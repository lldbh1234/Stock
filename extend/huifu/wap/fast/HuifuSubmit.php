<?php
namespace huifu\wap\fast;

class HuifuSubmit
{
    var $config;
    var $gateway = 'http://pay.danbaoshop.cn:9876/netrecv/merchant/bMerUnionPay';

    public function __construct($config) {
        $this->config = $config ? : config('huifu');
    }

    public function buildRequestPara($parameter)
    {
        $values = array_values($parameter);
        $string = implode('', $values);
        $parameter['check_value'] = strtoupper(md5("{$string}{$this->config['mac_key']}"));
        return $parameter;
    }

    public function buildRequestForm($parameter, $button_name = "submit") {
        $parameter = $this->buildRequestPara($parameter);
        //待请求参数数组
        $sHtml = "<form id='huifusubmit' name='huifusubmit' action='" . $this->gateway . "' method='post'>";
        foreach ($parameter as $key => $value){
            $sHtml .= "<input type='hidden' name='". $key ."' value='". $value ."'/>";
        }
        //submit按钮控件请不要含有name属性
        //$sHtml = $sHtml . "<input type='submit' value='" . $button_name . "'></form>";
        $sHtml = $sHtml . "<input type='submit' style='display:none;' value='" . $button_name . "'></form>";
        $sHtml = $sHtml . "<script>document.forms['huifusubmit'].submit();</script>";
        return $sHtml;
    }
}