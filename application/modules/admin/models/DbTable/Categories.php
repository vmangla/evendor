<?php
class Admin_Model_DbTable_Categories extends Zend_Db_Table
{
	 protected $_name=TBL_CATEGORIES;
	 
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
	 
	public function isNameExist($pWhere)
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
	 
	 public function getList()
	 {
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('cat'=>$this->_name),array('cat.*'));		
		//$query->joinLeft(array('cat1'=>$this->_name),'cat.id=cat1.parent_id',array('cat1.category_name as parent_category'));
		$query->order('cat.category_name ASC');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
	 public function getListFront()
	 {
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('cat'=>$this->_name),array('cat.*'));		
		//$query->joinLeft(array('cat1'=>$this->_name),'cat.id=cat1.parent_id',array('cat1.category_name as parent_category'));
		$query->where('cat.status= ?', '1');
		$query->order('cat.category_name ASC');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
	 public function getParentList()
	 {
		$list=array();
		$result=$this->fetchAll('parent_id=0');
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
}
?>