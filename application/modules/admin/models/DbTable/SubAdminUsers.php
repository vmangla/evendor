<?php
class Admin_Model_DbTable_SubAdminUsers extends Zend_Db_Table
{
	 protected $_name = TBL_ADMIN;
	 
	 
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
		$query->from(array('u'=>$this->_name),array('id','first_name','last_name','user_email','user_name','user_password','modules','is_active'));	
		$query->where('u.id!="1"');	
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