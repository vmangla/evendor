<?php
class Admin_Model_DbTable_AdminUsers extends Zend_Db_Table
{
	 protected $_name = TBL_ADMIN;
	 
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
	
	public function getInfoByAdminEmail($emailid)
	{
		$retVal=false;
		
		if(isset($emailid) && $emailid!="")
		{
			$result=$this->fetchRow("user_email='$emailid'");
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	}
}
?>