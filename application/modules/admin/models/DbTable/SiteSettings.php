<?php
class Admin_Model_DbTable_SiteSettings extends Zend_Db_Table
{
	 protected $_name=TBL_SITE_SETTINGS;
	 
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
	 
	 public function getList($whereconstants="")
	 {
		$list=array();
		
		if(!empty($whereconstants))
		{
			$result=$this->fetchAll($whereconstants);
		}
		else
		{
			$result=$this->fetchAll();
		}
		
		
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
 
}
?>