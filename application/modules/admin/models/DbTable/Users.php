<?php
class Admin_Model_DbTable_Users extends Zend_Db_Table
{
	 
	 protected $_name=TBL_COMPANIES;
	 protected $_countryname=TBL_COUNTRY;
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
	 
	 
	 public function getList()
	 {
	 
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('u.*'));	
		$query->where('u.account_type=1 OR u.account_type=2');	
		$query->order('u.id ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	 }
	 
	public function getCountryInfo($countid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_countryname.' where id="'.$countid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
}
?>