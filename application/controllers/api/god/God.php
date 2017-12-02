<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神申请接口
 * Class GodBattleRecord
 * @author    fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class God extends MY_Controller
{

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('God_Model', 'god');
        $this->load->model('User_Model', 'user');
    }

    /**
     * 查询大神所有信息
     */
    public function index_get()
    {

        $openid = $this->input->get_request_header('openid', TRUE);
        if (!empty($openid)) {
            // 根据openid获取用户ID
            $userInfo = $this->user->scalarBy(['openid' => $openid, 'is_god' => 2, 'status' => 1]);
            if (!empty($userInfo)) {
                $godInfo = $this->god->scalarBy(['user_id' => $userInfo['id'], 'status' => 1]);
                if (!empty($godInfo)) {
                    $data = $godInfo + $userInfo;
                    $data['game_type'] = game_type()[$data['game_type']];
                    $data['game_level_id'] = $data['game_level_id'];
                    $data['can_zone'] = can_zone()[$data['can_zone']];
                    $data['can_device'] = can_device()[$data['can_device']];
                    $this->responseJson(200, '数据获取成功', $data);
                } else {
                    $this->responseJson(502, '该用户不是大神');
                }
            } else {
                $this->responseJson(502, '没有该用户信息');
            }
        } else {
            $this->responseJson(502, 'openid参数缺失');
        }
    }

    /**
     * 根据大神用户id及订单id获取 获取大神订单状态
     * @param $user_id
     * @param $order_id
     * @return bool|int
     */
    public function getGodPlayStatus($user_id, $order_id)
    {
        $order = $this->Order_Model->scalar($order_id);
        $status = $order['status'];
        switch ($status) {
            case 1:
                $play_status = 1;  //等待抢单
                break;

            case 2:
            case 4:
                $play_status = 2;  //订单已取消
                break;

            case 3:
                if ($order['god_user_id'] == $user_id) {
                    $play_status = 3;  //抢单成功待用户准备
                } else {
                    $play_status = 4;  //抢单失败
                }
                break;

            case 5:
                $play_status = 5;  //待完成游戏
                break;

            case 6:
                $play_status = 6;  //待提交战绩
                break;

            case 7:
                $play_status = 7;  //申诉中
                break;

            case 8:
                $play_status = 8;  //订单完成
                break;
        }

        return $play_status;
    }


}
