<?php
/**
 * Created by PhpStorm.
 * User: guochao
 * Date: 2018/1/7
 * Time: 下午1:44
 */

class UserCenter extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Order_Model');
        $this->load->model('UserCashJournal_Model');
        $this->load->model('OrderComment_Model');
        $this->load->model('GameLevel_Model');

        $this->load->helper('used_helper');

        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 用户个人中心首页
     */
    public function index()
    {
        //钱包
        $user_info = $this->User_Model->getUserById($this->user_id);
        $user_wallet = [];
        if ($user_info) {
            $user_wallet = ['total_balance' => $user_info['available_balance'] + $user_info['freeze_balance'],
                'available_balance' => $user_info['available_balance'], 'freeze_balance' => $user_info['freeze_balance'],
                'withdrawal_limit' => $user_info['withdrawal_limit']];
        }
        //帐户明细
        $user_cash_data = $this->UserCashJournal_Model->fetchAll(['user_id' => $this->user_id]);
        $user_cash = [];
        if ($user_cash_data) {
            foreach ($user_cash_data as $key => $val) {
                $user_cash[$key]['trade_type'] = trade_type()[$val['trade_type']] ?? '';
                if ($val['trade_type'] == 4) {
                    $user_cash[$key]['status'] = withdraw_status()[$val['withdraw_status']];
                } elseif ($val['trade_type'] == 1) {
                    $user_cash[$key]['status'] = recharge_status()[$val['recharge_status']];
                } else {
                    $user_cash[$key]['status'] = '已完成';
                }

                $user_cash[$key]['money'] = $val['money'];
                $user_cash[$key]['create_time'] = $val['create_time'];
                $user_cash[$key]['inorout'] = $val['inorout'];
            }
        }

        //订单列表
        $order_list_data = $this->Order_Model->fetchAll(['user_id' => $this->user_id]);
        $order_total = count($order_list_data);
        $order_list = [];
        if ($order_list_data) {
            foreach ($order_list_data as $key => $val) {
                $order_list[$key]['create_time'] = $val['create_time'];
                $order_list[$key]['status'] = $this->changeStatus($val['status']);
                $order_list[$key]['game_zone'] = game_zone()[$val['game_zone']];
                $game_level = $this->GameLevel_Model->getGameLevelName($val['game_level_id']);
                $order_list[$key]['game_level'] = $game_level->game_level;
                $order_list[$key]['game_mode'] = game_mode()[$val['game_mode']];
                $order_list[$key]['order_fee'] = $val['order_fee'];
                $order_list[$key]['game_num'] = $val['game_num'];
                $order_comment = $this->OrderComment_Model->scalarBy(['order_id' => $val['id']]);//评价星数
                $order_list[$key]['star_num'] = $order_comment['star_num'] ?? '';
                $order_list[$key]['id'] = $val['id'];
            }
        }

        $this->responseToJson(200, '获取成功', ['user_wallet' => $user_wallet, 'user_cash' => $user_cash,
            'order_total'=>$order_total,'order_list' => $order_list, 'weixin_url' => $user_info['weixin_url'] ?? '']);
    }

    //用户订单状态  已取消  进行中  申诉中  已完成
    private function changeStatus($status)
    {
        switch ($status) {
            case 2:
            case 4:
                return '已取消';
                break;

            case 8:
                return '申诉中';
                break;

            case 9:
                return '已完成';
                break;

            default:
                return '进行中';
        }
    }
}