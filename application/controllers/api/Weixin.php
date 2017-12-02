<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class Weixin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->helper('cookie');
        $this->load->helper('used_helper');
    }

    //微信用户进行公众号授权
    public function oauth()
    {
        exit;
        $callback = urldecode($this->input->get('url')) . '?code=200';
        if (!$this->session->has_userdata($this->wechat_key)) {
            $response = $this->wechat->oauth->with(['state' => urlencode($callback)])->redirect();
            $response->send();
        } else {
            redirect($callback);
        }
    }

    //回调地址，获取用户基本信息  第一次注册入库
    public function oauthBack()
    {
        $user = $this->wechat->user->get('o05NB0w96SrxDgpS6ZzOapUNq1WY');
        dump($user);
        exit;

        $userArr = $user->toArray();
        $this->session->set_userdata([$this->wechat_key => $userArr['id']]);
        set_cookie('token', $userArr['id'], time() + 7200, '.eachfight.com', '/');
        redirect(urldecode($this->input->get('state')));
    }


    /**
     * 前端给code 授权获取用户访问随机token  注册入库
     */
    public function weboauth()
    {
        $User_Model = new User_Model();
        $code = $this->input->get('code', true);

        if (empty($code)) $this->responseToJson(502, 'code参数缺少');
        log_message('info', 'weboauth获取到的code:' . $code);

        try {
            $user = $this->wechat->oauth->user();
            $data = $user->getOriginal();
            //随机token
            $token = uuid();
            //给token赋值并加密
            $this->cache->redis->save($token, md5($data['openid'] . 'eachfight'), 7200);
            log_message('info', '授权获取用户随机token:' . $token);
            //没有注册过 注册
            if (!$this->User_Model->CheckRegister($data['openid'])) {
                if (!$User_Model->insert([
                    'openid' => $data['openid'],
                    'token' => $token,
                    'nickname' => replace_emoji($data['nickname']),
                    'gender' => $data['sex'],  //1时是男性，值为2时是女性，值为0时是未知
                    'headimg_url' => $data['headimgurl'],
                    'create_time' => date('Y-m-d H:i:s')
                ])) {
                    throw new \Exception('用户注册入库失败');
                }
            } else {  //更新token
                if (!$User_Model->update(['openid' => $data['openid']],
                    ['token' => $token, 'update_time' => date('Y-m-d H:i:s')])) {
                    throw new \Exception('更新访问token失败');
                }
            }

            $this->responseToJson(200, '登陆成功', ['token' => $token]);
        } catch (\Exception $e) {
            $this->responseToJson(502, $e->getMessage());
        }
    }
}
