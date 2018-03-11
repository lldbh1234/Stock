<?php
namespace app\web\controller;

use Endroid\QrCode\QrCode;
use think\Request;
use think\Controller;

class Base extends Controller
{
    protected $user_id;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assign('type', 1);
        $this->assign('userInfo', uInfo() ? uInfo() : []);

    }
    public function createManagerQrcode($uid)
    {
        if($uid > 0) {
            $qrCode = new QrCode();
            //想显示在二维码中的文字内容，这里设置了一个查看文章的地址
            $url = url('index/Home/register', ["pid" => $uid], true, true);
            $qrCode->setText($url)
                ->setSize(300)
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
//            ->setBackgroundColor(array('r' => 255, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setLabel('经纪人：' . uInfo()['username'], '.')
                ->setLabelFontSize(16)
                ->setLogoPath($_SERVER['DOCUMENT_ROOT'] . trim(uInfo()['face']))
                ->setWriterByName('png');

            $qrCode->writeFile('./upload/manager_qrcode/' . $this->user_id . '.png');
        }
    }
    public function checkUserLogin()
    {
        $this->user_id = isLogin();
        if(!$this->user_id){// 还没登录 跳转到登录页面
            return false;
        }
        return true;
    }
}