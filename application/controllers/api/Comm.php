<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Comm extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Sms');
        $this->load->helper('safe_helper');
        $this->load->model('User_Model');
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 发送短信,将验证码写入redis,10分钟有效
     */
    public function sendSms()
    {
        $mobile = $this->input->post('mobile');
        if (!isMobile($mobile)) $this->responseToJson(502, '手机格式错误');
        $key = "LAST_SMSCODE_{$mobile}";
        if ($this->cache->redis->get($key)) $this->responseToJson(502, '你已发送验证码，请勿频繁操作，该验证码十分钟内有效!');

        $code = rand(100000, 999999);
        $response = $this->sms->sendSms("猪游纪", "SMS_109490433", $mobile, ['code' => $code]);
        log_message('info', 'response:' . json_encode($response));

        if (isset($response->Code) && $response->Code == 'OK') {
            $this->cache->redis->save($key, $code, 600);
            $this->responseToJson(200, '发送成功');
        } else {
            $this->responseToJson(502, '发送失败');
        }
    }

    /**
     * 用户绑定手机号
     */
    public function bindingMobile()
    {
        $user_id = $this->user_id;
        $mobile = $this->input->post('mobile');
        $code = $this->input->post('code');
        if (!$mobile) $this->responseToJson(502, 'mobile参数缺少');
        if (!isMobile($mobile)) $this->responseToJson(502, '手机格式错误');
        if (strlen($code) != 6) $this->responseToJson(502, '验证码错误');
        //验证码校验
        $key = "LAST_SMSCODE_{$mobile}";
        $redis_code = $this->cache->redis->get($key);
        if (!$redis_code) $this->responseToJson(502, '验证码已过期');
        if ($redis_code != $code) $this->responseToJson(502, '验证码错误');
        //用户绑定手机号判定
        $User_Model = new User_Model();
        $user_data = $User_Model->getUserById($user_id);
        if (!$user_data) $this->responseToJson(502, '该用户还没注册');
        if (isset($user_data['mobile']) && $user_data['mobile']) $this->responseToJson(502, '该用户已经绑定手机号');
        //绑定手机号
        if ($User_Model->update(['id' => $user_id], ['mobile' => $mobile, 'update_time' => date('Y-m-d H:i:s')])) {
            $this->responseToJson(200, '绑定成功');
        } else {
            $this->responseToJson(502, '绑定失败');
        }
    }


}
