<?php
class Admin_Model_DbTable_ApiKey extends Zend_Db_Table
{
	 
	 protected $_name=TBL_API_KEYS;
	 
	 public function isExist($apikey)
	 {
		$retVal=false;
		if(isset($pId) && $pId>0)
		{
			$result=$this->fetchRow("apikey='$apikey'");
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
		$query->from(array('u'=>$this->_name),array('id','first_name','last_name','emailid','phone','add_time','profile_status'));	
		$query->where('u.user_type="model"');	
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