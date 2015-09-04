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
				//echo md5($formData['user_password']);
				$user_pwd = md5($formData['user_password']);
				$auth = Zend_Auth::getInstance();
				$authAdapter = new Zend_Auth_Adapter_DbTable($this->modelCompanies->getAdapter(),TBL_COMPANIES);
				$authAdapter->setIdentityColumn('user_email')
							->setCredentialColumn('user_password');
				$authAdapter->setIdentity($formData['user_name'])
							->setCredential($user_pwd);
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
							$email = md5($record->user_email);
							$id = $record->id;
							$link = $this->view->serverUrl().$this->view->baseUrl()."/user/auth/cngpwdweb/id/".$id."/key/".$email."/";
							
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
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;"></label><a href="'.$link.'">Click Here To Change Password</p>
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
					$this->_flashMessenger->addMessage('<div class="div-success">Please check your inbox to change your password.</div>');
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
	
	public function cngpwdwebAction()
    {
	$this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();
		$request = $this->getRequest();
		//echo ">>>".$request->getParam('id');
		//echo ">>>>".base64_encode($request->getParam('id'));
		//$test=base64_encode($request->getParam('id'));
		//echo ">>>>".base64_decode($test);
		//echo ">>>test".mt_rand();
		//echo $test;
		//die();
		//$userInfo=$this->modelCompany->fetchRow('id='.$this->sessUserInfo->id);	
		$id=$request->getParam('id');	
		$key = $request->getParam('key');
		$this->view->idVal=$id;
		$this->view->keyVal = $key;	
		
		if($this->getRequest()->isPost())
		{
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
					$userDataInfo = $this->modelCompanies->fetchRow("id='".$id_user."' and status='1'");
					if(md5($userDataInfo['user_email']) == $key_user)
					{
						$updateData['user_password']=md5($formData['new_password']);
						$result=$this->modelCompanies->update($updateData,"id='".$id_user."'");
						$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
						$this->_redirect('user/auth/cngpwdweb/');
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
	
	
	public function cngpwdAction()
    {
	$this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();
		$request = $this->getRequest();
		//echo ">>>".$request->getParam('id');
		//echo ">>>>".base64_encode($request->getParam('id'));
		//$test=base64_encode($request->getParam('id'));
		//echo ">>>>".base64_decode($test);
		//echo ">>>test".mt_rand();
		//echo $test;
		//die();
		//$userInfo=$this->modelCompany->fetchRow('id='.$this->sessUserInfo->id);
		
		if($this->getRequest()->isPost())
		{
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
					$updateData['user_password']=md5($formData['new_password']);
					$result=$this->modelCompanies->update($updateData,'id='.base64_decode($request->getParam('id')));
					
					$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
					$this->_redirect('user/auth/cngpwd/');
				}
				else
				{
					//$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
	}
	
	
}