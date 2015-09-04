<?php
class Admin_Model_DbTable_ProductImages extends Zend_Db_Table
{
	
	protected $_name =TBL_PRODUCTS_IMAGE;
	
	
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

	 public function getImageList($proid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where product_id="'.$proid.'" and status=1';
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	 public function getImageInfo($imgid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$imgid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}

}
?>