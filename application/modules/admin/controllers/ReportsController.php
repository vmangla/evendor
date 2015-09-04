<?php
class Admin_ReportsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;		
	var $modelCompany=null;
	var $modelPublisher=null;
	var $modelBooks=null;
	var $modelAuthor=null;		
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
        
		if($data)
		{
		$this->modelAdmiUsers = new Admin_Model_DbTable_AdminUsers();
		$this->sessAdminInfo=$this->modelAdmiUsers->fetchRow("id=".$data->id);
		}
		
		if(!$this->sessAdminInfo)
		{
            $this->_redirect('admin/auth/');
        }
		
		$this->sessAdminInfo->modules=explode(",",$this->sessAdminInfo->modules);
		$this->view->sessAdminInfo =$this->sessAdminInfo;
		
		$this->modelModuleMenus = new Admin_Model_DbTable_ModulesMenus();
		$this->view->modelModuleMenus = $this->modelModuleMenus->getModuleMenusList();
				
		$this->modelCompany=new Company_Model_DbTable_Subscriptions();
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		$this->modelBooks 	= new Publisher_Model_DbTable_Books();
		$this->modelAuthor 	= new Publisher_Model_DbTable_Author();
		
		$this->view->modelCompany=$this->modelCompany;
					
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		 $action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		/************* To Set The active section and links *******************/
		$moduleAccessRow=$this->modelModuleMenus->fetchRow("controller='$controller' AND action LIKE '%".$action."%'");
		
		$currentModuleId=$moduleAccessRow['id'];
	
		if(!in_array($currentModuleId,$this->sessAdminInfo->modules))
		{
			//$this->_flashMessenger->addMessage('<div class="div-error">"'.$moduleAccessRow['menu_name'].'"   module not accessable to you</div>');
			//$this->_redirect('admin/');
		}
		
	}
    public function  getbrandAction()
	{
		$post_data=$this->getRequest()->getPost();
		//echo $post_data['cat_id'];
		$ajx_url = $this->view->serverUrl().$this->view->baseUrl()."/admin/reports/getbookissues";
		if($post_data['cat_id'] == '1' || $post_data['cat_id']=='2')
		{
			$titleBrandDropdown=$this->modelBooks->getTitleBrandListForAdmin($post_data['brand'],'',$post_data['cat_id']);
		}
		else
		{
			$titleBrandDropdown=$this->modelBooks->getTitleBrandList('','',$post_data['cat_id']);
		}
		//echo "Count brand : - ".$titleBrandDropdown."<br>";
		echo $titleBrandDropdown;
		exit;
	}	 
					 
	
	public function indexAction()
    {
    	$publisher_user = new Publisher_Model_DbTable_Publishers();
		$params=$this->getRequest()->getParams();
		$category = $this->_request->getParam('category');
		$brand = $this->_request->getParam('brand');
		$productid = $this->_request->getParam('product_id');
		$frmdate = $this->_request->getParam('from_date');
		$todate = $this->_request->getParam('to_date');
		$publisher_id = $this->_request->getParam('publisher');
		$publisher_user_data = $publisher_user->fetchAll("user_type='publisher' and publisher!=''");
		$brand_exp = explode("**",$brand);
		if($this->getRequest()->isPost())
		{
			
			$formData=$this->getRequest()->getPost();
			/*if($formData['category']!='' && $formData['product_id']!='')
			{
				$productData = $this->modelBooks->getAllProductWithCategoryReport($formData['category'],$formData['product_id']);
			}
			else
			{
				
				$productData = $this->modelBooks->getProductReport($formData['category'],$formData['product_id'],$formData['from_date'],$formData['to_date']);
			}*/
			
			$productData = $this->modelBooks->getProductReport($category,$brand_exp[0],$productid,$frmdate,$todate ,$publisher_id);
	 	}		
		else {
			$productData = $this->modelBooks->getProductReport($category,$brand_exp[0],$productid,$frmdate,$todate,$publisher_id);
			//$productData = $this->modelBooks->getProductReport($formData['category'],$formData['product_id'],$formData['from_date'],$formData['to_date'],$formData['publisher']);
			//$productData = $this->modelBooks->getAllProductReport();
		}	
		$formData['category']=$category;
		$formData['brand']=$brand;
		$formData['product_id']=$productid;
		$formData['from_date'] = $frmdate;
		$formData['to_date']=$todate;
		$formData['publisher'] = $publisher_id;
		
		$this->view->prodList = $productData;
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($productData);
	    $paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($productData);
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->prodList=$paginator;
		$this->view->publisher_data=$publisher_user_data;
		$this->view->formData=$formData;
		
		

		
 		/*************** Group Informations ********************/
		/*$groupList=$this->modelPublisher->getGroupList();
		$this->view->totalGroupCount=count($groupList);
		
		$publicationManagersList=$this->modelPublisher->getPublicationManagersList();
		$this->view->totalPublicationManagersCount=count($publicationManagersList);
		
		$accountManagersList=$this->modelPublisher->getAccountManagersList();
		$this->view->totalAccountManagersCount=count($accountManagersList);
		$authorList=$this->modelAuthor->getAuthorList();
		$this->view->totalAuthorCount=count($authorList);
		
		$activeAuthorsList=$this->modelAuthor->getActiveAuthorList();	
		$this->view->totalActiveAuthorCount=count($activeAuthorsList);
		
		$publisherList=$this->modelCompany->getPublisherList();
		$this->view->totalPublisherCount=count($publisherList);
		
		$customerList=$this->modelCompany->getCustomerList();
		$this->view->totalPublisherCount=count($customerList);*/
		
	}
	function reportsearchAction()
	{
		$formData=$this->getRequest()->getPost();
			$params = 'admin/reports/index';
			if($formData['category']!='')
			{
				$params.="/category/".$formData['category'];
			}
			if($formData['brand']!='' && $formData['category']!='3')
			{
				$params.="/brand/".$formData['brand'];
			}
			if($formData['product_id']!='')
			{
				$params.="/product_id/".$formData['product_id'];
			}
			if($formData['from_date']!='')
			{
				$params.="/from_date/".$formData['from_date'];
			}
			if($formData['to_date'])
			{
				$params.="/to_date/".$formData['to_date'];
			}
			if($formData['publisher'])
			{
				$params.="/publisher/".$formData['publisher'];
			}
			//echo $params;
			//exit;
            //$redirectURL=CommonFunctions::convertQueryArrayToZendUrl('reports',$params);
            $this->_redirect($params);
	}
	
	public function exportreportxlsAction()
	{
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$producturrency = new Admin_Model_DbTable_Countries();
		$category = $this->_request->getParam('category');
		$brand = $this->_request->getParam('brand');
		$productid = $this->_request->getParam('productid');
		$frmdate = $this->_request->getParam('frmdate');
		$todate = $this->_request->getParam('todate');
		$publisher_id = $this->_request->getParam('publisher');
		$modelCategory = new Publisher_Model_DbTable_Books();	
		$brand_exp = explode("**",$brand);
		$request_list = $this->modelBooks->getProductReport($category,$brand_exp[0],$productid,$frmdate,$todate,$publisher_id);
	 				
			$separator=","; // separate column in csv
			if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>	
				  <th>Publication Id</th>
				   <th>Title / Brand</th>
				    <th>Category</th>
				   <th>Publisher</th>
				   <th>Total Downloads</th>
				   <th>Total Purchase</th>
				    <th>Base Amount</th>
					 <th>Total Amount</th>
				   <th>Last Purchased</th>
				</tr>';
				foreach($request_list as $req)
				{
					//$req['country'];		
					//$countryName = $this->modelUsers->getCountryInfo($req['country']); 
					$productPriceInfo = $productPrice->getPriceByStoreId($req['id'],$req['store_id']);
					$getBrandInfo=$modelCategory->getBrandInfo($req['title']);
					$getBrandInfo=$modelCategory->getBrandInfo($req['title']);
					if(!empty($getBrandInfo))
					{
						$titleBrand=$getBrandInfo['brand'];
					}else{
						$titleBrand=$req['title'];
					}
					if(!empty($row->parent_brand_id))
					{
						$productInfo=$this->modelPublications->fetchRow('id='.$req['parent_brand_id']);
						$getParentBrandInfo=$modelCategory->getBrandInfo($productInfo->title);
						if(!empty($getParentBrandInfo))
						{
							$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
						}
					}						
					$title =  stripslashes($titleBrand);
					   $catInfo=$modelCategory->getCategoryInfo($req['cat_id']);
					   $publiseherDetails = $this->modelPublisher->getInfoByPublisherId($req['publisher_id']);
					   $publisherName = $publiseherDetails['publisher'];	
					   $totalDownLoad = $req['no_download'];
					   $publication_id = $req['id'];
					   $price = $producturrency->getCurrencyInfo($req['store_id']).$req['price'];
					   $quantity = $req['total_count'];
					   $category_name = $catInfo['category_name'];
					   $quantity*$price;
					   $tot_p = $quantity*$req['price'];
					   $getCurrencyName = $producturrency->getCurrencyCode($req['store_id']);
					   $totNairaPrice = $producturrency->currencyconverter($getCurrencyName,"NGN",$tot_p);
					   $tot_p = $producturrency->getCurrencyInfo(226).@number_format($totNairaPrice,2);
					   $totalPurchase = $req['best_seller'];
					 $content.='<tr>
								<td>'.$publication_id.'</td>							
					 			<td>'. $title.'</td>
								<th>'.$category_name.'</th>
				 				<td>'.$publisherName.'</td>
				  				<td>'.$totalDownLoad.'</td>								
								<td>'.$quantity.'</td>
								<td>'.$price.'</td>	
								<td>'.$tot_p.'</td>	
				  				<td>'.$req['add_date'].'</td>
					   		</tr>';
									
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['user_name']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['user_email']).$separator.str_replace(",","",$req['phone'])."\n";
				}
			}
			else {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$file = "report";
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
	}
	public function membersAction()
    {
 		/*************** Group Informations ********************/
		$groupList=$this->modelPublisher->getGroupList();
		$this->view->totalGroupCount=count($groupList);
		
		$publicationManagersList=$this->modelPublisher->getPublicationManagersList();
		$this->view->totalPublicationManagersCount=count($publicationManagersList);
		
		$accountManagersList=$this->modelPublisher->getAccountManagersList();
		$this->view->totalAccountManagersCount=count($accountManagersList);
		
		/*************** Group Informations Ends Here ******************/
		
		/*************** Author Informations ********************/
		$authorList=$this->modelAuthor->getAuthorList();
		$this->view->totalAuthorCount=count($authorList);
		
		$activeAuthorsList=$this->modelAuthor->getActiveAuthorList();	
		$this->view->totalActiveAuthorCount=count($activeAuthorsList);
		
		$publisherList=$this->modelCompany->getPublisherList();
		$this->view->totalPublisherCount=count($publisherList);
		
		$customerList=$this->modelCompany->getCustomerList();
		$this->view->totalCustomerCount=count($customerList);
		
	}
	
	public function revenueAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$formData=$this->getRequest()->getPost();
		$formErrors=array();

		$publisherList=$this->modelCompany->getPublisherList();
		
		$prodList=$this->modelCompany->getProdListByParentPublisherId(0);
		
		$priceModel = new Publisher_Model_DbTable_BookPrices();
		//print_R($prodList);
		$total_sales=0;
		foreach($prodList as $row)
		{
			$priceInfo = $priceModel->getPriceByStoreId($row['product_id'],$row['store_id']);
			$total_sales+=$priceInfo['price'];
		}
				
		if(isset($formData['publisher_id']) && trim($formData['publisher_id'])!="")
		{
			$prodList=$this->modelCompany->getProdListByParentPublisherId($formData['publisher_id']);
		}
				
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($prodList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($prodList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->prodList=$paginator;
		
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		$this->view->publisherList=$publisherList;
		$this->view->totalSales=$total_sales;
		
	}
	
	public function subscriptionsAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$formData=$this->getRequest()->getPost();
		$formErrors=array();		
		
		if(isset($formData['category']) && (trim($formData['category'])!="" && isset($formData['product_id']) && trim($formData['product_id'])!=""))
		{
			$prodList=$this->modelCompany->getReportEBookIssues($formData['category'],$formData['product_id']);
		}
		else
		{
			$formData['category']=0;$formData['product_id']=0;
			$prodList=$this->modelCompany->getReportEBookIssues($formData['category'],$formData['product_id']);
		}
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($prodList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($prodList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->prodList=$paginator;
		
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
	}
	
	public function getbookissuesAction()
    {		
		if($this->getRequest()->isPost())
		{
	    	$post_data=$this->getRequest()->getPost();
			
			if(isset($post_data['cat_id']) && !empty($post_data['cat_id']))
			{
				$cat_obj = new Publisher_Model_DbTable_Books();
				$cat_info=$cat_obj->getCategoryInfo($post_data['cat_id']);
				
				$product_id=isset($post_data['product_id'])?$post_data['product_id']:0;
				if($post_data['cat_id']=='1' || $post_data['cat_id']=='2')
				{
					//$product_dropdown=$this->modelCompany->getProdListByParentCatIdBrandId($post_data['cat_id'],$post_data['brand_id'],$product_id);
					$product_dropdown=$this->modelCompany->getProdListByParentCatIdForAdmin($post_data['cat_id'],$post_data['brand_id'],$product_id);
				}
				else 
				{
					$product_dropdown=$this->modelCompany->getProdListByParentCatIdForAdmin($post_data['cat_id'],$post_data['brand_id'],$product_id);
				}
				echo $product_dropdown;
				exit;
			}
		}
		else
		{
			exit;
		}
	}
	
	public function getpublisherbookissuesAction()
    {		
		if($this->getRequest()->isPost())
		{
	    	$post_data=$this->getRequest()->getPost();
			
			if(isset($post_data['cat_id']) && !empty($post_data['cat_id']))
			{
				$cat_obj = new Publisher_Model_DbTable_Books();
				$cat_info=$cat_obj->getCategoryInfo($post_data['cat_id']);
				
				$product_id=isset($post_data['product_id'])?$post_data['product_id']:0;
				
				$product_dropdown=$this->modelCompany->getProdListByParentCatId($post_data['cat_id'],$cat_info['category_name'],$product_id);
				
				echo $product_dropdown;
				exit;
			}
		}
		else
		{
			exit;
		}
	}
	public function orderhistoryAction()
    {    	
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			$where ='';
			if($formData['order_key']!='' && $formData['order_key']!='')
			{
				$where .= " and order_id ='".$formData['order_key']."'";
			}
			if($formData['from_date']!='' && $formData['to_date']!='')
			{
				$where .= " and add_date BETWEEN '".$formData['from_date']."' and '".$formData['to_date']."'";
			}
			$productData = $this->modelBooks->getAllProductSearchGroupBy($where);
	 	}	
		else {
			$productData = $this->modelBooks->getAllProductOrderWithGroupBy();
		}		
		$this->view->prodList = $productData;		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($productData);
	    $paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);		
		$this->view->totalCount=count($productData);
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->prodList=$paginator;		
		$this->view->formData=$formData;		
	}
	function exportorderhistoryAction()
	{
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelCategory = new Publisher_Model_DbTable_Books();	
		$order_key = $this->_request->getParam('order_key');
		$to_date = $this->_request->getParam('to_date');
		$from_date = $this->_request->getParam('from_date');
		$formData=$this->getRequest()->getPost();
		
		$where ='';
		if($order_key!='' && $order_key!='')
		{
			$where .= " and pc.order_id ='".$order_key."'";
		}
		if($to_date!='' && $from_date!='')
		{
			$where .= " and pc.add_date BETWEEN '".$from_date."' and '".$to_date."'";
		}			
		$productData = $this->modelBooks->getAllProductSearchWithoutGroupBy($where);
		$request_list = $productData;
		if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>				 				  
				   <th>Title / Brand</th>
				   <th>Publisher</th>
				   <th>Quantity</th>
				   <th>Unit Price</th>
				   <th>Total Price</th>
				   <th>Payment Status</th>
				    <th>Transaction ID</th>
					 <th>Order ID</th>
				   <th>Last Purchased</th>
				</tr>';
				foreach($request_list as $req)
				{
					//$req['country'];		
					//$countryName = $this->modelUsers->getCountryInfo($req['country']); 
					$productPriceInfo = $productPrice->getPriceByStoreId($req['id'],$req['store_id']);
					$getBrandInfo=$modelCategory->getBrandInfo($req['title']);
					if(!empty($getBrandInfo))
					{
						$titleBrand=$getBrandInfo['brand'];
					}else{
						$titleBrand=$req['title'];
					}
					if(!empty($req['parent_brand_id']))
					{
						$productInfo=$this->modelCompany->fetchRow('id='.$req['parent_brand_id']);
						$getParentBrandInfo=$modelCategory->getBrandInfo($productInfo->title);
						if(!empty($getParentBrandInfo))
						{
							$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
						}
					}						
					$title =  stripslashes($titleBrand);
					   
					   $publiseherDetails = $this->modelPublisher->getInfoByPublisherId($req['publisher_id']);
					   $publisherName = $req['publisher'];	
					   $payment_status = $req['payment_status'];
					   if($payment_status =='1')
					   {
					   	 $st = "Completed";
					   }
					   elseif($payment_status == '2')
					   {
					   	 $st = "Declined";
					   }
					   elseif($payment_status =='0')
					   {
					   	 $st = "Pending";
					   }
					   $quantity = $req['quantity'];
					   $transaction_id = $req['transaction_id'];
					   if($req['group_id']!=0)
					  {
						 $priceG = $producturrency->getCurrencyInfo($req['store_id']).$productPriceInfo['group_price'];
					  }
					  else
					  {
						 $priceG = $producturrency->getCurrencyInfo($req['store_id']).$productPriceInfo['price'];
					  }	
					  $tot_p = $quantity*$req['price'];
					   $getCurrencyName = $producturrency->getCurrencyCode($req['store_id']);
						$totNairaPrice = $producturrency->currencyconverter($getCurrencyName,"NGN",$tot_p);
					  $tot_p = $producturrency->getCurrencyInfo(226).@number_format($totNairaPrice,2);
					 $content.='<tr>
					 			<td>'. $title.'</td>					 			
				 				<td>'.$publisherName.'</td>
				  				<td>'.$quantity.'</td>
								<td>'.$priceG.'</td>
								<td>'. $tot_p.'</td>
				  				<td>'.$st.'</td>
								<td>'.$req['transaction_id'].'</td>
								<td>'.$req['order_id'].'</td>
				  				<td>'.$req['add_date'].'</td>
					   		</tr>';
									
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['user_name']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['user_email']).$separator.str_replace(",","",$req['phone'])."\n";
				}
			}
			else {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$file = "order";
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
	}
	public function displayorderAction()
    {
    	
		$orderId=$this->_getParam('orderid');
		
		$productData = $this->modelBooks->getAllProductOrderWithOrderId($orderId);
		$this->view->prodList = $productData;
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($productData);
	    $paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($productData);
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->prodList=$paginator;		
		$this->view->formData=$formData;	
		$this->view->order_id=$orderId;	
	}
	public function exportordertxlsAction()
	{
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$producturrency = new Admin_Model_DbTable_Countries();
		//$category = $this->_request->getParam('category');
		$order_id = $this->_request->getParam('orderid');
		//$frmdate = $this->_request->getParam('frmdate');
		//$todate = $this->_request->getParam('todate');
		$modelCategory = new Publisher_Model_DbTable_Books();
		if($order_id!='')
		{
			$sql='SELECT * from  pclive_products as p inner join pclive_credit_history as pc on p.id=pc.bookid where pc.order_id="'.$order_id.'" order by credit_id';
		}
		else {
			$sql='SELECT * from pclive_products as p inner join pclive_credit_history as pc on p.id=pc.bookid  order by credit_id';
		}
			
			$separator=","; // separate column in csv				
			//$sql = "select first_name,last_name,user_name,country,user_email,phone from pclive_companies $where ";		
			//$request_list=$this->modelconnections->fetchAll("intro_id='".$_SESSION['Zend_Auth']['storage']->user_id."' and conn_linkedin_id!=''");
			$request_list = $this->modelBooks->getAdapter()->fetchAll($sql);
			if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				    <th>Transaction Id</th>				  
				   <th>Title / Brand</th>
				   <th>Publisher</th>
				   <th>Quantity</th>
				   <th>Unit Price</th>
				   <th>Total Price</th>
				   <th>Payment Status</th>
				   <th>Last Purchased</th>
				</tr>';
				foreach($request_list as $req)
				{
					//$req['country'];		
					//$countryName = $this->modelUsers->getCountryInfo($req['country']); 
					
					$getBrandInfo=$modelCategory->getBrandInfo($req['title']);
					$productPriceInfo = $productPrice->getPriceByStoreId($req['id'],$req['store_id']);
					if(!empty($getBrandInfo))
					{
						$titleBrand=$getBrandInfo['brand'];
					}else{
						$titleBrand=$req['title'];
					}
					if(!empty($req['parent_brand_id']))
					{
						$productInfo=$this->modelCompany->fetchRow('id='.$req['parent_brand_id']);
						$getParentBrandInfo=$modelCategory->getBrandInfo($productInfo->title);
						if(!empty($getParentBrandInfo))
						{
							$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
						}
					}						
					$title =  stripslashes($titleBrand);
					$price_in = $producturrency->getCurrencyInfo($req['store_id']).$req['price'];
					/*if($req['group_id']!=0)
					  {
						$price_in = $producturrency->getCurrencyInfo($req['store_id']).$productPriceInfo['group_price'];
					  }
					  else
					  {
						$price_in =  $producturrency->getCurrencyInfo($req['store_id']).$productPriceInfo['price'];
					  }	*/
					   $quantity = $req['quantity'];
					  $tot_p = $quantity*$req['price'];
					 $getCurrencyName = $producturrency->getCurrencyCode($req['store_id']);
					 $tot_p = $producturrency->currencyconverter($getCurrencyName,"NGN",$tot_p);
					 $tot_p = $producturrency->getCurrencyInfo(226).@number_format($tot_p,2);
					 $publiseherDetails = $this->modelPublisher->getInfoByPublisherId($req['publisher_id']);
					 $publisherName = $req['publisher'];	
					 $payment_status = $req['payment_status'];
					 if($payment_status =='1')
					 {
					   	 $st = "Completed";
					 }
					   elseif($payment_status == '2')
					   {
					   	 $st = "Declined";
					   }
					   elseif($payment_status =='0')
					   {
					   	 $st = "Incoplete";
					   }
					  
					   $transaction_id = $req['transaction_id'];
					 $content.='<tr>
					 			<td>'. $title.'</td>
					 			<td>'. $title.'</td>
				 				<td>'.$publisherName.'</td>
				  				<td>'.$quantity.'</td>
								<td>'.$price_in.'</td>
								<td>'.$tot_p.'</td>
				  				<td>'.$st.'</td>
				  				<td>'.$req['add_date'].'</td>
					   		</tr>';
									
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['user_name']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['user_email']).$separator.str_replace(",","",$req['phone'])."\n";
				}
			}
			else {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$file = "report";
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
	}
	function viewtransactionAction()
	{
		$trans_id = $this->_request->getParam('transid');
		$modelBooks = new Publisher_Model_DbTable_Books();
		$sql='SELECT * from  pclive_products as p inner join pclive_credit_history as pc on p.id=pc.bookid where pc.transaction_id="'.$trans_id.'" order by credit_id desc';		
		$order_list = $this->modelBooks->getAdapter()->fetchAll($sql);
		
		$trans_history_data = $modelBooks->getAllProductTransactionById($trans_id);
		$this->view->trans_history_data =$trans_history_data;
		$this->view->trans_id =$trans_id;
		$this->view->order_list =$order_list;
	}
	function printtransactionAction()
	{
		$this->_helper->layout->disableLayout();
		$trans_id = $this->_request->getParam('transid');
		$modelBooks = new Publisher_Model_DbTable_Books();
		$sql='SELECT * from  pclive_products as p inner join pclive_credit_history as pc on p.id=pc.bookid where pc.transaction_id="'.$trans_id.'" order by credit_id desc';		
		$order_list = $this->modelBooks->getAdapter()->fetchAll($sql);
		$trans_history_data = $modelBooks->getAllProductTransactionById($trans_id);
		$this->view->trans_history_data =$trans_history_data;
		$this->view->trans_id =$trans_id;
		$this->view->order_list =$order_list;
	}
	function viewxmlAction()
	{
		$this->_helper->layout->disableLayout();
		$trans_id = $this->_request->getParam('transid');
		$modelBooks = new Publisher_Model_DbTable_Books();		
		$trans_history_data = $modelBooks->getAllProductTransactionById($trans_id);
		/*if($trans_history_data[0]['transaction_xml']!='')
		{
			echo $trans_history_data[0]['transaction_xml'];		
		}
		else
		{
			echo "No Data";			
		}
		exit;*/
		$this->view->trans_history_data =$trans_history_data;
		$this->view->trans_id =$trans_id;
		
	}
	public function detailsreportAction()
	{
		$publisher_user = new Publisher_Model_DbTable_Publishers();		
		$publication = $this->_request->getParam('publication');	
		
		if($publication!='')
		{			
			$productData = $this->modelBooks->gerDetailsReportsById($publication);
			$this->view->prodList = $productData;		
			$page=$this->_getParam('page',1);
			$paginator = Zend_Paginator::factory($productData);
			$paginator->setItemCountPerPage(10);
			$paginator->setCurrentPageNumber($page);		
			$this->view->totalCount=count($productData);
			$this->view->pageSize=10;
			$this->view->page=$page;
			$this->view->prodList=$paginator;
			$this->view->publisher_data=$publisher_user_data;
			$formData['publication'] = $publication;
			$this->view->formData=$formData;			
	 	}
	}
	public function expordetailsreportxlsAction()
	{	
		$modelCategory = new Publisher_Model_DbTable_Books();
		$producturrency = new Admin_Model_DbTable_Countries();
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$separator=","; 
		$publication = $this->_request->getParam('publication');	
		$request_list = $this->modelBooks->gerDetailsReportsById($publication);	
		
			if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>	
				  <th>Publication Id</th>
				   <th>Title / Brand</th>
				    <th>Category</th>
				   <th>Publisher</th>
				   <th>Total Downloads</th>
				   <th>Total Purchase</th>
				    <th>Unit Price</th>
					 <th>Total Amount</th>
				   <th>Last Purchased</th>
				</tr>';
				foreach($request_list as $req)
				{
					//$req['country'];		
					//$countryName = $this->modelUsers->getCountryInfo($req['country']); 
					
					$getBrandInfo=$modelCategory->getBrandInfo($req['title']);
					if(!empty($getBrandInfo))
					{
						$titleBrand=$getBrandInfo['brand'];
					}else{
						$titleBrand=$req['title'];
					}
					if(!empty($row->parent_brand_id))
					{
						$productInfo=$this->modelPublications->fetchRow('id='.$req['parent_brand_id']);
						$getParentBrandInfo=$modelCategory->getBrandInfo($productInfo->title);
						if(!empty($getParentBrandInfo))
						{
							$titleBrand=$titleBrand.' ('.$getParentBrandInfo['brand'].')';
						}
					}						
					$title =  stripslashes($titleBrand);
					   $catInfo=$modelCategory->getCategoryInfo($req['cat_id']);
					   $productPriceInfo = $productPrice->getPriceByStoreId($req['id'],$req['store_id']);
					   $publiseherDetails = $this->modelPublisher->getInfoByPublisherId($req['publisher_id']);
					   $publisherName = $publiseherDetails['publisher'];	
					   $totalDownLoad = $req['no_download'];
					   $publication_id = $req['id'];
					   $price = $req['price'];
					   $quantity = $req['quantity'];
					   $category_name = $catInfo['category_name'];
					   $totalPurchase = $req['best_seller'];
					   $currency = $producturrency->getCurrencyInfo($req['store_id']);
					   $currencyN = $producturrency->getCurrencyInfo(226);
					   if($req['group_id']!=0)
					   {
							$price = $productPriceInfo['group_price'];
					   }
					   else
					   {
							$price = $productPriceInfo['group_price'];
					   }
					   $getCurrencyName = $producturrency->getCurrencyCode($req['store_id']);
					   $tot = $req['quantity']*$req['price'];
					  $totNairaPrice = $producturrency->currencyconverter($getCurrencyName,"NGN",$tot);
					 $content.='<tr>
								<td>'.$publication_id.'</td>							
					 			<td>'. $title.'</td>
								<th>'.$category_name.'</th>
				 				<td>'.$publisherName.'</td>
				  				<td>'.$totalDownLoad.'</td>								
								<td>'.$quantity.'</td>
								<td>'.$currency.$price.'</td>	
								<td>'.$currencyN.$totNairaPrice.'</td>	
				  				<td>'.$req['add_date'].'</td>
					   		</tr>';
									
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['user_name']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['user_email']).$separator.str_replace(",","",$req['phone'])."\n";
				}
			}
			else {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$file = "report";
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
	}
	############################### Free Report start ###################################
	public function freereportAction()
	{
		$publisher_user = new Publisher_Model_DbTable_Publishers();
		$params=$this->getRequest()->getParams();
		$category = $this->_request->getParam('category');
		$brand = $this->_request->getParam('brand');
		$productid = $this->_request->getParam('product_id');
		$frmdate = $this->_request->getParam('frmdate');
		$todate = $this->_request->getParam('todate');
		$publisher_id = $this->_request->getParam('publisher');
		$publisher_user_data = $publisher_user->fetchAll("user_type='publisher' and publisher!=''");
		$brand_exp = explode("**",$brand);
		if($this->getRequest()->isPost())
		{
			$productData = $this->modelBooks->getProductFreeReport($category,$brand_exp[0],$productid,$frmdate,$todate ,$publisher_id);
	 	}		
		else {
			$productData = $this->modelBooks->getProductFreeReport($category,$brand_exp[0],$productid,$frmdate,$todate ,$publisher_id);
		}	
		$formData['category']=$category;
		$formData['brand']=$brand;
		$formData['product_id']=$productid;
		$formData['from_date'] = $frmdate;
		$formData['to_date']=$todate;
		$formData['publisher'] = $publisher_id;		
		$this->view->prodList = $productData;		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($productData);
	    $paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($productData);
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->prodList=$paginator;
		$this->view->publisher_data=$publisher_user_data;
		$this->view->formData=$formData;	
		
	}
	public function freereportsearchAction()
	{
		$formData=$this->getRequest()->getPost();
			$params = 'admin/reports/freereport';
			if($formData['category']!='')
			{
				$params.="/category/".$formData['category'];
			}
			if($formData['brand']!='' && $formData['category']!='3')
			{
				$params.="/brand/".$formData['brand'];
			}
			if($formData['product_id']!='')
			{
				$params.="/product_id/".$formData['product_id'];
			}
			if($formData['from_date']!='')
			{
				$params.="/from_date/".$formData['from_date'];
			}
			if($formData['to_date'])
			{
				$params.="/to_date/".$formData['to_date'];
			}
			if($formData['publisher'])
			{
				$params.="/publisher/".$formData['publisher'];
			}
			//echo $params;
			//exit;
            //$redirectURL=CommonFunctions::convertQueryArrayToZendUrl('reports',$params);
            $this->_redirect($params);
	}
	public function exportfreereportxlsAction()
	{
		$category = $this->_request->getParam('category');
		$brand = $this->_request->getParam('brand');
		$productid = $this->_request->getParam('productid');
		$frmdate = $this->_request->getParam('frmdate');
		$todate = $this->_request->getParam('todate');
		$publisher_id = $this->_request->getParam('publisher');
		$modelCategory = new Publisher_Model_DbTable_Books();	
		$brand_exp = explode("**",$brand);
		$request_list = $this->modelBooks->getProductReport($category,$brand_exp[0],$productid,$frmdate,$todate,$publisher_id);
		$request_list = $this->modelBooks->getProductFreeReport($category,$brand_exp[0],$productid,$frmdate,$todate ,$publisher_id);			
			$separator=","; // separate column in csv
			if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>	
				  <th>Publication Id</th>
				  <th>Title / Brand</th>
				  <th>Category</th>
				  <th>Publisher</th>
				  <th>Total Downloads</th>
				 </tr>';
				foreach($request_list as $req)
				{					
					$getBrandInfo=$modelCategory->getBrandInfo($req['title']);
					if(!empty($getBrandInfo))
					{
						$titleBrand=$getBrandInfo['brand'];
					}else{
						$titleBrand=$req['title'];
					}
					if(!empty($row->parent_brand_id))
					{
						$productInfo=$this->modelPublications->fetchRow('id='.$req['parent_brand_id']);
						$getParentBrandInfo=$modelCategory->getBrandInfo($productInfo->title);
						if(!empty($getParentBrandInfo))
						{
							$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
						}
					}						
					$title =  stripslashes($titleBrand);
					   $catInfo=$modelCategory->getCategoryInfo($req['cat_id']);
					   $publiseherDetails = $this->modelPublisher->getInfoByPublisherId($req['publisher_id']);
					   $publisherName = $publiseherDetails['publisher'];	
					   $totalDownLoad = $req['no_download'];
					   $publication_id = $req['id'];
					   $price = $req['price'];
					   $quantity = $req['total_count'];
					   $category_name = $catInfo['category_name'];
					   $totalPurchase = $req['best_seller'];
					 $content.='<tr>
								<td>'.$publication_id.'</td>							
					 			<td>'.stripslashes($title).'</td>
								<th>'.$category_name.'</th>
				 				<td>'.$publisherName.'</td>
				  				<td>'.$totalDownLoad.'</td>								
								</tr>';
									
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['user_name']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['user_email']).$separator.str_replace(",","",$req['phone'])."\n";
				}
			}
			else {
				$content.='<tr><td colspan="5" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$file = "freereport";
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
	}
}

