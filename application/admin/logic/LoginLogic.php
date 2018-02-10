<?php
namespace app\admin\logic;

use app\admin\model\Admin;

class LoginLogic
{
    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @return integer 登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password)
    {
        $map = [];
        $map['username'] = $username;
        $map['status'] = 0;

        /* 获取用户数据 */
        $temp = Admin::with(['hasOneRole'])->where($map)->find();
        if($temp){
            $admin = $temp->toArray();
            /* 验证用户密码 */
            if(spComparePassword($password, $admin['password'])){
                //登录成功
                $adminId = $admin['admin_id'];
                // 更新登录信息
                $this->autoLogin($admin);
                return $adminId ; //登录成功，返回用户UID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在
        }
    }

    /**
     * 自动登录用户
     * @param  integer $admin 管理员信息数组
     */
    private function autoLogin($admin)
    {
        /* 更新登录信息 */
        $data = [
            'last_time'	=> request()->time(),
            'last_ip'	=> request()->ip()
        ];
        model("Admin")->save($data, ['admin_id' => $admin['admin_id']]);

        /* 记录登录SESSION和COOKIES */
        $auth = [
            'admin_id'  => $admin['admin_id'],
            'username'	=> $admin['username'],
            'last_time'	=> $admin['last_time'],
        ];
        session(config("admin_auth_key"), $admin['admin_id']);
        session('admin_info', $admin);
        session('admin_auth', $auth);
        session('admin_auth_sign', dataAuthSign($auth));
    }
}