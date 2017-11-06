<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
        $this->load->library('Sms');
//      $this->openid = $this->input->get_request_header('openid', TRUE);
    }

    public function index()
    {
        $user_id = $this->getUserId();
        dump($user_id);
        exit;
        $user_id = (ENVIRONMENT !== 'development') ? $this->getUserId() : 1;
    }

    /**
     * 获取段位价格配置
     */
    public function getGameLevel()
    {
        log_message('info', '获取到的headers' . json_encode($this->input->request_headers()));

        $GameLevel_Model = new GameLevel_Model();
        $data = $GameLevel_Model->getGameLevel(1);
        $this->responseToJson(200, '获取成功', $data);
    }
}
