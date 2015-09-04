<?php
class Publisher_Model_DbTable_Subscriptions extends Zend_Db_Table
{
	protected $_name ='pclive_subscriptions';
	
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
	public function getSubscriptionList($proid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where product_id="'.$proid.'"';
		
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	 
	
}
?>