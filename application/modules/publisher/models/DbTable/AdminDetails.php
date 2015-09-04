<?php
class Publisher_Model_DbTable_AdminDetails extends Zend_Db_Table
{
	
	protected $_name =TBL_ADMIN;
	
	public function CurrentRow($id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
}
?>