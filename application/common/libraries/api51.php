<?php
namespace app\common\libraries;

class api51
{
	const APP_CODE = "d43834bad1ae4bafaa3129b7b205a293";
	const REAL_REQUEST_URL = 'http://stock.api51.cn/real';
	
	public function realtime($code, $field = null){
		$field = $field ? : "prod_code,prod_name,data_timestamp,open_px,high_px,low_px,last_px,preclose_px,business_amount,business_balance,offer_grp,bid_grp,px_change,px_change_rate,amplitude";
		$data = [
			'en_prod_code'	=> $code,//接收手机号
			'fields'		=> $field,//模板变量，多个以英文逗号隔开
		];
		$data = http_build_query($data);
		$response = $this->api51_curl(self::REAL_REQUEST_URL, $data, 0, self::APP_CODE);
		return json_decode($response, true);
	}
	
	public function api51_curl($url, $data=false, $ispost=0, $appcode){
		$headers = array();
		//根据阿里云要求，定义Appcode
		array_push($headers, "Authorization:APPCODE " . $appcode);
		array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
  
		$httpInfo = array();
		 
		$ch = curl_init();
  		curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_USERAGENT , 'api51.cn' );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 300 );
		curl_setopt( $ch, CURLOPT_TIMEOUT , 300);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		if (1 == strpos("$".$url, "https://")) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		if($ispost){
			 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt( $ch , CURLOPT_POST , true );
			curl_setopt( $ch , CURLOPT_POSTFIELDS , $data );
			curl_setopt( $ch , CURLOPT_URL , $url );
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			if($data){
				curl_setopt( $ch , CURLOPT_URL , $url.'?'.$data );
			 
			}else{
				curl_setopt( $ch , CURLOPT_URL , $url);
			}
		}
		$response = curl_exec( $ch );
		if ($response === FALSE) {
			// echo "cURL Error: " . curl_error($ch);
			return false;
		}
		$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
		$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
		curl_close( $ch );
		return $response;
	}
}