<?php
class User_AuthController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;	
	var $modelPublishers=null;
	var $sessPublisherInfo=null;
	
	
	public function init()
    {	
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		/* Initialize action controller here */
		$storage = new Zend_Auth_Storage_Session('user_type');
        $data = $storage->read();
		
        $this->modelUsers = new User_Model_DbTable_Users();
		$this->modelCompanies = new Company_Model_DbTable_Companies();
		
		if($data && $data!=null)
		{
			$this->sessUserInfo=$this->modelCompanies->getInfoByCompanyId($data->id);
			$this->view->sessUserInfo =$this->sessUserInfo;
		}
		else
		{
			$this->view->sessUserInfo =$data; 
        }
        
	}
	
	
    public function indexAction()
    {
		if(!empty($this->sessUserInfo))
		{
			$this->_redirect('user/');
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
				$auth = Zend_Auth::getInstance();
				$authAdapter = new Zend_Auth_Adapter_DbTable($this->modelCompanies->getAdapter(),TBL_COMPANIES);
				$authAdapter->setIdentityColumn('user_email')
							->setCredentialColumn('user_password');
				$authAdapter->setIdentity($formData['user_name'])
							->setCredential($formData['user_password']);
				$result = $auth->authenticate($authAdapter);
				 
				if($result->isValid())
				{
					$userInfo=$authAdapter->getResultRowObject();
				 
					if($this->modelCompanies->isExist('status=1 AND parent_id=0 and id='.$userInfo->id))
					{
						$storage = new Zend_Auth_Storage_Session('account_type');
						$storage->write($userInfo);
						$this->_redirect('user/');
					}
					else if($this->modelCompanies->isExist('status=1 AND parent_id>0 and id='.$userInfo->id))
					{
						$this->view->errorMessage ='<div class="div-error">You can only login through mobile app.</div>';
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
		$storage = new Zend_Auth_Storage_Session('account_type');
        $storage->clear();
		$this->_flashMessenger->addMessage('<div class="div-success">You have logged out successfully</div>');
        $this->_redirect('user/auth/');
    }
	
	
	public function forgotpasswordAction()
    {
	
	    $this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();
				
        if($this->getRequest()->isPost())
		{
			$formData =$this->getRequest()->getPost();
		 
			$record=$this->modelCompanies->getInfoByCompanyEmail($formData['email_id']);
			
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
											<p style="color:#ffffff; font-weight:bold;">Your login details given below :</p>
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Username:</label>'.$record->user_email.'</p>
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Password:</label>'.$record->user_password.'</p>
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
							
							$mail = new Zend_Mail();
							$mail->addTo($formData['email_id']);
							$mail->setSubject("Forgot Password Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
					$this->_flashMessenger->addMessage('<div class="div-success">Your login details sent to your email-id</div>');
					$this->_redirect('user/auth/forgotpassword');
							} 
							else 
							{	
					$this->_flashMessenger->addMessage('<div class="div-error">Mail could not be sent. Try again later.</div>');
					$this->_redirect('user/auth/forgotpassword');
							}
			   
			}
			else
			{
			  $this->_flashMessenger->addMessage('<div class="div-error">Sorry! Invalid email address.</div>');
			  $this->_redirect('user/auth/forgotpassword');
			}
	
		}
		
	}
	
}