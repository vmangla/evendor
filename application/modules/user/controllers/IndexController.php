<?php
class User_IndexController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessUserInfo=null;
	
	var $modelUsers=null;
	
	var $modelBooks=null;
	var $modelAuthor=null;
	

	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$storage = new Zend_Auth_Storage_Session('account_type');
        $data = $storage->read();
	 
        if(!$data)
		{
            $this->_redirect('user/auth/');
        }
		
		$this->modelUsers = new User_Model_DbTable_Users();
		
		$this->modelBooks 	= new Publisher_Model_DbTable_Books();
		$this->modelAuthor 	= new Publisher_Model_DbTable_Author();
		
		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelGroup = new Model_DbTable_Users();
		
		
		$this->sessUserInfo=$this->modelCompany->getInfoByCompanyId($data->id);
		$this->view->sessUserInfo =$this->sessUserInfo; 
    }
	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		
	 
		 
	}
	
	public function profilesettingsAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$formData=array();
		$formErrors=array();
		
		$UserInfo=$this->modelCompany->fetchRow('id='.$this->sessUserInfo->id);
		
		$formData=$UserInfo->toArray();
	 
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
		 
			if(!empty($formData['update_profile']) && $formData['update_profile']=='Update')
			{
			 
				if(!(isset($formData['first_name'])) || trim($formData['first_name'])=="")
				{
					$formErrors['first_name']="Please enter first name";
				}
				if(!(isset($formData['last_name'])) || trim($formData['last_name'])=="")
				{
					$formErrors['last_name']="Please enter last name";
				}
			 
				if(!(isset($formData['country'])) || trim($formData['country'])=="")
				{
					$formErrors['country']="Please enter country";
				}
				/*if(!(isset($formData['user_email'])) || trim($formData['user_email'])=="")
				{
					$formErrors['user_email']="Please enter email address";
				}
				if(!(CommonFunctions::isValidEmail($formData['user_email'])))
				{
					if(!(array_key_exists('user_email',$formErrors)))
					{
						$formErrors['user_email']="Please enter valid email";
					}	
				}*/
				if(trim($formData['phone'])=='')
				{
					if(!(array_key_exists('phone',$formErrors)))
					{
						$formErrors['phone']="Please enter phone no.";
					}	
				}
			 
			 /*
				if($this->modelPublisher->isExist('emailid="'.$formData['user_email'].'"') || $this->modelUsers->isExist('emailid="'.$formData['user_email'].'"') || $this->modelCompany->isExist('user_email="'.$formData['user_email'].'" and id!="'.$this->sessUserInfo->id.'"'))
				{
					if(!(array_key_exists('user_email',$formErrors)))
					{
						$formErrors['user_email']="Email already exist";
					}
				}*/
				 
				if(count($formErrors)==0)
				{
					$userData=array( 
										'first_name'=>$formData['first_name'],
										'last_name'=>$formData['last_name'],
										'country'=>$formData['country'],
										'phone'=>$formData['phone'],
										'updated_date'=>date("Y-m-d H:i:s"),
										);
					 
				
					$result=$this->modelCompany->update($userData,'id='.$this->sessUserInfo->id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Profile updated successfully</div>');
					$this->_redirect('user/index/profilesettings/');
				}				 
			}
			else
			{
				$userInfo=$this->modelUsers->fetchRow('id='.$this->sessUserInfo->id);
				
				$formData=$userInfo->toArray();
				 
				//print_r($formData);exit;
			}
		}
		
		$this->view->formData=$formData;
		 
		$this->view->formErrors=$formErrors;
		
		
		
		
		
	}
	
	
	public function changepwdAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();
		
		$userInfo=$this->modelCompany->fetchRow('id='.$this->sessUserInfo->id);
		
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			if(!empty($formData['change_password']) && $formData['change_password']=='Update Password')
			{
			/*echo "<pre>";
			print_r($formData);
			echo "<pre>";
			exit;*/
				if(!(isset($formData['password'])) || trim($formData['password'])=="")
				{
					$formErrors['password']='Please enter your current password';
				}
				if(!(isset($formData['new_password'])) || trim($formData['new_password'])=="")
				{
					$formErrors['new_password']='Please enter your new password';
				}
				if(!(isset($formData['conf_new_password'])) || trim($formData['conf_new_password'])=="")
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				if(md5($formData['password'])!=$userInfo->user_password)
				{
					$formErrors['password']='Invalid current password';
				}
				if($formData['new_password']!=$formData['conf_new_password'])
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				
				if(count($formErrors)==0)
				{
					$updateData['user_password']=md5($formData['new_password']);
					$result=$this->modelCompany->update($updateData,'id='.$this->sessUserInfo->id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
					$this->_redirect('user/index/changepwd/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	
	public function purchasepointsAction()
	{
		$points = new Admin_Model_DbTable_Points();
		$points_data = $points->fetchAll('status="1"','sort_order asc');
		$this->view->pointsdata = $points_data; 
	}
	public function historyAction()
	{
	  $this->credit_history = new User_Model_DbTable_Chistory();
	  $this->credithistory = $this->credit_history->fetchAll('userid="'.$this->sessUserInfo->id.'"');
	  $this->view->creditdata = $this->credithistory; 
	  
	  
	  $this->payment_history = new User_Model_DbTable_Phistory();
	  $this->paymenthistory = $this->payment_history->fetchAll('user_id="'.$this->sessUserInfo->id.'"');
	  $this->view->paymentdata = $this->paymenthistory;
	  
	 
	  $UserInfo=$this->modelCompany->fetchRow('id='.$this->sessUserInfo->id);
	  $this->view->userdetails = $UserInfo; 
	  
 
	  
	  
		$select = $this->payment_history->select()
						 ->from('pclive_payment_history', new Zend_Db_Expr('SUM(amount)'))
						 ->where('user_id =' . $this->sessUserInfo->id);

		$this->timequery = $this->payment_history->fetchAll($select);
		
		
		$select_remaining = $this->credit_history->select()
						 ->from('pclive_credit_history', new Zend_Db_Expr('SUM(price)'))
						 ->where('userid =' . $this->sessUserInfo->id);

		$this->timequery_remaining = $this->credit_history->fetchAll($select_remaining);
		
	  
		$this->view->totalcredits = $this->timequery[0]['SUM(amount)'];
		
		$this->view->remainingcredits = $this->timequery[0]['SUM(amount)'] - $this->timequery_remaining[0]['SUM(price)'];
	 
	}
	public function transactionhistoryAction()
	{
		$creditHistoryObj = new User_Model_DbTable_Chistory();
		$this->view->messages = $this->_flashMessenger->getMessages();
		$sql = "select * from pclive_credit_history where userid='".$this->sessUserInfo->id."' group by order_id order by credit_id desc";
		$MemberList = $creditHistoryObj->getAdapter()->fetchAll($sql);	
		
		
	    //$MemberList = $transactionHistoryObj->getOrderHistory($this->sessCompanyInfo->id);		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($MemberList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		//$paginator->setItemCountPerPage(2);
		$paginator->setCurrentPageNumber($page);		
		$this->view->totalCount=count($MemberList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->creditdata=$paginator;
		$this->view->modelGroup=$this->modelGroup;
	}
	public function viewAction()
	{
		$creditHistoryObj = new User_Model_DbTable_Chistory();
		$this->view->messages = $this->_flashMessenger->getMessages();
		$order_id =$this->_request->getParam('orderid',0);
		
		$innerjoincond = "";
		$cond = "";
		
		$allparams = $this->_request->getParams();
		
		if($allparams['subscription_type']!='' && $allparams['subscription_type']!='NA')
		{
			$cond .= " and cr.subscription_name = '".$allparams['subscription_type']."'";
			$innerjoincond = "";
		}
		if($allparams['subscription_type']=='NA')
		{
			$innerjoincond .= " ,pclive_products as p";
			$cond .= " and p.id=cr.bookid and p.cat_id!=1 and p.cat_id!=2";
		}
		
		$this->view->selectedsubscription = $allparams['subscription_type'];
		$sql = "select cr.*,tch.* from pclive_credit_history as cr,pclive_transaction_history as tch ".$innerjoincond." where cr.order_id=tch.orderId and cr.order_id='".$order_id."' ".$cond." order by credit_id desc";
 
		 
		$MemberList = $creditHistoryObj->getAdapter()->fetchAll($sql);
		
	    //$MemberList = $transactionHistoryObj->getOrderHistory($this->sessCompanyInfo->id);		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($MemberList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		//$paginator->setItemCountPerPage(2);
		$paginator->setCurrentPageNumber($page);		
		$this->view->totalCount=count($MemberList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->creditdata=$paginator;
		$this->view->modelGroup=$this->modelGroup;
		$this->view->oderId = $order_id;
		
		
		
		
	}
	
	
	public function issuesavailableAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages(); 
		$todaysdate = date('Y-m-d');
		$titleBrand = array();
		$this->usersubscriptions = new Model_DbTable_Usersubscription();
		$this->modelBooks 	= new Publisher_Model_DbTable_Books();

		$this->userpublications = $this->usersubscriptions->getuserpublications($this->sessUserInfo->id);
		
		
		
		/*foreach($this->userpublications as $row)
		{
			$publications_id = $this->modelBooks->fetchAll('id="'.$row['product_id'].'"');
			
			$parent_brand_id  = $this->modelBooks->fetchAll('id="'.$publications_id[0]['parent_brand_id'].'"');	
			//echo $parent_brand_id[0]['title'];
			
	 	}*/
		$this->view->usersubscriptions = $this->userpublications;
		 
		
	}
	
	public function sendpushnotificationsAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages(); 
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$title =$this->_request->getParam('title');
		$reg='APA91bEZVTcqtar_br_cipLae9TyYe26JhsE2jHHnV76K_zWSAAyly2ZW6SCd4-REMgY_lGtHLh-2VvObjlj-pRvHd2932KlKY7anEOKQMyviVLs1A1Y7RYDPNtXwuRqTY7rILdF_LcXvY4GntxKouBU1EUv8E1mCQ';
		
		$title = str_replace("_"," ",$title);
		$regID = array($reg);
		// $message="You have registerted successfully";
		$message = array("userID" =>$this->sessUserInfo->id,"msg" =>$title." is ready to download","email" =>$this->sessUserInfo->user_email);
		send_push_notification($regID, $message);
		$this->_flashMessenger->addMessage('<div class="div-success">Notification sent successfully</div>');
		$this->_redirect('user/index/issuesavailable/');
		
	}
	
	public function devicetrackingAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$db = Zend_Registry::get('db');
		$query = "SELECT dev.*, com.user_email FROM pclive_deviceID dev
				  INNER JOIN pclive_companies com ON dev.user_publisher_id = com.id  and dev.user_publisher_id ='".$this->sessUserInfo->id."'
				  ORDER BY com.user_email ASC ";				  
		$result = $db->query($query); 		
		$dataRecord = $result->fetchAll();
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($dataRecord);
	    $paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($dataRecord);
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->dataRecord=$paginator;
	}
	public function deletedeviceidAction()
    {
		$id=$this->_request->getParam('id',0);
		if($id>0)
		{
			$db = Zend_Registry::get('db');			
			$query = "DELETE FROM pclive_deviceID WHERE id='".$id."'";		
			$result = $db->query($query); 		
			if($result)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Device-Id deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete device id</div>');
			}
		}
		$this->_redirect('user/index/devicetracking');
    }
	public function grouppublicationAction()
    {
		$db = Zend_Registry::get('db');	
		$user_id =$this->sessUserInfo->id;
		$query = "SELECT group_id FROM  pclive_companies WHERE id='".$user_id."'";		
		$result = $db->query($query);
		$dataRecord = $result->fetchAll();
		
		if(count($dataRecord)>0)
		{
			for($i=0;$i<count($dataRecord);$i++)
			{
				$group_ids .= $dataRecord[$i]['group_id'].",";
			}
			$group_ids  = rtrim($group_ids,",");			
		}
		else
		{
			$group_ids = "5555555555555";
		}
		$dataRecord ='';
		$query = "SELECT * FROM  pclive_credit_history WHERE group_id in (".$group_ids.") and group_id!='' group by order_id";		
		$result = $db->query($query);
		$creditdata = $result->fetchAll();
		$this->view->creditdata = $creditdata;
    }
	public function viewgrouppublicationAction()
	{
		$creditHistoryObj = new User_Model_DbTable_Chistory();
		$this->view->messages = $this->_flashMessenger->getMessages();
		$order_id =$this->_request->getParam('orderid',0);
		$sql = "select cr.*,tch.* from pclive_credit_history as cr,pclive_transaction_history as tch where cr.order_id=tch.orderId and cr.order_id='".$order_id."' and group_id!='' order by credit_id desc";	
		$MemberList = $creditHistoryObj->getAdapter()->fetchAll($sql);
		
	    //$MemberList = $transactionHistoryObj->getOrderHistory($this->sessCompanyInfo->id);		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($MemberList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		//$paginator->setItemCountPerPage(2);
		$paginator->setCurrentPageNumber($page);		
		$this->view->totalCount=count($MemberList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->creditdata=$paginator;
		$this->view->modelGroup=$this->modelGroup;
		$this->view->oderId = $order_id;
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
	
}

