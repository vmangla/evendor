<?php
class Admin_Model_DbTable_Publications extends Zend_Db_Table
{
	protected $_name=TBL_PRODUCTS;
	protected $_publishername =TBL_USERS;
	protected $_categoryname =TBL_CATEGORIES;
	protected $_productimage =TBL_PRODUCTS_IMAGE;
	protected $_productprice =TBL_PRODUCTS_PRICE;
    protected $_productlanguage =TBL_LANGUAGES;
	protected $_countryname =TBL_COUNTRY;
	 
	public function isExist($pId)
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
	public function getList()
	{
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('prod'=>$this->_name),array('prod.*'));		
		//$query->joinLeft(array('cat1'=>$this->_name),'prod.id=cat1.parent_id',array('cat1.category_name as parent_category'));
		$query->order('prod.id desc');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getListWhere($whrcls)
	{
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array("prod"=>$this->_name),array("prod.*"));	
		$query->where($whrcls);	
		//$query->joinLeft(array('cat1'=>$this->_name),'prod.id=cat1.parent_id',array('cat1.category_name as parent_category'));
		$query->order("prod.id desc");
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getPublisherInfo($publisher_id=0)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_publishername.' WHERE id="'.$publisher_id.'"';
		//exit;
		$result=$this->getAdapter()->fetchRow($select); 
		
		if($result && $result!=null)
		{
			$retVal=$result;
		}
		return $retVal;
	}
	
	public function getAuthorInfo($author_id=0)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_publishername.' WHERE id="'.$author_id.'" and user_type="author"';
		//exit;
		$result=$this->getAdapter()->fetchRow($select); 
		
		if($result && $result!=null)
		{
			$retVal=$result;
		}
		return $retVal;
	}
	
	
	
	public function getCategoryList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_categoryname;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	public function getCategoryInfo($catid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_categoryname.' where id="'.$catid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	 
	 public function getUserInfoList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_authorname;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getAuthorList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_authorname.' where user_type="author"';
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	 public function getCountryList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_countryname;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getCountryName($countid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_countryname.' where id="'.$countid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	 public function getLanguageList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_productlanguage.' where status=1'; 
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getLanguageName($langid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_productlanguage.' where id="'.$langid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getInfoByPublisherId($pPublisherId)
	 {
		$retVal=false;
		
		if(isset($pPublisherId) && $pPublisherId!="")
		{
			$result=$this->fetchRow('id="'.$pPublisherId.'"');
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	 
	 
	 public function getInfoByPublisherEmail($emailid)
	 {
		$retVal=false;
		
		if(isset($emailid) && $emailid!="")
		{
			$result=$this->fetchRow("emailid='".$emailid."'");
			if($result)
			{
				$retVal=$result;
			}
		}
		return $retVal;
	 }
	
}
?>