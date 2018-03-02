<?php
namespace app\admin\controller;

use app\admin\logic\AccessLogic;
use app\admin\logic\MenuLogic;
use app\common\model\BaseModel;
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
        $module_name = request()->module();
        $controller_name = request()->controller();
        $action_name = request()->action();
        $rule_name = $module_name.'/'.$controller_name.'/'.$action_name;

        if(
            in_array($controller_name, ['Index', 'index'])
            || in_array($action_name, ['login', 'logout'])
            || BaseModel::ADMINISTRATOR_ID == $this->adminId)
        {
            return true;
        }
        //读取用户权限列表
        $userNodeList = self::getUserNodeList();
        if(!in_array($rule_name, $userNodeList)){
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
        $accessLogic = new AccessLogic();
        $menueLogic = new MenuLogic();

        $userRoleId = manager()['role'];
        $nodeList = $accessLogic->getRoleBy(['role_id' => $userRoleId]);
        return $menueLogic->getActBy(['id' => $nodeList]);
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
}