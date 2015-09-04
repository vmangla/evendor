<?php
class Model_DbTable_Faqs extends Zend_Db_Table
{
	protected $_name=TBL_FAQS;
	
	public function getFaqList()
	{
		$list=array();

		$query=$this->select();
		$query->from(array('f'=>$this->_name),array('f.*'));	
		$query->where('f.status=1');
		
		$query->order('f.sort_order ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
}
?>