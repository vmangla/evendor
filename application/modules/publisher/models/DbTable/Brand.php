<?php
class Publisher_Model_DbTable_Brand extends Zend_Db_Table
{
	protected $_name=TBL_BRANDS;
	 
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
	
	/*public function isExist($pId)
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
	*/
	
	 public function getList($publisher_id)
	 {
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('brand'=>$this->_name),array('brand.*'));
		$query->where('brand.publisher_author_id="'.$publisher_id.'"');
		//$query->joinLeft(array('cat1'=>$this->_name),'brand.id=cat1.parent_id',array('cat1.category_name as parent_category'));
		$query->order('brand.id desc');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
	 public function getParentList()
	 {
		$list=array();
		$result=$this->fetchAll('parent_id=0');
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
	 
	 public function getBrandIds($search_str)
	 {
		$list=array();
			
		$ids = "SELECT GROUP_CONCAT(id,'%^') as brand_ids FROM ".$this->_name." WHERE brand LIKE '%".$search_str."%'";
		
		$get_ids = $this->getAdapter()->fetchRow($ids);

		if($get_ids && $get_ids!=null)
		{
			$get_ids_string = rtrim($get_ids['brand_ids'],'%^');
			$id_array 	= explode('%^,',$get_ids_string);
		
			$list=implode(",",$id_array);
		}
		return $list;
	 }
}
?>