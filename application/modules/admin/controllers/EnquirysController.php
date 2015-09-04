<?php
class Admin_EnquirysController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelEnquiry=null;
	
	
    public function init()
    {
		/* creating flashMessanger object to display message */
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login */
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

	    /* creating model object */
		$this->modelEnquiry=new Admin_Model_DbTable_Enquirys();
		
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
		$formData=$this->getRequest()->getPost();
		if((isset($formData['user_permission']) && trim($formData['user_permission'])!="") || (isset($formData['status']) && trim($formData['status'])!=""))
		{
		  $permission=trim($formData['user_permission']);
		  $status=trim($formData['status']);
		  
		  $whrcls="";
		  if($permission!="")
		  {
		    $whrcls.=" user_permission='".$permission."'";
		  }
		  if($status!="")
		  {
		    if($permission!="")
		    {
			$whrcls.=" AND status='".$status."'";
			}
			else
			{
		    $whrcls.=" status='".$status."'";
			}
		  }
			$enquiryList=$this->modelEnquiry->fetchAll($whrcls);
		}
		else
		{
		  $enquiryList=$this->modelEnquiry->getList();
		}
		
		
		//$enquiryList=$this->modelEnquiry->getList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($enquiryList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($enquiryList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->enquiryList=$paginator;
		
		$this->view->formData=$formData;
	}
	
	public function viewAction()
    {
		$this->view->messages = $this->_flashMessenger->getMessages();
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEnquiry->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelEnquiry->fetchRow('id='.$id);
			$formData=$userInfo->toArray();	
					
			if($this->getRequest()->isPost())
			{
				$formDataUpdate=$this->getRequest()->getPost();
				if(!(isset($formDataUpdate['email'])) || trim($formDataUpdate['email'])=="")
				{
					$formErrors['email']='Please enter email';
				}
				
				if(!(isset($formDataUpdate['contact_message'])) || trim($formDataUpdate['contact_message'])=="")
				{
					$formErrors['contact_message']='Please enter message';
				}
				
				if(!(CommonFunctions::isValidEmail($formDataUpdate['email'])))
				{
					if(!(array_key_exists('email',$formErrors)))
					{
						$formErrors['email']="Please enter valid email";
					}	
				}
				
				if(count($formErrors)==0)
				{
					
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
												<p style="color:#FFBF00; font-weight:bold;">Customer Enquiry Reply Email</p>
												<p style="color:#ffffff; font-weight:bold;">Message has been sent successfully.</p>
												<br />
												<p>&nbsp;</p>
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
								$mail->addTo($formData['email']);
								$mail->setSubject("Customer Reply Email");
								$mail->setBodyHtml($message);
								$mail->setFrom(SETFROM, SETNAME);
								
								if($mail->send())
								{
									$updateData['status']='replied';
									$result=$this->modelEnquiry->update($updateData,"id='$id'");
									if($result>0)
									{
										$this->_flashMessenger->addMessage('<div class="div-success">Your message has been sent successfully</div>');
									
										$this->_redirect('admin/enquirys/view/id/'.$id);
									}
									else
									{
										$this->view->errorMessage='<div class="div-error">Sorry!, unable to update status</div>';
									}
								} 
								else 
								{	
									$this->view->errorMessage='<div class="div-error">Evendor could not sent any confirmation mail</div>';
								}
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			$this->view->formDataUpdate=$formDataUpdate;	
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEnquiry->isExist($id))
		{
			$success=$this->modelEnquiry->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Enquiry deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete enquiry</div>');
			}
		}
		$this->_redirect('admin/enquirys/');
    }
	
	public function exportenquiriesAction()
    {
 		
		$enquiryList=$this->modelEnquiry->getList();
		$file="list_of_enquiries.xls";
		$content="";
		
		$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
		<tr>
		  <th>Name</th>
		  <th>Email Id</th>
		  <th>Phone</th>
		  </tr>';
			   
			if(isset($enquiryList) && count($enquiryList)>0)
			{
				$sNumber=1;
				foreach($enquiryList as $row)
				{
				$content.='<tr>
					   <td>'.$row->name.'</td> 
					   <td>'.$row->email.'</td>
					   <td>'.$row->phone.'</td> 
					   </tr>';
				}
				
			 }
			 else
			 {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			 }
			$content.='</table>';
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$file");
			echo $test;
			exit;
	}

	
}

