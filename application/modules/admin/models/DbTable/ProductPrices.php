<?php
class Admin_Model_DbTable_ProductPrices extends Zend_Db_Table
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
	
}
?>