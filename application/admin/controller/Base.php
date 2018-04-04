<?php
namespace app\admin\controller;

use app\admin\logic\AccessLogic;
use app\admin\logic\MenuLogic;
use app\admin\logic\UserLogic;
use app\common\model\BaseModel;
use Endroid\QrCode\QrCode;
use think\Controller;
use think\Request;


class Base extends Controller
{
    protected $adminId;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->adminId = isLogin();

        if(!$this->adminId){// 还没登录 跳转到登录页面
            return $this->redirect(url("admin/Home/login"));
            exit;
        }
        self::checkAuth();
    }

    /**
     * 检查用户权限
     */
    public function checkAuth()
    {
        $module = request()->module();
        $controller = request()->controller();
        $action = request()->action();
        $rule = "{$module}/{$controller}/{$action}";
        $userNodeList = self::getUserNodeList();
        if(
            in_array($controller, ['Index', 'index', 'Test'])
            || in_array($action, ['login', 'logout'])
            || BaseModel::ADMINISTRATOR_ID == $this->adminId)
        {
            return true;
        }
        //读取用户权限列表
        if(!in_array($rule, $userNodeList)){
            if(request()->isAjax()){
                return $this->fail('您没有权限访问');
            }
            exit('您没有权限访问');
//            $this->error('您没有权限访问');
        }
    }

    /**
     * 获取用户资源节点
     */
    public function getUserNodeList()
    {
        //if(session("?ACCESS_LIST")){
        //    $nodeActs = session("ACCESS_LIST");
        //}else{
            $accessLogic = new AccessLogic();
            $menuLogic = new MenuLogic();
            if(BaseModel::ADMINISTRATOR_ID == $this->adminId){
                $nodeActs = $menuLogic->getActBy();
            }else{
                $userRoleId = manager()['role'];
                $nodeList = $accessLogic->getRoleBy(['role_id' => $userRoleId]);
                $nodeActs = $menuLogic->getActBy(['id' => $nodeList]);
            }
            $nodeActs = array_filter($nodeActs);
            session("ACCESS_LIST", $nodeActs);
        //}
        return $nodeActs;
    }

    /**
     * 菜单获取
     * @return array
     */
    public function leftMenu()
    {
        $accessLogic = new AccessLogic();
        $menueLogic = new MenuLogic();

        $param = [];
        if(BaseModel::ADMINISTRATOR_ID != $this->adminId)
        {
            $userRoleId = manager()['role'];
            $nodeList = $accessLogic->getRoleBy(['role_id' => $userRoleId]);
            $param = ['id' => $nodeList];
        }
        return $menueLogic->getMenueBy($param);


    }
    public function createManagerQrcode($uid)
    {
        if($uid > 0) {
            $userInfo = (new UserLogic())->getOne($uid);
            $qrCode = new QrCode();
            //想显示在二维码中的文字内容，这里设置了一个查看文章的地址
            $url = url('index/Home/register', ["pid" => $uid], true, true);
            $qrCode->setText($url)
                ->setSize(300)
                ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
//            ->setBackgroundColor(array('r' => 255, 'g' => 0, 'b' => 0, 'a' => 0))
                ->setLabel('经纪人：' . $userInfo['username'], '.')
                ->setLabelFontSize(16)
                ->setLogoPath($_SERVER['DOCUMENT_ROOT'] . trim($userInfo['face']))
                ->setWriterByName('png');
            if(!file_exists('./upload/manager_qrcode/')){
                mkdir('./upload/manager_qrcode/');
            }
            $qrCode->writeFile('./upload/manager_qrcode/' . $uid . '.png');
        }
    }
}