<?php
class Publisher_Model_DbTable_Issues extends Zend_Db_Table
{
	protected $_name =TBL_PRODUCTS;
	protected $_productimage =TBL_PRODUCTS_IMAGE;
	protected $_productprice =TBL_PRODUCTS_PRICE;
    protected $_productlanguage =TBL_LANGUAGES;
	protected $_categoryname =TBL_CATEGORIES;
	protected $_authorname =TBL_USERS;
	protected $_countryname =TBL_COUNTRY;
	protected $_brandname =TBL_BRANDS;
	protected $_genrename =TBL_GENRES;
	
	
	
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
	
	public function getList($publisher_id,$parent_publication_id)
	{
		$list=array();

		$query=$this->select();
		$query->setIntegrityCheck(false);
		$query->from(array('u'=>$this->_name));
		
		if(!empty($publisher_id))
		{
			$query->where('u.publisher_id="'.$publisher_id.'"');
		}
		
		$query->where('u.parent_brand_id="'.$parent_publication_id.'"');
		
		//$query->join(array('up'=>TBL_PRODUCTS_IMAGE),'u.id=up.product_id');
		//$query->join(array('up1'=>TBL_PRODUCTS_PRICE),'u.id=up1.product_id');
		//$query->join(array('up2'=>TBL_LANGUAGES),'u.id=up2.user_id');
		$query->order(' u.id desc');
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		return $list;
	}
	
	public function getListWhere($pWhere)
	{
		$retVal=array();
		if(isset($pWhere) && $pWhere!="")
		{
			$result=$this->fetchAll($pWhere);
			if($result && $result!=null)
			{
				$retVal=$result;
			}
		}
		return $retVal;
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
	
	 public function getAuthorList($parent_publisher_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_authorname.' where user_type="author" AND parent_id='.$parent_publisher_id;
		$retVal=$this->getAdapter()->FetchAll($select); 
		return $retVal;
	}

	public function getParentPublicationInfo($parent_publication_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$parent_publication_id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getParentBrandInfo($brand_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_brandname.' where id="'.$brand_id.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		return $retVal;
	}
	
	public function getGenreInfo($genre_id)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_genrename.' where id="'.$genre_id.'"';
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


	//This function corps image to create exact square images, no matter what its original size!
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
	
	public function getTitleBrandList($title_brand,$publisher_id)
	{   
		$retVal=array();
		$brand_dropdown="";
		
		$select='SELECT * from '.$this->_brandname.' WHERE publisher_author_id="'.$publisher_id.'"';
		$retVal=$this->getAdapter()->FetchAll($select); 
		
		$brand_dropdown.='<select name="title" id="title" class="req"  message="Please select brand">
				<option value="">Select Brand</option>';
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
	function getPublicationList($publisher_id,$pub_type)
	{
		$retVal=array();  
		if($pub_type =='1' || $pub_type=='2')
		{
			$select='SELECT id,title from '.$this->_name.' where publisher_id="'.$publisher_id.'" and cat_id="'.$pub_type.'" and parent_brand_id=0';
		}
		else
		{
			$select='SELECT id,title from '.$this->_name.' where publisher_id="'.$publisher_id.'" and cat_id="'.$pub_type.'"';
		}
		
		$retVal=$this->getAdapter()->fetchAll($select); 
		return $retVal;
	}

}
?>