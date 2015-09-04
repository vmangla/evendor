<?php
class Model_DbTable_Checkout extends Zend_Db_Table
{
	 protected $_name="pclive_temp_card";
	 
	/* public function getInfoByUserId($pUserId)
	 {
		$retVal=false;
		
		if(isset($pUserId) && $pUserId!="")
		{
			$result=$this->fetchRow('id='.$pUserId);
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	 
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
	 public function getRattingProduct($product_id)
	 {
	 	//echo $product_id;
	 	$rat='';
		$select='SELECT  avg(rating) as rate,`productid` FROM pclive_review where productid="'.$product_id.'" group by productid';
		//$retVal=$this->fetchRow($select);
		$retVal=$this->getAdapter()->fetchRow($select); 
		if($retVal['rate']!='')
		{
			$rat = ceil($retVal['rate']);
		}
		return $rat;
	 }
	 public function insertRatting($userId,$bookId,$rating,$user_type,$comment)
	 {
	 	$db = Zend_Registry::get('db');
	 	$select="select id from pclive_review where userid='".$userId."' and productid='".$bookId."'";
		$retVal=$this->getAdapter()->fetchRow($select); 
		if(count($retVal)>0)
		{
			$sql="update pclive_review set rating='".$rating."', comments='".$comment."',add_time=now() where  userid='".$userId."' and productid='".$bookId."' ";
			//$this->getAdapter()->update($sql);
		}
		else
		{
			$sql="insert into pclive_review set userid='".$userId."',rating='".$rating."',comments='".$comment."',user_type='".$user_type."',productid='".$bookId."',add_time=now()";
			//$this->getAdapter()->insert($sql);
		}
		$db->query($sql);
	 }
	*/
}
?>