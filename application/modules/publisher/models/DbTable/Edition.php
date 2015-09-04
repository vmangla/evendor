<?php
class Publisher_Model_DbTable_Edition extends Zend_Db_Table
{
	protected $_name=TBL_EDITIONS;
	 
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
		$query->from(array('e'=>$this->_name),array('e.*'));
		if(!empty($publisher_id))
		{
			$query->where('e.publisher_author_id="'.$publisher_id.'"');
		}	
		
		$query->order('e.id ASC');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
	 
	 /*public function getEditionList()
	 {
		$list=array();
		$result=$this->fetchAll('parent_id=0');
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	 }
	 
	 public function getEditionIds($search_str)
	 {
		$list=array();
			
		$ids = "SELECT GROUP_CONCAT(id,'%^') as edition_ids FROM ".$this->_name." WHERE edition LIKE '%".$search_str."%'";
		
		$get_ids = $this->getAdapter()->fetchRow($ids);

		if($get_ids && $get_ids!=null)
		{
			$get_ids_string = rtrim($get_ids['edition_ids'],'%^');
			$id_array 	= explode('%^,',$get_ids_string);
		
			$list=implode(",",$id_array);
		}
		return $list;
	 }
	 */
}
?>