<?php
class User_Model_DbTable_Chistory extends Zend_Db_Table
{
	protected $_name ="pclive_credit_history";
	
	public function addnewField()
	{
		echo $sql = "alter table pclive_credit_history ADD client_ip varchar (55) after converted_price";

		$retVal=$this->getAdapter()->query($sql); 
		return $retVal;
	}
	 
}
?>