<?php
namespace fuiou\wap\fast;


class fuiouPay
{
    public static function encryptForDES($input,$key)
    {
        $size = mcrypt_get_block_size('des','ecb');
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    public static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text))
        {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
        {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
}