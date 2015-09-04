<?php
class Publisher_Model_DbTable_Genres extends Zend_Db_Table
{
	protected $_name =TBL_GENRES;
	protected $_categoryname =TBL_CATEGORIES;
	
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
	
		
	public function getGenreList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' order by genre asc';
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getGenreInfo($genre_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$genre_id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}

}
?>