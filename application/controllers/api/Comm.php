<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class Comm extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Sms');
        $this->load->helper('used_helper');

        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->model('UserCashJournal_Model');
    }

    /**
     * 发送短信,将验证码写入redis,10分钟有效
     * @author  guochao
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
     * @author  guochao
     */
    public function bindingMobile()
    {
        //获取用户uid
        $user_id = $this->getUserId();
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


    /**
     * 用户账户微信充值
     * @author  guochao
     */
    public function recharge()
    {
        //获取用户uid
        $user_id = $this->getUserId();
        $money = $this->input->post('money');
        if (!$money || !is_numeric($money) || strstr($money, '.'))
            $this->responseToJson(502, '金额错误');

        $money = 0.01;

        $user_data = $this->User_Model->getUserById($user_id);
        if (!isset($user_data['openid']) || !$user_data['openid'])
            $this->responseToJson(502, '该用户还未注册');

        $openid = $user_data['openid'];
        $original_available_balance = $user_data['available_balance'];
        $out_trade_no = uuid();
        $attributes = [
            'trade_type' => 'JSAPI',
            'body' => '猪游纪账户充值',
            'detail' => '用户id:' . $user_id . '|账户充值',
            'out_trade_no' => $out_trade_no,
            'total_fee' => intval($money * 100), // 单位：分
            'notify_url' => base_url() . 'api/comm/payNotify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid' => $openid // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
        ];

        $this->db->trans_begin();
        try {
            $this->UserCashJournal_Model->insert(['out_trade_no' => $out_trade_no, 'user_id' => $user_id,
                'trade_type' => 1, 'money' => $money, 'inorout' => 1, 'pay_type' => 2, 'recharge_status' => 1,
                'original_available_balance' => $original_available_balance, 'create_time' => date('Y-m-d H:i:s')]);

            $order = new \EasyWeChat\Payment\Order($attributes);
            $payment = $this->wechat->payment;
            $result = $payment->prepare($order);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                $this->db->trans_commit();
                $prepayId = $result->prepay_id;
                $data = $payment->configForJSSDKPayment($prepayId);
                $this->responseToJson(200, '创建成功', ['weixin_pay' => $data, 'out_trade_no' => $out_trade_no]);
            } else {
                throw new Exception($result->return_msg);
            }
        } catch (\Exception $exception) {
            $this->db->trans_rollback();
            log_message('error', '创建预充值订单接口异常' . $exception->getMessage());
            $this->responseToJson(502, $exception->getMessage());
        }
    }

    //微信异步通知
    public function payNotify()
    {
        $response = $this->wechat->payment->handleNotify(function ($notify, $successful) {
            log_message('info', '微信异步通知接口返回数据：'.json_encode($notify));
            $out_trade_no = $notify->out_trade_no;
            $userCashJournal = $this->UserCashJournal_Model->scalarBy(['out_trade_no' => $out_trade_no]);
            if (!$userCashJournal) return 'recharge order is not exist';

            //已处理
            if ($userCashJournal['recharge_status'] != 1) return true;
            //接口返回订单金额
            if ((100 * $userCashJournal['money']) != $notify->total_fee) {
                log_message('error', '微信异步通知接口返回订单金额不对');
                return false;
            }

            $user_data = $this->User_Model->getUserById($userCashJournal['user_id']);
            //用户是否支付
            if ($successful) {  //支付成功
                $this->db->trans_begin();
                $current_available_balance = $user_data['original_available_balance'] + $userCashJournal['money'];
                //用户账户加钱
                $res_1 = $this->User_Model->update(['id' => $userCashJournal['user_id'],
                    ['original_available_balance' => $current_available_balance, 'update_time' => date('Y-m-d H:i:s')]]);
                //更新用户资金流水状态
                $res_2 = $this->UserCashJournal_Model->update(['out_trade_no' => $out_trade_no],
                    ['recharge_status' => 2, 'transaction_id' => $notify->transaction_id,
                        'current_available_balance' => $current_available_balance, 'update_time' => date('Y-m-d H:i:s')]);

                if ($res_1 && $res_2) {
                    $this->db->trans_commit();
                    return true;
                } else {
                    log_message('error', '充值更新数据异常');
                    $this->db->trans_rollback();
                }
            } else {
                $this->UserCashJournal_Model->update(['out_trade_no' => $out_trade_no],
                    ['recharge_status' => 3, 'update_time' => date('Y-m-d H:i:s')]);
                log_message('error', '微信异步用户支付失败');
                return 'user not pay success';
            }
        });

        return $response;
    }

}
