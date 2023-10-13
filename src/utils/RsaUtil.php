<?php

namespace Zsgogo\utils;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;

class RsaUtil {

    private string $privateKey;

    private string $publicKey;

    /**
     * rsa.php
     * @param Filesystem $filesystem
     * @throws FilesystemException
     */
    public function __construct(Filesystem $filesystem) {
        $config = \Hyperf\Config\config("rsa");
        $this->privateKey = $filesystem->read($config["private_key"]);
        $this->publicKey = $filesystem->read($config["public_key"]);
    }

    /**
     * @param $data
     * @param string $type
     * @return mixed|string
     */
    public function RSAOpenssl($data, string $type='encode'): mixed {
        if (empty($data)) {
            return 'data参数不能为空';
        }
        if (is_array($data)) {
            return 'data参数不能是数组形式';
        }

        $rsa_public = $this->publicKey;
        $rsa_private = $this->privateKey;

        //私钥解密
        if ($type == 'decode') {
            $private_key = openssl_pkey_get_private($rsa_private);
            if (!$private_key) {
                return('私钥不可用');
            }
            $return_de = openssl_private_decrypt(base64_decode($data), $decrypted, $private_key);
            if (!$return_de) {
                return('解密失败,请检查RSA秘钥');
            }
            return $decrypted;
        }

        //公钥加密
        $key = openssl_pkey_get_public($rsa_public);
        if (!$key) {
            return('公钥不可用');
        }
        //openssl_public_encrypt 第一个参数只能是string
        //openssl_public_encrypt 第二个参数是处理后的数据
        //openssl_public_encrypt 第三个参数是openssl_pkey_get_public返回的资源类型
        $return_en = openssl_public_encrypt($data, $crypted, $rsa_public);
        if (!$return_en) {
            return('加密失败,请检查RSA秘钥');
        }
        return base64_encode($crypted);
    }
}