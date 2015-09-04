<?php
class CheckoutController extends Zend_Controller_Action
{
	var $sessPublisherInfo=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelCatalogue=null;
	var $modelBooks=null;
	
    public function init()
    {
        /*********** To check for publisher session data *********************/
       	$producturrency = new Admin_Model_DbTable_Countries();
		$storage_publisher = new Zend_Auth_Storage_Session('publisher_type');
		$publisher_data = $storage_publisher->read();
		
		$storage_company = new Zend_Auth_Storage_Session('company_type');
		$company_data = $storage_company->read();
		
		$storage = new Zend_Auth_Storage_Session('account_type');		
        $userSessData = $storage->read();
		if(!$userSessData || !$company_data)
		{
           // $this->_redirect('/');
        }		
		if($publisher_data && $publisher_data!=null)
		{
			$this->modelPublisher = new Model_DbTable_Users();
			$this->sessPublisherInfo = $this->modelPublisher->getInfoByUserId($publisher_data->id);
			
			$this->view->sessPublisherInfo=$this->sessPublisherInfo;
		}
		elseif($company_data && $company_data!=null)
		{
			$this->modelCompany = new Model_DbTable_Companies();
			$this->sessCompanyInfo = $this->modelCompany->getInfoByUserId($company_data->id);			
			$this->view->sessCompanyInfo = $this->sessCompanyInfo;
		}
		elseif($userSessData && $userSessData!=null)
		{
			$this->modelCompanies = new Company_Model_DbTable_Companies();
			$this->sessUserInfo=$this->modelCompanies->getInfoByCompanyId($userSessData->id);
			$this->view->sessUserInfo =$this->sessUserInfo;
		}		
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$title=$this->_getParam('title');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		$this->view->currentTitle=$title;
		$this->view->class_active='class="active"';
		/************* To Set The active section and links *******************/
		
		$this->view->headScript()->appendFile($this->view->serverUrl().$this->view->baseUrl().'/public/css/default/js/banner_and_slider.js');
		
		
		/******************************************************************/
		//$storeId=$this->getRequest()->getCookie('sid');
		//$storeName=$this->getRequest()->getCookie('sname');
		
		
		
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
		else
		{
			//$modelCountry = new Admin_Model_DbTable_Countries();
			//$country_info = $modelCountry->fetchRow('country="Nigeria"');
			//$this->_redirect('/auth/storechange/storeid/'.$country_info->id);
			//$this->_redirect('/auth/storechange/');
		}	
	}
	public function indexAction()
    {	
	 
	}
	public function cartAction()
    {
    	$tempObj = new Model_DbTable_Checkout();  
    	$formdata =$this->getRequest()->getPost();	
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$storage_company = new Zend_Auth_Storage_Session('company_type');	
		$company_data = $storage_company->read();			
		$storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
		if($data->id)
		{
			$user_id = $data->id;
		}
		elseif($company_data->id)
		{
			$user_id = $company_data->id;
		}
		
		if($company_data->account_type == '1')
		{
			$groupObj = new Company_Model_DbTable_Groups();
			$groupList = $groupObj->getGroupList($company_data->id);
		}
		if(count($groupList)>0)
		{
			$group_id = $groupList[0]['id'];
		}
		else {
			$group_id = 0;
		}	
		$sessionid = session_id();
		if($this->getRequest()->isPost())
		{
			$product_id = $formdata['product_id'];
			//$user_id = $formdata['user_id'];
			$user_type = $formdata['user_type'];
			$store_id = $formdata['store_id'];
			
			$getallData = $tempObj->fetchAll("session_id='".$sessionid."' and product_id='".$product_id."'",'id');
			$quntity = $formdata['quantity'];			
			$purchasedBook = $creditHistoryObj->fetchAll("bookid ='".$product_id."' and userid='".$user_id."'");
			if($quntity=='')
			{
				$quntity ='1';
			}
			else {
				$quntity =$quntity;
			}
			if(count($purchasedBook)>0)
			{
				$is_purchase = 1;
			}
			else
			{
				$is_purchase = 0;
			}
			if($group_id!='0')
			{
				$this->modelCompany = new Company_Model_DbTable_Companies();
				$listGroupMember = $this->modelCompany->getMemberByGroupId($group_id);
				$quntity = count($listGroupMember);				
			}			
			if(count($getallData) == 0)
			{
				$data_array = array("product_id"=>$product_id,"store_id"=>$store_id,"user_id"=>$user_id,"user_type"=>$user_type,"session_id"=>$sessionid,"quntity"=>$quntity,"group_id"=>$group_id,"is_purchase"=>$is_purchase);
				$tempObj->insert($data_array);
			}			
			
		}
		$this->view->sesid=$sessionid;
		
	}
	public function cartajaxAction()
	{
		//$this->view->messages = $this->_flashMessenger->getMessages();
		
		$storage_company = new Zend_Auth_Storage_Session('company_type');	
		$company_data = $storage_company->read();
			
		$storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
		if($data->id)
		{
			$user_id = $data->id;
		}
		elseif($company_data->id)
		{
			$user_id = $company_data->id;
		}
		
		$this->_helper->layout->disableLayout();
		$producturrency = new Admin_Model_DbTable_Countries();
		$tempObj = new Model_DbTable_Checkout();  
		$modelAuthor = new Publisher_Model_DbTable_Publishers();
		$modelLanguage = new Admin_Model_DbTable_Languages();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$modelStore = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		
		$formdata =$this->getRequest()->getPost();
		$sessionid = $formdata['sessionid'];
		$id = $formdata['id'];
		$action_type = $formdata['action'];
		$quantity = $formdata['quantity'];
		$grId = $formdata['grId'];
		if($company_data->account_type == '1')
		{
			$groupObj = new Company_Model_DbTable_Groups();
			$groupList = $groupObj->getGroupList($company_data->id);
		}		
		if($action_type=='delete' && $id!='')
		{
			$tempObj->delete("id='".$id."'");
		}
		if($action_type=='increasedecrease' && $id!='' && $quantity!='')
		{
			$array_data = array("quntity"=>$quantity);
			$tempObj->update($array_data,"id='".$id."'");
		}
		if($action_type=='includegroupmember' && $id!='' && $grId!='')
		{
			$this->modelCompany = new Company_Model_DbTable_Companies();
			$listGroupMember = $this->modelCompany->getMemberByGroupId($grId);
			$quantity = count($listGroupMember);
			$array_data = array("quntity"=>$quantity,"group_id"=>$grId);
			$tempObj->update($array_data,"id='".$id."'");
		}		
		$getallData = $tempObj->fetchAll("session_id='".$sessionid."' and user_id='".$user_id."'");
		//$this->view->tempOrderData = $getallData;
		$datahistory='';
		$datahistory="<div class='top_heading'>
		    	<div class='item'>Item(s)</div>
		        <div class='space'>&nbsp;</div>
		        <div class='prices'>Price</div>
				<div class='quantity'>";
				if($company_data->account_type == '1')
				{				
					$datahistory.="Quantity";
				}
				else
				{
					$datahistory.="&nbsp;";
				}
				$datahistory.="</div>";
				$datahistory.="<div class='total'>Total</div>
				</div>";
		$total_price = '';
		if(count($getallData)>0)
		{			
		    foreach($getallData as $temdataHistory)
			{
				//echo $temdataHistory->product_id;
				$productPriceInfo = $productPrice->getPriceByStoreId($temdataHistory->product_id,$temdataHistory->store_id);
				
				if($company_data->account_type == '1')
				{
					$productPriceInfo['price'] = $productPriceInfo['group_price'];
				}
				else {
					$productPriceInfo['price'] = $productPriceInfo['price'];
				}
				$product_details = $this->modelBooks->fetchRow("id='".$temdataHistory->product_id."'");
				$catInfo=$this->modelBooks->getCategoryInfo($product_details['cat_id']);
				$product_details['title'];
				$publisherInfo = $modelAuthor->getInfoByPublisherId($product_details['publisher_id']);
				$getBrandInfo=$this->modelBooks->getBrandInfo($product_details['title']);
				if(!empty($getBrandInfo) && is_numeric($this->$product_details['title']))
				{
					$titleBrand=$getBrandInfo['brand'];
				}
				else
				{
					$titleBrand=$product_details['title'];
				}
				if(!empty($product_details['parent_brand_id']) && $product_details['cat_id']!='3')
				{
					$productInfo=$this->modelBooks->fetchRow('id="'.$product_details['parent_brand_id'].'"');
					$getParentBrandInfo=$this->modelBooks->getBrandInfo($productInfo->title);
					if(!empty($getParentBrandInfo))
					{
						//$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
						$titleBrand=$getParentBrandInfo['brand']." - <em class='brndtitle'>".$titleBrand."</em>";
					}				
				}
				$getCurrencyName = $producturrency->getCurrencyCode($temdataHistory->store_id);				
				$authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
				$imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
				//$langDetail=$modelLanguage->fetchRow('id="'.$product_details['language_id'].'"');
				//$storeDetail=$modelStore->fetchRow('id="'.$product_details['country_id'].'"');
				$titleBrand=$titleBrand;
				$datahistory.='<div class="cart_detail">
		    	<div class="item_content">
		   	    	<img src="'.$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.'thumb1_'.$imageInfo['image_name'].'" width="140" height="175" alt="">
		            <h5>'.stripslashes($titleBrand).'</h5>';
					if($authorInfo['first_name']!='')
					{
						$datahistory.='<span><em>by: </em> '.stripslashes($authorInfo['first_name'].'&nbsp;'.$authorInfo['last_name']).'</span>';
					}
					$datahistory.='<span><em>Category: </em> '.stripslashes($catInfo['category_name']).'</span>
		            <span><em>Publisher</em>: '.$publisherInfo['publisher'].'</span>
		            <div class="remove_bt"><a href="javascript:void(0);" onclick=removeProduct("'.$temdataHistory->id.'","'.$sessionid.'");><img src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/remove_botton.png" width="90" height="25" alt=""></a></div>
		        </div>
		        <div class="space_content">';
				if(count($groupList)>0)
				{
					$drop ="<select name='group_id' onchange=getGroupmem(this.value,'".$temdataHistory->id."','".$sessionid."');>";
					if($temdataHistory->group_id =='0')
					{
						
						//$drop.="<option value='0' selected >individual</option>";
					}
					else{
						//$drop.="<option value='0' >individual</option>";
					}
					
					foreach($groupList as $group)
					{
						if($group->id == $temdataHistory->group_id)
						{
							$drop.="<option value='".$group->id."' selected>".$group->group_name."</option>";
						}
						else{
							$drop.="<option value='".$group->id."'>".$group->group_name."</option>";
						}
						
					}				       
				   $drop.="</select>";
				}
				if(count($groupList)>0)
				{
					//$drop.="<script type='text/javascript'>getGroupmem('".$groupList[0]['id']."','".$temdataHistory->id."','".$sessionid."');</script>";
				}
		       $datahistory.= $drop;  
		      $datahistory.='</div>';
			  if($temdataHistory->is_purchase ==1 && $company_data->account_type != '1')
			  {
					$datahistory.='<div class="prices_content">You have already purchased.</div>';
			  }
			  else
			  {
				$datahistory.='<div class="prices_content">&#x20a6;'.@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$productPriceInfo['price'])).'</div>';
			  }
				if($company_data->account_type == '1')
				{
					$datahistory.='<div class="quantity_content">
		        	<div class="quantity_count">
		            	<div class="mius"><a href="javascript:void(0);" onclick=increaseDecreaseQuantity("decrease","'.$temdataHistory->id.'","'.$sessionid.'");><img src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/minus_icon.png" width="15" height="15" alt=""></a></div>
		                <div class="inputbox"><input type="text" id="item_quantity'.$temdataHistory->id.'" name="item_quantity'.$temdataHistory->id.'" value="'.$temdataHistory->quntity.'" onchange=cangeItemVal(this.value,"'.$temdataHistory->id.'","'.$sessionid.'"); /></div>
		                <div class="plus"><a href="javascript:void(0);" onclick=increaseDecreaseQuantity("increase","'.$temdataHistory->id.'","'.$sessionid.'");><img src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/plus_icon.png" width="15" height="15" alt=""></a></div>
		            </div>
		        </div>';
				}
				else
				{
					$datahistory.="<div class='without_count'>&nbsp;</div>";
				}
			 if($temdataHistory->is_purchase ==1 && $company_data->account_type != '1')
			  {
			  }
			  else
			  {
		        $datahistory.='<div class="total_content">&#x20a6;'.@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$temdataHistory->quntity*$productPriceInfo['price']),4).'</div>    
		 		</div>';
				}
				if( $company_data->account_type == '1')
				{
					$total_price = $total_price + $producturrency->currencyconverter($getCurrencyName,"NGN",$temdataHistory->quntity*$productPriceInfo['price']);
				}
				else
				{
					if($temdataHistory->is_purchase!=1)
					{
						$total_price = $total_price + $producturrency->currencyconverter($getCurrencyName,"NGN",$temdataHistory->quntity*$productPriceInfo['price']);
					}
				}
			}
		}
		$totalDesc ='<div class="shipping_total">
					<div class="row">
				    	<div class="lt">Total</div>
				        <div class="rt">&#x20a6;'.@number_format($total_price,4).'</div>
				    </div>
				    <div class="row" style="border:none;">
				    	<div class="lt">Tax</div>
				        <div class="rt">&#x20a6;'.'00.00</div>
				    </div>
				    
				    <div class="row subtotal">
				    	<div class="lt">Subtotal</div>
				        <div class="rt">&#x20a6;'.@number_format($total_price,4).'</div>
				    </div>
					<div class="check_terms">
					<input type="checkbox" name="terms_condition" id="terms_condition" value="1" > I agree to the <a href="'.$this->view->serverUrl().$this->view->baseUrl().'/page/index/title/terms-and-conditions" target="_blank">Terms & Conditions</a>
					</div>
					<div>
					Payment option
						<select name="paymentOption" id="paymentOption" onchange="getPaymentOp(this.value);" >
						<option value="" >Select One</option>
						<option value="visa" >Visa</option>
						<option value="master" >MasterCard</option>
						</select>
					</div>
				   
				    <div class="row" style="border:none;">
				    <input type="hidden"  name="user_id"  id="user_id"  value="'.$user_id.'" >
				  				    <input type="hidden"  name="total_price"  id="total_price"  value="'.$total_price.'" >
									
				  				    <input type="hidden"  name="sess_id"  id="sess_id"  value="'.$sessionid.'" >';
				  				    
				if(count($groupList)<=0 && $company_data->account_type == '1')
				{
					$totalDesc .='<input type="image" value="image" name="button" onclick="return getNotGroupMessage()"  src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/checkout_bt.png" width="274" height="49" alt="">';
				}
				elseif($total_price=='' || $total_price==0) {
					$totalDesc .='<input type="image" value="image" name="button" onclick="return getNotPriceMessage()"  src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/checkout_bt.png" width="274" height="49" alt="">';
				}
				else{
					$totalDesc .='<input type="image" value="image" name="submit"  src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/checkout_bt.png" width="274" height="49" alt="">';
				}
				    
				  $totalDesc .=' </div>				   
				</div>'	;
		echo $datahistory."####".$totalDesc;
		exit;
	}
	public function procedcheckoutAction()
	{
		$tempObj = new Model_DbTable_Checkout(); 	
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelAuthor = new Publisher_Model_DbTable_Publishers();
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$modelStore = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$groupSubsObj = new Company_Model_DbTable_GroupSubscriptions();
		//$price = 1000;
		
		$formdata =$this->getRequest()->getPost();
		$price = $formdata['total_price'];
		$sessid = $formdata['sess_id'];
	//	echo "<pre>";
		//print_r($formdata);
		//exit;
		
		$user_id = $formdata['user_id'];
		if (isset($price) && $price!=0){
			$price = $price * 100; // multiply the price by 100 because TWPG deals price in kobo.
			$xml = "<?xml version='1.0' encoding='UTF-8'?>
					<TKKPG>
					<Request>
					<Operation>CreateOrder</Operation>
					<Language>EN</Language>
					<Order>
					<Merchant>EVENDOR</Merchant>
					<Amount>".$price."</Amount>
					<Currency>566</Currency>
					<Description>Payment for test</Description>
					<ApproveURL>".$this->view->serverUrl().$this->view->baseUrl()."/checkout/approved/</ApproveURL>
					<CancelURL>".$this->view->serverUrl().$this->view->baseUrl()."/checkout/declined/</CancelURL>
					<DeclineURL>".$this->view->serverUrl().$this->view->baseUrl()."/checkout/declined/</DeclineURL>
					</Order>
					</Request>
					</TKKPG>";
			$ch = curl_init(); //this is to initialize curl
			curl_setopt($ch, CURLOPT_URL,"https://196.46.20.36:5443/Exec"); 
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			curl_setopt($ch, CURLOPT_VERBOSE, '1');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CAINFO,  getcwd().'/public/paymentgateway/cert/CAcert.crt');
			curl_setopt($ch, CURLOPT_SSLCERT, getcwd().'/public/paymentgateway/cert/myshop.pem');
			curl_setopt($ch, CURLOPT_SSLKEY, getcwd().'/public/paymentgateway/cert/myshop.key');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);			
			$response = curl_exec($ch); // this line post to our server	
				//echo "<pre>";
				//print_r($response);
				//exit;
			//echo htmlentities($response); //use this check the response sent by our PG at every point
			//exit;
			
			if(!(curl_errno($ch)>0)){    //$ch>=0 shows error while $ch<=0 shows no error
	
				$parsedxml = simplexml_load_string($response);
	
				foreach($parsedxml->children() as $RESPONSENODE)
	 			{	
				 	foreach($RESPONSENODE->children() as $ORDERNODE)
	  				{
						foreach($ORDERNODE->children() as $child)
						{	
							if ($child->getName() == "OrderID")
								$orderid = $child;
								 
							if ($child->getName() == "SessionID")
								$sessionid = $child;
	
							if ($child->getName() == "URL")
								$url = $child;	
						}
					}	
	  			 }//end all loop
	
	  			 $gateway_url = $url."?ORDERID=".$orderid."&SESSIONID=".$sessionid;
	
	  			 /*
	  			 *
					THE ABOVE FORMED URL ($gateway_url) IS THE URL USED TO 
					CALL THE PAYMENT GATEWAY....
					YOU CAN USE THIS URL IN THE SOURCE (src) OF AN IFRAME.
					E.G  
					<iframe src= "<?php echo $gateway_url ?>" frameborder="0" scrolling="no"></iframe>
	  			 *
	  			 */
	  			if($orderid!='' && $sessionid!='')
				{
					$array_order_status = array("order_id"=>$orderid,"order_sessionid"=>$sessionid);
					$tempObj->update($array_order_status,'user_id="'.$user_id.'" and session_id="'.$sessid.'"');
				}
				$storage_company = new Zend_Auth_Storage_Session('company_type');	
				$company_data = $storage_company->read();
				if($company_data->account_type == '1')
				{
					$tempData = $tempObj->fetchAll("order_id='".$orderid."' and session_id='".$sessid."'");
				}
				else
				{
					$tempData = $tempObj->fetchAll("order_id='".$orderid."' and is_purchase!='1' and session_id='".$sessid."'");
				}
				
				$order_details = "<table cellpadding='1'><tr><td colspan='3'>Order Id:".$orderId."</td></tr><tr><td>Item</td><td>Price</td><td>Quantity</td></tr>";
			foreach($tempData as $dataDet)
			{
				$productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'],$dataDet['store_id']);
				if($dataDet['group_id']!='' && $dataDet['group_id']!=0)
				{
					$dateTime= date('Y-m-d H:i');
					$price = $productPriceInfo['group_price'];
					$arrayDataGroup = array("publication_id"=>$dataDet['product_id'],"group_id"=>$dataDet['group_id'],"company_id"=>$dataDet['user_id'],"assigned_date"=>$dateTime);
					$groupSubsObj->insert($arrayDataGroup);
					
				}
				else {
					$price = $productPriceInfo['price'];
				}
				$orderSt = 0;
				$product_details = $this->modelBooks->fetchRow("id='".$dataDet['product_id']."'");
				$getBrandInfo=$this->modelBooks->getBrandInfo($product_details['title']);
				if(!empty($getBrandInfo) && is_numeric($this->$product_details['title']))
				{
					$titleBrand=$getBrandInfo['brand'];
				}
				else
				{
					$titleBrand=$product_details['title'];
				}
				if(!empty($product_details['parent_brand_id']) && $product_details['cat_id']!='3')
				{
					$productInfo=$this->modelBooks->fetchRow('id="'.$product_details['parent_brand_id'].'"');
					$getParentBrandInfo=$this->modelBooks->getBrandInfo($productInfo->title);
					if(!empty($getParentBrandInfo))
					{
						//$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
						$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
					}				
				}
				$getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);				
				$authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
				$imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
				
				$tempDatInsert = array();
				$tempDatInsert['userid'] = $dataDet['user_id'];
				$tempDatInsert['bookid'] = $dataDet['product_id'];
				$tempDatInsert['store_id'] = $dataDet['store_id'];
				$tempDatInsert['price'] = $price;
				$tempDatInsert['userid'] = $dataDet['user_id'];
				$tempDatInsert['quantity'] = $dataDet['quntity'];
				$tempDatInsert['book_name'] = $titleBrand;
				$tempDatInsert['add_date'] = date('Y-m-d H:i:s');
				$tempDatInsert['group_id'] = $dataDet['group_id'];
				$tempDatInsert['order_id'] = $orderid;
				$tempDatInsert['payment_status'] = $orderSt;			
				$creditHistoryObj->insert($tempDatInsert);	
			}
			//echo $gateway_url;
			//exit;
	  		header("location: ".$gateway_url);
		}
		else
		{
			echo curl_error($ch);				
			exit;
		}
		exit;
		}
		else
			{
			$this->_redirect('/checkout/cart/');	
		}
	}
	public function approvedAction()
	{
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelAuthor = new Publisher_Model_DbTable_Publishers();
		$tempObj = new Model_DbTable_Checkout(); 
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$modelStore = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$groupSubsObj = new Company_Model_DbTable_GroupSubscriptions();
		$response = $_POST['xmlmsg'];
		$xml_string = $_POST['xmlmsg'];
		$parsedxml = simplexml_load_string(stripslashes($response));	
	
		$formDataTransApproved = array();	
		foreach($parsedxml as $RESPONSENODE)
		{
			//echo $RESPONSENODE."<br/>";
			if($RESPONSENODE->getName() == 'OrderID')
			{
				$orderId = $RESPONSENODE;
				$orderIdForPurchase = $RESPONSENODE;
			}	
			if($RESPONSENODE->getName() == 'TransactionType')
			{
				$transactionType = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'PAN')
			{
				$pan = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'PurchaseAmountScr')
			{
				$purchaseAmount = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'TranDateTime')
			{
				$tranDateTime = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ResponseCode')
			{
				$responseCode = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ResponseDescription')
			{
				$responseDescription = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'OrderStatusScr')
			{
				$orderStatus = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ApprovalCode')
			{
				$approvalCode = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'MerchantTranID')
			{
				$merchantTranId = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'OrderDescription')
			{
				$orderDescription = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ApprovalCodeScr')
			{
				$approvalCodeScr = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'CurrencyScr')
			{
				$currency = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ThreeDSVerificaion')
			{
				$threeDsVerification = $RESPONSENODE;
			}	
			if($RESPONSENODE->getName() == 'Brand')
			{
				$brandname = $RESPONSENODE;
			}	
			if($RESPONSENODE->getName() == 'Name')
			{
				$card_holder_name = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ThreeDSStatus')
			{
				$ThreeDSStatus = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'MerchantTranID')
			{
				$MerchantTranID = $RESPONSENODE;
			}
			
		}
		$formDataTransApproved = array(	"orderId"=>$orderId,
										"transactionType"=>$transactionType,
										"pan"=>$pan,
										"purchaseAmount"=>$purchaseAmount,
										"tranDateTime"=>$tranDateTime,
										"responseCode"=>$responseCode,
										"responseDescription"=>$responseDescription,
										"orderStatus"=>$orderStatus,
										"approvalCode"=>$approvalCode,
										"approvalCode"=>$approvalCode,
										"merchantTranId"=>$merchantTranId,
										"orderDescription"=>$orderDescription,
										"approvalCodeScr"=>$approvalCodeScr,
										"currency"=>$currency,
										"brand"=>$brandname,
										"card_holder_name"=>$card_holder_name,
										"ThreeDSStatus"=>$ThreeDSStatus,
										"threeDsVerification"=>$threeDsVerification,
										"transaction_xml"=>$xml_string);		
		
		$inserted_id = $transactionHistoryObj->insert($formDataTransApproved);
		
		
		##############  update table ###########################
		if($orderStatus=='APPROVED')
		{
			$orderSt = 1;
		}	
		else {
			$orderSt = 0;
		}				
		$tempTransactionUpdate = array("payment_status"=>$orderSt,"transaction_id"=>$inserted_id);
		//$tempObj->update($tempTransactionUpdate,'user_id="'.$user_id.'"');					
		$creditHistoryObj->update($tempTransactionUpdate,"order_id='".$orderId."'");
		##########################################################
			
		$tempData = $tempObj->fetchAll("order_id='".$orderId."' and is_purchase!='1'");
		$order_details = "<table cellpadding='1'><tr><td colspan='3'>Your reference Id</td></tr><tr><td colspan='3'>Order ID:".$orderId."</td></tr><tr><td colspan='3'>Transaction ID:".$inserted_id."</td></tr><tr><td>Item</td><td>Price</td><td>Quantity</td></tr>";
		foreach($tempData as $dataDet)
		{
			$productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'],$dataDet['store_id']);
			if($dataDet['group_id']!='' && $dataDet['group_id']!=0)
			{
				$dateTime= date('Y-m-d H:i');
				$price = $productPriceInfo['group_price'];
				$arrayDataGroup = array("publication_id"=>$dataDet['product_id'],"group_id"=>$dataDet['group_id'],"company_id"=>$dataDet['user_id'],"assigned_date"=>$dateTime);
				$groupSubsObj->insert($arrayDataGroup);
				
			}
			else {
				$price = $productPriceInfo['price'];
			}			
			$product_details = $this->modelBooks->fetchRow("id='".$dataDet['product_id']."'");
			$getBrandInfo=$this->modelBooks->getBrandInfo($product_details['title']);
			if(!empty($getBrandInfo) && is_numeric($this->$product_details['title']))
			{
				$titleBrand=$getBrandInfo['brand'];
			}
			else
			{
				$titleBrand=$product_details['title'];
			}
			if(!empty($product_details['parent_brand_id']) && $product_details['cat_id']!='3')
			{
				$productInfo=$this->modelBooks->fetchRow('id="'.$product_details['parent_brand_id'].'"');
				$getParentBrandInfo=$this->modelBooks->getBrandInfo($productInfo->title);
				if(!empty($getParentBrandInfo))
				{
					//$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
					$titleBrand=$getParentBrandInfo['brand']." - <span class='brndtitle'>".$titleBrand."</span>";
				}				
			}
			$getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);				
			$authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
			$imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
			
			$tempDatInsert = array();
			$tempDatInsert['userid'] = $dataDet['user_id'];
			$tempDatInsert['bookid'] = $dataDet['product_id'];
			$tempDatInsert['store_id'] = $dataDet['store_id'];
			$tempDatInsert['price'] = $price;
			$tempDatInsert['userid'] = $dataDet['user_id'];
			$tempDatInsert['quantity'] = $dataDet['quntity'];
			$tempDatInsert['book_name'] = $titleBrand;
			$tempDatInsert['transaction_id'] = $inserted_id;
			$tempDatInsert['add_date'] = date('Y-m-d H:i:s');
			$tempDatInsert['group_id'] = $dataDet['group_id'];
			$tempDatInsert['order_id'] = $dataDet['order_id'];
			$tempDatInsert['payment_status'] = $orderSt;			
			//$creditHistoryObj->insert($tempDatInsert);	
			
		
			
			$total_price = $total_price + ($dataDet['quntity']*$price);		
			
			$sql1="update pclive_products set best_seller=best_seller+1 where id=".$dataDet['product_id']."";
			$data_update = array("best_seller"=>"best_seller+1");
			$result1 = $this->modelBooks->getAdapter()->query($sql1);
			if($dataDet['is_purchase']!='1')
			{
				$quntity_price = $dataDet['quntity']*$price;
				$order_details.= "<tr><td>".$titleBrand."</td><td>".@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$price),4)."</td><td>".$dataDet['quntity']."</td></tr><tr><td colspan='3' align='left'>Sub Total: &#x20a6;".$producturrency->currencyconverter($getCurrencyName,"NGN",$quntity_price)."</td></tr>";
			}
			
		}
		$order_details.="<tr><td colspan='3'>Total: ".@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$total_price))."</td></tr>";
		$order_details.="</table>";
		###########################################  mail action ############################
		$userCompanyObj = new Company_Model_DbTable_Companies();
		$storage_company = new Zend_Auth_Storage_Session('company_type');	
		$company_data = $storage_company->read();			
		$storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
		if($data->id)
		{
			$user_id = $data->id;
		}
		elseif($company_data->id)
		{
			$user_id = $company_data->id;
		}
		$user_details = $userCompanyObj->fetchRow("id='".$user_id."'");
		
		$mailhost= SMTP_SERVER; 
		$mailconfig = array(
		'ssl' => SMTP_SSL, 
		'port' => SMTP_PORT, 
		'auth' => SMTP_AUTH, 
		'username' => SMTP_USERNAME, 
		'password' => SMTP_PASSWORD);		
		$transport = new Zend_Mail_Transport_Smtp ($mailhost, $mailconfig);
		Zend_Mail::setDefaultTransport($transport);									
		$message='<!DOCTYPE html>
				<html>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width,initial-scale=1.0">
				<title>Evendor</title>
				</head>
				<body style="font-family: \'Calibri\', arial;">
				<div id="container" style="width:80%; margin:0 auto;">
				<header id="heder">
				<div style="padding:15px 0px; background:#000000;"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'/" target="_blank"><img src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor"></a></div>
				</header>
				<div style="background:#656565; padding:15px; min-height:200px;">
					<aside> 
						Hi '.$user_details['first_name'].' '.$user_details['last_name'].',<br/><br/>
						Your order has been successfully completed.Please check your order details.<br/><br/>
						'.$order_details.'<br/>Transaction message:'.$responseDescription.'
						<br/>Masked PAN:'.$pan.'<br/>Card Holder Name:'.$card_holder_name.'
						<br/>Transaction Date & Time:'.$tranDateTime.'<br/>
						
						<br/>Transactions reference number(MerchantTranID):'.$MerchantTranID.'<br/>
						<br/>Transaction Amount:'.$purchaseAmount.'<br/>
						<br/>Transaction Currency:'.$currency.'<br/>
						<br/>Authorization Code:'.$approvalCodeScr.'<br/>
						<br/>Merchant Name:Evendor<br/>
						<br/>Site Url:'.$this->view->serverUrl().$this->view->baseUrl().'<br/>
						<br/><br/><br/>
						Thank You<br/>
						Evendor 
					</aside>
				</div>

				<div style="background:#000000;  text-align:center; color:#FFFFFF; font-size:14px; padding:15px;">
				<br>
				&copy; Copyright '.date("Y").' All Rights Reserved By Electronic Vendor Ltd.
				</div>

				</div>
				</body>
				</html>';
		$mail = new Zend_Mail();
		$mail->addTo($user_details['user_email']);
		$mail->setSubject("Order Approved message.");
		$mail->setBodyHtml($message);
		$mail->setFrom(SETFROM, SETNAME);		
		$mail->send();
		
		$tempObj->delete('order_id="'.$orderIdForPurchase.'"');
		$this->_redirect('/checkout/displayorder/orderid/'.$orderIdForPurchase);
		//exit;
	}
	public function declinedAction()
	{
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelAuthor = new Publisher_Model_DbTable_Publishers();
		$tempObj = new Model_DbTable_Checkout(); 
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$modelStore = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$response = $_POST['xmlmsg'];
		$xml_string = $_POST['xmlmsg'];
		$parsedxml = simplexml_load_string(stripslashes($response));
		
		$formDataTransDeclined = array();	
		foreach($parsedxml as $RESPONSENODE)
		{
			if($RESPONSENODE->getName() == 'OrderID')
			{
				$orderId = $RESPONSENODE;
				$orderIdForPurchase = $RESPONSENODE;
			}	
			if($RESPONSENODE->getName() == 'TransactionType')
			{
				$transactionType = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'PAN')
			{
				$pan = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'PurchaseAmountScr')
			{
				$purchaseAmount = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'TranDateTime')
			{
				$tranDateTime = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ResponseCode')
			{
				$responseCode = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ResponseDescription')
			{
				$responseDescription = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'OrderStatusScr')
			{
				$orderStatus = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ApprovalCode')
			{
				$approvalCode = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'MerchantTranID')
			{
				$merchantTranId = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'OrderDescription')
			{
				$orderDescription = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ApprovalCodeScr')
			{
				$approvalCodeScr = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'CurrencyScr')
			{
				$currency = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ThreeDSVerificaion')
			{
				$threeDsVerification = $RESPONSENODE;
			}	
			if($RESPONSENODE->getName() == 'Brand')
			{
				$brandname = $RESPONSENODE;
			}	
			if($RESPONSENODE->getName() == 'Name')
			{
				$card_holder_name = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'ThreeDSStatus')
			{
				$ThreeDSStatus = $RESPONSENODE;
			}
			if($RESPONSENODE->getName() == 'MerchantTranID')
			{
				$MerchantTranID = $RESPONSENODE;
			}
		}
		$formDataTransDeclined = array(	"orderId"=>$orderId,
										"transactionType"=>$transactionType,
										"pan"=>$pan,
										"purchaseAmount"=>$purchaseAmount,
										"tranDateTime"=>$tranDateTime,
										"responseCode"=>$responseCode,
										"responseDescription"=>$responseDescription,
										"orderStatus"=>$orderStatus,
										"approvalCode"=>$approvalCode,
										"approvalCode"=>$approvalCode,
										"merchantTranId"=>$merchantTranId,
										"orderDescription"=>$orderDescription,
										"approvalCodeScr"=>$approvalCodeScr,
										"currency"=>$currency,
										"brand"=>$brandname,
										"card_holder_name"=>$card_holder_name,
										"ThreeDSStatus"=>$ThreeDSStatus,
										"threeDsVerification"=>$threeDsVerification,
										"transaction_xml"=>$xml_string);
		//$dataTrans = array("orderId"=>)		
		$inserted_id = $transactionHistoryObj->insert($formDataTransDeclined);
		
		##############  update table ###########################
		if($orderStatus=='DECLINED')
		{
			$orderSt = 2;
		}	
		else {
			$orderSt = 0;
		}			
		$tempTransactionUpdate = array("payment_status"=>$orderSt,"transaction_id"=>$inserted_id);
		//$tempObj->update($tempTransactionUpdate,'user_id="'.$user_id.'"');					
		$creditHistoryObj->update($tempTransactionUpdate,"order_id='".$orderId."'");
	
		##########################################################
		$tempData = $tempObj->fetchAll("order_id='".$orderIdForPurchase."' and is_purchase!='1'");
		$order_details = "<table cellpadding='1'><tr><td colspan='3'>Your reference Id</td></tr><tr><td colspan='3'>Order Id:".$orderId."</td></tr><tr><td colspan='3'>Transaction ID:".$inserted_id."</td></tr><tr><td>Item</td><td>Price</td><td>Quantity</td></tr>";
		foreach($tempData as $dataDet)
		{
			$productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'],$dataDet['store_id']);
			if($dataDet['group_id']!='')
			{
				$price = $productPriceInfo['group_price'];
			}
			else {
				$price = $productPriceInfo['price'];
			}
			$getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);	
			$product_details = $this->modelBooks->fetchRow("id='".$dataDet['product_id']."'");
			$getBrandInfo=$this->modelBooks->getBrandInfo($product_details['title']);
			if(!empty($getBrandInfo) && is_numeric($this->$product_details['title']))
			{
				$titleBrand=$getBrandInfo['brand'];
			}
			else
			{
				$titleBrand=$product_details['title'];
			}
			if(!empty($product_details['parent_brand_id']) && $product_details['cat_id']!='3')
			{
				$productInfo=$this->modelBooks->fetchRow('id="'.$product_details['parent_brand_id'].'"');
				$getParentBrandInfo=$this->modelBooks->getBrandInfo($productInfo->title);
				if(!empty($getParentBrandInfo))
				{
					//$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
					$titleBrand=$getParentBrandInfo['brand']." - <em class='brndtitle'>".$titleBrand."</em>";
				}				
			}	
			$total_price = $total_price + ($dataDet['quntity']*$price);		
			$tempDatInsert = array();
			$tempDatInsert['userid'] = $dataDet['user_id'];
			$tempDatInsert['bookid'] = $dataDet['product_id'];
			$tempDatInsert['store_id'] = $dataDet['store_id'];
			$tempDatInsert['price'] = $price;
			$tempDatInsert['userid'] = $dataDet['user_id'];
			$tempDatInsert['quantity'] = $dataDet['quntity'];
			$tempDatInsert['book_name'] = $titleBrand;
			$tempDatInsert['transaction_id'] = $inserted_id;
			$tempDatInsert['add_date'] = date('Y-m-d H:i:s');
			$tempDatInsert['group_id'] = $dataDet['group_id'];
			$tempDatInsert['order_id'] = $dataDet['order_id'];	
			$tempDatInsert['payment_status'] = $orderSt;
			//$creditHistoryObj->insert($tempDatInsert);	
			
			$quntity_price = $dataDet['quntity']*$price;
			
			$order_details.= "<tr><td>".$titleBrand."</td><td>".@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$price),4)."</td><td>".$dataDet['quntity']."</td></tr><tr><td colspan='3' align='left'>Sub Total: ".$producturrency->currencyconverter($getCurrencyName,"NGN",$quntity_price)."</td></tr>";
			
		}
		$order_details.="<tr><td colspan='3'>Total Price:".@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$total_price),4)."</td>";
		$order_details.="</table>";
		###########################################  mail action ############################
		$userCompanyObj = new Company_Model_DbTable_Companies();
		$storage_company = new Zend_Auth_Storage_Session('company_type');	
		$company_data = $storage_company->read();			
		$storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
		if($data->id)
		{
			$user_id = $data->id;
		}
		elseif($company_data->id)
		{
			$user_id = $company_data->id;
		}
		$user_details = $userCompanyObj->fetchRow("id='".$user_id."'");
		
		$mailhost= SMTP_SERVER; 
		$mailconfig = array(
		'ssl' => SMTP_SSL, 
		'port' => SMTP_PORT, 
		'auth' => SMTP_AUTH, 
		'username' => SMTP_USERNAME, 
		'password' => SMTP_PASSWORD);		
		$transport = new Zend_Mail_Transport_Smtp ($mailhost, $mailconfig);
		Zend_Mail::setDefaultTransport($transport);									
		$message='<!DOCTYPE html>
				<html>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width,initial-scale=1.0">
				<title>Evendor</title>
				</head>
				<body style="font-family: \'Calibri\', arial;">
				<div id="container" style="width:80%; margin:0 auto;">
				<header id="heder">
				<div style="padding:15px 0px; background:#000000;"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'/" target="_blank"><img src="'.$this->view->serverUrl().$this->view->baseUrl().'/public/css/default/images/logo.png" style="border:none;" alt="E-Vendor"></a></div>
				</header>
				<div style="background:#656565; padding:15px; min-height:200px;">
					<aside> 
						Hi '.$user_details['first_name'].' '.$user_details['last_name'].',<br/>
						Your order has been Declined.Please check your order details.<br/><br/>
						'.$order_details.'<br/>Transaction message:'.$responseDescription.'
						<br/>Masked PAN:'.$pan.'<br/>Card Holder Name:'.$card_holder_name.'						
						<br/>Transaction Date & Time:'.$tranDateTime.'<br/>
						<br/>Transactions reference number(MerchantTranID):'.$MerchantTranID.'<br/>
						<br/>Transaction Amount:&#x20a6;'.$purchaseAmount.'<br/>
						<br/>Transaction Currency:'.$currency.'<br/>
						<br/>Authorization Code:'.$approvalCodeScr.'<br/>
						<br/>Merchant Name:Evendor<br/>
						<br/>Site Url:'.$this->view->serverUrl().$this->view->baseUrl().'<br/>
						<br/><br/><br/>
						Thank You<br/>
						Evendor 
					</aside>
				</div>

				<div style="background:#000000;  text-align:center; color:#FFFFFF; font-size:14px; padding:15px;">
				<br>
				&copy; Copyright '.date("Y").' All Rights Reserved By Electronic Vendor Ltd.
				</div>

				</div>
				</body>
				</html>';
		$mail = new Zend_Mail();
		$mail->addTo($user_details['user_email']);
		$mail->setSubject("Order Declined message");
		$mail->setBodyHtml($message);
		$mail->setFrom(SETFROM, SETNAME);		
		$mail->send();		
		$tempObj->delete('order_id="'.$orderIdForPurchase.'"');
		$this->_redirect('/checkout/displayorder/orderid/'.$orderIdForPurchase);
	}

	function displayorderAction()
	{
		##########################   data ###########################
		$orderIdForPurchase=$this->_getParam('orderid');
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelAuthor = new Publisher_Model_DbTable_Publishers();
		$tempObj = new Model_DbTable_Checkout(); 
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$modelStore = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$storage_company = new Zend_Auth_Storage_Session('company_type');	
		$company_data = $storage_company->read();			
		$storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
		if($data->id)
		{
			$user_id = $data->id;
		}
		elseif($company_data->id)
		{
			$user_id = $company_data->id;
		}
		
		if($company_data->account_type == '1')
		{
			$groupObj = new Company_Model_DbTable_Groups();
			$groupList = $groupObj->getGroupList($company_data->id);
		}
		$datahistory="<div class='top_heading'>				
		    	<div class='item'>Item(s)</div>
		        <div class='space'>&nbsp;</div>
		        <div class='prices'>Price</div>";
				if($company_data->account_type == '1')
				{
				
					$datahistory.="<div class='quantity'>Quantity</div>";
				}
				else
				{
					$datahistory.="<div class='quantity'>&nbsp;</div>";
				}
		        $datahistory.="<div class='total'>Total</div>
		    </div>";
			
		$orderData = $creditHistoryObj->fetchAll("order_id='".$orderIdForPurchase."'");
		$dataDet ='';
		foreach($orderData as $dataDet)
		{
			$price = $dataDet['price'];			
			$product_details = $this->modelBooks->fetchRow("id='".$dataDet['bookid']."'");
			$transaction_details = $transactionHistoryObj->fetchRow("id='".$dataDet['transaction_id']."'");
			$catInfo=$this->modelBooks->getCategoryInfo($product_details['cat_id']);
			$getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);				
			$authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
			$imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
			$total_price = $total_price + $producturrency->currencyconverter($getCurrencyName,"NGN",$dataDet['quantity']*$price);	
			if($dataDet['payment_status']==1)
			{
				$orderStatus = 'Approved';
			}
			elseif($dataDet['payment_status']==2)
			{
				$orderStatus = 'Declined';
			}
			else {
				$orderStatus = 'Pending';
			}			
			$datahistory.='<div class="cart_detail">
		    	<div class="item_content">
		   	    	<img src="'.$this->view->serverUrl().$this->view->baseUrl().'/'.USER_UPLOAD_DIR.'thumb1_'.$imageInfo['image_name'].'" width="140" height="175" alt="">
		            <h5>'.stripslashes($dataDet['book_name']).'</h5>';
					if($authorInfo['first_name']!='')
					{
						$datahistory.='<span><em>by: </em> '.stripslashes($authorInfo['first_name'].'&nbsp;'.$authorInfo['last_name']).'</span>';
					}
					$datahistory.='<span><em>Category: </em> '.stripslashes($catInfo['category_name']).'</span>
					<span><em>publisher:</em> '.stripslashes($product_details['publisher']).'</span>
		           </div>       <div class="space_content">'; 
			 $datahistory.='</div>
		        <div class="prices_content">&#x20a6;'.$producturrency->currencyconverter($getCurrencyName,"NGN",$price).'</div>
		        <div style="float: left;padding: 5% 0;text-align: center;width: 109px;">
		        	<div class="">';
					if($dataDet['group_id']!=0)
					{
		                $datahistory.='<div >'.$dataDet['quantity'].'</div>';
					}
		           $datahistory.='</div>
		        </div>
		        <div class="total_content">&#x20a6;'.@number_format($producturrency->currencyconverter($getCurrencyName,"NGN",$dataDet['quantity']*$price),4).'</div>    
		 		</div>';
		}
		$datahistory.='<div class="shipping_total">
					<div class="row">
				    	<div class="lt">Subtotal</div>
				        <div class="rt">&#x20a6;'.@number_format($total_price,4).'</div>
				    </div>
				 		    
				    <div class="row" style="border:none;">
				    	<div class="lt">Tax</div>
				        <div class="rt">&#x20a6;'.'00.00</div>
				    </div>
				    
				    <div class="row subtotal">
				    	<div class="lt">Total</div>
				        <div class="rt">&#x20a6;'.@number_format($total_price,4).'</div>
				    </div>	
				      <div class="row subtotal">
				    	<div class="lt">Order Id</div>
				        <div class="rt">'.$orderIdForPurchase.'</div>
				    </div>
				      <div class="row subtotal">
				    	<div class="lt">Payment Status</div>
				        <div class="rt">'.$orderStatus.'</div>
				    </div>		   
				</div>'	;
		$this->view->datahistory = $datahistory;
		$this->view->orderId = $orderIdForPurchase;
		$this->view->transactionId = $orderData[0]['transaction_id'];
		$this->view->orderStatus = $orderStatus;
		$this->view->responseDescription = $transaction_details['responseDescription'];
	}
	public function freepurchaseAction()
	{
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelAuthor = new Publisher_Model_DbTable_Publishers();
		$tempObj = new Model_DbTable_Checkout(); 
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$modelImage = new Publisher_Model_DbTable_BookImages();
		$modelStore = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$orderId =time();		
		$formDataTransApproved = array(	"orderId"=>$orderId,
										"transactionType"=>$transactionType,
										"pan"=>$pan,
										"purchaseAmount"=>$purchaseAmount,
										"tranDateTime"=>$tranDateTime,
										"responseCode"=>$responseCode,
										"responseDescription"=>$responseDescription,
										"orderStatus"=>$orderStatus,
										"approvalCode"=>$approvalCode,
										"approvalCode"=>$approvalCode,
										"merchantTranId"=>$merchantTranId,
										"orderDescription"=>$orderDescription,
										"approvalCodeScr"=>$approvalCodeScr,
										"currency"=>$currency,
										"threeDsVerification"=>$threeDsVerification);		
		
		$inserted_id = $transactionHistoryObj->insert($formDataTransApproved);
		$tempData = $tempObj->fetchAll("order_id='".$orderIdForPurchase."'");
		foreach($tempData as $dataDet)
		{
			$productPriceInfo = $productPrice->getPriceByStoreId($dataDet['product_id'],$dataDet['store_id']);
			if($dataDet['group_id']!='')
			{
				$price = $productPriceInfo['group_price'];
			}
			else {
				$price = $productPriceInfo['price'];
			}
			if($orderStatus=='APPROVED')
			{
				$orderSt = 1;
			}	
			else {
				$orderSt = 0;
			}	
			$product_details = $this->modelBooks->fetchRow("id='".$dataDet['product_id']."'");
			$getBrandInfo=$this->modelBooks->getBrandInfo($product_details['title']);
			if(!empty($getBrandInfo) && is_numeric($this->$product_details['title']))
			{
				$titleBrand=$getBrandInfo['brand'];
			}
			else
			{
				$titleBrand=$product_details['title'];
			}
			if(!empty($product_details['parent_brand_id']) && $product_details['cat_id']!='3')
			{
				$productInfo=$this->modelBooks->fetchRow('id="'.$product_details['parent_brand_id'].'"');
				$getParentBrandInfo=$this->modelBooks->getBrandInfo($productInfo->title);
				if(!empty($getParentBrandInfo))
				{
					$titleBrand= $getParentBrandInfo['brand'] ." - ".$titleBrand;
				}				
			}
			$getCurrencyName = $producturrency->getCurrencyCode($dataDet['store_id']);				
			$authorInfo = $modelAuthor->getInfoByPublisherId($product_details['author_id']);
			$imageInfo = $modelImage->getImageInfoByProductId($product_details['id']);
			
			$tempDatInsert = array();
			$tempDatInsert['userid'] = $dataDet['user_id'];
			$tempDatInsert['bookid'] = $dataDet['product_id'];
			$tempDatInsert['store_id'] = $dataDet['store_id'];
			$tempDatInsert['price'] = $price;
			$tempDatInsert['userid'] = $dataDet['user_id'];
			$tempDatInsert['quantity'] = $dataDet['quntity'];
			$tempDatInsert['book_name'] = $titleBrand;
			$tempDatInsert['transaction_id'] = $inserted_id;
			$tempDatInsert['add_date'] = date('Y-m-d H:i:s');
			$tempDatInsert['group_id'] = $dataDet['group_id'];
			$tempDatInsert['order_id'] = $dataDet['order_id'];
			$tempDatInsert['payment_status'] = $orderSt;			
			$creditHistoryObj->insert($tempDatInsert);	
			$total_price = $total_price + ($dataDet['quntity']*$price);		
			
			
		}
		$tempObj->delete('order_id="'.$orderIdForPurchase.'"');
		$this->_redirect('/checkout/displayorder/orderid/'.$orderIdForPurchase);
		//exit;
	}
}


