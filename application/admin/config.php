<?php
return [
    'pwd_auth_key' => "yFjXp2Qxkf",
    'admin_auth_key' =>  'M_ADMIN_ID',	// 管理员认证SESSION标记

    'withdraw_poundage' => 2, //用户提现手续费（元/笔）
    'proxy_withdraw_poundage' => 2,//代理商提现手续费（%/笔）
    'page_size' => 15,

    'view_replace_str'=>[
        '__STATIC__'    => '/static',
        '__RESOURCE__'  => '/resource/admin',
    ],

    'user_record_type' => [
        "0_1" => '建仓费收入',
        "0_2" => '建仓费支出',
        "1_1" => '递延费收入',
        "1_2" => '递延费支出',
        "2_1" => '牛人跟买收入',
        "3_2" => '递延费提成',
        "4_1" => '平仓返还保证金',
        "4_2" => '持仓占用保证金',
        "5_1" => '充值',
        "6_1" => '提现拒绝',
        "6_2" => '余额提现',
        "7_1" => '收益分红',
        "8_1" => '申请经纪人手续费拒绝返还',
        "8_2" => '申请经纪人手续费',
        "9_1" => '牛人收入转出',
        "10_1" => '经纪人收入转出',
        "11_1" => '系统赠送金额',
    ],

    'recharge_way' => [
        0 => "支付宝",
        1 => "微信",
        2 => '连连支付',
        3 => '汇付天下',
    ]
];