<?php
class Model_DbTable_Companies extends Zend_Db_Table
{
	protected $_name =TBL_COMPANIES;
	
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
}
?>