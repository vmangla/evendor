<?php
class Admin_SubscriptionController extends Zend_Controller_Action
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
		$this->modelUserSubscription = new Model_DbTable_Usersubscription();
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
     	 
					 
	public function indexAction()
    {
		$this->view->messages = $this->_flashMessenger->getMessages(); 
		//echo "meenakshi makkar ";
	 
		$condition = "";
		$order_key = $this->_request->getParam('order_key');
		$from_date = $this->_request->getParam('from_date');
		$to_date = $this->_request->getParam('to_date');
		$subscription_name = $this->_request->getParam('subscription_name');
		$user_type = $this->_request->getParam('user_type');
		
		
		if(isset($order_key) && $order_key!='')
		{
			$condition .= ' and order_id="'.$order_key.'"';
		}
		if(isset($from_date) && $from_date!='')
		{
			$condition .= ' and date(start_date) ="'.$from_date.'"';
		}
		if(isset($to_date) && $to_date!='')
		{
			$condition .= ' and date(end_date) ="'.$to_date.'"';
		}
		if(isset($subscription_name) && $subscription_name!='')
		{
			$condition .= ' and subscription_name ="'.$subscription_name.'"';
		}
		
		if(isset($user_type) && $user_type=='Individual')
		{
			$condition .= ' and user_id > 0 and group_id=0';
		}
		
		if(isset($user_type) && $user_type=='Group')
		{
			$condition .= ' and user_id > 0 and group_id > 0';
		}
		
		// echo "condition".$condition;
		 
		$formData=$this->getRequest()->getPost();
		
		$productData = $this->modelUserSubscription->fetchAll('subscription_type > 0 '.$condition.'','id desc');
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
	 
  
	function exportorderhistoryAction()
	{
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$producturrency = new Admin_Model_DbTable_Countries();
		$modelCategory = new Publisher_Model_DbTable_Books();	
		$order_key = $this->_request->getParam('order_key');
		$to_date = $this->_request->getParam('to_date');
		$from_date = $this->_request->getParam('from_date');
		$subscription_name = $this->_request->getParam('subscription_name');
		$user_type = $this->_request->getParam('user_type');
		$formData=$this->getRequest()->getPost();
		
		$where ='';
		
		if(isset($order_key) && $order_key!='')
		{
			$condition .= ' and order_id="'.$order_key.'"';
		}
		if(isset($from_date) && $from_date!='')
		{
			$condition .= ' and date(start_date) ="'.$from_date.'"';
		}
		if(isset($to_date) && $to_date!='')
		{
			$condition .= ' and date(end_date) ="'.$to_date.'"';
		}
		if(isset($subscription_name) && $subscription_name!='')
		{
			$condition .= ' and subscription_name ="'.$subscription_name.'"';
		}
		
		if(isset($user_type) && $user_type=='individual')
		{
			$condition .= ' and user_id > 0 and group_id=0';
		}
		
		if(isset($user_type) && $user_type=='Group')
		{
			$condition .= ' and user_id > 0 and group_id > 0';
		}
 	 
		$formData=$this->getRequest()->getPost();
		
		$productData = $this->modelUserSubscription->fetchAll('subscription_type > 0 '.$condition.'');
		$productPrice = new Publisher_Model_DbTable_BookPrices();
		$producturrency = new Admin_Model_DbTable_Countries();

		$this->modelCompanydata = new Model_DbTable_Companies();
		
		$this->group_members = new Company_Model_DbTable_Groups();
		$request_list = $productData;
		if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				   <th>Order Id</th>	
				   <th>User</h>
				   <th>Group Name</th>
				   <th>Company User</th>	
				   <th>Title / Brand</th>
				   <th>Subscription Name</th>
				   <th>Number of issues</th>
				   <th>Number of Downloaded issues</th>
				   <th>Subscription Price</th>
				   <th>Start Date</th>				 
				   <th>End Date</th>
			   </tr>';
			   $sNumber=1;
				foreach($request_list as $row)
				{
					$this->sessCompanyInfo = $this->modelCompanydata->getInfoByUserId($row['user_id']);
					
					$this->sesscompany_user = $this->modelCompanydata->getInfoByUserId($row['company_id']);
				 
					$this->sessgroup_members = $this->group_members->getInfoByGroupId($row['group_id']);
					
					$modelCategory = new Publisher_Model_DbTable_Books();
					$getProductInfo=$this->modelCompany->getProductInfo($row['product_id']);

					$getBrandInfo=$modelCategory->getBrandInfo($getProductInfo['title']);
					if(!empty($getBrandInfo))
					{
						$titleBrand=$getBrandInfo['brand'];
					}
					else
					{
						$titleBrand=$getProductInfo['title'];
					}
					if(!empty($getProductInfo['parent_brand_id']))
					{
					$productInfo=$this->modelCompany->getProductInfo($getProductInfo['parent_brand_id']);
					$getParentBrandInfo=$modelCategory->getBrandInfo($productInfo['title']);
						if(!empty($getParentBrandInfo))
						{
							$titleBrand=$getParentBrandInfo['brand']." - ".$titleBrand;
						}
					}
					 $getCurrencyName = $producturrency->getCurrencyCode($row['country']);
					  $totNairaPrice = $producturrency->currencyconverter($getCurrencyName,"NGN",$row['subscription_price']);
					    
					 $content.='<tr>
					 			<td>'. $row['order_id'].'</td>					 			
				 				<td>'.$this->sessCompanyInfo['first_name']." ".$this->sessCompanyInfo['last_name'].'</td>
								 <td>'.$this->sesscompany_user['first_name']." ".$this->sesscompany_user['last_name'].'</td> 
						         <td>'.$this->sessgroup_members['group_name'].'</td> 
				  				<td>'.stripslashes($titleBrand).'</td>
								<td>'.$row['subscription_name'].'</td>
								<td>'. $row['number_of_issues'].'</td>
				  				<td>'.$row['number_of_downloaded'].'</td>
								<td>'.$producturrency->getCurrencyInfo($row['country']).@number_format($totNairaPrice,2).'</td>
								<td>'.$row['start_date'].'</td>
				  				<td>'.$row['end_date'].'</td>
					   		</tr>';
					$sNumber++;				
					 
				}
			}
			else {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$file = "subscription_report";
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
	}
	public function cancelsubscriptionAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$updatearray = array("cancel_subscription"=>1);
		$id = $this->_request->getParam('id');
		$wherecondition = "id='".$id."'";
		$productData = $this->modelUserSubscription->update($updatearray,$wherecondition);
	 
		$this->_flashMessenger->addMessage('<div class="div-success">Subscription cancelled successfully</div>');
		$this->_redirect('admin/subscription');
		exit();
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
	 
}

