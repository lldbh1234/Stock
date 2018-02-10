<?php
return [
    'pwd_auth_key' => "yFjXp2Qxkf",
    'admin_auth_key' =>  'M_ADMIN_ID',	// 管理员认证SESSION标记

    'page_size' => 15,

    'captcha'  => [
        // 验证码字符集合
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        // 验证码字体大小(px)
        'fontSize' => 25,
        // 是否画混淆曲线
        'useCurve' => true,
        // 验证码图片高度
        'imageH'   => 0,
        // 验证码图片宽度
        'imageW'   => 0,
        // 验证码位数
        'length'   => 4,
        // 验证成功后是否重置
        'reset'    => true
    ],

    'view_replace_str'=>[
        '__STATIC__'    => '/static',
        '__RESOURCE__'  => '/resource/admin',
    ],
];