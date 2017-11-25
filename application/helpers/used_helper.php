<?php
/**
 * Created by PhpStorm.
 * User: guochao
 * Date: 2017/11/25
 * Time: 下午4:24
 */


//手机号验证
if (!function_exists('isMobile')) {
    function isMobile($mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }

        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,3,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
}

//uuid 32位不重复字符
if (!function_exists('uuid')) {
    function uuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = $chars;

        return $uuid;
    }
}