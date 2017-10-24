<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function index()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $demo = new SmsDemo(
          
        );

        echo "SmsDemo::sendSms\n";
        $response = $demo->sendSms(
            "短信签名", // 短信签名
            "SMS_0000001", // 短信模板编号
            "12345678901", // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>"12345",
                "product"=>"dsd"
            ),
            "123"
        );
        print_r($response);
    }
}
