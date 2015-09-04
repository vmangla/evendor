<?php
class Company_Model_DbTable_Subscriptions extends Zend_Db_Table
{
	protected $_name =TBL_COMPANY_SUBSCRIPTIONS;
	protected $_groupname =TBL_GROUP_SUBSCRIPTIONS;
	protected $_publishername =TBL_USERS;
	protected $_customername =TBL_COMPANIES;
	protected $_categoryname =TBL_CATEGORIES;
	protected $_productname =TBL_PRODUCTS;
	protected $_productimage =TBL_PRODUCTS_IMAGE;
	protected $_brandname =TBL_BRANDS;
	
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
	
	public function getSubscriptionList($parentCompany_id=0)
	{
		$list=array();

		$query="SELECT s.id,s.product_id,s.store_id,s.total_downloads,s.status,s.added_date,s.updated_date, p.product_type,p.publisher_id,p.author_id,p.title,p.edition_id,p.description,p.isbn_number,p.publisher,p.total_pages,p.cat_id,p.file_name,p.file_size, pp.country_id,pp.language_id,pp.price FROM pclive_company_subscriptions as s INNER JOIN pclive_products as p ON s.product_id=p.id INNER JOIN pclive_product_prices as pp ON s.product_id=pp.product_id WHERE s.company_id<>0 AND s.company_id='$parentCompany_id' AND s.store_id=pp.country_id ORDER BY s.id ASC";
		
		//$query="SELECT * FROM ".$this->_name." WHERE company_id<>0 AND company_id=".$parentCompany_id." ORDER BY id ASC";
		
		//$query="SELECT * FROM ".$this->_name." WHERE company_id<>0 AND company_id=".$parentCompany_id." AND parent_id=0 ORDER BY id ASC";
		
		$result=$this->getAdapter()->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getSubscriptionListCompany($parentCompany_id=0)
	{
		$list=array();

		$query="SELECT p.id as product_id,p.product_type,p.publisher_id,p.author_id,p.title,p.edition_id,p.description,p.isbn_number,p.publisher,p.total_pages,p.cat_id,p.file_name,p.file_size, pp.country_id,pp.language_id,pp.price FROM pclive_products as p INNER JOIN  pclive_credit_history as cr ON cr.bookid=p.id INNER JOIN pclive_product_prices as pp ON p.id=pp.product_id WHERE cr.userid<>0 AND cr.userid='$parentCompany_id' ORDER BY p.id ASC";
		
		//$query="SELECT * FROM ".$this->_name." WHERE company_id<>0 AND company_id=".$parentCompany_id." ORDER BY id ASC";
		
		//$query="SELECT * FROM ".$this->_name." WHERE company_id<>0 AND company_id=".$parentCompany_id." AND parent_id=0 ORDER BY id ASC";
		
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
		
		/*echo"<pre>";
		print_r($retVal);
		echo"</pre>";
		exit;*/
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
		
		/*echo"<pre>";
		print_r($retVal);
		echo"</pre>";
		exit;*/
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
		if($parentCatId!=3)
		{
			$query->where('u.parent_brand_id>0');
		}
		
		$query->order('u.title ASC');
		echo $query;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		$dropdown.='<select name="product_id" id="product_id" class=""  message="Please select a product">
				<option value="">Select Product</option>';
				for($ii=0;$ii<count($list);$ii++)
				{
					$getBrandInfo=$this->getBrandInfo($list[$ii]['title']);
					if($product_id==$list[$ii]['id'])
					{
						$selected="selected";
					}
					else
					{
						$selected="";
					}
					if(!empty($getBrandInfo))
					{
						$brand_title =$getBrandInfo['brand'];
					}
					else
					{
						$brand_title = $list[$ii]['title'];
					}
				$dropdown.='<option value="'.$list[$ii]['id'].'" '.$selected.'>'.stripslashes($brand_title).'</option>';
				}
				
		$dropdown.='</select>';
		
		return $dropdown;
		//return $list;
	}
	public function getBrandInfo($brandid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_brandname.' where id="'.$brandid.'" order by brand asc';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	public function getProdListByParentCatIdBrandId($parentCatId,$brand_id,$product_id)
	{				
		$list=array();
		$dropdown='';
		$product_id=$product_id;

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('u'=>$this->_productname));
		$query->where('u.cat_id="'.$parentCatId.'"');
		if($parentCatId!=3)
		{
			$query->where('u.parent_brand_id>0');
			$query->where('u.parent_brand_id="'.$brand_id.'"');
		}
		
		$query->order('u.title ASC');
		$query->order('br.brand ASC');
		echo $query;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		$dropdown.='<select name="product_id" id="product_id" class=""  message="Please select a product">
				<option value="">Select Product</option>';
				for($ii=0;$ii<count($list);$ii++)
				{
					$getBrandInfo=$this->getBrandInfo($list[$ii]['title']);
					if($product_id==$list[$ii]['id'])
					{
						$selected="selected";
					}
					else
					{
						$selected="";
					}
					if(!empty($getBrandInfo))
					{
						$brand_title =$getBrandInfo['brand'];
					}
					else
					{
						$brand_title = $list[$ii]['title'];
					}
				$dropdown.='<option value="'.$list[$ii]['id'].'" '.$selected.'>'.$list[$ii]['title'].'</option>';
				}
				
		$dropdown.='</select>';
		
		return $dropdown;
		//return $list;
	}
	
	public function getProdListByParentCatIdForAdmin($parentCatId,$brand_id,$product_id)
	{
		$list=array();
		$dropdown='';
		$product_id=$product_id;
		$where ='';
		if($parentCatId!=3)
		{
			$where .= ' and u.parent_brand_id>0';
			if($brand_id!='')
			{
				$where .=' and u.parent_brand_id="'.$brand_id.'"';
			}
		}
		echo $querysss = "SELECT u.*,br.brand as brand FROM pclive_products AS u left join  pclive_brands as br on u.title=br.id  WHERE  u.cat_id='".$parentCatId."' ".$where." and u.status='1' ORDER BY br.brand,u.title ASC";
		$result=$this->getAdapter()->fetchAll($querysss);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		$dropdown.='<select name="product_id" id="product_id" class=""  message="Please select a product">
				<option value="">Select Product</option>';
				for($ii=0;$ii<count($list);$ii++)
				{
					$getBrandInfo=$this->getBrandInfo($list[$ii]['title']);
					if($product_id==$list[$ii]['id'])
					{
						$selected="selected";
					}
					else
					{
						$selected="";
					}
					if(is_numeric($list[$ii]['title']))
					{
						$brand_title =$list[$ii]['brand'];
					}
					else
					{
						$brand_title = $list[$ii]['title'];
					}
				$dropdown.='<option value="'.$list[$ii]['id'].'" '.$selected.'>'.stripslashes($brand_title).'</option>';
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
	
	public function getAllTotalSubscription($parentCompany_id=0)
	{
		$product_query="SELECT GROUP_CONCAT(product_id,'%^') as product_ids FROM ".$this->_name." WHERE company_id<>0 AND company_id=".$parentCompany_id." ORDER BY id ASC";
		
		$get_prod_ids = $this->getAdapter()->fetchRow($product_query);
		if($get_prod_ids && $get_prod_ids!=null)
		{
			$get_prod_ids_string = rtrim($get_prod_ids['product_ids'],'%^');
			$prod_id_array 	= explode('%^,',$get_prod_ids_string);
		
			$prod_id_string=implode(",",$prod_id_array);
		}
		//echo $prod_id_string;
			
		$list=array();
		
		if(!empty($prod_id_string))
		{
			$ids = "SELECT GROUP_CONCAT(id,'%^') as product_ids, cat_id as category FROM ".$this->_productname." WHERE id in($prod_id_string) group by cat_id";
			
			$get_ids = $this->getAdapter()->fetchAll($ids);
			if($get_ids && $get_ids!=null)
			{
				$category_products=array();$subscription_cat_array=array(); 
				
				$category_info=$this->getAllCategoryArray();
				$cat_id_array=$category_info['category_id_array'];
				
				foreach($get_ids as $key=>$subscription_array)
				{
				$get_ids_string = rtrim($subscription_array['product_ids'],'%^');
				$total_products = count(explode('%^,',$get_ids_string));
				
				$category_products[$key]['category_id']=$subscription_array['category'];
				$subscription_cat_array[]=$subscription_array['category'];
				
				$cat_info=$this->getCategoryInfoById($subscription_array['category']);
				$category_products[$key]['category_name']=$cat_info['category_name'];
				$category_products[$key]['total_product']=$total_products;
				}
							
				foreach($cat_id_array as $cat_id)
				{
					$key+=1;
					if(!in_array($cat_id,$subscription_cat_array))
					{
						$category_products[$key]['category_id']=$cat_id;
						$category_products[$key]['category_name']=$category_info['category_detail_array'][$cat_id];
						$category_products[$key]['total_product']=0;
					}
				}
				
				$list=$category_products;
				
				
			}
		}
		//print_r($list);
		
		return $list;
	}
	
	public function getCategoryInfoById($catId)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_categoryname.' where id="'.$catId.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getAllCategoryArray()
	{   
		$retVal=array(); $list=array(); 
		$select='SELECT * from '.$this->_categoryname;
		$retVal=$this->getAdapter()->fetchAll($select); 
		
		$id_array=array();$detail_array=array();
		foreach($retVal as $row)
		{
			$id_array[]=$row['id'];
			$detail_array[$row['id']]=$row['category_name'];
		}
		$list['category_id_array']=$id_array;
		$list['category_detail_array']=$detail_array;
		//print_r($list);
		return $list;
	}

}
?>