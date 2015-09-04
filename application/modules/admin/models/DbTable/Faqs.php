<?php
class Admin_Model_DbTable_Faqs extends Zend_Db_Table
{
	 protected $_name=TBL_FAQS;
	 
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