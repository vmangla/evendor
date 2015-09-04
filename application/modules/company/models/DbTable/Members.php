<?php
class Company_Model_DbTable_Members extends Zend_Db_Table_Abstract
{
	
	protected $_name =TBL_COMPANY_MEMBERS;
	
	
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
	
	/*public function isExist($pId)
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
	*/

	
	public function getInfoByMemberId($MemberUserId)
	{
		$retVal=false;
		
		if(isset($MemberUserId) && $MemberUserId!="")
		{
			$result=$this->fetchRow('id='.$MemberUserId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	}
	 
	 
	public function getInfoByMemberEmail($emailid)
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
	 
	 
	 
	public function getMemberList($company_id=0)
	{
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','company_id','first_name','last_name','emailid','phone','added_date','status'));	
		$query->where('u.company_id='.$company_id);
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