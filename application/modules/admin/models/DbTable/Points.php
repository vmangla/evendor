<?php
class Admin_Model_DbTable_Points extends Zend_Db_Table
{
	 protected $_name="pclive_points";
	 
	 public function isExist($pId)
	 {
		$retVal=false;
		if(isset($pId) && $pId>0)
		{
			$result=$this->fetchRow('point_id='.$pId);
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	 }
}
?>