<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/*return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];*/
use think\Route;
//test
Route::any('test', 'admin/Test/test');
// Index
Route::any('/$','index/Index/index');
Route::any('login','index/Home/login');
Route::post('captcha','index/Home/captcha'); // 验证码
Route::any('register','index/Home/register');
Route::any('forget','index/Home/forget');
Route::any('logout','index/Home/logout');
Route::group("index", function () {
    Route::any('/$','index/Index/index');
    Route::any('index', 'index/Index/index');
});

//我的
Route::group("user", function () {
    Route::any('home','index/User/index'); //用户中心
    Route::any('setting','index/User/setting'); //设置
    Route::any('password','index/User/password'); //修改密码
    Route::any('recharge','index/User/recharge'); //充值
    Route::any('withdraw','index/User/withdraw'); //提现
});

//Ai
Route::group("ai", function () {
    Route::any('index','index/Ai/index'); //推荐列表
});

Route::group("stock", function () {
    Route::any('buy','index/Stock/stockBuy'); //购买
});

Route::group("cron", function () {
    Route::any('stock', 'index/Cron/grabStockLists');
});

Route::group(["domain" => "stock.lc"], function() {
    // Admin
    Route::group("admin", function () {
        Route::any('/$','admin/Index/index');
        Route::any('index','admin/Index/index');
        Route::any('welcome','admin/Index/welcome');
        Route::any('login', 'admin/Home/login');
        Route::any('logout', 'admin/Home/logout');

        // 组织架构
        Route::group("team", function () {
            Route::any('settle', 'admin/Team/settle');  // 结算中心
            Route::any('add-settle', 'admin/Team/createSettle');
            Route::any('edit-settle', 'admin/Team/modifySettle');
            Route::any('operate', 'admin/Team/operate'); // 运营中心
            Route::any('add-operate', 'admin/Team/createOperate');
            Route::any('edit-operate', 'admin/Team/modifyOperate');
            Route::any('member', 'admin/Team/member'); // 微会员
            Route::any('add-member', 'admin/Team/createMember');
            Route::any('edit-member', 'admin/Team/modifyMember');
            Route::any('member-wechat', 'admin/Team/memberWechat');
            Route::any('ring', 'admin/Team/ring'); // 微圈
            Route::any('add-ring', 'admin/Team/createRing');
            Route::any('edit-ring', 'admin/Team/modifyRing');
            Route::post('recharge', 'admin/Team/recharge');
            Route::any('create', 'admin/Team/createUser'); //添加用户
        });

        // 插件管理
        Route::group("plugins", function(){
            Route::any('lists', 'admin/Plugins/lists');  // 插件列表
        });

        // 产品管理
        Route::group("product", function(){
            Route::any('index', 'admin/Product/index');  // 产品列表
            Route::any('create', 'admin/Product/add');
        });

        // 交易模式
        Route::group("mode", function(){
            Route::any('lists', 'admin/Mode/index');
            Route::any('add', 'admin/Mode/create');
            Route::any('edit', 'admin/Mode/modify');
            Route::post('delete', 'admin/Mode/remove');
            Route::any('deposit', 'admin/Mode/deposit'); //保证金列表
            Route::any('add-deposit', 'admin/Mode/createDeposit');
            Route::any('edit-deposit', 'admin/Mode/modifyDeposit');
            Route::post('del-deposit', 'admin/Mode/removeDeposit');
            Route::any('lever', 'admin/Mode/lever'); //保证金列表
            Route::any('add-lever', 'admin/Mode/createLever');
            Route::any('edit-lever', 'admin/Mode/modifyLever');
            Route::post('del-lever', 'admin/Mode/removeLever');
        });

        Route::group("ai", function(){
            Route::any('index', 'admin/Ai/index'); //智能推荐类型
            Route::any('add-type', 'admin/Ai/createType'); //添加推荐类型
            Route::any('edit-type', 'admin/Ai/modifyType'); //修改推荐类型
            Route::post('del-type', 'admin/Ai/removeType'); //删除推荐类型
            Route::any('stocks', 'admin/Ai/stocks'); //智能推荐股票
            Route::any('add', 'admin/Ai/create'); //添加推荐股票
            Route::any('edit', 'admin/Ai/modify'); //修改推荐股票
            Route::post('del', 'admin/Ai/remove'); //删除推荐股票
        });

        // 角色管理
        Route::group("role", function(){
            Route::any('lists', 'admin/Admin/roles');  // 角色列表
            Route::any('create', 'admin/Admin/roleCreate'); //添加角色
            Route::post('remove', 'admin/Admin/roleRemove'); //删除角色
            Route::post('delete', 'admin/Admin/rolePatchRemove'); //批量删除
            Route::any('modify', 'admin/Admin/roleEdit'); //修改角色
        });

        // 管理员管理
        Route::group("admin", function(){
            Route::any('lists', 'admin/Admin/lists');  // 管理员列表
            Route::any('add', 'admin/Admin/create'); // 添加管理员
            Route::any('edit', 'admin/Admin/modify'); // 修改管理员
            Route::post('del', 'admin/Admin/remove'); // 删除管理员
            Route::post('delete', 'admin/Admin/patchRemove'); //批量删除
        });
    });
});