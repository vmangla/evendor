<?php
class Model_DbTable_Usersubscription extends Zend_Db_Table
{
	 protected $_credit_history = "pclive_credit_history";
	 protected $_name="pclive_user_sub_details";
	
	 
	 public function getGroupSubscriptionList($parentCompany_id=0,$condition)
	 {
		$list=array();
		$query="SELECT * FROM ".$this->_credit_history." WHERE userid<>0 AND userid=".$parentCompany_id."  and quantity>0 ".$condition." ORDER BY credit_id ASC";
	  
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getGroupQuantity($group_id,$company_id,$publication_id)
	{
		$query="SELECT count(*) as ctr FROM ".$this->_name." WHERE product_id='".$publication_id."' AND company_id='".$company_id."' AND group_id='".$group_id."'";
		$result=$this->getAdapter()->fetchAll($query);
		return $result;
	}
	public function getuserpublications($userid)
	{
		$query="SELECT distinct(publication_id) FROM ".$this->_name." WHERE subscription_type > 0 and number_of_issues>number_of_downloaded and user_id=".$userid." and cancel_subscription=0";
		$result=$this->getAdapter()->fetchAll($query);
		return $result;
	}
 
}
?>