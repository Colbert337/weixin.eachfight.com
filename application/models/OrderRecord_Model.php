<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * OrderRecord Model Class
 *
 * 战绩操作Model
 * @category	Models
 * @author		fengchen <fengchenorz@gmail.com>
 */
class OrderRecord_Model extends MY_Model {

	const TBL = 'order_record';
    const TBL_ORDER = 'order';
	
	/**
     * 标识战绩的唯一键：{"id"}
     * 
     * @access private
     * @var array
     */
	private $_unique_key = array('id');

	/**
     * 构造函数
     *
     * @access public
     * @return void
     */
    function __construct()
    {
        parent::__construct();

    }
	
	/**
    * 提交战绩
    * 
    * @access public
	* @param int - $data 战绩信息
    * @return boolean - success/failure
    */	
	public function submitOrderRecord($data)
	{
	    $flag = 0;

		$data['create_time'] = date("Y-m-d H:i:s",time());

		// 战绩表数据新增
        $this->db->trans_start();

		$this->insert($data);

		// 变更订单表状态
        $order = array('status' => ORDER_GOD_SUB_ORDER, 'sumbit_time' => $data['create_time']);

        $where = $this->db->where('id', $data['order_id']);

        $this->db->update(self::TBL_ORDER, $order, $where);

        if ($this->db->trans_status() === FALSE){

            $this->db->trans_rollback();
        }else{
            $flag = 1;

            $this->db->trans_commit();
        }
		return ($flag > 0) ? TRUE : FALSE;
	}

	/**
    * 检查订单是否已经提交战绩
    * @access public
	* @param int - $order_id 订单ID
    * @return boolean - success/failure
    */	
	public function checkExist($order_id)
	{
		if(!empty($order_id))
		{
			$this->db->select('id')->from(self::TBL)->where("order_id", $order_id);
			
			$query = $this->db->get();

			$num = $query->num_rows();
			
			$query->free_result();
			
			return ($num > 0) ? TRUE : FALSE;
		}
		
		return FALSE;		
	}

    /**
     * 检查订单是否存在
     * @access public
     * @param int - $order_id 订单ID
     * @return boolean - success/failure
     */
    public function checkOrder($order_id)
    {
        if(!empty($order_id))
        {
            $this->db->select('id')->from(self::TBL_ORDER)->where("id", $order_id);

            $query = $this->db->get();

            $num = $query->num_rows();

            $query->free_result();

            return ($num > 0) ? TRUE : FALSE;
        }

        return FALSE;
    }


}