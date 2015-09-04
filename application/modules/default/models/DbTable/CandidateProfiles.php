<?php
class Model_DbTable_CandidateProfiles extends Zend_Db_Table
{
	 protected $_name=TBL_CANDIDATES_PROFILES;
	 
	 public function getInfoByUserId($pUserId)
	 {
		$retVal=false;
		
		if(isset($pUserId) && $pUserId!="")
		{
			$result=$this->fetchRow('user_id='.$pUserId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
}
?>