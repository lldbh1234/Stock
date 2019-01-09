<?php
/**
 * 富友代付
 */
namespace app\common\payment;


class paymentFuiou
{
    protected $config;
    protected $bankno;
    public function __construct()
    {
        $this->config = config("fuiou.fuiou_payment_config");
        $this->bankno = config("fuiou.payment_bank_no");
    }

    public function payment($orderSn, $amt, $card)
    {
        $memo = "58好策略余额提现";
        $data = $this->getData($orderSn, $amt, $memo, $card);
        $query = http_build_query($data);
        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
                    "Content-Length: ".strlen($query)."\r\n".
                    "User-Agent:MyAgent/1.0\r\n",
                'method'  => "POST",
                'content' => $query,
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($this->config['gateway'], false, $context, -1, 40000);
        $response = simplexml_load_string($result);
        return $response;
    }

    public function getData($orderSn, $amt, $memo, $card)
    {
        $merdt = date("Ymd");
        $amt = $amt * 100;
        $cityno = "";
        $xml = "<?xml version='1.0' encoding='utf-8' standalone='yes'?>";
        $xml .= "<payforreq>";
        $xml .= "<ver>{$this->config['ver']}</ver>";
        $xml .= "<merdt>{$merdt}</merdt>";
        $xml .= "<orderno>{$orderSn}</orderno>";
        $xml .= "<bankno>{$this->bankno[$card['bank']]}</bankno>";
        $xml .= "<cityno>{$cityno}</cityno>";
        $xml .= "<accntno>{$card['card']}</accntno>";
        $xml .= "<accntnm>{$card['name']}</accntnm>";
        $xml .= "<branchnm>{$card['addr']}</branchnm>";
        $xml .= "<amt>{$amt}</amt>";
        $xml .= "<mobile>{$card['mobile']}</mobile>";
        $xml .= "<entseq>{$orderSn}</entseq>";
        $xml .= "<memo>{$memo}</memo>";
        $xml .= "</payforreq>";
        $data = [
            "merid" => $this->config["mchntcd"],
            "reqtype" => $this->config["reqtype"],
            "xml"   => $xml,
            "mac"   => $this->signature($xml)
        ];
        return $data;
    }

    public function checkSignature($response)
    {
        $outSign = $response['mac'];
        $array = [
            $this->config['mchntcd'],
            $this->config["mchntkey"],
            $response['orderno'],
            $response['merdt'],
            $response['accntno'],
            $response['amt']
        ];
        $string = implode("|", $array);
        return $outSign == md5($string);
    }

    public function signature($xml)
    {
        $array = [
            $this->config['mchntcd'],
            $this->config['mchntkey'],
            $this->config['reqtype'],
            $xml
        ];
        $string = implode("|", $array);
        $signature = strtoupper(md5($string));
        return $signature;
    }
}