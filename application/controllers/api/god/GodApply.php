<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神申请接口
 * Class GodBattleRecord
 * @author	fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class GodApply extends MY_Controller
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('GodApply_Model', 'godApply');
    }

    /**
     * 查询大神申请
     */
    public function index_get()
    {
        $id = $this->uri->segment(3);
        if(!empty($id)){
            if (!is_numeric($id)){
                $this->responseJson(502, 'ID参数错误');
            }
            $data = $this->godApply->scalar($id);
        }else{
            $data = $this->godApply->fetchAll();
        }
        if(!empty($data)){
            $this->responseJson(200, '数据获取成功', $data);
        }else{
            $this->responseJson(502, '数据获取失败');
        }

    }

    /**
     * 数据提交
     */
    public function index_post(){

        $flag = false;
        $post = $this->input->post();
        $this->validPost($post);
        //获取当前登陆人信息
        $post['user_id'] = "1";
        $post['create_time'] = date("Y-m-d H:i:s",time());
        // 提交数据
        $data = $this->godApply->submitGodApply($post);
        if ($data) $flag = true;
        $flag ? $this->responseJson(200, '数据写入成功', $data) : $this->responseJson(200, '数据写入失败');
    }

    /**
     * 数据验证
     * @param $data 需验证的数据数组
     */
    private function validPost($data)
    {
        $require = [
            'game_type'=>'游戏类型',
            'game_level_id'=>'段位id',
            'level_url'=>'段位截图',
            'can_zone'=>'可接大区',
            'can_device'=>'可接设备系统',
            'card_url'=>'身份证正面照'
        ];
        foreach ($require as $key => $val) {
            // 非空验证
            if (!isset($data[$key]) || empty($data[$key])) {
                $this->responseJson(401, $val.' 不能为空');
                break;
            }
        }
    }
}
