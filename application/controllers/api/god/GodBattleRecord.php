<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 大神战绩接口
 * Class GodBattleRecord
 * @author	fengchen <fengchenorz@gmail.com>
 * @time    21017/10/25
 */
class GodBattleRecord extends MY_Controller
{

    private $wechat = 'wechat_user';

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('OrderRecord_Model', 'orderRecord');
    }

    /**
     * 查询大神战绩
     */
    public function index_get()
    {
        $id = $this->uri->segment(3);
        if(!empty($id)){
            if (!is_numeric($id)){
                $this->response(['status'=>false, 'msg'=>'战绩ID参数错误'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
            $data = $this->orderRecord->getOrderRecordById($id);
        }else{
            $data = $this->orderRecord->getOrderRecords();
        }
        if(!empty($data)){
            $this->response(['status'=>true, 'msg'=>'数据查询成功', 'data'=>$data],MY_Controller::HTTP_OK);
        }else{
            $this->response(['status'=>false, 'msg'=>'数据查询失败'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * 大神提交战绩
     */
    public function index_post(){
        $flag = false;
        $post = $this->input->post();
        $this->validPost($post);
        // 是否已经提交战绩
        if($this->orderRecord->checkExist($post['order_id'])){
            $this->response(['status'=>false, 'msg'=>'该订单已经提交过战绩了'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
        // 是否订单是否存在
        if(!$this->orderRecord->checkOrder($post['order_id'])){
            $this->response(['status'=>false, 'msg'=>'该订单不存在'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
        $dataField = ['order_id'=>'订单ID', 'victory_num'=>'胜利局数'];
        $data = [];
        foreach ($dataField as $key=>$val) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // 提交数据
        $bool = $this->orderRecord->submitOrderRecord($data);
        if ($bool) $flag = true;
        $flag ? $this->response(['status'=>true, 'msg'=>'数据写入成功', 'data'=>$data],MY_Controller::HTTP_CREATED):
            $this->response(['status'=>false, 'msg'=>'数据写入失败'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * 大神提交数据验证
     * @param $data
     */
    private function validPost($data)
    {
        $require = ['order_id'=>'订单ID', 'victory_num'=>'胜利局数'];
        foreach ($require as $key => $val) {
            // 非空验证
            if (!isset($data[$key]) || empty($data[$key])) {
                $this->response(['status'=>false, 'message'=>$val.' 不能为空'], MY_Controller::HTTP_UNPROCESSABLE_ENTITY);
                break;
            }
            // 数据格式
            if($data['order_id'] < 0 || !is_numeric($data['order_id'])){
                $this->response(['status'=>false, 'msg'=>'订单数据类型错误'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
                break;
            }
            if($data['victory_num'] > 3 || $data['victory_num'] < 0 || !is_numeric($data['victory_num'])){
                $this->response(['status'=>false, 'msg'=>'胜利局数数据类型错误'],MY_Controller::HTTP_INTERNAL_SERVER_ERROR);
                break;
            }
        }
    }
}
