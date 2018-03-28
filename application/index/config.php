<?php
return [
    'default_face'  => '/resource/home/img/default-user-img.png',

    'withdraw_poundage' => 2,
    'nickname_prefix'   => "策略家",

    'view_replace_str'=>[
        '__STATIC__'    => '/static',
        '__RESOURCE__'  => '/resource/home',
        '__MANAGERQRCODE__' => '/upload/manager_qrcode',
    ],

    'llpay_fast_wap' =>[
        'oid_partner'   => '201803270001673008',
        'RSA_PRIVATE_KEY' => EXTEND_PATH . "llpay/wap/fast/key/rsa_private_key.pem",
        'key'           => '', //MD5key, 现在已经不支持MD5了
        'version'       => '1.2',
        'app_request'   => '3',
        'sign_type'     => strtoupper('RSA'),
        'valid_order'   => '10080',
        'input_charset' => strtolower('utf-8'),//需要放在根目录
        'transport'     => 'http',
        'busi_partner'  => '101001',
        'bg_color'      => 'FC5155',
    ],

    'llpay_auth_wap' =>[
        'oid_partner'   => '201803270001673008',
        'RSA_PRIVATE_KEY' => EXTEND_PATH . "llpay/wap/fast/key/rsa_private_key.pem",
        'key'           => '', //MD5key, 现在已经不支持MD5了
        'version'       => '1.2',
        'app_request'   => '3',
        'sign_type'     => strtoupper('RSA'),
        'valid_order'   => '10080',
        'input_charset' => strtolower('utf-8'),//需要放在根目录
        'transport'     => 'http',
        'busi_partner'  => '101001',
        'bg_color'      => 'FC5155',
    ],
];