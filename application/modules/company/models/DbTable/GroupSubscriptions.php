<?php
class Company_Model_DbTable_GroupSubscriptions extends Zend_Db_Table
{
	protected $_name =TBL_GROUP_SUBSCRIPTIONS;
	protected $_subname =TBL_COMPANY_SUBSCRIPTIONS;
	protected $_publishername =TBL_USERS;
	protected $_customername =TBL_COMPANIES;
	
	protected $_productname =TBL_PRODUCTS;
	protected $_productimage =TBL_PRODUCTS_IMAGE;
	protected $_brandname =TBL_BRANDS;
	protected $_credit_history = "pclive_credit_history";
	
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
	
	public function getGroupSubscriptionList($parentCompany_id=0)
	{
		$list=array();
		$query="SELECT * FROM ".$this->_name." WHERE company_id<>0 AND company_id=".$parentCompany_id." group by publication_id  ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getSubscriptionList($group_id=0)
	{
		$list=array();
		$query="SELECT * FROM ".$this->_name." WHERE group_id<>0 AND group_id=".$group_id." ORDER BY id ASC";
		
		/*$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array("gs"=>$this->_name),array("gs.*"));
		$query->join(array("cs"=>$this->_subname), "gs.publication_id = cs.product_id", array("cs.*")); 
		$query->where('gs.group_id= ?', $group_id);
		*/
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getSubscriptionMembersList($parentCompany_id=0)
	{
		$list=array();

		$query="SELECT * FROM ".$this->_name." WHERE company_id<>0 AND parent_id='".$parentCompany_id."' ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getProductInfo($prod_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_productname.' where id="'.$prod_id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getImageInfo($prod_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_productimage.' where product_id="'.$prod_id.'" LIMIT 1';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getReportEBookIssues($parentCatTypeID,$productID)
	{ 
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array("prod"=>$this->_productname),array("prod.*"));
		$query->join(array("subscribe"=>$this->_name), "prod.id = subscribe.product_id", array("subscribe.*")); 
		if(!empty($parentCatTypeID))
		{
			$query->where('prod.cat_id= ?', $parentCatTypeID);
		}
		$query->where('subscribe.parent_id= ?', '0');
		
		if(!empty($productID))
		{
			$query->where('subscribe.product_id= ?', $productID);
		}
		$retVal=$this->getAdapter()->FetchAll($query); 
		
		return $retVal;
	}
	
	public function getProdListByParentPublisherId($parentPublisherId)
	{
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array("prod"=>$this->_productname),array("prod.*"));
		$query->join(array("subscribe"=>$this->_name), "prod.id = subscribe.product_id", array("subscribe.*")); 
		if(!empty($parentPublisherId))
		{
			$query->where('prod.publisher_id= ?', $parentPublisherId);
		}
		$query->where('subscribe.parent_id= ?', '0');
		$retVal=$this->getAdapter()->FetchAll($query); 
		
		return $retVal;
	}
	
	public function getProdListByParentCatId($parentCatId,$parentCatName,$product_id)
	{
		$list=array();
		$dropdown='';
		$product_id=$product_id;

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('u'=>$this->_productname));
		$query->where('u.cat_id="'.$parentCatId.'"');
		if(strtolower($parentCatName)!=strtolower('eBook') && strtolower($parentCatName)!=strtolower('eBooks'))
		{
			$query->where('u.parent_brand_id>0');
		}
		
		$query->order('u.id ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		$dropdown.='<select name="product_id" id="product_id" class="req"  message="Please select a product">
				<option value="">Select Product</option>';
				for($ii=0;$ii<count($list);$ii++)
				{
					if($product_id==$list[$ii]['id'])
					{
						$selected="selected";
					}
					else
					{
						$selected="";
					}
				$dropdown.='<option value="'.$list[$ii]['id'].'" '.$selected.'>'.$list[$ii]['title'].'</option>';
				}
				
		$dropdown.='</select>';
		
		return $dropdown;
		//return $list;
	}
	
	public function getPublisherList()
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array('p'=>$this->_publishername),array('id','user_type','parent_id','first_name','last_name','emailid','username','phone','add_time','profile_status'));	
		$query->where('p.parent_id=0');
		$query->where('p.user_type="publisher"');
		$query->where('p.profile_status=1');
		$query->order('p.id ASC');
		$result=$this->fetchAll($query);
		
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	
	public function getCustomerList()
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array('c'=>$this->_customername),array('id','account_type','parent_id','first_name','last_name','user_email','user_name','phone','added_date','status','updated_date'));	
		$query->where('c.parent_id=0');
		$query->order('c.id ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	}
	function getGroupQuantity($group_id,$company_id,$publication_id)
	{
		$query="SELECT quantity FROM ".$this->_credit_history." WHERE bookid='".$publication_id."' AND userid='".$company_id."' AND group_id='".$group_id."'";
		$result=$this->getAdapter()->fetchAll($query);
		return $result;
	}
}
?>