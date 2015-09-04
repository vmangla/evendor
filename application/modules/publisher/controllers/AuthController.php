<?php
class Publisher_AuthController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;	
	var $modelPublishers=null;
	var $sessPublisherInfo=null;
	
	
	public function init()
    {	
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		/* Initialize action controller here */
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
        $this->modelPublishers = new Publisher_Model_DbTable_Publishers();
		
		if($data && $data!=null)
		{
			$this->sessPublisherInfo=$this->modelPublishers->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo;
		}
		else
		{
			$this->view->sessPublisherInfo =$data; 
        }
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$this->view->modelBooks = $this->modelBooks; 
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
        
	}
	
	
    public function indexAction()
    {
		if(!empty($this->sessPublisherInfo))
		{
			$this->_redirect('publisher/');
		}
		
		//$this->_helper->layout()->setLayout('publisherlogin');
		$this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();		
		
        if($this->getRequest()->isPost())
		{
			$formData =$this->getRequest()->getPost();
			//print_r($formData);exit;
			
			if(!(isset($formData['user_name'])) || trim($formData['user_name'])=="")
			{
				$formErrors['user_name']="Please enter your username";
			}
			if(!(isset($formData['user_password'])) || trim($formData['user_password'])=="")
			{
				$formErrors['user_password']="Please enter your password";
			}
			
			if(count($formErrors)==0)
			{
			    $usermailID=trim($formData['user_name']);
			    $getLoginRecord=$this->modelPublishers->getInfoByLoginEmail($usermailID);
				
				if(count($getLoginRecord)>0)
				{
				    for($countUser=0;$countUser<count($getLoginRecord);$countUser++)
					{
					    if($getLoginRecord[$countUser]['user_type']!='author')
						{
								$auth = Zend_Auth::getInstance();
								$authAdapter = new Zend_Auth_Adapter_DbTable($this->modelPublishers->getAdapter(),TBL_PUBLISHERS);
								
								$authAdapter->setIdentityColumn('emailid')
								->setCredentialColumn('password');
								
								$authAdapter->setIdentity($formData['user_name'])
								->setCredential(md5($formData['user_password']));
								
								$authAdapter->getDbSelect()->where('user_type="'.$getLoginRecord[$countUser]['user_type'].'"'); 
								
								$result = $auth->authenticate($authAdapter);
								
								if($result->isValid())
								{
								    $userInfo=$authAdapter->getResultRowObject();
								
									if($this->modelPublishers->isExist('profile_status=1 AND id='.$userInfo->id))
									{
									$storage = new Zend_Auth_Storage_Session('publisher_type');
									$storage->write($userInfo);
									$this->_redirect('publisher/');
									}
									else 
									{
									$this->view->errorMessage ='<div class="div-error">Sorry, user is not active</div>';
									} 	
								} 
								else 
								{
								$this->view->errorMessage ='<div class="div-error">Invalid username or password</div>';
								} 
								
						}
							
					}
				}
			    else
				{
				   $this->view->errorMessage ='<div class="div-error">Invalid username or password</div>';
				}
			}
			else
			{
				 $this->view->errorMessage = '<div class="div-error">Please enter username and password</div>';
			}
			       
        }
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		$sessionMsg->formData=$formData;
		$sessionMsg->formErrors=$formErrors;
		$sessionMsg->errorMessage=$this->view->errorMessage;
		
		//$this->_redirect('/');
    }
	
	
    public function logoutAction()
    {
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $storage->clear();
		$this->_flashMessenger->addMessage('<div class="div-success">You have logged out successfully</div>');
        $this->_redirect('publisher/auth/');
    }
	
	
	public function forgotpasswordAction()
    {
	
	    $this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();
				
        if($this->getRequest()->isPost())
		{
			$formData =$this->getRequest()->getPost();
		
			$record=$this->modelPublishers->getInfoByPublisherEmail($formData['email_id']);
			
			if(isset($record) && $record!="")
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
							$id=$record->id;
							$key = md5($record->emailid);
							$link=$this->view->serverUrl().$this->view->baseUrl()."/publisher/auth/changepwdpublisher/id/".$id."/key/".$key."/";
							
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
											<p style="color:#FFBF00; font-weight:bold;">Forgot Password Email</p>
											<p style="color:#ffffff; font-weight:bold;">To change your password please click the given link below :</p>
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;"></label><a href="'.$link.'" style="color:blue;">Click Here To Change Password</a></p>										
											
											<BR />
											<br>
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
									/*<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Username:</label>'.$record->emailid.'</p>
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Password:</label>'.$record->password.'</p>*/				
							
							$mail = new Zend_Mail();
							$mail->addTo($formData['email_id']);
							$mail->setSubject("Forgot Password Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
					$this->_flashMessenger->addMessage('<div class="div-success">Your login details sent to your email-id</div>');
					$this->_redirect('publisher/auth/forgotpassword');
							} 
							else 
							{	
					$this->_flashMessenger->addMessage('<div class="div-error">Mail could not be sent. Try again later.</div>');
					$this->_redirect('publisher/auth/forgotpassword');
							}
			   
			}
			else
			{
			  $this->_flashMessenger->addMessage('<div class="div-error">Sorry! Invalid email address.</div>');
			  $this->_redirect('publisher/auth/forgotpassword');
			}
	
		}
		
	}
	function changepwdpublisherAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();		 
		$formData=array();
		$formErrors=array();
		$request = $this->getRequest();		
		$id=$request->getParam('id');	
		$key = $request->getParam('key');		
		$this->view->idVal=$id;
		$this->view->keyVal = $key;			
		if($this->getRequest()->isPost())		{
				
			$formData=$this->getRequest()->getPost();			
			
			if(!empty($formData['change_password']) && $formData['change_password']=='Update Password')
			{
			/*echo "<pre>";
			print_r($formData);
			echo "<pre>";
			exit;*/
				//if(!(isset($formData['password'])) || trim($formData['password'])=="")
				//{
					//$formErrors['password']='Please enter your current password';
				//}
				if(!(isset($formData['new_password'])) || trim($formData['new_password'])=="")
				{
					$formErrors['new_password']='Please enter your new password';
				}
				if(!(isset($formData['conf_new_password'])) || trim($formData['conf_new_password'])=="")
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				//if($formData['password']!=$userInfo->user_password)
				//{
					//$formErrors['password']='Invalid current password';
				//}
				if($formData['new_password']!=$formData['conf_new_password'])
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				
				if(count($formErrors)==0)
				{
					$id_user= $formData['id_user'];
					$key_user = $formData['key_user'];
					$userDataInfo = $this->modelPublishers->fetchRow("id='".$id_user."'");
					if(md5($userDataInfo['emailid']) == $key_user)
					{
						$updateData['password']=md5($formData['new_password']);
						$result=$this->modelPublishers->update($updateData,"id='".$id_user."'");
						$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
						$this->_redirect('publisher/auth/changepwdpublisher/');
					}
					else {
						$this->view->errorMessage='<div class="div-error">You are not authorised user.</div>';
					}
					
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
	
}