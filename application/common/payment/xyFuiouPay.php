<?php
/**
 * 富友支付协议
 */
namespace app\common\payment;

use fuiou\wap\fast\fuiouPay;

class xyFuiouPay
{
    protected $config;
    protected $successUrl;
    protected $failUrl;
    protected $callbackUrl;
    public function __construct()
    {
        $this->config = config("fuiou.fuiou_wap_config");
        $this->failUrl = url("index/Index/index", "", true, true);
        $this->successUrl = url("index/Index/index", "", true, true);
        $this->callbackUrl = url("index/Notify/fuiouXyNotify", "", true, true);
    }

    public function getHtml($orderId, $userId, $amt, $card)
    {
        $amt = $amt * 100; //金额分为单位
        $code = $this->encryptCode($orderId, $userId, $amt, $card);
        $html = "<form id='fuiousubmit' name='fuiousubmit' action='{$this->config['GATEWAY']}' method='post'>";
        $html .= "<input type='hidden' name='ENCTP' value='{$this->config['ENCTP']}' />";
        $html .= "<input type='hidden' name='VERSION' value='{$this->config['VERSION']}' />";
        $html .= "<input type='hidden' name='MCHNTCD' value='{$this->config['MCHNTCD']}' />";
        $html .= "<input type='hidden' name='LOGOTP' value='{$this->config['LOGOTP']}' />";
        $html .= "<input type='hidden' name='FM' value='{$code}' />";
        //$html .= "<input type='submit' value='submit'></form>";
        $html .= "<input type='submit' style='display:none;' value='submit'></form>";
        $html .= "<script>document.forms['fuiousubmit'].submit();</script>";
        return $html;
    }

    public function encryptCode($orderId, $userId, $amt, $card)
    {
        $signature = $this->signature($orderId, $userId, $amt, $card['bank_card'], $card['bank_user'], $card['id_card']);
        $fm = "<ORDER>"
            ."<VERSION>{$this->_formatInput($this->config['VERSION'])}</VERSION>"
            ."<LOGOTP>{$this->_formatInput($this->config['LOGOTP'])}</LOGOTP>"
            ."<MCHNTCD>{$this->_formatInput($this->config['MCHNTCD'])}</MCHNTCD> "
            ."<TYPE>{$this->_formatInput($this->config['TYPE'])}</TYPE>"
            ."<MCHNTORDERID>{$this->_formatInput($orderId)}</MCHNTORDERID>"
            ."<USERID>{$this->_formatInput($userId)}</USERID>"
            ."<AMT>{$this->_formatInput($amt)}</AMT>"
            ."<BANKCARD>{$this->_formatInput($card['bank_card'])}</BANKCARD>"
            ."<NAME>{$this->_formatInput($card['bank_user'])}</NAME>"
            ."<IDTYPE>{$this->_formatInput($this->config['IDTYPE'])}</IDTYPE>"
            ."<IDNO>{$this->_formatInput($card['id_card'])}</IDNO>"
            ."<BACKURL>{$this->_formatInput($this->callbackUrl)}</BACKURL>"
            ."<HOMEURL>{$this->_formatInput($this->successUrl)}</HOMEURL>"
            ."<REURL>{$this->_formatInput($this->failUrl)}</REURL>"
            ."<REM1></REM1>"
            ."<REM2></REM2>"
            ."<REM3></REM3>"
            ."<SIGNTP>{$this->_formatInput($this->config['SIGNTP'])}</SIGNTP>"
            ."<SIGN>{$signature}</SIGN>"
            ."</ORDER>";
        return fuiouPay::encryptForDES($fm, $this->config['KEY']);
    }

    // 签名
    // $orderId- 订单号
    // $userId- 用户Id
    // $amt - 金额（分）
    // $bankcard - 卡号
    // $name -姓名
    // $idno - 身份证号
    public function signature($orderId, $userId, $amt, $bankcard, $name, $idno)
    {
        $array = [
            $this->_formatInput($this->config['TYPE']),
            $this->_formatInput($this->config['VERSION']),
            $this->_formatInput($this->config['MCHNTCD']),
            $this->_formatInput($orderId),
            $this->_formatInput($userId),
            $this->_formatInput($amt),
            $this->_formatInput($bankcard),
            $this->_formatInput($this->callbackUrl),
            $this->_formatInput($name),
            $this->_formatInput($idno),
            $this->_formatInput($this->config['IDTYPE']),
            $this->_formatInput($this->config['LOGOTP']),
            $this->_formatInput($this->successUrl),
            $this->_formatInput($this->failUrl),
            $this->config['KEY']
        ];
        $string = implode("|", $array);
        $string = str_replace(' ', '', $string);
        $signature = md5($string);
        return $signature;
    }

    public function checkSignature($request)
    {
        $outSign = $request['SIGN'];
        $array = [
            $request['TYPE'],
            $request['VERSION'],
            $request['RESPONSECODE'],
            $request['MCHNTCD'],
            $request['MCHNTORDERID'],
            $request['ORDERID'],
            $request['AMT'],
            $request['BANKCARD'],
            $this->config['KEY']
        ];
        $string = implode("|", $array);
        return $outSign == md5($string);
    }

    // 格式化
    private function _formatInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}