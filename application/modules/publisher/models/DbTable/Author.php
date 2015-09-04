<?php
class Publisher_Model_DbTable_Author extends Zend_Db_Table_Abstract
{
	
	protected $_name =TBL_USERS;
	
	
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

	public function getAuthorList($parent_publisher_id=0)
	{
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','user_type','parent_id','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.parent_id<>0');
		
		if(!empty($parent_publisher_id))
		{
			$query->where('u.parent_id='.$parent_publisher_id);
		}
		
		$query->where('u.user_type="author"');
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getActiveAuthorList($parent_publisher_id=0)
	{
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','user_type','parent_id','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.parent_id<>0');
		
		if(!empty($parent_publisher_id))
		{
			$query->where('u.parent_id='.$parent_publisher_id);
		}	
		
		$query->where('u.user_type="author"');
		$query->where('u.profile_status="1"');
			
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getInfoByAuthorId($AuthorUserId)
	{
		$retVal=false;
		
		if(isset($AuthorUserId) && $AuthorUserId!="")
		{
			$result=$this->fetchRow('id='.$AuthorUserId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	}
	 
	 
	public function getInfoByAuthorEmail($emailid)
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
	 

}
?>