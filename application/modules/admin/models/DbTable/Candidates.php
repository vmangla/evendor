<?php
class Admin_Model_DbTable_Candidates extends Zend_Db_Table
{
	 protected $_name=TBL_CANDIDATES;
	 
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
		$query->from(array('u'=>$this->_name),array('id','user_email','status'));		
		$query->join(array('up'=>TBL_CANDIDATES_PROFILES),'u.id=up.user_id',array('user_name','full_name','gender','added_date'));
		$query->order('u.id ASC');
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
}
?>