<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function index()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $demo = new \App\Libraries\Sms();
        $response = $demo->sendSms(
            "短信签名", // 短信签名
            "SMS_0000001", // 短信模板编号
            "13127529625", // 短信接收者
            Array(  // 短信模板中字段的值
                "code"=>"123456",
            ),
            "123456789"
        );
        print_r($response);
    }
}
