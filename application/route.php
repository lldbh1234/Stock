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

Route::group(["domain" => "stock.lc"], function() {
    // Index
    Route::any('/$','index/Index/index');
    Route::group("index", function () {
        Route::any('/$','index/Index/index');
        Route::any('index', 'index/Index/index');
    });
    Route::group("cron", function () {
        Route::any('stock', 'index/Stock/grabStockLists');
    });

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
            Route::any('operate', 'admin/Team/operate'); // 运营中心
            Route::any('member', 'admin/Team/member'); // 微会员
            Route::any('ring', 'admin/Team/ring'); // 微圈
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
    });
});