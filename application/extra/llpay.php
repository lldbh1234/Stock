<?php
return [
    'llpay_wap_config' =>[
        'oid_partner'   => '201803270001673008',
        'RSA_PRIVATE_KEY' => EXTEND_PATH . "llpay/wap/key/rsa_private_key.pem",
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