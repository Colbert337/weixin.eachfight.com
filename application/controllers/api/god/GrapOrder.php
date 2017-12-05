<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神抢单接口
 * Class GodBattleRecord
 * @author	fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class GrapOrder extends MY_Controller
{
    const GRAP_KEY = "GrapOrder_";

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('God_Model', 'god');
        $this->load->model('User_Model', 'user');
        $this->load->model('Order_Model', 'order');
        $this->load->model('OrderLog_Model', 'orderlog');

        //获取用户uid
        $this->user_id = $this->getUserId();
    }

    /**
     * 大神抢单
     */
    public function index_post()
    {
        // 订单ID
        $orderId = $this->input->post('order_id');
        // 判断订单状态，是否可以抢单
        $orderInfo = $this->order->scalar($orderId);
        if(!empty($orderInfo)){
            if(!in_array($orderInfo['status'],[ORDER_CANCER_NO_ACCEPT,ORDER_GOD_GRAB])){
                $this->responseJson(502, '订单已过期');
            }
            if(!empty($this->user_id)){
                // 大神身份验证
                $godInfo = $this->god->scalarBy(['user_id' => $this->user_id, 'status' => 1]);
                if(!empty($godInfo)){
                    if($this->cache->redis->get(self::GRAP_KEY.$orderId)){
                        $this->responseJson(502, '手慢了，订单已经被抢');
                    }else{
                        $this->cache->redis->save( self::GRAP_KEY.$orderId, $this->user_id);
                        // 变更订单状态
                        $new_info = array(
                            'status'=>ORDER_GOD_GRAB,
                            'god_user_id'=>$this->user_id,
                            'grab_time'=>date('Y-m-d H:i:s'),
                        );
                        $insert_id = $this->order->insert($new_info, true);
                        if($insert_id){
                            $log_data = array(
                                'order_id'=>$orderId,
                                'begin_status'=>ORDER_CANCER_NO_ACCEPT,
                                'end_status'=>ORDER_GOD_GRAB,
                                'remark'=>"大神ID".$this->user_id."抢单",
                                'create_time'=>date('Y-m-d H:i:s'),
                            );
                            if($this->orderlog->insert($log_data, true)){
                                $return_data['play_status'] = ORDER_GOD_GRAB;
                                $return_data['user_info'] = $godInfo;
                                $return_data['god_info'] = $godInfo;
                                $return_data['order_info'] = $orderInfo;
                                $this->responseJson(200, '抢单成功', $return_data);
                            }else{
                                $this->responseJson(200, '订单日志记录失败');
                            }
                        }else{
                            $this->responseJson(502, '信息写入失败');
                        }
                    }
                }else{
                    $this->responseJson(502, '只有认证大神才能抢单');
                }
            }else{
                $this->responseJson(502, '抢单大神信息获取失败');
            }
        }else{
            $this->responseJson(502, '订单不存在');
        }
    }
}
