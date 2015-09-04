<?php
class Publisher_Model_DbTable_Group extends Zend_Db_Table_Abstract
{
	
	protected $_name =TBL_GROUP_USERS;
	
	
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

	
	public function getInfoByGroupUserId($GroupUserId)
	{
		$retVal=false;
		
		if(isset($GroupUserId) && $GroupUserId!="")
		{
			$result=$this->fetchRow('id='.$GroupUserId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	}
	 
	 
	public function getInfoByGroupUserEmail($emailid)
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
	 
	 
	 
	public function getGroupList($publisher_author_id=0)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE publisher_author_id <>0 AND publisher_author_id=".$publisher_author_id." AND (user_type='Pmanager' OR user_type='Amanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','publisher_author_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Pmanager" OR u.user_type="Amanager"');
		$query->where('u.publisher_author_id='.$publisher_author_id);
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getPublicationManagersList($publisher_author_id=0)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE publisher_author_id <>0 AND publisher_author_id=".$publisher_author_id." AND (user_type='Pmanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','publisher_author_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Pmanager"');
		$query->where('u.publisher_author_id='.$publisher_author_id);
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getAccountManagersList($publisher_author_id=0)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE publisher_author_id <>0 AND publisher_author_id=".$publisher_author_id." AND (user_type='Amanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','publisher_author_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Amanager"');
		$query->where('u.publisher_author_id='.$publisher_author_id);
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
}
?>