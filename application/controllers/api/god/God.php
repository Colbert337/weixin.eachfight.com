<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神申请接口
 * Class GodBattleRecord
 * @author	fengchen <fengchenorz@gmail.com>
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
        if(!empty($openid)){
            // 根据openid获取用户ID
            $userInfo = $this->user->scalarBy(['openid'=>$openid, 'is_god'=>2, 'status'=>1]);
            if(!empty($userInfo)){
                $godInfo = $this->god->scalarBy(['user_id'=>$userInfo['id'], 'status'=>1]);
                if(!empty($godInfo)){
                    $data = $godInfo+$userInfo;
                    $data['game_type'] = god_game_type()[$data['game_type']];
                    $data['game_level_id'] = gok_game_level()[$data['game_level_id']];
                    $data['can_zone'] = limit_can_zone()[$data['can_zone']];
                    $data['can_device'] = limit_can_device()[$data['can_device']];
                    $this->responseJson(200, '数据获取成功', $data);
                }else{
                    $this->responseJson(502, '该用户不是大神');
                }
            }else{
                $this->responseJson(502, '没有该用户信息');
            }
        }else{
            $this->responseJson(502, 'openid参数缺失');
        }
    }


}
