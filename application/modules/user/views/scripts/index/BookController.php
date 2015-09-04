<?php
class Publisher_BookController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	var $parentPublisherId=null;
	
	var $modelPublisher=null;
	var $modelBook=null;
	var $modelBrand=null;
	
    public function init()
    {
		 
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');		
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
		if($data && $data!=null)
		{
            $this->modelPublisher = new Publisher_Model_DbTable_Publishers();
			$this->modelBooks = new Publisher_Model_DbTable_Books();
			$this->modelBrand=new Publisher_Model_DbTable_Brand();
			
			$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo;
			
			$this->parentPublisherId = !empty($this->sessPublisherInfo->parent_id)?$this->sessPublisherInfo->parent_id:$this->sessPublisherInfo->id;
			
						
			$tab_ajax=$this->_request->getParam('tab_ajax',0);
			if(empty($tab_ajax))
			{
				$this->_redirect('publisher/');
			}
			
			if($data->user_type!='publisher' && $data->user_type!='Pmanager' && $data->user_type!='Amanager') 
			{
			$this->_redirect('publisher/access/index/tab_ajax/book/');
			}
		}
		else
		{
			$this->_redirect('publisher/auth/');
		}
		
	}	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
		$formData=$this->getRequest()->getPost();
		
		if(isset($formData['searchword']) && (trim($formData['searchword'])!="" || trim($formData['booktype'])!=""))
		{
		  $bookname=trim($formData['searchword']);
		  $booktype=trim($formData['booktype']);
		  
		  $getBrandIds=$this->modelBrand->getBrandIds($bookname);
		  	  
		  $whrcls="";
		  if($bookname!="")
		  {
		    $whrcls.=" p.title LIKE '%".$bookname."%'";
			
			if(!empty($getBrandIds))
			{
				$whrcls.=" OR p.title in(".$getBrandIds.")";
			}
		  }
		  if($booktype!="")
		  {
		    if($bookname!="")
		    {
			$whrcls.=" AND p.product_type LIKE '%".$booktype."%'";
			}
			else
			{
		     // $whrcls.=" p.product_type LIKE '%".$booktype."%'";
			  $whrcls.=" p.product_type='".$booktype."'";
			}
		  }
		  
		  $bookList=$this->modelBooks->getList($this->parentPublisherId,$whrcls);
		}
		else
		{
			if($data->user_type == 'Pmanager' || $data->user_type == 'Amanager')
			{
				$where="";
				$groupPublicationsList=$this->modelPublisher->getAssignPublicationsList($this->sessPublisherInfo->id);
			
				foreach($groupPublicationsList as $group_pub_ids)
				{
					$publisher_id.= $group_pub_ids['product_id'].",";
				}
				$publisher_id = rtrim($publisher_id,",");
				$where = " id in (".$publisher_id.") OR parent_brand_id in (".$publisher_id.")";
			}
			if($publisher_id!='')
			{	
				$bookList=$this->modelBooks->getListGroupUser($this->parentPublisherId,$where);
			}
			else
			{
				$bookList=$this->modelBooks->getList($this->parentPublisherId);
			}
		}	
		
			
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($bookList);
	    $paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($bookList);
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->bookList=$paginator;
		
		$this->view->parentPublisherId=$this->parentPublisherId;
		$this->view->formData=$formData;
		
	}
	 
	public function updateproductpriceAction()
	{
		
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$createpriceURL = "http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/publisher/book/addprice/";
	
		$formData=array();
		$formErrors=array();
		
		$formData=$this->getRequest()->getPost();
	 
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$createpriceURL = "http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/publisher/book/addprice/";
	
		$formData=array();
		$formErrors=array();
		
		$formData=$this->getRequest()->getPost();
	 
	 
if(isset($_REQUEST))
		{
		            if(!(isset($_REQUEST['country'])) || trim($_REQUEST['country'])=="")
					{
						$formErrors['country']="Please select country ";
					}
					if(!(isset($_REQUEST['languageval'])) || trim($_REQUEST['languageval'])=="")
					{
						$formErrors['language']="Please select language";
					}
					if(!(isset($_REQUEST['priceval'])) || trim($_REQUEST['priceval'])=="")
					{
						$formErrors['price']="Please select price";
					}
					if(count($formErrors)==0)
					{
						$priceId=0; $price_error=array();
						$this->modelBooks = new Publisher_Model_DbTable_Books();
						if($this->modelBooks->isPriceExist($_REQUEST['productid'],$_REQUEST['country'],$_REQUEST['languageval'],$_REQUEST['priceval'],$_REQUEST['priceid']))
						{
							//echo"<strong><font color='red'>Price already exist.</font></strong>";exit;
							$price_error[]="Price already exist.";
						 
						}
						else if($this->modelBooks->isGroupPriceExist($_REQUEST['productid'],$_REQUEST['country'],$_REQUEST['languageval'],$_REQUEST['grouppriceval'],$_REQUEST['priceid']))
						{
							//echo"<strong><font color='red'>Price already exist.</font></strong>";exit;
							$price_error[]="Group Price already exist.";
							 
						}
						else
						{
							$priceData=array(
									'country_id'=>$_REQUEST['country'],
									'language_id'=>$_REQUEST['languageval'],
									'price'=>$_REQUEST['priceval'],
									'group_price'=>$_REQUEST['grouppriceval']
									 );
									 
							 				
								
							$modelPrice  = new Publisher_Model_DbTable_BookPrices();

							$where = "id='".$_REQUEST['priceid']."'";		
							$priceId  = $modelPrice->update($priceData,$where);
						}
						?>	
							<form name="form-add-price" id="form-add-price" method="post" onsubmit="return validate_pricing_form('form-add-price','<?php echo $createpriceURL;?>');">
	  <fieldset class="dashboard_fieldset">
			<legend>Publication Store & Langauge</legend>
	  		<table width="99%"  class="table-list publisher"  border="0" cellspacing="0" cellpadding="0">
				<tr>
				<td align="center" colspan="2"><div id="priceoutput"></div></td>
				</tr>
				<tr>
				<td class="tdleftBold">Store<span class="required">*</span> : </td>
				<td>
				<?php
				$modelCountry = new Publisher_Model_DbTable_Books();
				$countryList   = $modelCountry->getCountryList();
				?>
				<select name="country" id="country" class="req"  message="Please select country">
				<option value="">Select Store</option>
				
				<?php
				for($ii=0;$ii<count($countryList);$ii++)
				{
				?>
				<option value="<?php echo $countryList[$ii]['id']; ?>"><?php echo $countryList[$ii]['country']; ?></option>
				<?php
				}
				?>		   
				</select>
				
				<?php if(array_key_exists('country',$formErrors)){echo '<div class="inline-error">'.$formErrors['country'].'</div>';}?>

				</td>
				</tr>
	
				<tr>
				<td class="tdleftBold">Language<span class="required">*</span> : </td>
				<td>
				<?php
				$modelLanguage = new Publisher_Model_DbTable_Books();
				$languageList  = $modelLanguage->getLanguageList();
				?>
				<select name="language" id="language" class="req"  message="Please select language">
				<option value="">Select Language</option>
				
				<?php
				for($ii=0;$ii<count($languageList);$ii++)
				{
				?>
				<option value="<?php echo $languageList[$ii]['id']; ?>"><?php echo $languageList[$ii]['language_name']; ?></option>
				<?php
				}
				?>		   
				</select>
				
				<?php if(array_key_exists('language',$formErrors)){echo '<div class="inline-error">'.$formErrors['language'].'</div>';}?>

				</td>
				</tr>
		
				<tr>
				<td class="tdleftBold">Price<span class="required">*</span> : </td>
				<td>
				<input type="text" name="price" id="price"  class="req"  message="Please enter price"/>
				<?php if(array_key_exists('price',$formErrors)){echo '<div class="inline-error">'.$formErrors['price'].'</div>';}?>
				</td>
				</tr>
				
				<tr>
				<td class="tdleftBold">Group Price : </td>
				<td>
				<input type="text" name="group_price" id="group_price" />
				<?php if(array_key_exists('price',$formErrors)){echo '<div class="inline-error">'.$formErrors['price'].'</div>';}?>
				</td>
				</tr>
		
				<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="text-align:center;">
							<input type="hidden" name="createprice" value="Add Price">
							<input type="hidden" name="productid" id="productid" value="<?php echo $_REQUEST['productid']; ?>" />
							<input type="submit" name="createprice" id="createprice" value="Add Price" class="button-Save"/>
						</div>
						
					</td>
				</tr>

				</table>
		</fieldset>
		</form>
		
 77777777777<fieldset class="dashboard_fieldset">
			<legend>Store Price</legend>
<table width="100%" class="table-list" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="20%"><div align="left"><strong>Store</strong></div></td>
<td width="20%"><div align="left"><strong>Language</strong></div></td>
<td width="10%"><div align="left"><strong>Price</strong></div></td>
<td width="20%"><div align="left"><strong>Group Price</strong></div></td>
<td width="10%"><div align="left"><strong>Action</strong></div></td>

</tr>

<?php
 
	$modelPrice    = new Publisher_Model_DbTable_BookPrices();
	$producturrency = new Admin_Model_DbTable_Countries();
	$priceList     = $modelPrice->getPriceList($_REQUEST['productid']);
	
	 
	
	
for($jj=0;$jj<count($priceList);$jj++)
{	
?>
<tr>
<td>
    <div align="left">
	   <?php
			$modelCountry    = new Publisher_Model_DbTable_Books();
			$countryRecord   = $modelCountry->getCountryName($priceList[$jj]['country_id']);
			echo $countryRecord['country'];
	   ?>
    </div>
</td>
<td>
   <div align="left">
    <?php
			$modelLanguage    = new Publisher_Model_DbTable_Books();
			$languageRecord   = $modelLanguage->getLanguageName($priceList[$jj]['language_id']);
			echo $languageRecord['language_name'];
	?>
    </div>
</td>
<td><div align="left" id="show_price_<?php echo $priceList[$jj]['id']; ?>" style="display:block;"><?php echo $producturrency->getCurrencyInfo($priceList[$jj]['country_id'])." ".$priceList[$jj]['price']; ?></div>
 
</td>
<td><div align="left"><?php echo $producturrency->getCurrencyInfo($priceList[$jj]['country_id'])." ".$priceList[$jj]['group_price']; ?></div></td>
<td>
   <div align="left">
    <a href="http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/publisher/book/deleteprice/id/<?php echo $priceList[$jj]['id'];?>" onclick="return deleteprice(this.href)" title="delete">
			<img src="http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/public/css/publisher/images/trash.gif" width="10" height="10"/>
			</a>
			<a href="javascript:void(0);" onclick="return updateprice1('<?php echo $priceList[$jj]['id'];?>','http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor//publisher/book/saveprice','<?php echo $_REQUEST['productid'];?>');">
			<img src="http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/public/css/publisher/images/icon_edit.png" width="10" height="10"/>
			</a>
	 
    </div>
</td>

</tr>
<?php
}
?>
</table>
</fieldset>
 
							
						<?php	
						 
							 	 
		  }

		if(!empty($price_error))
		{
		foreach($price_error as $error_msg)
		{
		echo"<table><tr><td colspan='4'><strong><font color='red'>".$error_msg."</font></strong></td></tr></table>";
		}
		}
		}
		exit;		 
	}	
	 
	
	public function savepriceAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
	
		$formData=array();
		$formErrors=array();
		
		$formData=$this->getRequest()->getPost();
		
	 
		
		if($this->getRequest()->isPost() && (!empty($formData['createprice']) && $formData['createprice']=='Update Price'))
		{
		            if(!(isset($formData['country'])) || trim($formData['country'])=="")
					{
						$formErrors['country']="Please select country ";
					}
					if(!(isset($formData['language'])) || trim($formData['language'])=="")
					{
						$formErrors['language']="Please select language";
					}
					if(!(isset($formData['price'])) || trim($formData['price'])=="")
					{
						$formErrors['price']="Please select price";
					}
					 
					 
	}
	
	 
		
	 $modelPrice  = new Publisher_Model_DbTable_BookPrices();
	 $pricedata = $modelPrice->fetchAll("id='".$_POST['priceid']."'");
	 
	?>	
	  <form name="form-add-price" id="form-add-price" method="post" onsubmit="return updateprice1('<?php echo $_POST['priceid'];?>','http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/publisher/book/updateproductprice/productid/<?php echo $_POST['productid'];?>','<?php echo $_POST['productid'];?>');">
	  <fieldset class="dashboard_fieldset">
			<legend>Publication Store & Langauge</legend>
	  		<table width="99%"  class="table-list publisher"  border="0" cellspacing="0" cellpadding="0">
				<tr>
				<td align="center" colspan="2"><div id="priceoutput"></div></td>
				</tr>
				<tr>
				<td class="tdleftBold">Store<span class="required">*</span> : </td>
				<td>
				<?php
				$modelCountry = new Publisher_Model_DbTable_Books();
				$countryList   = $modelCountry->getCountryList();
				?>
				<select name="country" id="country" class="req"  message="Please select country">
				<option value="">Select Store</option>
				
				<?php
				for($ii=0;$ii<count($countryList);$ii++)
				{
				?>
				<option value="<?php echo $countryList[$ii]['id']; ?>" <?php if($pricedata[0]["country_id"]==$countryList[$ii]["id"]){ ?> selected <?php } ?> ><?php echo $countryList[$ii]['country']; ?></option>
				<?php
				}
				?>		   
				</select>
				
				<?php if(array_key_exists('country',$formErrors)){echo '<div class="inline-error">'.$formErrors['country'].'</div>';}?>

				</td>
				</tr>
	
				<tr>
				<td class="tdleftBold">Language<span class="required">*</span> : </td>
				<td>
				<?php
				$modelLanguage = new Publisher_Model_DbTable_Books();
				$languageList  = $modelLanguage->getLanguageList();
				?>
				<select name="language" id="language" class="req"  message="Please select language">
				<option value="">Select Language</option>
				
				<?php
				for($ii=0;$ii<count($languageList);$ii++)
				{
				?>
				<option value="<?php echo $languageList[$ii]['id']; ?>" <?php if($pricedata[0]["language_id"]==$languageList[$ii]['id']){ ?> selected <?php } ?>><?php echo $languageList[$ii]['language_name']; ?></option>
				<?php
				}
				?>		   
				</select>
				
				<?php if(array_key_exists('language',$formErrors)){echo '<div class="inline-error">'.$formErrors['language'].'</div>';}?>

				</td>
				</tr>
		
				<tr>
				<td class="tdleftBold">Price<span class="required">*</span> : </td>
				<td>
				<input type="text" name="price" id="price"  class="req" value="<?php echo $pricedata[0]["price"];?>"  message="Please enter price"/>
				<?php if(array_key_exists('price',$formErrors)){echo '<div class="inline-error">'.$formErrors['price'].'</div>';}?>
				</td>
				</tr>
				
				<tr>
				<td class="tdleftBold">Group Price : </td>
				<td>
				<input type="text" name="group_price" id="group_price" value="<?php echo $pricedata[0]["group_price"];?>" />
				<?php if(array_key_exists('price',$formErrors)){echo '<div class="inline-error">'.$formErrors['price'].'</div>';}?>
				</td>
				</tr>
		
				<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="text-align:center;">
							<input type="hidden" name="createprice" value="Update Price">
							<input type="hidden" name="productid" id="productid" value="<?php echo $_POST['productid']; ?>" />
							<input type="hidden" name="priceid" id="priceid" value="<?php echo $_POST['priceid']; ?>" />
							<input type="submit" name="updateprice" id="updateprice" value="Update Price" class="button-Save"/>
						</div>
						
					</td>
				</tr>

				</table>
		</fieldset>
		</form>
 
	<?php 
	exit();
	}
	 
	
	public function addpriceAction()
	{
	   
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
	
		$formData=array();
		$formErrors=array();
		
		$formData=$this->getRequest()->getPost();
		
		if($this->getRequest()->isPost() && (!empty($formData['createprice']) && $formData['createprice']=='Add Price'))
		{
		            if(!(isset($formData['country'])) || trim($formData['country'])=="")
					{
						$formErrors['country']="Please select country ";
					}
					if(!(isset($formData['language'])) || trim($formData['language'])=="")
					{
						$formErrors['language']="Please select language";
					}
					if(!(isset($formData['price'])) || trim($formData['price'])=="")
					{
						$formErrors['price']="Please select price";
					}
					
					if(count($formErrors)==0)
					{
						$priceId=0; $price_error=array();
						if($this->modelBooks->isPriceExist($formData['productid'],$formData['country'],$formData['language'],''))
						{
							//echo"<strong><font color='red'>Price already exist.</font></strong>";exit;
							$price_error[]="Price already exist.";
						}
						else if($this->modelBooks->isGroupPriceExist($formData['productid'],$formData['country'],$formData['language'],$formData['group_price'],''))
						{
							//echo"<strong><font color='red'>Price already exist.</font></strong>";exit;
							$price_error[]="Group Price already exist.";
						}
						else
						{
							if($formData['group_price']=='')
							{
								$group_price = $formData['price'];
							}
							else
							{
								$group_price = $formData['group_price'];
							}
							$priceData=array(
									'product_id'=>$formData['productid'],
									'country_id'=>$formData['country'],
									'language_id'=>$formData['language'],
									'price'=>$formData['price'],
									'group_price'=>$group_price
									 );
									 
							$modelPrice  = new Publisher_Model_DbTable_BookPrices();						
							$priceId     = $modelPrice->insert($priceData);
						}	
				
								if(empty($priceId))
								{
									$price_error[]="Price could not be saved.";
								}
	$createpriceURL = "http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/publisher/book/addprice/";
	
	?>
	<form name="form-add-price" id="form-add-price" method="post" onsubmit="return  validate_pricing_form('form-add-price','<?php echo $createpriceURL;?>');">
	  <fieldset class="dashboard_fieldset">
			<legend>Publication Store & Langauge</legend>
	  		<table width="99%"  class="table-list publisher"  border="0" cellspacing="0" cellpadding="0">
				<tr>
				<td align="center" colspan="2"><div id="priceoutput"></div></td>
				</tr>
				<tr>
				<td class="tdleftBold">Store<span class="required">*</span> : </td>
				<td>
				<?php
				$modelCountry = new Publisher_Model_DbTable_Books();
				$countryList   = $modelCountry->getCountryList();
				?>
				<select name="country" id="country" class="req" onchange="getCurrency(this.value);"  message="Please select country">
				<option value="">Select Store</option>
				
				<?php
				for($ii=0;$ii<count($countryList);$ii++)
				{
				?>
				<option value="<?php echo $countryList[$ii]['id']; ?>"><?php echo $countryList[$ii]['country']; ?></option>
				<?php
				}
				?>		   
				</select>
				
				<?php if(array_key_exists('country',$formErrors)){echo '<div class="inline-error">'.$formErrors['country'].'</div>';}?>

				</td>
				</tr>
	
				<tr>
				<td class="tdleftBold">Language<span class="required">*</span> : </td>
				<td>
				<?php
				$modelLanguage = new Publisher_Model_DbTable_Books();
				$languageList  = $modelLanguage->getLanguageList();
				?>
				<select name="language" id="language" class="req"  message="Please select language">
				<option value="">Select Language</option>
				
				<?php
				for($ii=0;$ii<count($languageList);$ii++)
				{
				?>
				<option value="<?php echo $languageList[$ii]['id']; ?>"><?php echo $languageList[$ii]['language_name']; ?></option>
				<?php
				}
				?>		   
				</select>
				
				<?php if(array_key_exists('language',$formErrors)){echo '<div class="inline-error">'.$formErrors['language'].'</div>';}?>

				</td>
				</tr>
		
				<tr>
				<td class="tdleftBold">Price<span class="required">*</span> : </td>
				<td><span id="currency_singh"></span>
				<input type="text" name="price" id="price"  class="req"  message="Please enter price"/>
				<?php if(array_key_exists('price',$formErrors)){echo '<div class="inline-error">'.$formErrors['price'].'</div>';}?>
				</td>
				</tr>
				
				<tr>
				<td class="tdleftBold">Group Price : </td>
				<td><span id="currency_singh"></span>
				<input type="text" name="group_price" id="group_price" />
				<?php if(array_key_exists('price',$formErrors)){echo '<div class="inline-error">'.$formErrors['price'].'</div>';}?>
				</td>
				</tr>
		
				<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="text-align:center;">
							<input type="hidden" name="createprice" value="Add Price">
							<input type="hidden" name="productid" id="productid" value="<?php echo $_REQUEST['productid']; ?>" />
							<input type="submit" name="createprice" id="createprice" value="Add Price" class="button-Save"/>
						</div>
						
					</td>
				</tr>

				</table>
		</fieldset>
		</form>
	
<?php	
echo "77777777777<fieldset class='dashboard_fieldset'>
			<legend>Store Price</legend><table width='100%' class='table-list' border='0' cellspacing='0' cellpadding='0'><tr>
<td width='50%'><div align='left'><strong>Store</strong></div></td>
<td width='30%'><div align='left'><strong>Language</strong></div></td>
<td width='10%'><div align='left'><strong>Price</strong></div></td>
<td width='20%'><div align='left'><strong>Group Price</strong></div></td>
<td width='10%'><div align='left'><strong>Action</strong></div></td>
</tr>";

$modelPrice    = new Publisher_Model_DbTable_BookPrices();
$priceList     = $modelPrice->getPriceList($formData['productid']);
 
	
for($jj=0;$jj<count($priceList);$jj++)
{	

$deletePriceUrl=$this->view->url(array('module'=>'publisher','controller'=>'book','action'=>'deleteprice','id'=>$priceList[$jj]['id']),'',true);

echo "<tr><td><div align='left'>";

$modelCountry    = new Publisher_Model_DbTable_Books();
$producturrency = new Admin_Model_DbTable_Countries();
$countryRecord   = $modelCountry->getCountryName($priceList[$jj]['country_id']);
echo $countryRecord['country'];

echo "</div></td><td><div align='left'>";

$modelLanguage    = new Publisher_Model_DbTable_Books();
$languageRecord   = $modelLanguage->getLanguageName($priceList[$jj]['language_id']);
echo $languageRecord['language_name'];
	
echo "</div></td><td><div align='left'>"; 

echo $producturrency->getCurrencyInfo($priceList[$jj]['country_id'])."".$priceList[$jj]['price']; 

echo "</div></td><td><div align='left'>"; 

echo $producturrency->getCurrencyInfo($priceList[$jj]['country_id']).$priceList[$jj]['group_price']; 

echo "</div></td><td><div align='left'>";
  
echo "<a class='action-icon' href='".$deletePriceUrl."' onclick='return deleteprice(this.href);' title='Delete'>";
echo "<img src='".$this->view->serverUrl().$this->view->baseUrl()."/public/css/publisher/images/trash.gif' height='10' width='10'>";
echo  "</a>&nbsp;";
?>
<a href="javascript:void(0);" onclick="return updateprice1('<?php echo $priceList[$jj]['id'];?>','<?php echo $this->view->serverUrl().$this->view->baseUrl()."/publisher/book/saveprice";?>','<?php echo $formData['productid'];?>','<?php echo $priceList[$jj]['id'];?>','<?php echo $this->view->serverUrl().$this->view->baseUrl()."/publisher/book/saveprice";?>','<?php echo $formData['productid'];?>');">
<img src="<?php echo $this->view->serverUrl().$this->view->baseUrl()."/public/css/publisher/images/icon_edit.png"?>" width="10" height="10"/>
</a>
<?php	
echo "</div></td></tr>";
}

if(!empty($price_error))
{
	foreach($price_error as $error_msg)
	{
		echo"<tr><td colspan='4'><strong><font color='red'>".$error_msg."</font></strong></td></tr>";
	}
}
echo "</table></fieldset>";

			}
}
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		exit;	
}	
	
	
	public function createAction()
	{
	   
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
	
		$formData=array();
		$formErrors=array();
		
		$formData=$this->getRequest()->getPost();
	 
		/*if($this->getRequest()->isPost() && (!empty($formData['tab_ajax']) && $formData['tab_ajax']=='book'))
		{
		   $this->_helper->layout->disableLayout();
		}*/
		
	 
		
		if($this->getRequest()->isPost() && (!empty($formData['createbook']) && $formData['createbook']=='Add Book'))
		{
			 
			$cat_info=$this->modelBooks->getCategoryInfo($formData['category']);
			//echo $cat_info['category_name'];
			if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazine')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazines')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Newspapers')))
			{
				$isbnisn = "ISSN";
			}
			if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebook')))
			{
				$isbnisn = "ISBN";
			}
			if(!empty($formData['createbook']) && $formData['createbook']=='Add Book')
			{ 
			   		if(!(isset($formData['category'])) || trim($formData['category'])=="")
					{
						$formErrors['category']="Please select category";
					}
					if(!(isset($formData['book_genre'])) || trim($formData['book_genre'])=="")
					{
						$formErrors['book_genre']="Please select book genre";
					}
					if((isset($formData['author'])) && trim($formData['author'])=="")
					{
						$formErrors['author']="Please select author";
					}
					if(trim($formData['title'])=="" && trim($formData['title_txt']) =='')
					{
						$formErrors['title']="Please enter/select title or brand";
					}
					/*if($formData['isbn_number']!='')
					{
						if(!CommonFunctions::is_isbn_13_valid($formData['isbn_number']))
						{
							$formErrors['isbn_number']="Please enter valid $isbnisn number";
						}
					}*/
					
					if((isset($formData['total_pages'])) && trim($formData['total_pages'])=="")
					{
						$formErrors['total_pages']="Please enter total pages";
					}
					else
					{
						$formData['total_pages']=0;
					}
					if(!(isset($formData['description'])) || trim($formData['description'])=="")
					{
						$formErrors['description']="Please enter description";
					}
					
					if($this->modelBooks->isExist('title="'.$formData['title'].'" and cat_id='.$formData['category'].'') && $formData['title']!='')
					{
						if(!(array_key_exists('title',$formErrors)))
						{
							$formErrors['title']="Publication already exist in this category";
						}
					}
					
					if(isset($formData['isbn_number']) && !empty($formData['isbn_number']))
					{
						//bool ctype_alpha ( string $text )
					
						//if(!(CommonFunctions::is_isbn_13_valid($formData['isbn_number'])))
						if(!(ctype_alnum($formData['isbn_number'])))
						{
							echo "fals";
							if(!(array_key_exists('isbn_number',$formErrors)))
							{
								$formErrors['isbn_number']="Please enter a valid $isbnisn number format";
							}	
						}
					}
					/*if(isset($formData['isbn_number']) && !empty($formData['isbn_number']))
					{
						if(!(CommonFunctions::is_issn_valid($formData['isbn_number'])))
						{
							if(!(array_key_exists('isbn_number',$formErrors)))
							{
								$formErrors['isbn_number']="Please enter a valid $isbnisn number format";
							}	
						}	
					}*/
					
					if(isset($formData['isbn_number']) && !empty($formData['isbn_number']))
					{
						if(strlen($formData['isbn_number']) >=1 ||  strlen($formData['isbn_number'])<=25 )
						{						
						}
						else
						{
							$cat_info=$this->modelBooks->getCategoryInfo($formData['category']);
							if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazine')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazines')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Newspapers')))
							{
								$formErrors['isbn_number']="Please enter a valid $isbnisn number format of 1 to 25 digit.";
							}							
							if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebook')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebooks')))
							{
								$formErrors['isbn_number']="Please enter a valid $isbnisn number format of 1 to 25 digit.";
							}
						}
					}
					
					if(isset($formData['isbn_number']) && !empty($formData['isbn_number']) &&  $this->modelBooks->isExist("isbn_number='$formData[isbn_number]'"))
					{
						if(!(array_key_exists('isbn_number',$formErrors)))
						{
							$formErrors['isbn_number']="$isbnisn already exist";
						}
					}
					if(isset($formData['isbn_number']) && !empty($formData['isbn_number']) &&  $this->modelBooks->isExist("isbn_number='$formData[isbn_number]'"))
					{
						if(!(array_key_exists('isbn_number',$formErrors)))
						{
							$formErrors['isbn_number']="$isbnisn already exist";
						}
					}
					
									
					if(count($formErrors)==0)
					{
						
								$publish_time=date('Y-m-d H:i:s');
								$add_time=date('Y-m-d H:i:s');
								
								$publisher_id = !empty($this->sessPublisherInfo->parent_id)?$this->sessPublisherInfo->parent_id:$this->sessPublisherInfo->id;
							
								if(isset($formData['author']) && $formData['author']=="Self")
								{
								  $author_id=$publisher_id;
								}
								elseif(isset($formData['author']) && !empty($formData['author']))
								{
								  $author_id=$formData['author'];
								}
								else
								{
									$author_id='';
								}
								
								$formData['isbn_number']=!empty($formData['isbn_number'])?$formData['isbn_number']:!empty($formData['isbn_number'])?$formData['isbn_number']:"";
								
								if($formData['title']=='')
								{
									$title = $formData['title_txt'];
								}
								else {
									$title = $formData['title'];
								}
								
								$publisherData=array(
										'product_type'=>$formData['book_genre'],
										'publisher_id'=>$publisher_id,
										'author_id'=>$author_id,
										'title'=>$title,
										'description'=>$formData['description'],
										'isbn_number'=>$formData['isbn_number'],
										'publisher'=>$formData['publisher'],
										'total_pages'=>$formData['total_pages'],
										'cat_id'=>$formData['category'],
										'status'=>1,
										'admin_approve'=>0,
										'publish_time'=>$formData['publish_time'],
										'add_time'=>$add_time
										 );
								$lastId=$this->modelBooks->insert($publisherData);							
								if($lastId>0)
								{
									$imageData=array(
										'product_id'=>$lastId,
										'image_name'=>$formData['image_name'],
										'image_name_thumb'=>$formData['image_name_thumb'],  
										'filesize'=>$formData['filesize'],
										'status'=>'1'
										 );										 
									$modelImage = new Publisher_Model_DbTable_BookImages();
									$imageId=$modelImage->insert($imageData);
									
									$Filedata=array('file_name'=>$formData['epub_file'],
												    'file_size'=>$formData['epub_file_size']);		
									$result=$this->modelBooks->update($Filedata,'id='.$lastId);  
								}
							
								if($lastId>0)
								{
								    $adminDetails = new Publisher_Model_DbTable_AdminDetails();
									$adminRecord  =$adminDetails->CurrentRow('1');
									
									$mailhost= SMTP_SERVER; 
									
									$mailconfig = array(
									'ssl' => SMTP_SSL, 
									'port' => SMTP_PORT, 
									'auth' => SMTP_AUTH, 
									'username' => SMTP_USERNAME, 
									'password' => SMTP_PASSWORD);
									
									$transport = new Zend_Mail_Transport_Smtp ($mailhost, $mailconfig);
									Zend_Mail::setDefaultTransport($transport);
								
								    	$message ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
									<html xmlns="http://www.w3.org/1999/xhtml">
									<head>
									<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
									<title>Electronic Vendor Ltd</title>
									<style type="text/css">
									body{
									margin:0;
									padding:0px;
									}
									#container{
									width:700px;
									margin:0 auto;
									}
									#header{
									width:700px;
									float:left;
									padding:40px 0 10px 0;
									font-family:Arial, Helvetica, sans-serif;
									color:#3A3B3F;
									text-align:center;
									font-size:11px;
									
									}
									#header a{
									color:#3A3B3F;
									font-weight:bold;
									text-decoration:none;
									}
									#header a:hover{
									color:#40BBE3;
									}
									#logopart
									{
									border:0px solid red;
									width:698px;
									height:140px;
									background-color:#1B75BB;
									margin-left:0px;
									}
									#content{
									width:698px;
									float:left;
									padding:0px 0px 10px 0px;
									font-family:Arial, Helvetica, sans-serif;
									color:#3A3B3F;
									border:1px solid #D6D6D6;
									font-size:12px;
									}
									#content p{
									margin:0px 20px;
									padding:0px 0 20px 0;
									font-family:Arial, Helvetica, sans-serif;
									font-size:12px;
									color:#3A3B3F;
									}
									#content p.logo{
									margin:0px;
									padding:15px 0 0 20px;
									height:77px
									}
									#content p.title{
									margin:0px;
									font-size:20px;
									font-family:Arial, Helvetica, sans-serif;
									border-bottom:3px solid #D6D6D6;
									padding:0px 0 13px 0;
									margin:25px 20px 14px 20px;
									color:#3A3B3F;
									}
									#content p a{
									color:#40BBE3;
									text-decoration:none;
									}
									#content p a:hover{
									color:#3A3B3F;
									text-decoration:underline;
									}
									#content h2{
									margin:0px;
									padding:0 0 14px 0;
									font-size:14px;
									font-family:Arial, Helvetica, sans-serif;
									font-weight:bold;
									}
									#footer{
									width:700px;
									float:left;
									}
									#footer p{
									margin:0 0 0 0;
									padding:0 0 0 0;
									font-family:Arial, Helvetica, sans-serif;
									font-size:11px;
									color:#78797E;
									}
									#footer p.disclamer{
									margin: 0 0 0 0;
									padding:16px 6px 10px 6px;
									text-align: justify;
									border-bottom:1px solid #3A3B3F;
									color:#78797E;
									}
									#footer p.notice{
									margin: 0 0 15px 0;
									padding:16px 6px 10px 6px;
									text-align: justify;
									color:#78797E;
									}
									
									</style>
									</head>
									<body>
									
									<div id="container">
									<div id="header"></div>
									<div id="content">
									
									<div id="logopart">
									<p class="logo"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'/" target="_blank"><img src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor"></a>
									</p>
									</div>
									
									<p class="title">New Book Uploaded Email</p>
									<p>A new book has been added to the site. </p>
									<p>Book :&nbsp;'.$formData['title'].'</p>
									<p>Book Type :&nbsp;'.$formData['booktype'].'</p>
									<p>Description :&nbsp;'.$formData['description'].'</p>
									<BR />
									<p>&nbsp;</p>
									
									</div>
									<div id="footer">&nbsp;</div>
									</div>
									</body>
									</html>';
									
									
									$mail = new Zend_Mail();
									$mail->addTo($adminRecord['user_email']);
									$mail->setSubject("New Book Uploaded Email");
									$mail->setBodyHtml($message);
									$mail->setFrom(SETFROM, SETNAME);
									
									
									if($mail->send())
									{
									$this->_flashMessenger->addMessage('<div class="div-success">Publication Added successfully</div>');
									} 
									else 
									{	
									$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
									}
								
								$this->_redirect('publisher/book/index/tab_ajax/book');
								}
								else
								{
								$this->view->errorMessage='<div class="div-error">Publication could not be save. Try again later.</div>';
								}
						
			       }			
			}	
				
		}
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	

	public function thanksAction()
	{
	
	}
	
	
	public function inactiveAction()
    {
 		if($this->sessPublisherInfo->user_type!='publisher') 
		{
			$this->_redirect('publisher/access/index/tab_ajax/book');
		}
		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBooks->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelBooks->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Book deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate book</div>');
			}
		}
		
		$this->_redirect('publisher/book/index/tab_ajax/book');

    }
	
	
	public function activeAction()
    {
 		if($this->sessPublisherInfo->user_type!='publisher') 
		{
			$this->_redirect('publisher/access/index/tab_ajax/book');
		}
		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBooks->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelBooks->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Book activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate book</div>');
			}
		}
		
		$this->_redirect('publisher/book/index/tab_ajax/book');
    }
	
	
	
	public function deleteAction()
    {
 		if($this->sessPublisherInfo->user_type!='publisher') 
		{
			$this->_redirect('publisher/access/index/tab_ajax/book');
		}
		
		$id=$this->_request->getParam('id',0);
		
		
		if($id>0 && $this->modelBooks->isExist($id))
		{
			/********* Delete Publication Prices **********/
			$modelPrice = new Publisher_Model_DbTable_BookPrices();
			$price_success=$modelPrice->delete('product_id='.$id);
			/********* Delete Publication Prices **********/
			
			/********* Delete Publication Images **********/
			$modelImage = new Publisher_Model_DbTable_BookImages();
			$image_success=$modelImage->delete('product_id='.$id);
			/********* Delete Publication Images **********/
			
			/********* Delete Publication ePub File  **********/
			$bookDetail=$this->modelBooks->getInfoByPublicationId($id);
			unlink(EPUB_UPLOAD_DIR.$bookDetail['file_name']);
			/********* Delete Publication ePub File **********/
			
			
			$success=$this->modelBooks->delete('id='.$id);
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Book deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete book</div>');
			}
			
		}
	
		$this->_redirect('publisher/book/index/tab_ajax/book');
	   
    }
	
	
	public function viewAction()
    {			
	    $this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$formData=array();
		$formErrors=array();
		$formDataSave=array();
				
		$id=$this->_request->getParam('id',0);
		
		$formDataSave=$this->getRequest()->getPost();
		
		
		if(!empty($formDataSave['updatebook']) && $formDataSave['updatebook']=='Edit Book')
		{
			/*echo "new world";
			die();
		 */
			if((isset($formDataSave['author'])) && trim($formDataSave['author'])=="")
			{
				$formErrors['author']="Please select author";
			}
			if(trim($formDataSave['title'])=="" && trim($formDataSave['title_text'])=='')
			{
				$formErrors['title']="Please enter/select title or brand";
			}
			if(!(isset($formDataSave['total_pages'])) || trim($formDataSave['total_pages'])=="")
			{
				$formErrors['total_pages']="Please enter total pages";
			}
			if(!(isset($formDataSave['description'])) || trim($formDataSave['description'])=="")
			{
				$formErrors['description']="Please enter description";
			}
			
			if($this->modelBooks->isExist("title='$formDataSave[title]' AND cat_id='".$formDataSave['category']."' AND id<>'$id'") && $formDataSave[title]!='')
			{
				if(!(array_key_exists('title',$formErrors)))
				{
					
					$formDataSave['title']=$formDataSave['title'];
					$formErrors['title']="Publication already exist in this category.";
				}
			}
			
			/*if($this->modelBooks->isExist("isbn_number='$formDataSave[isbn_number]' AND id<>'$id'"))
			{
				if(!(array_key_exists('isbn_number',$formErrors)))
				{
					$formData['isbn_number']=$formDataSave['isbn_number'];
					$formErrors['isbn_number']="ISBN already exist";
				}
			}
			*/
			
			if(count($formErrors)==0)
			{
				$publish_time=date('Y-m-d H:i:s');
							
				$publisher_id=$this->sessPublisherInfo->id;
			
				if(isset($formDataSave['author']) && $formDataSave['author']=="Self")
				{
				  $author_id=$publisher_id;
				}
				elseif(isset($formDataSave['author']) && !empty($formDataSave['author']))
				{
				  $author_id=$formDataSave['author'];
				}
				if($formDataSave['title']!='')
				{
					$title= $formDataSave['title'];
				}
				else {
					$title =  $formDataSave['title_text'];
				}
				
				$bookData=array(
						'author_id'=>$author_id,
						'title'=>$title,
						'description'=>$formDataSave['description'],
						'total_pages'=>$formDataSave['total_pages'],
						'publish_time'=>$formDataSave['publish_time']
						 );
				
				$result=$this->modelBooks->update($bookData,'id='.$id);
				
				$this->_flashMessenger->addMessage('<div class="div-success">Book updated successfully</div>');
				$this->_redirect('publisher/book/view/id/'.$id.'/tab_ajax/book');
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		
		
		if($id>0 && $this->modelBooks->isExist($id))
		{
			$formData=array();
			$userInfo=$this->modelBooks->fetchRow('id='.$id);
			if(isset($userInfo))			
			{
				$formData=$userInfo->toArray();	
				$formData["country"]=!empty($formData["country"])?$formData["country"]:0;
				$formData["parentPublisherId"]=$this->parentPublisherId;
				$formData['title']=!empty($formDataSave["title"])?$formDataSave["title"]:$formData['title'];
			
				$this->view->formData=$formData;
				$this->view->formErrors=$formErrors;
		   }
		}
		else
		{
			$this->_redirect('publisher/book/index/tab_ajax/book');
		}

    }
	
	
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBooks->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			$formDataSave=array();
			
			$bookInfo=$this->modelBooks->fetchRow('id='.$id);
			$formData=$bookInfo->toArray();
			$formData['book_genre']=$formData['product_type'];
							
				
			if($this->getRequest()->isPost())
			{
			  
			  $formDataSave=$this->getRequest()->getPost();
			  		  
			  if(!empty($formDataSave['updatebook']) && $formDataSave['updatebook']=='Edit Book')
			  {
			     	if(!(isset($formDataSave['category'])) || trim($formDataSave['category'])=="")
					{
						$formErrors['category']="Please select category";
					}
					if(!(isset($formDataSave['book_genre'])) || trim($formDataSave['book_genre'])=="")
					{
						$formErrors['book_genre']="Please select book type ";
					}
					if((isset($formDataSave['author'])) && trim($formDataSave['author'])=="")
					{
						$formErrors['author']="Please select author";
					}
					if(!(isset($formDataSave['title'])) || trim($formDataSave['title'])=="")
					{
						$formErrors['title']="Please enter/select title or brand";
					}
					
					/*if(isset($formDataSave['isbn_number']) && trim($formDataSave['isbn_number'])=="")
					{
						$formErrors['isbn_number']="Please enter isbn number";
					}
					elseif(isset($formDataSave['isbn_number']) && trim($formDataSave['isbn_number'])=="")
					{
						$formErrors['isbn_number']="Please enter issn number 1";
					}*/
					
					if(!(isset($formDataSave['publisher'])) || trim($formDataSave['publisher'])=="")
					{
						$formErrors['publisher']="Please enter publisher name";
					}
					if((isset($formDataSave['total_pages'])) && trim($formDataSave['total_pages'])=="")
					{
						$formErrors['total_pages']="Please enter total pages";
					}
					else
					{
						$formData['total_pages']=0;
					}
					
					if(!(isset($formDataSave['description'])) || trim($formDataSave['description'])=="")
					{
						$formErrors['description']="Please enter description";
					}
					
					if($this->modelBooks->isExist("title='$formDataSave[title]' AND id<>'$id'"))
					{
						if(!(array_key_exists('title',$formErrors)))
						{
							
							$formData['title']=$formDataSave['title'];
							$formErrors['title']="Publication already exist";
						}
					}
					$formDataSave['isbn_number'];
				
					if(empty($formDataSave['isbn_number']) || $formDataSave['isbn_number']!='')
					{
					
						 if(isset($formDataSave['isbn_number']) && !empty($formDataSave['isbn_number']))
						{
							 
							if(strlen($formDataSave['isbn_number'])==10 ||  strlen($formDataSave['isbn_number'])==13 )
							{
								 
							}
							else
							{
								$cat_info=$this->modelBooks->getCategoryInfo($formData['category']);
								if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazine')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazines')))
								{
									$formErrors['isbn_number']="Please enter a valid ISSN number format of 10 or 13 digit.";
								}
								
								if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebook')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebooks')))
								{
									$formErrors['isbn_number']="Please enter a valid ISBN number format of 10 or 13 digit.";
								}
							}				
						}
						 
						if(isset($formDataSave['isbn_number']) && !empty($formDataSave['isbn_number']) && $this->modelBooks->isExist("isbn_number='$formDataSave[isbn_number]' AND id<>'$id'"))
						{
							if(!(array_key_exists('isbn_number',$formErrors)))
							{
								$formData['isbn_number']=$formDataSave['isbn_number'];
								$formErrors['isbn_number']="ISBN already exist";
							}
						}elseif(isset($formDataSave['isbn_number']) && !empty($formDataSave['isbn_number']) && $this->modelBooks->isExist("isbn_number='$formDataSave[isbn_number]' AND id<>'$id'"))
						{
							if(!(array_key_exists('isbn_number',$formErrors)))
							{
								$formData['isbn_number']=$formDataSave['isbn_number'];
								$formErrors['isbn_number']="ISSN already exist";
							}
						}
					}
	
					if(count($formErrors)==0)
					{
						
						        $publish_time=date('Y-m-d H:i:s');
								$add_time=date('Y-m-d H:i:s');
						
								if(isset($formDataSave['author']) && $formDataSave['author']=="Self")
								{
								  $author_id=$publisher_id;
								}
								elseif(isset($formDataSave['author']) && !empty($formDataSave['author']))
								{
								  $author_id=$formDataSave['author'];
								}
								else
								{
									$author_id='';
								}
								
								$formDataSave['isbn_number']=!empty($formDataSave['isbn_number'])?$formDataSave['isbn_number']:!empty($formDataSave['isbn_number'])?$formDataSave['isbn_number']:"";
								
								
								$publisherData=array(
										'product_type'=>$formDataSave['book_genre'],
										'author_id'=>$author_id,
										'title'=>$formDataSave['title'],
										'description'=>$formDataSave['description'],
										'isbn_number'=>$formDataSave['isbn_number'],
										'publisher'=>$formDataSave['publisher'],
										'total_pages'=>$formDataSave['total_pages'],
										'cat_id'=>$formDataSave['category'],
										'publish_time'=>$formDataSave['publish_time']
										);
					    
					 
						
						$result=$this->modelBooks->update($publisherData,'id='.$id);
						
						$this->_flashMessenger->addMessage('<div class="div-success">Publication updated successfully</div>');
						$this->_redirect('publisher/book/index/tab_ajax/book');
					}
					else
					{
						$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
					}
				}	
					
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			//print_r($formData);exit;
		}
		else
		{
			$this->_redirect('publisher/book/index/tab_ajax/book');
		}
    }
	
	
	public function uploadimageAction()
    {	
	
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
		
		$BackURL =$this->view->url(array('module'=>'publisher','controller'=>'book','action'=>'view','id'=>$id),'',true);
		
		$formDataSave=$this->getRequest()->getPost();
		
		//Some Settings
		
		$ThumbSquareSize 		= 100; //Thumbnail will be 100x100
		$BigImageMaxSize 		= 600; //Image Maximum height or width
		$ThumbPrefix			= "thumb_"; //Normal thumb Prefix
		$ThumbPrefix1			= "thumb1_"; //Normal thumb Prefix
		$ThumbPrefix2			= "thumb2_"; //Normal thumb Prefix
		$DestinationDirectory	= USER_UPLOAD_DIR; //Upload Directory ends with / (slash)
		$Quality 				= 90;
			
		if(!isset($_FILES['ImageFile']) || !is_uploaded_file($_FILES['ImageFile']['tmp_name']))
		{
		  //echo "<font color='red'>Please select image</font>";
		  /* echo "<script>alert('Please select image');</script>";*/
		$this->_flashMessenger->addMessage('<div class="div-error">Please select image!</div>');
		echo $BackURL;
		exit;
		}
		
		// Random number for both file, will be added after image name
		$RandomNumber 	= rand(0, 9999999999); 
			

		$ImageName 		= str_replace(' ','-',strtolower($_FILES['ImageFile']['name'])); 
		$ImageSize 		= $_FILES['ImageFile']['size']; 
		$TempSrc	 	= $_FILES['ImageFile']['tmp_name']; 
		$ImageType	 	= $_FILES['ImageFile']['type']; 
			
		//Let's use $ImageType variable to check wheather uploaded file is supported.
		//We use PHP SWITCH statement to check valid image format, PHP SWITCH is similar to IF/ELSE statements 
		//suitable if we want to compare the a variable with many different values
		switch(strtolower($ImageType))
		{
		case 'image/png':
		$CreatedImage =  imagecreatefrompng($_FILES['ImageFile']['tmp_name']);
		break;
		case 'image/gif':
		$CreatedImage =  imagecreatefromgif($_FILES['ImageFile']['tmp_name']);
		break;			
		case 'image/jpeg':
		case 'image/pjpeg':
		$CreatedImage = imagecreatefromjpeg($_FILES['ImageFile']['tmp_name']);
		break;
		default:
		 //echo "<font color='red'>Unsupported File!</font>";
	/*	 echo "<script>alert('Unsupported File!');</script>";*/
		 $this->_flashMessenger->addMessage('<div class="div-error">Invalid File Type!</div>');
		echo $BackURL;
		 exit;
		}
			
		//PHP getimagesize() function returns height-width from image file stored in PHP tmp folder.
		//Let's get first two values from image, width and height. list assign values to $CurWidth,$CurHeight
		list($CurWidth,$CurHeight)=getimagesize($TempSrc);
		//if($CurWidth<315 || $CurWidth>325 || $CurHeight<440 || $CurHeight>450)
		if($CurWidth<312 || $CurHeight<439)
		{
			$this->_flashMessenger->addMessage('<div class="div-error">Invalid Image Dimension!<br /> Acceptable Width: Minimum 312<br />
			Acceptable Height: Minimum 439<br />
			</div>');
			echo $BackURL;
			exit;
		}
		
		//Get file extension from Image name, this will be re-added after random name
		$ImageExt = substr($ImageName, strrpos($ImageName, '.'));
		$ImageExt = str_replace('.','',$ImageExt);
			
		//remove extension from filename
		$ImageName 		= preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName); 
		
		//Construct a new image name (with random number added) for our new image.
		$NewImageName = $ImageName.'-'.$RandomNumber.'.'.$ImageExt;
		//set the Destination Image
		$thumb_DestRandImageName 	= $DestinationDirectory.$ThumbPrefix.$NewImageName; //Thumb name
		$thumb1_DestRandImageName 	= $DestinationDirectory.$ThumbPrefix1.$NewImageName; //Thumb name
		$DestRandImageName 			= $DestinationDirectory.$NewImageName; //Name for Big Image
			
			//Resize image to our Specified Size by calling resizeImage function.
	if($this->modelBooks->resizeImage($CurWidth,$CurHeight,$BigImageMaxSize,$DestRandImageName,$CreatedImage,$Quality,$ImageType,312,439))
	{
		if(!$this->modelBooks->cropImage($CurWidth,$CurHeight,$ThumbSquareSize,$thumb_DestRandImageName,$CreatedImage,$Quality,$ImageType,130,160))
		{
			$this->_flashMessenger->addMessage('<div class="div-error">Error Creating thumbnail for sliders!</div>');
			echo $BackURL;
			exit;
		}
		
		if(!$this->modelBooks->cropImage($CurWidth,$CurHeight,$ThumbSquareSize,$thumb1_DestRandImageName,$CreatedImage,$Quality,$ImageType,130,160))
		{
			$this->_flashMessenger->addMessage('<div class="div-error">Error Creating thumbnail for catalogue and search pages!</div>');
			echo $BackURL;
			exit;
		}
		
		/*
		At this point we have succesfully resized and created thumbnail image
		We can render image to user's browser or store information in the database
		For demo, we are going to output results on browser.
		*/
		
		//Get New Image Size
		list($ResizedWidth,$ResizedHeight)=getimagesize($DestRandImageName);
			
		/*echo $ThumbPrefix.$NewImageName;
		echo "<br>";
		echo $NewImageName;*/
		if($formDataSave['productid']=='')
		{
			echo $NewImageName."~".$ThumbPrefix.$NewImageName."~".$ImageSize;
			exit;
		}
		$imageData=array(
				'product_id'=>$formDataSave['productid'],
				'image_name'=>$NewImageName,
				'image_name_thumb'=>$ThumbPrefix.$NewImageName,  
				'filesize'=>$ImageSize,
				'status'=>'1'
				 );
				 
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$imageId=$modelImage->insert($imageData);
		
		if($imageId>0)
		{
		//echo "<font color='green'>Image Uploaded Successfully</font>";
		//echo "<BR><BR>";
		//echo "<img src='".$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$ThumbPrefix.$NewImageName."' height='50' width='50'>"; 
		
		$this->_flashMessenger->addMessage('<div class="div-success">Image Uploaded Successfully!</div>');
		echo$BackURL;
		exit;
		}                              
		/*
		// Insert info into database table!
		mysql_query("INSERT INTO myImageTable (ImageName, ThumbName, ImgPath)
		VALUES ($DestRandImageName, $thumb_DestRandImageName, 'uploads/')");
		*/
		}
		else
		{
		 //echo "<font color='red'>Resize Error!</font>";
		/* echo "<script>alert('Resize Error!');</script>";*/
		$this->_flashMessenger->addMessage('<div class="div-error">Resize Error!</div>');
		echo$BackURL;
		exit;
		}
			
	}
	
	public function deleteimageAction()
    {
	
		$this->view->messages = $this->_flashMessenger->getMessages();
			
		$id=$this->_request->getParam('id',0);
		$viewid=$this->_request->getParam('viewid',0);
		
				
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$imageDetail=$modelImage->getImageInfo($id);
		
		unlink(USER_UPLOAD_DIR.$imageDetail['image_name']);
		unlink(USER_UPLOAD_DIR.$imageDetail['image_name_thumb']);
		unlink(USER_UPLOAD_DIR.'thumb1_'.$imageDetail['image_name']);
	
		if($id>0 && $modelImage->isExist($id))
		{
			$success=$modelImage->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Image deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete image</div>');
			}
			
		}
		$this->_redirect('publisher/book/view/id/'.$viewid.'/tab_ajax/book');
			
	}
	
	
	public function deletepriceAction()
    {
    	$producturrency = new Admin_Model_DbTable_Countries();
		$id=$this->_request->getParam('id',0);
		
		$modelPrice = new Publisher_Model_DbTable_BookPrices();
		$currentrecord=$modelPrice->CurrentRow($id);
		
		if($id>0 && $modelPrice->isExist($id))
		{
			$success=$modelPrice->delete('id='.$id);
			
			if($success)
			{	
								
echo "<table width='100%' class='table-list' border='0' cellspacing='0' cellpadding='0'><tr>
<td width='20%'><div align='left'><strong>Store</strong></div></td>
<td width='20%'><div align='left'><strong>Language</strong></div></td>
<td width='10%'><div align='left'><strong>Price</strong></div></td>
<td width='20%'><div align='left'><strong>Group Price</strong></div></td>
<td width='10%'><div align='left'><strong>Action</strong></div></td>
</tr>";

$modelPrice    = new Publisher_Model_DbTable_BookPrices();
$priceList     = $modelPrice->getPriceList($currentrecord['product_id']);
	
for($jj=0;$jj<count($priceList);$jj++)
{	
$deletePriceUrl=$this->view->url(array('module'=>'publisher','controller'=>'book','action'=>'deleteprice','id'=>$priceList[$jj]['id']),'',true);

echo "<tr><td><div align='left'>";

$modelCountry    = new Publisher_Model_DbTable_Books();
$countryRecord   = $modelCountry->getCountryName($priceList[$jj]['country_id']);
echo $countryRecord['country'];

echo "</div></td><td><div align='left'>";

$modelLanguage    = new Publisher_Model_DbTable_Books();
$languageRecord   = $modelLanguage->getLanguageName($priceList[$jj]['language_id']);
echo $languageRecord['language_name'];
	
echo "</div></td><td><div align='left'>"; 

echo $producturrency->getCurrencyInfo($priceList[$jj]['country_id'])."".$priceList[$jj]['price']; 

echo "</div></td><td><div align='left'>"; 

echo $producturrency->getCurrencyInfo($priceList[$jj]['country_id'])."".$priceList[$jj]['group_price']; 

echo "</div></td><td><div align='left'>";
  
echo "<a class='action-icon' href='".$deletePriceUrl."' onclick='return deleteprice(this.href);' title='Delete'>";
echo "<img src='".$this->view->serverUrl().$this->view->baseUrl()."/public/css/publisher/images/trash.gif' height='10' width='10'>";


echo  "</a>&nbsp;";
?>
<a href="javascript:void(0);" onclick="return updateprice1('<?php echo $priceList[$jj]['id'];?>','<?php echo $this->view->serverUrl().$this->view->baseUrl()."/publisher/book/saveprice";?>','<?php echo $currentrecord['product_id'];?>');"'<?php echo $priceList[$jj]['id'];?>','<?php echo $this->view->serverUrl().$this->view->baseUrl()."/publisher/book/saveprice";?>','<?php echo $formData['id'];?>');">
<img src="<?php echo $this->view->serverUrl().$this->view->baseUrl()."/public/css/publisher/images/icon_edit.png"?>" width="10" height="10"/>
</a>
<?php 
echo "</div></td></tr>";
}
echo "</table>";
				
				
				exit;
			}
			else
			{
				echo "<font color='red'><strong>Sorry!, unable to delete price</strong></font>";
				exit;
			}
			
		}		
	}

	
	public function uploadepubfileAction()
    {	
	    $this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
		$BackURL =$this->view->url(array('module'=>'publisher','controller'=>'book','action'=>'view','id'=>$id),'',true);
			
		$formDataSave=$this->getRequest()->getPost();
		
		//Some Settings
	
		$FilePrefix			    = "epub_"; //Normal thumb Prefix
		$DestinationDirectory	= EPUB_UPLOAD_DIR; //Upload Directory ends with / (slash)
			
		if(!isset($_FILES['ImageEFile']))
		{
		   /*echo "<script>alert('Please select epub file');</script>";*/
		   //echo "<font color='red'>Please select epub file</font>";
		   $this->_flashMessenger->addMessage('<div class="div-error">Please select epub file!</div>');
		   echo $BackURL;
		   exit;
		} 
		 
	
		$RandomNumber 	= rand(0, 9999999999); 
		
		$FileName 		= str_replace(' ','-',strtolower($_FILES['ImageEFile']['name'])); 
		$FileSize 		= $_FILES['ImageEFile']['size']; 
		$FileSrc	 	= $_FILES['ImageEFile']['tmp_name']; 
		$FileType	 	= $_FILES['ImageEFile']['type']; 

		$max_filesize_in_mib = min((int)(ini_get('upload_max_filesize')), (int)(ini_get('post_max_size')), (int)(ini_get('memory_limit')));
		
		if($FileSize >= 1048576)
		{
			$filesizenew = number_format($FileSize / 1048576, 2);
		}
		
		if(!is_uploaded_file($_FILES['ImageEFile']['tmp_name']) && $filesizenew > $max_filesize_in_mib)
		{
			 $this->_flashMessenger->addMessage('<div class="div-error">You can only upload upto '.$max_filesize_in_mib.' MB</div>');
			echo $BackURL;
			exit;
		
		}
		/*echo ">>>>>".$FileType;
		die(); */
		//$FileType=="application/octet-stream" || $FileType=="application/epub" || $FileType=="application/pdf" || $FileType=="application/epub+zip" || $FileType=="application/x-zip-compressed") && $_FILES['ImageEFile']['error']==0
		if($FileSrc!='')
		{
			//Get file extension from Image name, this will be re-added after random name
			$FileExt = substr($FileName, strrpos($FileName, '.'));
			$FileExt = str_replace('.','',$FileExt);
			
			if($FileExt!="epub" || $FileExt!="pdf")
			{
			  
			}
			else {
				  //echo "<font color='red'>Invalid EPub File Extension!</font>";
			   /* echo "<script>alert('Invalid EPub File Extension!');</script>";*/
			   $this->_flashMessenger->addMessage('<div class="div-error">Invalid EPub File Extension!</div>');
			   echo $BackURL;
		       exit;
			}
			//remove extension from filename
		 	$FileName 		= preg_replace("/\\.[^.\\s]{3,4}$/", "", $FileName); 
		
		}
		else
		{
		   	//echo "<font color='red'>File type not supported!</font>";
		    /*echo "<script>alert('Unsupported EPub File!');</script>";*/
			
		    $this->_flashMessenger->addMessage('<div class="div-error">File type not supported!</div>');
			echo $BackURL;
			exit;
		}    
	
	    $NewFileName = $FileName.'-'.$RandomNumber.'.'.$FileExt;
		$targetFile  = EPUB_UPLOAD_DIR.$FilePrefix.$NewFileName;
		
		if(move_uploaded_file($FileSrc,$targetFile))
		{	
			 
			if($FileSize >= 1048576)
			{
				$filesizenew = number_format($FileSize / 1048576, 2).' Mb';
			}
			elseif($FileSize >= 1024)
			{
			$filesizenew = number_format($FileSize / 1024, 2).' Kb';
			}
			else
			{
				$filesizenew = $FileSize." Bytes";
			} 			 
			if($formDataSave['productid'] == '')
			{
				echo $FilePrefix.$NewFileName."~".$filesizenew;
				exit;
			}
			else 
			{
				$Filedata=array('file_name'=>$FilePrefix.$NewFileName,
							    'file_size'=>$filesizenew);		
				$result=$this->modelBooks->update($Filedata,'id='.$formDataSave['productid']);  
			}			
			if($result>0)
			{
			//echo "<font color='green'>EPub File Uploaded Successfully</font>";
			//echo "<BR><BR>";
			$this->_flashMessenger->addMessage('<div class="div-success">File Uploaded Successfully!</div>');
			}        
			echo $BackURL;
			exit;
		}
		else
		{
			$this->_flashMessenger->addMessage('<div class="div-error">File could not be uploaded!</div>');
			echo$BackURL;
			exit;
		}
	}
	
	public function deleteepubfileAction()
    {
	
		$id=$this->_request->getParam('id',0);
		$viewid=$this->_request->getParam('viewid',0);
	
		$bookDetail=$this->modelBooks->getInfoByPublicationId($id);
		
		unlink(EPUB_UPLOAD_DIR.$bookDetail['file_name']);
	
		if($id>0 && $this->modelBooks->isExist($id))
		{
		   
		    $Filedata=array('file_name'=>'',
						    'file_size'=>'');
		
			$result=$this->modelBooks->update($Filedata,'id='.$id);  
			
			if($result)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Epub file deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete epub file</div>');
			}
			
		}
	
		$this->_redirect('publisher/book/view/id/'.$viewid.'/tab_ajax/book');
			
	}
	
	public function downloadpubfileAction()
    {
		    $this->_helper->layout->disableLayout();
			
			$id=$this->_request->getParam('id',0);
			
			$bookDetail=$this->modelBooks->getInfoByPublicationId($id);
			
			if($id>0 && $this->modelBooks->isExist($id))
			{
			  
					$filename = EPUB_UPLOAD_DIR.$bookDetail['file_name']; 
					
					header('Pragma: public');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Cache-Control: private', false);
					header('Content-Type: application/epub');
					header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
					header('Content-Transfer-Encoding: binary');
					header('Content-Length: ' . filesize($filename));
					readfile($filename);
					exit;
					
					
					/****** Also you can alternatively use this code *************/
					/*
					$path = EPUB_UPLOAD_DIR; 
					$fullPath = $path.basename($bookDetail['file_name']);
					if(is_readable($fullPath))
					{
						$fsize = filesize($fullPath);
						$path_parts = pathinfo($fullPath);
						$ext = strtolower($path_parts["extension"]);
						switch($ext) 
						{
							case "pdf":
							case "epub":
							header("Content-type: application/pdf");
							header("Content-type: application/epub");							
							//add here more headers for diff. extensions
							header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
							break;
							default;
							header("Content-type: application/octet-stream");
							header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
						}
						header("Content-length: $fsize");
						header("Cache-control: private"); //use this to open files directly
						readfile($fullPath);
						exit;
					}
					else 
					{
						die("Invalid request:[$fullPath]");
					}
					*/
					/****** Also you can alternatively use this code *************/
			}
	$this->_redirect('publisher/book/index/tab_ajax/book');	
				
	}
	
	public function gettitlebrandAction()
    {		
		$brand_id=$this->_request->getParam('title_brand',0);
		$show_author = false;
		if(!empty($brand_id))
		{
			$title_brand=$brand_id;
		}
		else
		{
			$title_brand="";
		}
		
		if(empty($author_id))
		{
			$author_id="";
		}
		
		
		if($this->getRequest()->isPost())
		{
	    	$post_data=$this->getRequest()->getPost();
			
			if(isset($post_data['cat_id']) && !empty($post_data['cat_id']))
			{
				$cat_info=$this->modelBooks->getCategoryInfo($post_data['cat_id']);
				
				$titleBrandDropdown=$this->modelBooks->getTitleBrandList('',$this->parentPublisherId,$post_data['cat_id']);
				
				 
				 //echo "Count brand : - ".$titleBrandDropdown."<br>";
			 
					 
					$titleBrandDropdown=$titleBrandDropdown;
					
					$author_label='';
					$author_dropdown='';
					
					$page_label='';
					$page_field='';
					
					if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazine')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Magazines')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Newspapers')))
					{
						$isbn_label='ISSN Number<span class="required">*</span>: ';
						$isbn_field='<input type="text" name="isbn_number" id="isbn_number" value=""  class="req issn"  message="Please enter ISSN number" invalidmessage="Please enter a valid ISSN number format." maxlength="25" /><br /><br />';
						$isbn_field='<input type="text" name="isbn_number" id="isbn_number" value=""  class="req issn"  message="Please enter ISSN number" invalidmessage="Please enter a valid ISSN number format." maxlength="25"/><br /><br />';
					}
					else if(strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebook')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('Ebooks')))
					{
						$show_author = true;
						
						$author_label='Author<span class="required">*</span>: ';
						$page_label='Total Pages<span class="required">*</span> : ';
						$page_field='<input type="text" name="total_pages" id="total_pages" value=""  class="req number"  message="Please enter total pages" invalidmessage="Please enter numeric value" maxlength="4" /><br /><br />';
					
						$isbn_label='ISBN Number:<span class="required">*</span>';
						$isbn_field='<input type="text" name="isbn_number" id="isbn_number" value="" message="Please enter ISBN number" invalidmessage="Please enter a valid ISBN number format."  class="req isbn" maxlength="25"/><br /><br />';
					
					
						$authorList=$this->modelBooks->getAuthorList($this->parentPublisherId);
						$author_dropdown='';
						$author_dropdown.='<select name="author" id="author" class="req"  message="Please select author">
						<option value="">Select Author</option>';
						for($ii=0;$ii<count($authorList);$ii++)
						{
							if($author_id==$authorList[$ii]['id'])
							{
								$selected="selected";
							}
							else
							{
								$selected="";
							}
							$author_dropdown.='<option value="'.$authorList[$ii]['id'].'" '.$selected.'>'.$authorList[$ii]['first_name'].'&nbsp;'.$authorList[$ii]['last_name'].'</option>';
						}
						$author_dropdown.='</select>';
					}					
					else					
					{
						$isbn_label='';
						$isbn_field='';
					}
					echo $titleBrandDropdown.'^*^*hide_author^*^*'.$author_label.'^*^*'.$author_dropdown.'^*^*'.$page_label.'^*^*'.$page_field.'^*^*'.$isbn_label.'^*^*'.$isbn_field.'^*^*'.$show_author;
					
				/*}
				else 
				{
					 
					$author_label='Author<span class="required">*</span> : ';
					$page_label='Total Pages<span class="required">*</span> : ';
					$page_field='<input type="text" name="total_pages" id="total_pages" value=""  class="req number"  message="Please enter total pages" invalidmessage="Please enter numeric value" maxlength="4" /><br /><br />';
					
					$isbn_label='ISBN Number<span class="required">*</span> : ';
					$isbn_field='<input type="text" name="isbn_number" id="isbn_number" value=""  class="req isbn"  message="Please enter isbn number" invalidmessage="Please enter a valid ISBN number format." maxlength="13"/><br /><br />';
					
					
					$authorList=$this->modelBooks->getAuthorList($this->parentPublisherId);
					$author_dropdown='';
					$author_dropdown.='<select name="author" id="author" class="req"  message="Please select author">
					<option value="">Select Author</option>';
					for($ii=0;$ii<count($authorList);$ii++)
					{
						if($author_id==$authorList[$ii]['id'])
						{
							$selected="selected";
						}
						else
						{
							$selected="";
						}
					$author_dropdown.='<option value="'.$authorList[$ii]['id'].'" '.$selected.'>'.$authorList[$ii]['first_name'].'&nbsp;'.$authorList[$ii]['last_name'].'</option>';
					}
					$author_dropdown.='</select>';
					
					echo '<input type="text" name="title" id="title" value="'.$title_brand.'"  class="req"  message="Please enter title"/>'.'^*^*show_author^*^*'.$author_label.'^*^*'.$author_dropdown.'^*^*'.$page_label.'^*^*'.$page_field.'^*^*'.$isbn_label.'^*^*'.$isbn_field;
				}*/
				exit;
			}
			else
			{
				echo '<input type="text" name="title" id="title" value="'.$title_brand.'"  class="req"  message="Please enter title"/>'.'^*^*show_author';
				exit;
			}
		}
		else
		{
			echo '<input type="text" name="title" id="title" value="'.$title_brand.'"  class="req"  message="Please enter title"/>'.'^*^*show_author';
			exit;
		}
	}
	public function getcurrencyAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$postData =$this->getRequest()->getPost();
		$this->currencyObj = new Admin_Model_DbTable_Currency();
		$this->countryObj = new Admin_Model_DbTable_Countries();
		$currencyData = $this->countryObj->fetchRow("id='".$postData['country_id']."'");		
		$currency_data = $this->currencyObj->fetchRow("currency_id='".$currencyData['currency_id']."'");
		$data = $currency_data['currency_sign'];
		echo $data;
		exit;
	}
	
}

