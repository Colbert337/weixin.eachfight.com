<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
        $this->load->model('Order_Model');
        $this->load->library('Sms');
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 获取用户游戏及手机绑定状态
     */
    public function index()
    {
        try {
            //手机绑定状态
            $mobile_bind = $this->User_Model->CheckBindMobile($this->user_id);
            //获取当前订单
            $user_order = $this->Order_Model->getUserOrder($this->user_id);
            $play_status = $this->getUserPlayStatus($user_order->status ?? '');
            $order_id = $user_order->id ?? '';
            $data = ['play_status' => $play_status, 'order_id' => $order_id, 'mobile_bind' => $mobile_bind,
                'order_info' => [], 'god_info' => []];

            $this->responseToJson(200, '获取成功', $data);
        } catch (\Exception $exception) {
            $this->responseToJson(200, $exception->getMessage());
        }


        dump($play_status);

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

    //根据订单状态获取用户游戏状态
    private function getUserPlayStatus($status)
    {
        switch ($status) {
            case 1:
                $play_status = 2;  //等待接单
                break;

            case 3:
                $play_status = 3;  //运行游戏(大神抢单后,用户还没有准备)
                break;

            case 5:
            case 6:
                $play_status = 4;  //运行游戏(大神抢单后,用户已准备)
                break;

            case 7:
                $play_status = 5;  //付款(大神提交战绩)
                break;

            case 2:
            case 4:
            case 8:
            case 9:
                $play_status = 1;  //去下单   未接取消/已接取消/用户发起申诉/订单完成
                break;

            default:
                $play_status = 1;  //去下单 当前没有订单
        }

        return $play_status;
    }
}
