<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
    }

    public function index()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $demo = new \App\Libraries\Sms();
        $response = $demo->sendSms(
            "猪游纪", // 短信签名
            "SMS_107810012", // 短信模板编号
            "13127529625", // 短信接收者
            Array(  // 短信模板中字段的值
                "code" => "123456",
            ),
            "123456789"
        );
        
        log_message('info', 'response:'.json_encode($response));
        print_r($response);
    }

    /**
     * 获取段位价格配置
     */
    public function getGameLevel()
    {
        $GameLevel_Model = new GameLevel_Model();
        $data = $GameLevel_Model->getGameLevel(1);
        $this->responseToJson(200, '获取成功', $data);
    }
}
