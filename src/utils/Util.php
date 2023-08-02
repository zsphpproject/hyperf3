<?php

namespace Zsgogo\utils;

class Util {

    /**
     * 随机数字字符串
     * @param int $length
     * @return string
     */
    public function getRandomNumber(int $length = 4): string {
        $chars = '0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    public function getRandomStr(int $length = 4) :string{
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghigklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            //$code .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $code;
    }


    /**
     * 对象转数组
     * @param $array
     * @return array
     */
    public function objectToArray($array) :array {
        if (is_object($array)) $array = (array)$array;
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->objectToArray($value);
            }
        }
        return $array;
    }

    /**
     * base64图片保存到本地
     * @param $base64_image_content
     * @param $path
     * @param $filename
     * @return bool
     */
    public function base64_image_content($base64_image_content,$path,$filename): bool {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            // $type = $result[2];
            $type = 'png';
            $new_file = $path."/";
            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700,true);
            }
            $file_name = $filename.".{$type}";
            $new_file = $new_file.$file_name;

            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 生成红包
     * @param $sum
     * @param $num
     * @return float|void
     */
    public function redPack($sum , $num){
        $sum = $sum*100;
        $sumall = 0;
        for($i=0 ; $i<$num ; $i++){
            $temp = rand(1 , $sum);
            $arr[$i] = $temp;
            $sumall += $temp;
        }
        $k = $sum/$sumall;
        for($i=0 ; $i<sizeof($arr); $i++){
            $arr2[$i] = $arr[$i]*$k/100;
            $m=round($arr2[$i]);
            if($m<=0){
                return 0.01;
            }
            else{
                return  $m;
            }
        }
    }

    /**
     * 验证手机号
     * @param string $phoneNumber
     * @return bool
     */
    public function checkPhoneNum(string $phoneNumber = ''): bool {
        if (preg_match("/^1[34578]{1}\d{9}$/", $phoneNumber)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 过滤emoji表情
     * @param $str
     * @return array|string|null
     */
    public function filterEmoji($str): array|string|null {
        return preg_replace_callback( '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
    }

}