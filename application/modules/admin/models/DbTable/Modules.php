<?php
class Admin_Model_DbTable_Modules extends Zend_Db_Table
{
	protected $_name=TBL_ADMIN_MODULES;
	 
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
	  
}
?>