<?php
class Admin_Model_DbTable_Reviews extends Zend_Db_Table
{
	 
	 protected $_name=TBL_REVIEW;
	 
	 public function isExist($pId)
	 {
		$retVal=false;
		if(isset($pId) && $pId>0)
		{
			$result=$this->fetchRow('id='.$pId);
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	 }
	 
	 
	 public function getList()
	 {
	 
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','userid','name','email','rating','comments','status','add_time','productid','product_type','product_name'));	
		$query->where('u.productid != ?','');	
		$query->order('u.id ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	 }
}
?>