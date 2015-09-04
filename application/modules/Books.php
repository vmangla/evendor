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
	protected $_genners =TBL_GENRES;
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
	
	public function getListGroupUser($publisher_id,$where="")
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('p'=>$this->_name));
		$query->where('p.publisher_id="'.$publisher_id.'"');
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
		$select='SELECT * from '.$this->_categoryname." order by category_name asc";
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	
	public function getGenreCount($genreid)
	{   
		$retVal=array();  
		$select='SELECT prod.*,storeprice.product_id,storeprice.country_id,storeprice.language_id,storeprice.price from '.$this->_name.' prod INNER JOIN '.$this->_productprice.' storeprice on prod.id=storeprice.product_id INNER JOIN pclive_product_images as prImg ON prImg.product_id = prod.id  where prod.product_type="'.$genreid.'" and prod.status=1 and prod.admin_approve=1 ';
		 
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}
	public function getGenreCountAccordingStore($genreid,$storeid)
	{   
		$retVal=array();  
		$select='SELECT prod.*,storeprice.product_id,storeprice.country_id,storeprice.language_id,storeprice.price from '.$this->_name.' prod INNER JOIN '.$this->_productprice.' storeprice on prod.id=storeprice.product_id INNER JOIN pclive_product_images as prImg ON prImg.product_id = prod.id  where prod.product_type="'.$genreid.'"  and prod.status=1 and prod.admin_approve=1 and storeprice.country_id="'.$storeid.'" ';
		 
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
		$select='SELECT * from '.$this->_countryname.' order by country';
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
		if($publisher_id!='')
		{
			$select='SELECT * from '.$this->_brandname.' as br WHERE publisher_author_id="'.$publisher_id.'" and category="'.$cat_id.'" order by br.brand asc';
		}
		else
		{
			$select='SELECT * from '.$this->_brandname.' as br WHERE  category="'.$cat_id.'" order by br.brand asc';
		}
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
	
	public function getTitleBrandListForAdmin($title_brand,$publisher_id,$cat_id)
	{   
		$retVal=array();
		$brand_dropdown="";
		$brand = explode("**",$title_brand);
		if($publisher_id!='')
		{
			$select='SELECT * from '.$this->_brandname.' as br WHERE publisher_author_id="'.$publisher_id.'" and category="'.$cat_id.'" order by br.brand asc ';
		}
		else
		{
			$select='SELECT * from '.$this->_brandname.' as br WHERE  category="'.$cat_id.'" order by br.brand asc';
		}
		
		$retVal=$this->getAdapter()->FetchAll($select); 
	 		if($cat_id == '3')
			{
				
				$brand_dropdown.='<select name="title" id="title" message="Please select brand">
				<option value="">Select Brand</option>';
			}
			else {
				$brand_dropdown.='<select name="title" id="title" class=""  message="Please select brand">
				<option value="">Select Brand</option>';
			}
				for($ii=0;$ii<count($retVal);$ii++)
				{
					if($brand[0]==$retVal[$ii]['id'])
					{
						$selected="selected";
					}
					else
					{
						$selected="";
					}
				$brand_dropdown.='<option value="'.$retVal[$ii]['id']."**".$cat_id.'" '.$selected.'>'.$retVal[$ii]['brand'].'</option>';
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
		//$get_searchfield1 ='';		
		$list=array();
		
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$cat_array=!empty($cat_Name)?$this->getCategoryInfoByName($cat_Name):array();
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
		if($product_id=='' || $product_id=='0')
		{
			$query->join(array("genner"=>$this->_genners), "prod.product_type = genner.id", array('genner.id'));
		}
	 
		
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
	
	public function getPublicationIdsByCategoryNameCount($storeId,$cat_Name,$admin_Approve=1,$is_Featured=0,$parentBrandID=0,$product_id=0)
	{
		
		
		//$postkeyword1 = new Zend_Session_Namespace('searchkeyword');
	//	$get_searchfield1 = $postkeyword1->searchword;
		//$get_searchfield1 ='';		
		$list=array();
		
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$cat_array=!empty($cat_Name)?$this->getCategoryInfoByName($cat_Name):array();
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
		$query->join(array("genner"=>$this->_genners), "prod.product_type = genner.id", array('genner.id'));
		
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
			if($is_Featured==1)
			{
				$query->where('prod.is_featured= ?', '1');
			}
			if(!empty($cat_array))
			{
				$query->where('prod.cat_id= ?', $cat_array['id']);
			}
			$query->where('storeprice.country_id= ?', $storeId);
			$query->where('storeprice.product_id= ?', $productID);
			$query->where('storeprice.language_id= ?', $languageID);
			//echo $query;			
			
			$retVal=$this->getAdapter()->FetchAll($query); 
			if(isset($retVal) && $retVal!="")
			{
				$list=$retVal;
			}
		}
		
		/*echo"<pre>";
		print_r($list);
		echo"</pre>";
		exit;*/
		
		return $list;
	}
	
	
	public function getPublicationFeaturedItem($storeId,$cat_Name,$admin_Approve=1,$is_Featured=0)
	{
			
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$cat_array=$cat_Name;
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		//$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
				
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
			$query->where('prod.cat_id= ?', $cat_array);
		}
		
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?', $storeId);
		}
	
		$query->order('prod.id DESC');
		
	
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		
		/*echo"<pre>";
		print_r($list);
		echo"</pre>";
		exit;*/
		
		return $list;
	}
	public function getBestSellerProduct($storeId,$cate_id,$status,$admin_approve=1)
	{
		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		//prod.best_seller >1 AND 
		$query->where('prod.admin_approve=1 AND status=1 AND cat_id="'.$cate_id.'"');
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?',$storeId);
		}
		$query->order('prod.best_seller DESC');
		$query->limit(13,0);
		$retVal=$this->getAdapter()->FetchAll($query);
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		return $list;
	}
	public function getBackIssue($storeId,$parent_brand_id)
	{
			
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
	
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
	
	    $query->where('prod.parent_brand_id='.$parent_brand_id.' AND prod.admin_approve=1');
		
		
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?', $storeId);
		}
	
		$query->order('prod.id DESC');
		
	
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		
		return $list;
	}
	
	
	public function getPublicationListByStore($storeId,$admin_Approve=1)
	{
			
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
		$query->join(array("genner"=>$this->_genners), "genner.id = prod.product_type", array('genner.id'));			
		if($admin_Approve==1)
		{
			$query->where('prod.admin_approve= ?', '1');
		}
	
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?', $storeId);
		}
	
		$query->order('prod.id DESC');
		
	
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		
		/*echo"<pre>";
		print_r($list);
		echo"</pre>";
		exit;*/
		
		return $list;
	}
	
	public function getPublicationListByGenre($genreId,$storeId='',$admin_Approve=1)
	{
			
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
				
		if($admin_Approve==1)
		{
			$query->where('prod.admin_approve= ?', '1');
		}		
		if(!empty($genreId))
		{
			$query->where('prod.product_type= ?', $genreId);
		}	
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?', $storeId);
		}	
		$query->order('prod.id DESC');
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		
		/*echo"<pre>";
		print_r($list);
		echo"</pre>";
		exit;*/
		
		return $list;
	}
	public function getPublicationListByGenreByStore($genreId,$storeId,$admin_Approve=1,$cat_id='')
	{
			
		$list=array();
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array("prod"=>$this->_name),array("prod.*"));
		
		$query->join(array("storeprice"=>$this->_productprice), "prod.id = storeprice.product_id", array('storeprice.product_id','storeprice.country_id','storeprice.language_id','storeprice.price'));
		
		$query->join(array("prodimage"=>$this->_productimage), "prod.id = prodimage.product_id", array('prodimage.image_name'));
		$query->join(array("genner"=>$this->_genners), "genner.id = prod.product_type", array('genner.id'));		
		if($admin_Approve==1)
		{
			$query->where('prod.admin_approve= ?', '1');
		}		
		if(!empty($genreId))
		{
			$query->where('prod.product_type= ?', $genreId);
		}	
		if(!empty($storeId))
		{
			$query->where('storeprice.country_id= ?', $storeId);
		}
		if(!empty($cat_id))
		{
			$query->where('prod.cat_id= ?', $cat_id);
		}
		$query->order('prod.id DESC');
		
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		
		/*echo"<pre>";
		print_r($list);
		echo"</pre>";
		exit;*/
		
		return $list;
	}
	public function getProductPrice($productId)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_productprice.' where id="'.$productId.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	public function getPublicationProductList($publisher_id)
	{
		$retVal=array();  
		$select='SELECT p.*,sum(p.no_download) as tot_down,Cr.credit_id,Cr.price,sum(Cr.quantity*Cr.price) as tot_price,Cr.price as cr_price,sum(Cr.quantity) as quantity,Cr.store_id,Cr.add_date,Cr.group_id,trh.purchaseAmount,trh.currency from '.$this->_name.' as p LEFT JOIN '.$this->_credit_history.' as Cr ON p.id=Cr.bookid LEFT JOIN '.$this->_transaction_history.' as trh ON Cr.transaction_id=trh.id where publisher_id="'.$publisher_id.'" group by Cr.bookid order by Cr.credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	public function getreportByPublication($publication_id,$publisher_id)
	{
		$retVal=array();  
		$select='SELECT p.*,Cr.credit_id,Cr.price,Cr.price as cr_price,Cr.quantity as quantity,Cr.store_id,Cr.add_date,trh.purchaseAmount,trh.currency from '.$this->_name.' as p LEFT JOIN '.$this->_credit_history.' as Cr ON p.id=Cr.bookid LEFT JOIN '.$this->_transaction_history.' as trh ON Cr.transaction_id=trh.id where publisher_id="'.$publisher_id.'" and Cr.bookid ="'.$publication_id.'" order by Cr.add_date desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	##################### free publication #########################################
	public function getPublicationProductListFree($publisher_id)
	{
		$retVal=array();  
		$select='SELECT p.*,pr.* from '.$this->_name.' as p INNER JOIN '.$this->_productprice.' as pr ON p.id=pr.product_id  where p.publisher_id="'.$publisher_id.'"  and (pr.price="" OR pr.price="0")  order by p.no_download desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	###########################  end Freee ###############################################
	public function getPublicationProductListDate($publisher_id,$from_date,$to_date)
	{
		$retVal=array();  
		//$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid where p.publisher_id="'.$publisher_id.'" and pc.add_date between "'.$from_date.'" and "'.$to_date.'"';
		$select='SELECT p.*,Cr.credit_id,Cr.price,Cr.store_id,Cr.price as cr_price,Cr.quantity,trh.purchaseAmount,trh.currency from '.$this->_name.' as p INNER JOIN  '.$this->_credit_history.' as Cr ON p.id=Cr.bookid INNER JOIN '.$this->_transaction_history.' as trh ON Cr.transaction_id=trh.id where p.publisher_id="'.$publisher_id.'" and Cr.add_date between "'.$from_date.'" and "'.$to_date.'" group by Cr.bookid order by Cr.credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	############# free publication List #########################################
	public function getPublicationProductListDateFree($publisher_id,$from_date,$to_date)
	{
		$retVal=array();  
		//$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid where p.publisher_id="'.$publisher_id.'" and pc.add_date between "'.$from_date.'" and "'.$to_date.'"';
		$select='SELECT p.*,pr.* from '.$this->_name.' as p INNER JOIN '.$this->_productprice.' as pr ON p.id=pr.product_id  where p.publisher_id="'.$publisher_id.'" and p.add_time between "'.$from_date.'" and "'.$to_date.'"  and (pr.price="" OR pr.price="0")  order by p.no_download desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	#################### end free publication List ##############################
	
	public function getPublicationAssignList($publisher_id,$group_user_id)
	{
		$retVal=array();  
		$select='SELECT p.*,pc.price as cr_price,pc.quantity from '.$this->_name.' as p inner join '.$this->_publication_assign.' as pas on p.id=pas.product_id inner join '.$this->_credit_history.' as pc on p.id=pc.bookid  where pas.group_user_id="'.$group_user_id.'" and pas.publisher_id="'.$publisher_id.'" group by pc.bookid order by pc.credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	############ free publication List #######################################
	public function getPublicationAssignListFree($publisher_id,$group_user_id)
	{
		$retVal=array();  
		//$select='SELECT p.*,pc.price as cr_price,pc.quantity from '.$this->_name.' as p inner join '.$this->_publication_assign.' as pas on p.id=pas.product_id inner join '.$this->_credit_history.' as pc on p.id=pc.bookid  where pas.group_user_id="'.$group_user_id.'" and pas.publisher_id="'.$publisher_id.'" group by pc.bookid order by pc.credit_id desc';
		$select='SELECT p.*,pc.price as cr_price from '.$this->_name.' as p inner join '.$this->_publication_assign.' as pas on p.id=pas.product_id inner join '.$this->_productprice.' as pc on p.id=pc.product_id  where pas.group_user_id="'.$group_user_id.'" and (pc.price="" or pc.price="0") and pas.publisher_id="'.$publisher_id.'" order by p.no_download desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	##################################################################################
	public function getPublicationAssignListDate($publisher_id,$group_user_id,$from_date,$to_date)
	{
		$retVal=array();  
		$select='SELECT p.*,pc.price as cr_price,pc.quantity from '.$this->_name.' as p inner join '.$this->_publication_assign.' as pas on p.id=pas.product_id inner join '.$this->_credit_history.' as pc on p.id=pc.bookid  where pas.group_user_id="'.$group_user_id.'" and pas.publisher_id="'.$publisher_id.'" and add_date between "'.$from_date.'" and "'.$to_date.'" group by pc.bookid order by pc.credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	############# free asign publication ###################################
	public function getPublicationAssignListDateFree($publisher_id,$group_user_id,$from_date,$to_date)
	{
		$retVal=array();  
		$select='SELECT p.* from '.$this->_name.' as p inner join '.$this->_publication_assign.' as pas on p.id=pas.product_id inner join '.$this->_productprice.' as pc on p.id=pc.product_id  where pas.group_user_id="'.$group_user_id.'" and (pc.price="" or pc.price="0") and pas.publisher_id="'.$publisher_id.'" and p.add_time between "'.$from_date.'" and "'.$to_date.'" order by p.no_download desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	####################  end free assign publication ########################
	################################ free publication ####################################
	public function getPublicationAssignUser($product_id,$publisher_id)
	{
		$retVal=array();  
		$select='SELECT * from '.$this->_publication_assign.' where product_id ="'.$product_id.'" and publisher_id="'.$publisher_id.'"';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}
	
	public function getProductCreditHistory($product_id)
	{
		$retVal=array();  
		$select='SELECT *,count(bookid) as total_count,sum(price) as total_price from '.$this->_credit_history.' where bookid ="'.$product_id.'" group by bookid order by total_count ';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	 public function getAllProductReport()
	 {
	 	$retVal=array();  
		$select='SELECT *,count(pc.bookid) as total_count from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid group by pc.bookid';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getAllProductWithCategoryReport($category_id,$product_id)
	 {
	 	$retVal=array();  
		$select='SELECT *,count(pc.bookid) as total_count from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid where p.id="'.$product_id.'" and p.cat_id="'.$category_id.'" group by pc.bookid';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getProductReport($category='',$brand='',$product_id='',$from_date='',$to_date='',$publisher_id='')
	 {
	 	$retVal=array();  
		if($category!='')
		{
			$whereCond.= " and p.cat_id='".$category."'"; 
		}
		if($product_id!='')
		{
			$whereCond.= " and p.id='".$product_id."'"; 
		}
		if($brand!='' && $category!=3)
		{
			$whereCond.= " and p.parent_brand_id='".$brand."'"; 
		}
		if($from_date!='' && $to_date!='')
		{
			$whereCond.=" and pc.add_date between '".$from_date."' and '".$to_date."'";
		}
		if($publisher_id!='')
		{
			$whereCond.=" and p.publisher LIKE '%".$publisher_id."%'";
		}
		$select='SELECT p.*,pc.*,sum(pc.quantity) as total_count,sum(pc.price*pc.quantity) as grtotal,sum(pc.price) as total_price,p.no_download as tot_download,pc.group_id,trhis.purchaseAmount as totAmnt from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid inner join '.$this->_transaction_history.' as trhis on pc.order_id=trhis.orderId where p.status="1" '.$whereCond.' group by pc.bookid order by pc.add_date desc';
		
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getProductFreeReport($category='',$brand='',$product_id='',$from_date='',$to_date='',$publisher_id='')
	 {
	 	$retVal=array();  
		if($category!='')
		{
			$whereCond.= " and p.cat_id='".$category."'"; 
		}
		if($product_id!='')
		{
			$whereCond.= " and p.id='".$product_id."'"; 
		}
		if($brand!='')
		{
			$whereCond.= " and p.parent_brand_id='".$brand."'"; 
		}
		if($from_date!='' && $to_date!='')
		{
			$whereCond.=" and pc.add_date between '".$from_date."' and '".$to_date."'";
		}
		if($publisher_id!='')
		{
			$whereCond.=" and p.publisher LIKE '%".$publisher_id."%'";
		}
		$select='SELECT p.*,pc.price,pc.group_price from '.$this->_name.' as p inner join '.$this->_productprice.' as pc on p.id=pc.product_id  where p.status="1" and (pc.price="0") and p.no_download>0 '.$whereCond.' group by pc.product_id order by no_download desc';
		
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function gerDetailsReportsById($publication_id)
	 {
		$select='SELECT p.*,pc.quantity,pc.price,pc.add_date,pc.order_id,pc.store_id,pc.transaction_id,pc.group_id,trhis.purchaseAmount as totAmnt from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid inner join '.$this->_transaction_history.' as trhis on pc.order_id=trhis.orderId where p.status="1" and p.id="'.$publication_id.'" '.$whereCond.' order by pc.add_date desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getAllProductWithCategoryOrder($category_id,$product_id)
	 {
	 	$retVal=array();  
		$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid where p.id="'.$product_id.'" and p.cat_id="'.$category_id.'" and transaction_id!="" order by credit_id';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
     public function getAllProductOrder()
	 {
	 	$retVal=array();  
		$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid and transaction_id!="" order by credit_id';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getAllProductOrderWithGroupBy()
	 {
	 	$retVal=array();  
		$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid group by pc.order_id order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 	/*$retVal=array();  
		$select='SELECT * from '.$this->_credit_history.' as pc left join '.$this->_transaction_history.' as tc on pc.order_id = tc.orderId group by order_id order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;*/
	 }
	 
	  public function getAllProductWithCategoryOrderGroupBy($category_id,$product_id)
	 {
	 	$retVal=array();  
		$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid where p.id="'.$product_id.'" and p.cat_id="'.$category_id.'" and transaction_id!="" group by pc.order_id order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
		
		/*$retVal=array();  
		$select='SELECT * from '.$this->_credit_history.' as pc left join '.$this->_transaction_history.' as tc on pc.order_id = tc.orderId group by order_id order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;*/
	 }
	 public function getAllProductOrderWithOrderId($orderId)
	 {
	 	$retVal=array();  
		$select='SELECT * from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid  and pc.order_id="'.$orderId.'" order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getAllProductSearchGroupBy($where)
	 {		
		$retVal=array();  
		$select='SELECT pc.* from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid where 1=1 '.$where.' group by order_id order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	 public function getAllProductSearchWithoutGroupBy($where)
	 {		
		$retVal=array();  
		$select='SELECT p.*,pc.price,pc.quantity,pc.store_id,pc.add_date,pc.payment_status,pc.transaction_id,pc.order_id from '.$this->_name.' as p inner join '.$this->_credit_history.' as pc on p.id=pc.bookid  where 1=1 '.$where.' order by credit_id desc';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	  public function getAllProductTransactionById($trans_id)
	 {		
		$retVal=array();  
		$select='SELECT * from '.$this->_transaction_history.' where id="'.$trans_id.'"';
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	 }
	
}
?>