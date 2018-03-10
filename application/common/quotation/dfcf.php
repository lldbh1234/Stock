<?php
/**
 * 东方财富网
 */

namespace app\common\quotation;


class dfcf
{
    // 6000001,6000041,6000071
    const REAL_REQUEST_URL = "http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?&type=CT&sty=GB20GFBTC&st=z&js=((x))&token=4f1862fc3b5e77c150a2b985b12db0fd&cmd=%s&_=1520424312152";

    public function real()
    {
        $url = sprintf(self::REAL_REQUEST_URL, "6000001,6000041,6000071");
        $data = $this->_curl($url);
        $data = ltrim($data, '("');
        $data = rtrim($data, '")');
        $data = explode('","', $data);
        $real = [];
        foreach ($data as $val){
            $real[] = explode(',', $val);
        }
        dump($real);
    }

    public function _curl($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }
}