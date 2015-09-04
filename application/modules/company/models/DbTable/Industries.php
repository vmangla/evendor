<?php
class Company_Model_DbTable_Industries extends Zend_Db_Table
{
	protected $_name =TBL_INDUSTRIES;
	
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
}
?>