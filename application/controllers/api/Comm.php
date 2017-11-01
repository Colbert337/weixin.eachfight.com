<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Comm extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Sms', 'RedisLib');
        $this->load->helper('safe_helper');
    }

    /**
     * 发送短信,将验证码写入redis,10分钟有效
     */
    public function SendSms()
    {
        $mobile = $this->input->post('mobile');
        if (!isMobile($mobile)) $this->responseToJson(502, '手机格式错误');
        $code = rand(100000, 999999);
        $response = $this->sms->sendSms("猪游纪", "SMS_107810012", $mobile, ['code' => $code]);
        log_message('info', 'response:' . json_encode($response));
//        $key = "LAST_SMSCODE_{$mobile}";
//        $this->redislib->setex($key, $code, '600');
        if (isset($response->Code) && $response->Code == 'OK') {
            $this->responseToJson(200, '发送成功');
        } else {
            $this->responseToJson(502, '发送失败');
        }
    }

    public function index()
    {
        dump($this->redislib);
        $this->redislib->set(100, 200000, '600');
        dump($this->redislib->get(100));
        exit;
    }


}
