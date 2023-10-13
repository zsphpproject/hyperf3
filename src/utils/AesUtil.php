<?php

namespace Zsgogo\utils;



class AesUtil
{
    public static function encrypt(string $context,string $key){
        // 加密数据 'AES-128-ECB' 可以通过openssl_get_cipher_methods()获取
        return openssl_encrypt($context, 'AES-256-ECB', $key, 0);
    }


    public static function decrypt(string $encrypt,string $key){
        return openssl_decrypt($encrypt, 'AES-256-ECB', $key, 0);
    }
}
