<?php
class Publisher_Model_DbTable_Books extends Zend_Db_Table
{
	protected $_name =TBL_PRODUCTS;
	protected $_productimage =TBL_PRODUCTS_IMAGE;
	protected $_productprice =TBL_PRODUCTS_PRICE;
    protected $_productlanguage =TBL_LANGUAGES;
	protected $_categoryname =TBL_CATEGORIES;
	protected $_authorname =TBL_USERS;
	protected $_countryname =TBL_COUNTRY;
	protected $_brandname =TBL_BRANDS;
	protected $_publication_assign = "pclive_publication_assign";
	protected $_credit_history = "pclive_credit_history";
	protected $_transaction_history = "pclive_transaction_history";
	
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
	
	public function isPriceExist($productID,$countryID,$languageID,$price="",$priceid="")
	{
		$retVal=false;
		if($priceid!='')
		{
			$select='SELECT * from '.$this->_productprice.' where product_id="'.$productID.'" AND country_id="'.$countryID.'" AND language_id="'.$languageID.'" and price="'.$price.'" and id!="'.$priceid.'"';
		}
		else if($price!='')
		{	
			$select='SELECT * from '.$this->_productprice.' where product_id="'.$productID.'" AND country_id="'.$countryID.'" AND language_id="'.$languageID.'" and price="'.$price.'"';
		}
		else
		{
			$select='SELECT * from '.$this->_productprice.' where product_id="'.$productID.'" AND country_id="'.$countryID.'" AND language_id="'.$languageID.'"';
		}
		
		$result=$this->getAdapter()->fetchRow($select);
		if($result && $result!=null)
		{
			$retVal=true;
		}
		
		return $retVal;
	}
	
	
	public function isGroupPriceExist($productID,$countryID,$languageID,$grouppriceval,$priceid="")
	{
		$retVal=false;
		if($priceid!='')
		{
			$select='SELECT * from '.$this->_productprice.' where product_id="'.$productID.'" AND country_id="'.$countryID.'" AND language_id="'.$languageID.'" and group_price="'.$grouppriceval.'" and id!="'.$priceid.'"';
		}
		else
		{
			$select='SELECT * from '.$this->_productprice.' where product_id="'.$productID.'" AND country_id="'.$countryID.'" AND language_id="'.$languageID.'" and group_price="'.$grouppriceval.'"';
		}		
		$result=$this->getAdapter()->fetchRow($select);
		if($result && $result!=null)
		{
			$retVal=true;
		}
		
		return $retVal;
	}
	
	public function getList($publisher_id,$where="")
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('p'=>$this->_name));
		$query->where('p.publisher_id="'.$publisher_id.'"');
		$query->where('p.parent_brand_id=0');
		
		if(!empty($where))
		{
			$query->where($where);
		}
		
		//$query->join(array('up'=>TBL_PRODUCTS_IMAGE),'p.id=up.product_id');
		//$query->join(array('up1'=>TBL_PRODUCTS_PRICE),'p.id=up1.product_id');
		//$query->join(array('up2'=>TBL_LANGUAGES),'p.id=up2.user_id');
		$query->order(' p.id desc');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getkeywordlist($keyword,$storeid,$languageid,$genreid)
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('p'=>$this->_name));
		$query->join(array("storeprice"=>$this->_productprice), "p.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		$query->where('admin_approve="1"');
		if(!empty($storeid))
		{
			$query->where('storeprice.country_id= ?', $storeid);
		}
		if(!empty($languageid))
		{
			$query->where('storeprice.language_id= ?', $languageid);
		}
		if(!empty($genreid))
		{
			$query->where('p.product_type= ?', $genreid);
		}
		
		$query->where('(p.title like "%'.$keyword.'%") OR (p.description like "%'.$keyword.'%") OR (p.publisher like "%'.$keyword.'%")  OR (p.isbn_number like "%'.$keyword.'%")');
	 
		 
		 //echo "select: ".$query->__toString();
		//die();
		
		//$query->join(array('up'=>TBL_PRODUCTS_IMAGE),'p.id=up.product_id');
		//$query->join(array('up1'=>TBL_PRODUCTS_PRICE),'p.id=up1.product_id');
		//$query->join(array('up2'=>TBL_LANGUAGES),'p.id=up2.user_id');
		$query->order(' p.id desc');
		//echo $query;exit;
		$result=$this->fetchAll($query);
		
		/* echo "<pre>";
		print_r($result);
		echo "</pre>";
		die();*/
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getgenrelist($storeid,$languageid,$genreid)
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('p'=>$this->_name));
		$query->join(array("storeprice"=>$this->_productprice), "p.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		$query->where('admin_approve="1"');
		if(!empty($storeid))
		{
			$query->where('storeprice.country_id= ?', $storeid);
		}
		if(!empty($languageid))
		{
			$query->where('storeprice.language_id= ?', $languageid);
		}
		if(!empty($genreid))
		{
			$query->where('p.product_type= ?', $genreid);
		}
		
	
		$query->order(' p.id desc');
		//echo "select: ".$query->__toString();
		$result=$this->fetchAll($query);
		

		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	
	
	
	public function getListByCategoryName($publisher_id,$cat_Name,$admin_Approve='0')
	{
		$cat_array=$this->getCategoryInfoByName($cat_Name);
		
		$list=array();
		
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('u'=>$this->_name));
		$query->where('u.publisher_id="'.$publisher_id.'"');
		$query->where('u.parent_brand_id=0');
		$query->where('u.cat_id="'.$cat_array['id'].'"');
		
		if(!empty($admin_Approve))
		{
		$query->where('u.admin_approve="'.$admin_Approve.'"');
		}
		
		$query->order(' u.id desc');
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getApprovedList()
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('u'=>$this->_name));		
		$query->where('u.admin_approve="1"');
		$query->order('u.id desc');
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	 
	public function getCategoryList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_categoryname;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getGenreCount($genreid)
	{   
		$retVal=array();  
		$select='SELECT prod.*,storeprice.product_id,storeprice.country_id,storeprice.language_id,storeprice.price from '.$this->_name.' prod INNER JOIN '.$this->_productprice.' storeprice on prod.id=storeprice.product_id where prod.product_type="'.$genreid.'" and prod.status=1 and prod.admin_approve=1 ';
		 
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
	
	public function getCategoryInfoByName($catName)
	{   
		$retVal=array();  
		
		if(strtolower(trim($catName))==strtolower(trim('ebook')))
		{
			$catNameOR='eBooks';
		}
		elseif(strtolower(trim($catName))==strtolower(trim('ebooks')))
		{
			$catNameOR='eBook';
		}
		elseif(strtolower(trim($catName))==strtolower(trim('newspaper')))
		{
			$catNameOR='newspapers';
		}
		elseif(strtolower(trim($catName))==strtolower(trim('newspapers')))
		{
			$catNameOR='newspaper';
		}
		elseif(strtolower(trim($catName))==strtolower(trim('magazine')))
		{
			$catNameOR='magazines';
		}
		else
		{
			$catNameOR='magazine';
		}
		
		
		$select='SELECT * from '.$this->_categoryname.' where category_name="'.$catName.'" OR category_name="'.$catNameOR.'" LIMIT 1';
		$retVal=$this->getAdapter()->fetchRow($select); 
		//print_r($retVal);exit;
		return $retVal;
	}
	
	 public function getUserInfoList()
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_authorname;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	 public function getAuthorList($parent_publisher_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_authorname.' where user_type="author" AND parent_id='.$parent_publisher_id;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}

	 public function getAuthorInfo($authid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_authorname.' where id="'.$authid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
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
	
	 public function getInfoByPublicationId($pPublisherId)
	 {
		$retVal=false;
		
		if(isset($pPublisherId) && $pPublisherId!="")
		{
			$result=$this->fetchRow('id='.$pPublisherId);
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
	 
	 
	public function activateUser($varificationcode)
	{   
        $retVal=array();  
		
		$datavalue=$this->fetchRow("activation_code='".$varificationcode."' and activation_status='1'"); 
		
		if(isset($datavalue) && $datavalue!="")
		{
			$update="update ".$this->_name." set profile_status=1, activation_status=0 where activation_code='".$varificationcode."'";
			$retVal=$this->getAdapter()->query($update); 
			
			if(isset($retVal) && $retVal!="")
		    {
			   return 1;
			}
			else
			{
			   return 0;
			}
			
		}
		else
		{
		    return 0;
		}
	}
	
	
	
	// This function will proportionally resize image 
	public function resizeImage($CurWidth,$CurHeight,$MaxSize,$DestFolder,$SrcImage,$Quality,$ImageType,$NewWidth,$NewHeight)
	{
		if($CurWidth <= 0 || $CurHeight <= 0) 
		{
			return false;
		}
		
		if($CurWidth < $NewWidth)
		{
			$NewWidth = $CurWidth;
		}
		if($CurHeight < $NewHeight)
		{
			$NewHeight = $CurHeight;
		}
		
		$NewCanves 	= imagecreatetruecolor($NewWidth, $NewHeight);
		
		if(imagecopyresampled($NewCanves, $SrcImage,0, 0, 0, 0, $NewWidth, $NewHeight, $CurWidth, $CurHeight))
		{
			switch(strtolower($ImageType))
			{
				case 'image/png':
					imagepng($NewCanves,$DestFolder);
					break;
				case 'image/gif':
					imagegif($NewCanves,$DestFolder);
					break;			
				case 'image/jpeg':
				case 'image/pjpeg':
					imagejpeg($NewCanves,$DestFolder,$Quality);
					break;
				default:
					return false;
			}
		if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
		return true;
		}
	}
	
	public function cropImage($CurWidth,$CurHeight,$iSize,$DestFolder,$SrcImage,$Quality,$ImageType,$NewWidth,$NewHeight)
	{	 
			if($CurWidth <= 0 || $CurHeight <= 0) 
			{
				return false;
			}
			
			if($CurWidth < $NewWidth)
			{
				$NewWidth = $CurWidth;
			}
			if($CurHeight < $NewHeight)
			{
				$NewHeight = $CurHeight;
			}
			
			$NewCanves 	= imagecreatetruecolor($NewWidth, $NewHeight);	
			if(imagecopyresampled($NewCanves, $SrcImage,0, 0,0,0, $NewWidth, $NewHeight, $CurWidth, $CurHeight))
			{
				switch(strtolower($ImageType))
				{
					case 'image/png':
						imagepng($NewCanves,$DestFolder);
						break;
					case 'image/gif':
						imagegif($NewCanves,$DestFolder);
						break;			
					case 'image/jpeg':
					case 'image/pjpeg':
						imagejpeg($NewCanves,$DestFolder,$Quality);
						break;
					default:
						return false;
				}
			if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
			return true;
			}
	}
	
	/************ Old ************************************/ 
	/*
	public function resizeImage($CurWidth,$CurHeight,$MaxSize,$DestFolder,$SrcImage,$Quality,$ImageType)
	{
		//Check Image size is not 0
		if($CurWidth <= 0 || $CurHeight <= 0) 
		{
			return false;
		}
		
		//Construct a proportional size of new image
		$ImageScale      	= min($MaxSize/$CurWidth, $MaxSize/$CurHeight); 
		$NewWidth  			= ceil($ImageScale*$CurWidth);
		$NewHeight 			= ceil($ImageScale*$CurHeight);
		
		if($CurWidth < $NewWidth || $CurHeight < $NewHeight)
		{
			$NewWidth = $CurWidth;
			$NewHeight = $CurHeight;
		}
		$NewCanves 	= imagecreatetruecolor($NewWidth, $NewHeight);
		// Resize Image
		if(imagecopyresampled($NewCanves, $SrcImage,0, 0, 0, 0, $NewWidth, $NewHeight, $CurWidth, $CurHeight))
		{
			switch(strtolower($ImageType))
			{
				case 'image/png':
					imagepng($NewCanves,$DestFolder);
					break;
				case 'image/gif':
					imagegif($NewCanves,$DestFolder);
					break;			
				case 'image/jpeg':
				case 'image/pjpeg':
					imagejpeg($NewCanves,$DestFolder,$Quality);
					break;
				default:
					return false;
			}
		//Destroy image, frees up memory	
		if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
		return true;
		}
	
	}
	public function cropImage($CurWidth,$CurHeight,$iSize,$DestFolder,$SrcImage,$Quality,$ImageType)
	{	 
			//Check Image size is not 0
			if($CurWidth <= 0 || $CurHeight <= 0) 
			{
				return false;
			}
			
			if($CurWidth>$CurHeight)
			{
				$y_offset = 0;
				$x_offset = ($CurWidth - $CurHeight) / 2;
				$square_size 	= $CurWidth - ($x_offset * 2);
			}else{
				$x_offset = 0;
				$y_offset = ($CurHeight - $CurWidth) / 2;
				$square_size = $CurHeight - ($y_offset * 2);
			}
			
			$NewCanves 	= imagecreatetruecolor($iSize, $iSize);	
			if(imagecopyresampled($NewCanves, $SrcImage,0, 0, $x_offset, $y_offset, $iSize, $iSize, $square_size, $square_size))
			{
				switch(strtolower($ImageType))
				{
					case 'image/png':
						imagepng($NewCanves,$DestFolder);
						break;
					case 'image/gif':
						imagegif($NewCanves,$DestFolder);
						break;			
					case 'image/jpeg':
					case 'image/pjpeg':
						imagejpeg($NewCanves,$DestFolder,$Quality);
						break;
					default:
						return false;
				}
			//Destroy image, frees up memory	
			if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
			return true;
			}
		  
	}
	Old*/
	
	public function getTitleBrandList($title_brand,$publisher_id,$cat_id)
	{   
		$retVal=array();
		$brand_dropdown="";
		
		$select='SELECT * from '.$this->_brandname.' WHERE publisher_author_id="'.$publisher_id.'" and category="'.$cat_id.'"';
		$retVal=$this->getAdapter()->FetchAll($select); 
	 		if($cat_id == '3')
			{
				$brand_dropdown.='<select name="title" id="title" message="Please select brand">
				<option value="">Select Brand</option>';
			}
			else {
				$brand_dropdown.='<select name="title" id="title" class="req"  message="Please select brand">
				<option value="">Select Brand</option>';
			}
				for($ii=0;$ii<count($retVal);$ii++)
				{
					if($title_brand==$retVal[$ii]['id'])
					{
						$selected="selected";
					}
					else
					{
						$selected="";
					}
				$brand_dropdown.='<option value="'.$retVal[$ii]['id'].'" '.$selected.'>'.$retVal[$ii]['brand'].'</option>';
				}
				
			$brand_dropdown.='</select>';
		 
		return $brand_dropdown;
	}
	
	public function getBrandInfo($brandid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_brandname.' where id="'.$brandid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getAllStores()
	{
		$retVal=array();
		
		$store_query="SELECT GROUP_CONCAT(country,'%^') as country_names, GROUP_CONCAT(id,'%^') as country_ids, continent_id FROM ".$this->_countryname." WHERE id<>0 GROUP BY continent_id ORDER BY country_names DESC";
		
		$get_stores = $this->getAdapter()->fetchAll($store_query);
		
		//echo "<pre>";

		if($get_stores && $get_stores!=null)
		{
			$tooltip_stores=array();
			foreach($get_stores as $row)
			{
				//$tooltip_stores[];
				$get_country_ids_string = rtrim($row['country_ids'],'%^');
				$get_country_names_string = rtrim($row['country_names'],'%^');
				
				$country_ids_array 	= explode('%^,',$get_country_ids_string);
				$country_names_array 	= explode('%^,',$get_country_names_string);
												
				foreach($country_ids_array as $key=>$country_id)
				{
					$tooltip_stores[$row['continent_id']][$country_id]=$country_names_array[$key];
				}
				asort($tooltip_stores[$row['continent_id']]);
			}
			
			$retVal=$tooltip_stores;
		}
		
		//print_r($retVal);
		return $retVal;
	}
	
	public function getPublicationIdsByCategoryName($storeId,$cat_Name,$admin_Approve=1,$is_Featured=0,$parentBrandID=0,$product_id=0)
	{
		
		
		$postkeyword1 = new Zend_Session_Namespace('searchkeyword');
		$get_searchfield1 = $postkeyword1->searchword;
				
		$list=array();
		
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$cat_array=!empty($cat_Name)?$this->getCategoryInfoByName($cat_Name):array();
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
	 
		
		if($product_id>0)
		{
			$query->where("prod.id NOT IN (".$product_id.")");
		}
		
		
		if($admin_Approve==1)
		{
			$query->where('prod.admin_approve= ?', '1');
		}
		if($is_Featured==1)
		{
			$query->where('prod.is_featured= ?', '1');
		}
		
		if(!empty($cat_array))
		{
			$query->where('prod.cat_id= ?', $cat_array['id']);
		}
		
		if(!empty($parentBrandID) && $parentBrandID>0)
		{
			$query->where('prod.product_type= ?', $parentBrandID);
		}	
				
		
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?', $storeId);
		}
		
		if(!empty($get_searchfield1))
		{
			 
			$query->where('(prod.title like "%'.$get_searchfield1.'%") OR (prod.description like "%'.$get_searchfield1.'%") OR (prod.publisher like "%'.$get_searchfield1.'%")  OR (prod.isbn_number like "%'.$get_searchfield1.'%")');
		}
		
		
		
		$query->order('prod.id DESC');
	 
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
	 
		
		return $list;
	}
	
	public function getStorePublicationDetail($productID,$storeId,$languageID,$is_Featured=0)
	{
		$list=array();
		
		if(!empty($storeId) && !empty($productID) && !empty($languageID))
		{
			$query=$this->select();
			$query->setIntegrityCheck(false);
			
			$cat_array=!empty($cat_Name)?$this->getCategoryInfoByName($cat_Name):array();
			
			$query->from(array("prod"=>$this->_name),array("prod.*"));
			$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
			
			//$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
					
			$query->where('prod.admin_approve= ?', '1');
			if($i