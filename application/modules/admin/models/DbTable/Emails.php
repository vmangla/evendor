<?php
class Admin_Model_DbTable_Emails extends Zend_Db_Table
{
	 protected $_name=TBL_EMAILS;
	 
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
		$result=$this->fetchAll();
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
}
?>