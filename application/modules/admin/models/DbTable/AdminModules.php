<?php
class Admin_Model_DbTable_AdminModules extends Zend_Db_Table
{
	 protected $_name = TBL_ADMIN_MODULES;
	 protected $_nameuser = TBL_ADMIN;
	 
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
	 
	 public function getModuleList()
	 {
	 
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','moduleid','modulename'));	
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