<?php
class Model_DbTable_CompanyProfiles extends Zend_Db_Table
{
	 protected $_name=TBL_COMPANIES_PROFILES;
	 
	 public function getInfoByCompanyId($pCompanyId)
	 {
		$retVal=false;
		
		if(isset($pCompanyId) && $pCompanyId!="")
		{
			$result=$this->fetchRow('company_id='.$pCompanyId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
}
?>