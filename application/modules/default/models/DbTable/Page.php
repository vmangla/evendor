<?php
class Model_DbTable_Page extends Zend_Db_Table
{
	protected $_name=TBL_PAGES;
	 
	public function isExist($pId)
	{
		$retVal=false;
		if(isset($pId) && $pId!=null)
		{
			//$result=$this->fetchRow('id='.$pId.' AND status=1');
			$result=$this->fetchRow('url_friendly_title="'.$pId.'" AND status=1');
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	}
	
	
	public function getInfoById($pId)
	 {
		$retVal=false;
		
		if(isset($pId) && $pId!="")
		{
			$result=$this->fetchRow('id='.$pId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
}
?>