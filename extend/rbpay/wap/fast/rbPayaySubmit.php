<?php
/**
 * 融宝支付
 *
 */
namespace rbpay\wap\fast;

class rbPayaySubmit {

	private $rbpay_config;
	private $apiUrl;
	public $rbUnit;

	public function __construct() {
		$this->rbpay_config = config('rbpay.rbpay_wap_config');
        $this->apiUrl = config('rbpay.rbpay_wap_config')['apiUrl'];
        $this->rbUnit = new rbPayUnit();
	}

    public function unbindBank($data = [])
    {
        $paramArr = $data;
        //访问储蓄卡签约服务
        $url = $this->apiUrl.'/fast/cancle/bindcard';
        return $this->reponse($paramArr, $url);
    }
    public function findCard($data = [])
    {
        $paramArr = $data;
        //访问储蓄卡签约服务
        $url = $this->apiUrl.'/fast/bindcard/list';
        return $this->reponse($paramArr, $url);
    }
    public function notify($param = [])
    {
        $data           = $param['data'];
        $encrypt_key    = $param['encryptkey'];
        $encrypt        = $this->rbUnit->RSADecryptkey($encrypt_key, $this->rbpay_config['privateKey']);
        $decryData      = $this->rbUnit->AESDecryptResponse($encrypt, $data);
        $jsonObject     = json_decode($decryData,true);
        $member_id      = $jsonObject['member_id'];
        $owner          = $jsonObject['owner'];
        $order_no       = $jsonObject['order_no'];
        $bank_code      = $jsonObject['bank_code'];
        $cert_no        = $jsonObject['cert_no'];
        $card_no        = $jsonObject['card_no'];
        $total_fee      = $jsonObject['total_fee'];
        $bank_name      = $jsonObject['bank_name'];
        $trade_no       = $jsonObject['trade_no'];
        $notify_id      = $jsonObject['notify_id'];
        $status         = $jsonObject['status'];
        $sign           = $jsonObject['sign'];
        $paramArr = [
            'member_id' => $member_id,
            'owner'     => $owner,
            'order_no'  => $order_no,
            'bank_code' => $bank_code,
            'cert_no'   => $cert_no,
            'card_no'   => $card_no,
            'total_fee' => $total_fee,
            'bank_name' => $bank_name,
            'trade_no'  => $trade_no,
            'notify_id' => $notify_id,
            'status'    => $status
        ];
        $paramArr = self::argSort($paramArr);
        $mysign = self::buildMysign($paramArr, $this->rbpay_config['apiKey']);
        if ($mysign === $sign){
            return $paramArr;
        }else {
            return [];
        }
    }
    public function reponse($paramArr, $url)
    {
        $result = $this->rbUnit->send($paramArr, $url, $this->rbpay_config['apiKey'], $this->rbpay_config['publicKey'], $this->rbpay_config['merchant_id']);
        $response = json_decode($result,true);
        $encryptkey = $this->rbUnit->RSADecryptkey($response['encryptkey'], $this->rbpay_config['privateKey']);
        return json_decode($this->rbUnit->AESDecryptResponse($encryptkey, $response['data']), true);
    }
    public function buildForm($paramArr){
        $gateway = $this->apiUrl.'/mobile/same/portal';
        $paramArr['payment_type'] = '2';
        $paramArr['pay_method'] = 'bankPay';
        $paramArr = self::argSort($paramArr);
        $mySign = self::buildMysign($paramArr, $this->rbpay_config['apiKey']);//生成签名结果
        $paramArr['sign'] = $mySign;
        $generateAESKey = $this->rbUnit->generateAESKey();
        $encryptkey = $this->rbUnit->RSAEncryptkey($generateAESKey, $this->rbpay_config['publicKey']);
        $data = $this->rbUnit->AESEncryptRequest($generateAESKey, $paramArr);

        //post方式传递
        $sHtml = "<form id=\"rongpaysubmit\" name=\"rongpaysubmit\" action=\"".$gateway."\" method=\"post\">"
            ."<input type=\"hidden\" name=\"merchant_id\" value=\"".$paramArr['merchant_id']."\"/>"
            ."<input type=\"hidden\" name=\"data\" value=\"".$data."\"/>"
            ."<input type=\"hidden\" name=\"encryptkey\" value=\"".$encryptkey."\"/>"
            //submit按钮控件请不要含有name属性
            ."<input type=\"submit\" style=\"display:none;\" class=\"button_p2p\" value=\"融宝支付确认付款\"></form>";
        $sHtml .= "<script>document.forms['rongpaysubmit'].submit();</script>";

        return $sHtml;
    }

    private static function buildMysign($paramArr, $key){
        $prestr = self::createLinkString($paramArr);
        return md5($prestr.$key);
    }

    private static function createLinkString($array){
        $paramArr = self::paraFilter($array);
        $arg  = "";
        while (list ($key, $val) = each ($paramArr))
        {
            $arg.=$key."=".$val."&";
        }
        $arg = substr($arg,0,count($arg)-2);		     //去掉最后一个&字符
        return $arg;
    }

    /**
     *除去数组中的空值和签名参数
     *$parameter 签名参数组
     *return 去掉空值与签名参数后的新签名参数组
     */
    private static function paraFilter($parameter)
    {
        $para = [];
        while (list ($key, $val) = each ($parameter))
        {
            if($key == "sign" || $key == "sign_type" || $val == "")
            {
                continue;
            }
            else
            {
                $para[$key] = $parameter[$key];
            }
        }
        return $para;
    }
    /********************************************************************************/

    /**对数组排序
     *$array 排序前的数组
     *return 排序后的数组
     */
    private static function argSort($array)
    {
        ksort($array);
        reset($array);
        return $array;
    }

}