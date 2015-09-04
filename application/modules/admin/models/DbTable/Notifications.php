<?php
class Admin_Model_DbTable_Notifications extends Zend_Db_Table
{
	 protected $_name="pclive_notifications";
	 
	 public function isExist($pId)
	 {
		$retVal=false;
		if(isset($pId) && $pId>0)
		{
			$result=$this->fetchRow('nid='.$pId);
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	 }
	 
	  
 
}
?>