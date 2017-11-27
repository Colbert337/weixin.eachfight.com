<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
        $this->load->model('Order_Model');
        $this->load->model('God_Model');
        $this->load->model('OrderRecord_Model');

        $this->load->library('form_validation');

        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));
        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 获取用户游戏及手机绑定状态
     */
    public function index()
    {
        try {
            //获取当前用户游戏状态
            $user_order = $this->Order_Model->getUserOrder($this->user_id);
            $play_status = $this->getUserPlayStatus($user_order->status ?? '');

            //获取用户信息
            $user_data = $this->User_Model->getUserById($this->user_id);
            //手机绑定状态
            $user_info = ['mobile_bind' => $user_data['mobile'] ? 1 : 0, 'available_balance' => $user_data['available_balance']];

            $order_id = $user_order->id ?? '';
            //存在大神时获取大神信息
            $god_info = ($order_id && $user_order->god_user_id) ?
                $this->getGodInfo($user_order->god_user_id, $user_order->game_type) : [];

            $victory_num = [];
            if ($order_id) {
                $OrderRecord_Model = $this->OrderRecord_Model->scalarBy(['order_id' => $user_order->id]);
                $victory_num = $OrderRecord_Model['victory_num'] ?? [];
            }

            $data = ['play_status' => $play_status, 'user_info' => $user_info, 'god_info' => $god_info,
                'order_info' => ['order_id' => $order_id, 'victory_num' => $victory_num]];

            $this->responseToJson(200, '获取成功', $data);
        } catch (\Exception $exception) {
            $this->responseToJson(200, $exception->getMessage());
        }
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


    /**
     * 用户下单
     */
    public function createOrder()
    {
        $params = $this->input->post();
        //参数校验
        $this->form_validation->set_data($params);
        if ($this->form_validation->run('create_order') == false) {
            $errors = array_values($this->form_validation->error_array());
            $this->responseToJson(502, array_shift($errors));
        }
        //排除游戏中
        $user_order = $this->Order_Model->getUserOrder($this->user_id);
        $play_status = $this->getUserPlayStatus($user_order->status ?? '');
        if ($play_status != 1) $this->responseToJson(502, '该用户有一笔未完成的订单');
        //每局价格
        $GameLevel_Model = $this->GameLevel_Model->scalar($params['game_level_id']);
        $one_price = $GameLevel_Model['one_price'];
        //订单金额
        $order_fee = $one_price * $params['game_num'];
        //是否计胜负
        $discount_rax = 0.3;
        if ($params['is_except'] == 2) {  //不计优惠
            $order_fee = $order_fee * (1 - $discount_rax);
        }
        //金额判定
        $user_info = $this->User_Model->getUserById($this->user_id);
        if ($order_fee > $user_info['available_balance'])
            $this->responseToJson(502, '该用户账户余额不足');

        //入库给大神推送微信模板消息(结算才扣用户的钱)
        $insert_id = $this->Order_Model->insert([
            'user_id' => $this->user_id,
            'game_type' => $params['game_type'],
            'game_mode' => $params['game_mode'],
            'is_except' => $params['is_except'],
            'device' => $params['device'],
            'game_zone' => $params['game_zone'],
            'game_level_id' => $params['game_level_id'],
            'one_price' => $one_price,
            'game_num' => $params['game_num'],
            'discount_rax' => $discount_rax,
            'order_fee' => $order_fee,
            'remark' => htmlspecialchars($params['remark']),
            'create_time' => date('Y-m-d H:i:s')
        ],true);
        if ($insert_id) {
            //订单信息
            $game_level = $GameLevel_Model['game_level'];
            $order_info = game_type()[$params['game_type']] . '|' . game_mode()[$params['game_mode']] . '|'
                . device()[$params['device']] . game_zone()[$params['game_zone']]
                . '|' . $game_level . '|' . $params['game_num'] . '局';
            //发消息
            $god_openids = ['o05NB0w96SrxDgpS6ZzOapUNq1WY', 'o05NB08e8bdzMV7Kc6Nj3-0zwYaU', 'o05NB0xCHKaf1j1hqnSJzA7NMBD4'];
            $this->sendNotice($god_openids, $order_fee, $order_info,$insert_id);
            $this->responseToJson(200, '下单成功');
        } else {
            $this->responseToJson(502, '下单失败');
        }
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

    //根据用户id及游戏类型获取大神的信息
    private function getGodInfo($user_id, $game_type)
    {
        $god_info = $this->God_Model->getGodInfo($user_id, $game_type);
        $user_info = $this->User_Model->getUserById($user_id);

        $result = ['headimg_url' => $user_info['headimg_url'], 'nickname' => $user_info['nickname'],
            'gender' => $user_info['gender'], 'mobile' => $user_info['mobile'], 'weixin_url' => $user_info['weixin_url'],
            'order_num' => $god_info['order_num'], 'comment_score' => $god_info['comment_score']];

        $game_level = $god_info['game_level_id'] ? $this->GameLevel_Model->getGameLevelName($god_info['game_level_id']) : '';

        return array_merge($result, ['game_level' => $game_level->game_level ?? null]);
    }

    //给大神推送模板消息
    private function sendNotice($god_openids, $order_fee, $order_info,$order_id)
    {
        $notice = $this->wechat->notice;
        $templateId = 'A4XHF6abZqWpDg6f0lLvpJceFQ7Fb0GwnWVpptNfdm4';
        $url = 'http://weixin.eachfight.com/#/mantio?order_id='.$order_id;  //大神端入口地址
        $data = array(
            "first" => "收到一笔新的陪练需求",
            "keyword1" => time(),
            "keyword2" => $order_fee . '元',
            "keyword3" => $order_info
        );

        foreach ($god_openids as $val) {
            $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($val)->send();
            log_message('info', '给大神推送模板消息opendid:' . $val . '--' . json_encode($result));
        }
    }
}
