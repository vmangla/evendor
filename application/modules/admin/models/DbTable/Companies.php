<?php
class Admin_Model_DbTable_Companies extends Zend_Db_Table
{
	 protected $_name=TBL_COMPANIES;
	 
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
		$query->setIntegrityCheck(false);
		$query->from(array('c'=>$this->_name),array('id','user_email','status'));		
		$query->join(array('cp'=>TBL_COMPANIES_PROFILES),'c.id=cp.company_id',array('business_name','contact_name','added_date'));
		$query->order('c.id ASC');
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
}
?>