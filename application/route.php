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
Route::any('protocol','index/Home/protocol'); // 合作协议
Route::any('forget','index/Home/forget');
Route::any('logout','index/Home/logout');
Route::post('members','index/Home/getMember'); // 微会员
Route::group("index", function () {
    Route::any('/$','index/Index/index');
    Route::any('index', 'index/Index/index');
    Route::post('get-region', 'index/Index/getRegion');
    Route::any('help', 'index/Index/help');
});

Route::group("notify", function () {
    Route::post('auth-llpay','index/Notify/authLLpay'); // wap认证入金
    Route::post('payment','index/Notify/payment'); // 代付出金
    Route::post('proxy-payment','index/Notify/proxyPayment'); // 代理商代付出金
    Route::post('huifu-notify','index/Notify/huifuNotify'); // 汇付天下入金
});

//我的
Route::group("user", function () {
    Route::any('home','index/User/index'); //用户中心
    Route::any('optional','index/User/optional'); //自选
    Route::any('add-optional','index/User/createOptional'); //添加自选
    Route::any('del-optional','index/User/removeOptional'); //删除自选
    Route::any('setting','index/User/setting'); //设置
    Route::any('password','index/User/password'); //修改密码
    Route::any('recharge','index/User/recharge'); //充值
    Route::any('withdraw','index/User/withdraw'); //提现
    Route::any('record','index/User/record');  //资金明细
    Route::any('notice-lists','index/User/noticeLists');
    Route::any('notice-detail','index/User/noticeDetail');
    Route::post('avatar','index/User/avatar'); //用户中心
    Route::post('nick-edit','index/User/nickEdit');
    Route::any('cards', 'index/User/cards'); //银行卡列表
    Route::any('modify-card', 'index/User/modifyCard'); // 修改银行卡
    Route::post('notify/auth-rbpay','index/Notify/authRbPay'); // 融宝回调
});

// 订单
Route::group("order", function(){
    Route::any('index','index/Order/position'); // 持仓
    Route::any('real','index/Order/ajaxPosition'); // 实时信息
    Route::any('entrust','index/Order/entrust'); // 委托
    Route::any('history','index/Order/history'); // 历史
    Route::post('cancel','index/Order/cancel'); //撤销委托
    Route::post('deposit','index/Order/deposit'); //补充保证金
    Route::post('edit-p-l','index/Order/modifyPl'); //修改止盈止损
    Route::post('selling','index/Order/selling'); //平仓申请
});

// 经纪人
Route::group("manager", function(){
    Route::any('home','index/Manager/manager'); // 经纪人首页
    Route::post('register','index/Manager/RegisterManager');
    Route::any('income-lists','index/Manager/incomeLists');
    Route::any('children','index/Manager/children');
    Route::any('follow-evening','index/Manager/followEvening');
    Route::any('follow-position','index/Manager/followPosition');
    Route::any('remove-capital', 'index/Manager/removeCapital'); // 可转资金转出

});

//关注
Route::group("attention", function(){
    Route::any('index','index/Attention/index'); // 关注
});

//Ai
Route::group("ai", function () {
    Route::any('index','index/Ai/index'); //推荐列表
});

//策略
Route::group("strategy", function () {
    Route::any('home','index/Strategy/index');
});
//牛人
Route::group("cattle", function () {
    Route::any('index','index/Cattle/index');
    Route::post('apply','index/Cattle/apply');//申请牛人
    Route::post('follow','index/Cattle/follow');//关注
    Route::any('more-master','index/Cattle/moreMaster');
    Route::any('more-strategy','index/Cattle/moreStrategy');
    Route::any('my-income','index/Cattle/myIncome');
    Route::any('follow-evening','index/Cattle/strategyEvening');//跟单平仓
    Route::any('follow-position','index/Cattle/strategyPosition');//跟单持仓
    Route::any('niuren-detail','index/Cattle/niurenDetail');
    Route::any('more-evening','index/Cattle/moreEvening');
    Route::any('more-position','index/Cattle/morePosition');
    Route::any('remove-capital', 'index/Cattle/removeCapital'); // 可转资金转出
});

Route::group("stock", function () {
    Route::any('buy', 'index/Stock/stockBuy'); //购买
    Route::any('home', 'index/Stock/info'); //购买
    Route::any('real', 'index/Stock/real'); //实时行情
    Route::any('inc-real', 'index/Stock/incReal'); //增量
    Route::any('simple', 'index/Stock/simple'); //行情基本数据
    Route::any('kline', 'index/Stock/kline'); //K线
});

Route::group("cron", function () {
    Route::any('plate', 'index/Cron/grabPlateIndex'); // 板块指数
    Route::any('stock', 'index/Cron/grabStockLists'); // 股票列表
    Route::any('defer', 'index/Cron/scanOrderDefer'); // 订单递延
    Route::any('non-defer-notice', 'index/Cron/scanDeferNotice'); // 未开启递延短信提醒
    Route::any('balance-notice', 'index/Cron/scanBalanceNotice'); // 递延余额不足短信提醒
    Route::any('sell', 'index/Cron/scanOrderSell'); // 订单爆仓、止盈、止损
    Route::any('niuren-rebate', 'index/Cron/handleNiurenRebate'); // 牛人返点
    Route::any('proxy-rebate', 'index/Cron/handleProxyRebate'); // 代理商返点
    Route::any('jiancang', 'index/Cron/handleJiancangRebate'); // 建仓费返点
    Route::any('best-order', 'index/Cron/scanBestOrders'); // 最优持仓列表统计
    Route::any('clear-best', 'index/Cron/scanClearBest'); // 删除最优持仓中已平仓的策略
    Route::any('clear-job', 'index/Cron/clearJobTable'); // 清除队列表数据
});
//web
Route::group([], function() {
    Route::group("web", function () {
        Route::any('/$', 'web/Stock/stockBuy');//首页
        Route::any('index', 'web/Index/index');//首页
        Route::any('login', 'web/Home/login');//登陆
        Route::any('logout', 'web/Home/logout');//登出
        Route::any('register', 'web/Home/register');//注册
        Route::any('mobile', 'web/Home/mobile');
        Route::any('send-code', 'web/Home/sendMobileCode');
        //A股购买
        Route::group("stock", function () {
            Route::any('buy', 'web/Stock/stockBuy'); //点买
            Route::any('sell', 'web/Stock/stockSell'); //点卖
            Route::any('history', 'web/Stock/stockHistory'); //结算
            Route::any('detail', 'web/Stock/stockDetail');
        });
        Route::group("user", function () {
            Route::any('index','web/User/index'); //用户中心
            Route::any('bank-cards','web/User/bankCards'); //银行卡管理
            Route::any('payment','web/User/payMent'); //用户充值
            Route::any('auth-payment','web/User/authPayment'); //认证支付
            Route::any('withdraw','web/User/withdraw'); //体现
        });
    });
});

// www.baonastone.com.cn
// stock.lc
//Route::group(["domain" => "www.baonastone.com.cn"], function() {
Route::group([], function() {
    // Admin
    Route::group("admin", function () {
        Route::any('/$','admin/Index/index');
        Route::any('index','admin/Index/index');
        Route::any('welcome','admin/Index/welcome');
        Route::any('userinfo','admin/Index/userinfo');
        Route::any('my-card','admin/Index/myCard');
        Route::post('get-region', 'admin/Index/getRegion');
        Route::any('withdraw','admin/Index/withdraw'); //提现
        Route::any('withdraw-list','admin/Index/withdrawList'); //提现记录
        Route::any('password','admin/Index/password');
        Route::any('login', 'admin/Home/login');
        Route::any('logout', 'admin/Home/logout');
        Route::post('verify_sms','admin/Home/verifySMS');
        Route::post('give-account-sms','admin/Home/giveAccountSms');

        // 组织架构
        Route::group("team", function () {
            Route::any('settle', 'admin/Team/settle');  // 结算中心
            Route::any('add-settle', 'admin/Team/createSettle');
            Route::any('edit-settle', 'admin/Team/modifySettle');
            Route::any('give-settle', 'admin/Team/giveSettle'); // 结算中心赠金
            Route::any('operate', 'admin/Team/operate'); // 运营中心
            Route::any('add-operate', 'admin/Team/createOperate');
            Route::any('edit-operate', 'admin/Team/modifyOperate');
            Route::any('give-operate', 'admin/Team/giveOperate'); // 运营中心赠金
            Route::any('member', 'admin/Team/member'); // 微会员
            Route::any('add-member', 'admin/Team/createMember');
            Route::any('edit-member', 'admin/Team/modifyMember');
            Route::any('member-wechat', 'admin/Team/memberWechat');
            Route::any('give-member', 'admin/Team/giveMember'); // 微会员赠金
            Route::any('ring', 'admin/Team/ring'); // 微圈
            Route::any('add-ring', 'admin/Team/createRing');
            Route::any('edit-ring', 'admin/Team/modifyRing');
            Route::any('give-ring', 'admin/Team/giveRing'); // 微圈赠金
            Route::post('recharge', 'admin/Team/recharge');
            Route::any('member-point', 'admin/Team/memberPoint');
            Route::any('settle-point', 'admin/Team/settlePoint');
            Route::any('operate-point', 'admin/Team/operatePoint');
            Route::any('ring-point', 'admin/Team/ringPoint');// 返点修改
            Route::any('ring-share', 'admin/Team/ringShare');// 推广链接
            //Route::post('rebate', 'admin/Team/rebate');
            //Route::any('create', 'admin/Team/createUser'); //添加用户
            Route::any('give-logs', 'admin/Team/giveLog'); // 赠金日志
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
            Route::any('set-deposit', 'admin/Mode/setDeposit');
            Route::any('set-lever', 'admin/Mode/setLever');
            //Route::any('deposit', 'admin/Mode/deposit'); //保证金列表
            //Route::any('add-deposit', 'admin/Mode/createDeposit');
            //Route::any('edit-deposit', 'admin/Mode/modifyDeposit');
            //Route::post('del-deposit', 'admin/Mode/removeDeposit');
            //Route::any('lever', 'admin/Mode/lever'); //保证金列表
            //Route::any('add-lever', 'admin/Mode/createLever');
            //Route::any('edit-lever', 'admin/Mode/modifyLever');
            //Route::post('del-lever', 'admin/Mode/removeLever');
        });

        Route::group("deposit", function(){
            Route::any('lists', 'admin/Deposit/index');
            Route::any('add', 'admin/Deposit/create');
            Route::any('edit', 'admin/Deposit/modify');
            Route::post('del', 'admin/Deposit/remove');
        });

        Route::group("lever", function(){
            Route::any('lists', 'admin/Lever/index');
            Route::any('add', 'admin/Lever/create');
            Route::any('edit', 'admin/Lever/modify');
            Route::post('del', 'admin/Lever/remove');
        });

        Route::group("hot", function(){
            Route::any('lists', 'admin/Hot/index');
            Route::any('add', 'admin/Hot/create');
            Route::any('edit', 'admin/Hot/modify');
            Route::post('del', 'admin/Hot/remove');
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

        //权限管理
        Route::group("permission", function(){
            Route::any('lists', 'admin/Permission/lists');  // 权限资源列表
            Route::any('add', 'admin/Permission/add'); // 添加权限资源
            Route::any('modify', 'admin/Permission/modify'); // 修改权限资源
            Route::post('del', 'admin/Permission/del'); // 删除权限资源
            Route::any('role-push', 'admin/Permission/rolePush'); // 角色授权
        });

        //系统管理
        Route::group("system", function(){
            Route::any('lists', 'admin/System/lists');  // 系统设置
            Route::post('modify', 'admin/System/modify'); // 修改
        });

        //会员管理
        Route::group("user", function(){
            Route::any('lists', 'admin/User/lists');  // 会员列表
            Route::any('modify', 'admin/User/modify'); // 修改
            Route::any('detail', 'admin/User/detail'); // 详细信息
            Route::any('modify-pwd', 'admin/User/modifyPwd'); // 修改密码
            Route::any('give-lists', 'admin/User/giveLists');  // 会员赠金列表
            Route::any('give-account', 'admin/User/giveAccount');  // 会员赠金
            Route::any('give-log', 'admin/User/giveLog');  // 会员赠金日志
            Route::any('withdraw-lists', 'admin/User/withdrawLists');  // 会员出金列表
            Route::any('withdraw-detail', 'admin/User/withdrawDetail');  // 会员出金列表
            Route::any('withdraw', 'admin/User/withdraw');  // 会员出金
            Route::any('withdraw-state', 'admin/User/withdrawState');  // 关闭or开启出金

            Route::any('virtual-lists', 'admin/User/virtualLists');  // 虚拟会员列表
            Route::any('create-virtual', 'admin/User/createVirtual'); // 添加虚拟会员
            Route::any('modify-virtual', 'admin/User/modifyVirtual'); // 修改虚拟会员
            Route::any('virtual-detail', 'admin/User/virtualDetail'); // 虚拟会员详情
        });

        //经纪人管理
        Route::group("manager", function(){
            Route::any('lists', 'admin/Manager/lists');
            Route::any('audit-lists', 'admin/Manager/auditLists');
            Route::post('audit', 'admin/Manager/audit'); // 审核
            Route::any('point', 'admin/Manager/point'); // 返点修改
        });

        // 订单管理
        Route::group("order", function(){
            Route::any('lists', 'admin/Order/index'); //委托订单
            Route::any('entrust-detail', 'admin/Order/entrustDetail'); // 委托订单详情
            Route::any('entrust-rebate', 'admin/Order/entrustRebate'); // 委托订单返点记录
            Route::any('history', 'admin/Order/history'); //已平仓订单
            Route::any('history-detail', 'admin/Order/historyDetail'); // 平仓订单详情
            Route::any('history-rebate', 'admin/Order/historyRebate'); // 平仓订单返点记录
            Route::any('position', 'admin/Order/position'); //持仓订单
            Route::any('position-detail', 'admin/Order/positionDetail'); // 持仓订单详情
            Route::any('position-rebate', 'admin/Order/positionRebate'); // 持仓订单返点记录
            Route::any('position-give', 'admin/Order/positionGive'); // 持仓订单送股
            //Route::post('buy-ok', 'admin/Order/buyOk'); //建仓成功
            //Route::post('buy-fail', 'admin/Order/buyFail'); //建仓失败
            Route::post('sell-ok', 'admin/Order/sellOk'); //平仓成功
            Route::post('sell-fail', 'admin/Order/sellFail'); //平仓失败
            Route::post('force-sell', 'admin/Order/forceSell'); //强制平仓
            Route::post('hedging', 'admin/Order/hedging'); // 持仓订单对冲
            Route::any('force', 'admin/Order/force'); // 强制平仓订单
            Route::any('give', 'admin/Order/give'); // 转送股
            Route::post('ware', 'admin/Order/ware'); // 穿仓价
            Route::post('to-position', 'admin/Order/toPosition'); // 订单转为持仓
            Route::get('position-export', 'admin/Order/positionExport'); // 持仓单导出
            Route::get('history-export', 'admin/Order/historyExport'); // 平仓单导出

        });

        // 记录
        Route::group("record", function(){
            Route::any('recharge', 'admin/Record/recharge'); //充值
            Route::any('niuren', 'admin/Record/niuren'); //牛人
            Route::any('manager', 'admin/Record/manager'); // 经纪人返点
            Route::any('proxy', 'admin/Record/proxy'); // 后台代理商
            Route::any('proxy-withdraw', 'admin/Record/proxyWithdrawLists'); // 后台代理商出金列表
            Route::any('proxy-withdraw-detail', 'admin/Record/proxyWithdrawDetail'); // 后台代理商出金详情
            Route::post('do-proxy-withdraw', 'admin/Record/doProxyWithdraw'); // 后台代理商出金操作
            Route::any('defer', 'admin/Record/defer'); // 递延费自动扣除
            Route::any('my', 'admin/Record/my'); // 我的返佣记录
        });
    });
});