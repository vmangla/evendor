<?php
class Company_SubscriptionController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompanySubscription=null;
	var $modelGroup=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelCompanySubscription = new Company_Model_DbTable_Subscriptions();
		$this->modelCompanyGroupSubscription = new Company_Model_DbTable_GroupSubscriptions();
		
		$this->modelUserSubscription = new Model_DbTable_Usersubscription();
		
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelGroup = new Company_Model_DbTable_Groups();
		
		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		
		$this->modelCreditHistory = new User_Model_DbTable_Chistory();
				
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
		
        if($data && $data!=null)
		{
            $this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
			$this->view->sessCompanyInfo =$this->sessCompanyInfo;
			$this->view->modelCompanySubscription =$this->modelCompanySubscription;
        }
	}
		
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		if(empty($this->sessCompanyInfo->parent_id))
		{
			$MySubscriptionsList=$this->modelCompanySubscription->getSubscriptionList($this->sessCompanyInfo->id);
		}
		else
		{
			$MySubscriptionsList=$this->modelCompanyGroupSubscription->getSubscriptionList($this->sessCompanyInfo->group_id);
		}
		
		//$SubscriptionMemberList=$this->modelCompanySubscription->getSubscriptionMembersList($this->sessCompanyInfo->id);
		
		$page=$this->_getParam('page',1);
	    $paginator_m = Zend_Paginator::factory($MySubscriptionsList);
	    $paginator_m->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		$paginator_m->setCurrentPageNumber($page);
		$this->view->totalCount=count($MySubscriptionsList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->MySubscriptionsList=$paginator_m;
		
	}
	
	public function manageAction()
	{
		
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/subscription/');
		}
		 //$MySubscriptionsList=$this->modelCompanySubscription->getSubscriptionList($this->sessCompanyInfo->id); 
		//$MySubscriptionsList=$this->modelCreditHistory->fetchAll("userid=".$this->sessCompanyInfo->id);
		
		//$this->view->MySubscriptionsList=$MySubscriptionsList;
		
		/*$MemberList=$this->modelCompany->getMemberList($this->sessCompanyInfo->id);
		$this->view->MemberList=$MemberList;*/
		
		
		$MySubscriptionsList=$this->modelUserSubscription->getGroupSubscriptionList($this->sessCompanyInfo->id); 
		 
	
		$page=$this->_getParam('page',1);
		
		$paginator_s = Zend_Paginator::factory($MySubscriptionsList);
	    $paginator_s->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		$paginator_s->setCurrentPageNumber($page);
		$this->view->totalCount=count($MySubscriptionsList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->MySubscriptionsList=$paginator_s;
			 
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		if(isset($sessionMsg) && !empty($sessionMsg))
		{
			$this->view->formData=$sessionMsg->formData;
			$this->view->formErrors=$sessionMsg->formErrors;
			$this->view->errorMessage=$sessionMsg->errorMessage;
	
			//Zend_Session::namespaceUnset('step1Msg');
		}
	}
	
	public function viewmembersAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$orderid=$this->_request->getParam('orderid',0);
		$group_id=$this->_request->getParam('group_id',0);
		
		$userdata = $this->modelUserSubscription->fetchAll('order_id='.$orderid.' and group_id='.$group_id.'');
		echo '<table>';
		foreach($userdata as $rowusers)
		{
			$this->userinfo=$this->modelCompany->getInfoByCompanyId($rowusers['user_id']);
			echo '<tr><td width="30%">'.$this->userinfo['first_name']." ".$this->userinfo['last_name'].'</td><td>'.$this->userinfo['user_email'].'</td></tr>';
			 
		}
		echo '</table>';

	}
	
	public function createAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		 
		$formData=array();
		$formErrors=array();
				
		$formData =$this->getRequest()->getPost();
		//print_r($formData);exit;
        if($this->getRequest()->isPost() && isset($formData['create_subscription']) && $formData['create_subscription']=='Create')
		{
		  //print_r($formData['member']);exit;
			if(!(isset($formData['publication'])) || trim($formData['publication'])=="")
			{
				$formErrors['publication']="Please select a publication";
			}
			
			/*if(!(isset($formData['member'])) || empty($formData['member']))
			{
				$formErrors['member']="Please select atleast one  member";
			}
			*/
			
			if(!(isset($formData['group_id'])) || empty($formData['group_id']))
			{
				$formErrors['group_id']="Please select atleast one group";
			}
			
			if(count($formErrors)==0)
			{
				$added_subscribe=0;
				foreach($formData['group_id'] as $group_id)
				{
					if($this->modelCompanyGroupSubscription->isExist('publication_id="'.$formData['publication'].'" AND group_id="'.$group_id.'"'))
					{
						$company_group_info=$this->modelGroup->getInfoByGroupId($group_id);
						$formErrors['group_id'][$group_id]="Publication already assigned to ".$company_group_info->group_name;
					}
					else
					{			
					//$added_date=date('Y-m-d H:i:s');
					
					$GroupSubscriptionData=array(
							    'publication_id'=>$formData['publication'],
								'group_id'=>$group_id,
								'company_id'=>$this->sessCompanyInfo->id
								);
										
						$lastId=$this->modelCompanyGroupSubscription->insert($GroupSubscriptionData);
						
						if($lastId>0)
						{
						$added_subscribe++;
							/*
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
							<p class="logo"><a href="'.SVN_URL.'" target="_blank">
							Electronic Vendor Ltd
							</a></p>
							</div>
							
							<p class="title">Member Registration Email</p>
							<p>Your login details given below :</p>
							<p>Username:&nbsp;'.$formData['user_email'].'</p>
							<p>Password:&nbsp;'.$formData['user_password'].'</p>
							<BR />
							<p>&nbsp;</p>
							
							</div>
							<div id="footer">&nbsp;</div>
							</div>
							</body>
							</html>';
														
							
							$mail = new Zend_Mail();
							$mail->addTo($formData['user_email']);
							$mail->setSubject("Member Registration Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
							$this->_flashMessenger->addMessage('<div class="div-success">Member created successfully</div>');
							} 
							else 
							{	
							$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
							}
							*/
						}
						}
					}
				//$this->_redirect('company/subscription/manage/');	
			}
			else
			{
				 $this->view->errorMessage = '<div class="div-error">Please enter required fields to register.</div>';
				 //$this->_redirect('company/member/');
			}      
        }
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		$sessionMsg->formData=$formData;
		$sessionMsg->formErrors=$formErrors;
		$sessionMsg->errorMessage=$this->view->errorMessage;
		
		if($added_subscribe>0)
		{
		$this->_flashMessenger->addMessage('<div class="div-success">'.$added_subscribe.' Group(s)  subscribed successfully</div>');
		}
		$this->_redirect('company/subscription/manage/');
	}
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompanyGroupSubscription->isExist($id))
		{
			$success=$this->modelCompanyGroupSubscription->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Subscription deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete subscription</div>');
			}
			
		}
		
		$this->_redirect('company/subscription/manage/');
    }
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompanySubscription->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelCompanySubscription->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Subscription deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate subscription</div>');
			}
		}
		$this->_redirect('company/subscription/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompanySubscription->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelCompanySubscription->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Subscription activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate subscription</div>');
			}
		}
		$this->_redirect('company/subscription/');
    }
	
	
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
				
		if($id>0 && $this->modelCompanySubscription->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelCompanySubscription->fetchRow('id='.$id);
			$formData=$userInfo->toArray();		
			if($this->getRequest()->isPost() && isset($_REQUEST['edit_member']) && $_REQUEST['edit_member']=='Edit Member')
			{
				$formData=$this->getRequest()->getPost();
				//print_r($formData);exit;
				
				if(!(isset($formData['first_name'])) || trim($formData['first_name'])=="")
				{
					$formErrors['first_name']="Please enter first name";
				}
				if(!(isset($formData['last_name'])) || trim($formData['last_name'])=="")
				{
					$formErrors['last_name']="Please enter last name";
				}
				if(!(isset($formData['user_email'])) || trim($formData['user_email'])=="")
				{
					$formErrors['user_email']="Please enter email";
				}
				if(!(isset($formData['user_password'])) || trim($formData['user_password'])=="")
				{
					$formErrors['user_password']="Please enter password";
				}
				
				if(!(CommonFunctions::isValidEmail($formData['user_email'])))
				{
					if(!(array_key_exists('user_email',$formErrors)))
					{
						$formErrors['user_email']="Please enter valid email";
					}	
				}
				
				/*if($this->modelCompanySubscription->isExist('username="'.$formData['username'].'"'))
				{
					if(!(array_key_exists('username',$formErrors)))
					{
						$formErrors['username']="Username already exist";
					}
				}
				*/
				if($this->modelCompanySubscription->isExist('user_email="'.$formData['user_email'].'" AND id<>'.$id) || $this->modelPublisher->isExist('emailid="'.$formData['user_email'].'"'))
				{
					if(!(array_key_exists('user_email',$formErrors)))
					{
						$formErrors['user_email']="Email already exist";
					}
				}
				
				if(count($formErrors)==0)
				{
					$username_array=explode("@",$formData['user_email']);
					$formData['username']=$username_array[0];
									
					$MemberUserData=array(
					                'user_email'=>$formData['user_email'],
									'user_password'=>$formData['user_password'],
									'first_name'=>$formData['first_name'],
									'last_name'=>$formData['last_name'],
									'phone'=>$formData['phone'],
									'status'=>'1',
									'updated_date'=>date("Y-m-d H:i:s")
								);
					
					$result=$this->modelCompanySubscription->update($MemberUserData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Member user updated successfully</div>');
					$this->_redirect('company/member/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('company/member/');
		}
    }
	
	public function viewAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		$tab_ajax=$this->_request->getParam('tab_ajax');
		
		if($id>0 && $this->modelCompanySubscription->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelCompanySubscription->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
				
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('company/member/');
		}
    }
	
	public function downloadpubfileAction()
    {
		    $this->_helper->layout->disableLayout();
			
			$id=$this->_request->getParam('id',0);
			
			$bookDetail=$this->modelBooks->getInfoByPublisherId($id);
			
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
	$this->_redirect('company/subscription/index/tab_ajax/subscription');	
				
	}
	
}

