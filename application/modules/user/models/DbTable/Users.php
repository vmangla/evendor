<?php
class User_Model_DbTable_Users extends Zend_Db_Table
{
	protected $_name =TBL_USERS;
	protected $_countryname =TBL_COUNTRY;
	
		
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
	
	 public function getInfoByUserId($pPublisherId)
	 {
		$retVal=false;
		
		if(isset($pPublisherId) && $pPublisherId!="")
		{
			$result=$this->fetchRow('id='.$pPublisherId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	 
	 
	 public function getInfoByUserEmail($emailid)
	 {
		$retVal=false;
		
		if(isset($emailid) && $emailid!="")
		{
			$result=$this->fetchRow("emailid='".$emailid."'");
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	 
	 
	 public function getInfoByLoginEmail($emailid)
	 {
		$retVal=array();  
		
		if(isset($emailid) && $emailid!="")
		{
			$result=$this->fetchAll("emailid='".$emailid."'");
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	 
	 
	 
	public function getCountryList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_countryname.' order by country asc';
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function activateUser($varificationcode)
	{   
        $retVal=array();  
		
		$datavalue=$this->fetchRow("activation_code='".$varificationcode."' and activation_status='1'"); 
		
		if(isset($datavalue) && $datavalue!="")
		{
			$update="update ".$this->_name." set profile_status=1, activation_status=0 where activation_code='".$varificationcode."'";
			$retVal=$this->getAdapter()->query($update); 
			
			if(isset($retVal) && $retVal!="")
		    {
			   return 1;
			}
			else
			{
			   return 0;
			}
			
		}
		else
		{
		    return 0;
		}
	}
	
	public function getCountryInfo($country_id=0)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_countryname.' WHERE id="'.$country_id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	 
	
 
	
}
?>