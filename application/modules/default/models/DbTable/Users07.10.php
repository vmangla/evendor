<?php
class Model_DbTable_Users extends Zend_Db_Table
{
	 protected $_name=TBL_USERS;
	 
	 public function getInfoByUserId($pUserId)
	 {
		$retVal=false;
		
		if(isset($pUserId) && $pUserId!="")
		{
			$result=$this->fetchRow('id='.$pUserId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	 
	 public function isExist($pWhere)
	{
		$retVal=false;
		if(isset($pWhere) && $pWhere!="")
		{
			$result=$this->fetchRow($pWhere);
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	}
}
?>