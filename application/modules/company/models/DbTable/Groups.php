<?php
class Company_Model_DbTable_Groups extends Zend_Db_Table_Abstract
{
	
	protected $_name =TBL_COMPANY_GROUPS;
	//protected $_membername =TBL_COMPANY_MEMBERS;
	
	
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
	
	public function getInfoByGroupId($GroupId)
	{
		$retVal=false;
		
		if(isset($GroupId) && $GroupId!="")
		{
			$result=$this->fetchRow('id='.$GroupId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	}
	 
	 
	public function getGroupList($company_id=0)
	{
		$list=array();

		$query=$this->select();
		$query->from(array('g'=>$this->_name),array('id','company_id','group_name','status','created_date','updated_date'));	
		$query->where('g.company_id='.$company_id);
		$query->where('g.status=1');
		$query->order('g.id ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
}
?>