<?php
return [
    'rbpay_wap_config' => [
        'merchant_id'           => '100000001303731',//商户ID
        'seller_email'          => 'lyj99842@163.com',//商户邮箱
        'cert_type'             => '01',//证件类型
        'privateKey'            => EXTEND_PATH . "rbpay/wap/cert/user-rsa.pem",// 商户私钥
        'publicKey'             => EXTEND_PATH . "rbpay/wap/cert/rongbao-public.pem",// 融宝公钥
        'apiKey'                => 'c4ge8c9518818d5e45638289867b0c9deb7803d16a90a7e4b3b6ded2g57aa5ag',
        'apiUrl'                => 'http://api.reapal.com',
    ],

];