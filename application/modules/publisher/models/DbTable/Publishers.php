<?php
class Publisher_Model_DbTable_Publishers extends Zend_Db_Table
{
	protected $_name =TBL_USERS;
	protected $_countryname =TBL_COUNTRY;
	protected $_assign_publications ="pclive_publication_assign";
		
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
	
	 public function getInfoByPublisherId($pPublisherId)
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
	 
	 
	 public function getInfoByPublisherEmail($emailid)
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
	
	public function getGroupList($parent_id=0)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE parent_id <>0 AND parent_id=".$parent_id." AND (user_type='Pmanager' OR user_type='Amanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','parent_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Pmanager" OR u.user_type="Amanager"');
		
		if(!empty($parent_id))
		{
			$query->where('u.parent_id='.$parent_id);
		}
		
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getGroupListSearch($parent_id=0,$searchKey)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE parent_id <>0 AND parent_id=".$parent_id." AND (user_type='Pmanager' OR user_type='Amanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','parent_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Pmanager" OR u.user_type="Amanager"');
		
		if(!empty($parent_id))
		{
			$query->where('u.parent_id='.$parent_id);
		}
		if($searchKey!='')
		{
			$query->where('u.username LIKE"%'.$searchKey.'%" or u.first_name LIKE"%'.$searchKey.'%" or u.last_name LIKE "%'.$searchKey.'%"');
		}
		
		$query->order('u.id desc');
		//echo $query;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getPublicationManagersList($parent_id=0)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE parent_id <>0 AND parent_id=".$parent_id." AND (user_type='Pmanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','parent_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Pmanager"');
		
		if(!empty($parent_id))
		{
			$query->where('u.parent_id='.$parent_id);
		}
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getAccountManagersList($parent_id=0)
	{
		$list=array();

		/*$query="SELECT * FROM ".$this->_name." WHERE parent_id <>0 AND parent_id=".$parent_id." AND (user_type='Amanager') ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		*/
		
		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('id','parent_id','user_type','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('u.user_type="Amanager"');
		
		if(!empty($parent_id))
		{
			$query->where('u.parent_id='.$parent_id);
		}
		
		$query->order('u.id desc');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	##################### assign publication #########################
	public function insertAssignUserPublication($data)
	{
		$sql="insert into ".$this->_assign_publications." set ";
		foreach($data as $field=>$value){
		if(is_string($value)){
		$value=trim(addslashes($value));
		}
			$data_arr[]=$field."="."'".$value."'";
		}
		$insertData=implode(", ",$data_arr);
		$sql .=$insertData;
		$retVal=$this->getAdapter()->query($sql);
		return $retVal;
	}
	public function getAssignPublicationsList($group_user_id)
	{
		$retVal=array();  
		$select='SELECT * from '.$this->_assign_publications.' WHERE group_user_id="'.$group_user_id.'"';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	public function deleteAssignPublication($publisher_id,$user_id)
	{
		//$retVal=array();  
		$select='DELETE from '.$this->_assign_publications.' WHERE group_user_id="'.$user_id.'" and publisher_id="'.$publisher_id.'"';
		$retVal=$this->getAdapter()->query($select); 
		//return $retVal;
	}
}
?>