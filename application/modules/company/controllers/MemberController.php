<?php
class Company_MemberController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelGroup=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelGroup = new Company_Model_DbTable_Groups();
		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
				
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
		
        if($data && $data!=null)
		{
            $this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
			$this->view->sessCompanyInfo =$this->sessCompanyInfo;
        }
	}
		
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/subscription/');
		}
		
		$GroupList=$this->modelGroup->getGroupList($this->sessCompanyInfo->id);
		$this->view->GroupList=$GroupList;
		
		$MemberList=$this->modelCompany->getMemberList($this->sessCompanyInfo->id);
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($MemberList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		//$paginator->setItemCountPerPage(2);
	    
		$paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($MemberList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		//$this->view->pageSize=2;
		
		$this->view->page=$page;
		$this->view->MemberList=$paginator;
		$this->view->modelGroup=$this->modelGroup;
		
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		if(isset($sessionMsg) && !empty($sessionMsg))
		{
			$this->view->formData=$sessionMsg->formData;
			$this->view->formErrors=$sessionMsg->formErrors;
			$this->view->errorMessage=$sessionMsg->errorMessage;
			
			Zend_Session::namespaceUnset('step1Msg');
		}
	}
	
	public function createAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		 
		$formData=array();
		$formErrors=array();
				
		$formData =$this->getRequest()->getPost();
		//print_r($formData);exit;
        if($this->getRequest()->isPost() && isset($formData['create_member']) && $formData['create_member']=='Create')
		{
		  
			if(!(isset($formData['group_id'])) || empty($formData['group_id']))
			{
				$formErrors['group_id']="Please select a group";
			}
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
			
			/*if($this->modelCompany->isExist('username="'.$formData['username'].'"'))
			{
				if(!(array_key_exists('username',$formErrors)))
				{
					$formErrors['username']="Username already exist";
				}
			}
			*/			
			if($this->modelCompany->isExistCompany('user_email="'.$formData['user_email'].'" and (account_type="1" or account_type="3")') || $this->modelPublisher->isExist('emailid="'.$formData['user_email'].'"'))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Email already exist for company or member of company or publisher";
				}
			}	
			if($this->modelCompany->isExist('user_email="'.$formData['user_email'].'" and (parent_id!="0" or group_id!="0")'))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Email already member of a group.";
				}
			}		
			if(count($formErrors)==0)
			{
				if($this->getRequest()->isPost())
				{
					$groupName = $this->modelGroup->getInfoByGroupId($formData['group_id']);
					########################## get user who are not publisher,company and member of other company ######################
					$memberData = $this->modelCompany->getNonMemberUser($formData['user_email']);
					if(count($memberData)>0 )
					{
						//$groupName = $this->modelGroup->getInfoByGroupId($formData['group_id']);
						$activationCode=CommonFunctions::generateGUID();
						$add_time=date('Y-m-d H:i:s');
						$formData['user_password']=empty($formData['user_password'])?CommonFunctions::getRandomNumberPassword(8):$formData['user_password'];
						$formData['company_id']=(!empty($this->sessCompanyInfo->id))?$this->sessCompanyInfo->id:0;
						$MemberUserData=array(
							    'parent_id'=>$formData['company_id'],
								'group_varify_id'=>$formData['group_id'],
								'user_email'=>$formData['user_email'],
								'account_type'=>3,							
								'status'=>'1',
								'updated_date'=>date("Y-m-d H:i:s"),
								'activation_code'=>$activationCode,
								'added_date'=>$add_time
								);
								$group_id = $formData['group_id'];	
						$lastId=$this->modelCompany->update($MemberUserData,"id='".$memberData[0]['id']."'");						
						if($memberData[0]['id']!='')
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
								$varification_url = $this->view->serverUrl().$this->view->baseUrl().'/user/register/groupverification/code/'.$activationCode.'/id/'.$group_id;
								
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
								<p class="title">To be active member of this "'.$groupName['group_name'].'" group click below link.</p>
								<BR />
								<p style="color:#ffffff;"><a href="'.$varification_url.'">Group Activation Link</a></p>
								<br>
								<p class="title">Membership of a '.$groupName['group_name'].' group email</p>
								<p>Congrats you are member of a '.$groupName['group_name'].' group :</p>							
								<BR />
								<p>&nbsp;</p>
								
								</div>
								<div id="footer">&nbsp;</div>
								</div>
								</body>
								</html>';
															
								
								$mail = new Zend_Mail();
								$mail->addTo($formData['user_email']);
								$mail->setSubject("Membership of a ".$groupName['group_name']." group email.");
								$mail->setBodyHtml($message);
								$mail->setFrom(SETFROM, SETNAME);
								
								if($mail->send())
								{
								$this->_flashMessenger->addMessage('<div class="div-success">Individual member is updated to member of this group.User Name and password will remain same of the user.</div>');
								} 
								else 
								{	
								$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
								}
								$this->_redirect('company/member/');
							}
					}
					else {
					$activationCode=CommonFunctions::generateGUID();
					$add_time=date('Y-m-d H:i:s');
					$formData['user_password']=empty($formData['user_password'])?CommonFunctions::getRandomNumberPassword(8):$formData['user_password'];
					$formData['company_id']=(!empty($this->sessCompanyInfo->id))?$this->sessCompanyInfo->id:0;
					$username_array=explode("@",$formData['user_email']);
					$formData['user_name']=$username_array[0];
					
									
					$MemberUserData=array(
							    'parent_id'=>$formData['company_id'],
								'group_id'=>$formData['group_id'],
								'user_email'=>$formData['user_email'],
								'user_password'=>md5($formData['user_password']),
								'user_name'=>$formData['user_name'],
								'first_name'=>$formData['first_name'],
								'account_type'=>3,
								'last_name'=>$formData['last_name'],
								'status'=>'0',
								'updated_date'=>date("Y-m-d H:i:s"),
								'activation_code'=>$activationCode,
								'added_date'=>$add_time
								);
										
						$lastId=$this->modelCompany->insert($MemberUserData);
						
						if($lastId>0)
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
							<BR />
							<p style="color:#ffffff;">Activate your account by using given below link :</p>
							<BR />
							<br>
							<p class="title">To activate your membership of '.$groupName['group_name'].' and active member of this group please click below link.</p>
							<p style="color:#ffffff;"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'/user/register/verificationwithgroup/'.$activationCode.'">Account Activation Link</a></p>
							<br>
							<p class="title">After activating your account login with following information.</p>
							<p>Your login details given below :</p>
							<p>Username:&nbsp;'.$formData['user_email'].'</p>
							<p>Password:&nbsp;'.$formData['user_password'].'</p>
							<p>Password:&nbsp;'.$groupName['group_name'].'</p>
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
							$this->_redirect('company/member/');		
						}
					}
				}		
			}
			else
			{
				 //$this->view->errorMessage = '<div class="div-error">Please enter required fields properly.</div>';
				 //$this->_redirect('company/member/');
			}      
        }
		else
		{
			$formData['first_name']="";
			$formData['last_name']="";
			$formData['user_email']="";
			$formData['user_password']="";
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		$sessionMsg->formData=$formData;
		$sessionMsg->formErrors=$formErrors;
		$sessionMsg->errorMessage=$this->view->errorMessage;
		
		$this->_redirect('company/member/');
	}
	



	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompany->isExist($id))
		{
			//$data['group_status']=0;
			$array_data = array("parent_id"=>"0","group_id"=>"0","group_status"=>"0","account_type"=>"2");
		    $success=$this->modelCompany->update($array_data,'id="'.$id.'"');
			//$success=$this->modelCompany->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Member user deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete Member user</div>');
			}
			
		}
		
		$this->_redirect('company/member/');
    }
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompany->isExist($id))
		{
		
		    $data['group_status']=0;
		    $success=$this->modelCompany->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Member user deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate Member user</div>');
			}
		}
		$this->_redirect('company/member/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompany->isExist($id))
		{
		
		    $data['group_status']=1;
		    $success=$this->modelCompany->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Member user activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate Member user</div>');
			}
		}
		$this->_redirect('company/member/');
    }
	
	
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
				
		if($id>0 && $this->modelCompany->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelCompany->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
			
			$GroupList=$this->modelGroup->getGroupList($this->sessCompanyInfo->id);
			$this->view->GroupList=$GroupList;
			
			if($this->getRequest()->isPost() && isset($_REQUEST['edit_member']) && $_REQUEST['edit_member']=='Edit Member')
			{
				$formData=$this->getRequest()->getPost();
				//print_r($formData);exit;
				
				if(!(isset($formData['group_id'])) || empty($formData['group_id']))
				{
					$formErrors['group_id']="Please select a group";
				}
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
				
				/*if($this->modelCompany->isExist('username="'.$formData['username'].'"'))
				{
					if(!(array_key_exists('username',$formErrors)))
					{
						$formErrors['username']="Username already exist";
					}
				}
				*/
				if($this->modelCompany->isExist('user_email="'.$formData['user_email'].'" AND id<>'.$id) || $this->modelPublisher->isExist('emailid="'.$formData['user_email'].'"'))
				{
					if(!(array_key_exists('user_email',$formErrors)))
					{
						$formErrors['user_email']="Email already exist";
					}
				}
				if($formData['user_password']!="Please enter new password")
				{
					$enc_password = md5($formData['user_password']);						
				}
				else {
					$enc_password=$userInfo['user_password'];
				}
				if(count($formErrors)==0)
				{
					$username_array=explode("@",$formData['user_email']);
					$formData['username']=$username_array[0];
									
					$MemberUserData=array(
					                'group_id'=>$formData['group_id'],
									'user_email'=>$formData['user_email'],
									'user_password'=>$enc_password,
									'first_name'=>$formData['first_name'],
									'last_name'=>$formData['last_name'],
									'phone'=>$formData['phone'],
									'status'=>'1',
									'group_status'=>'1',
									'updated_date'=>date("Y-m-d H:i:s")
								);
					
					$result=$this->modelCompany->update($MemberUserData,'id='.$id);
					
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
		
		if($id>0 && $this->modelCompany->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelCompany->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
				
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('company/member/');
		}
    }
	
}