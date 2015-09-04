<?php
class Publisher_Model_DbTable_BookPrices extends Zend_Db_Table
{
	
	protected $_name =TBL_PRODUCTS_PRICE;
	
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
	
    public function getPriceList($proid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where product_id="'.$proid.'"';
		
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getPriceByStoreId($proid,$storeId)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where product_id="'.$proid.'" AND country_id="'.$storeId.'" LIMIT 1';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	
	public function CurrentRow($id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
}
?>