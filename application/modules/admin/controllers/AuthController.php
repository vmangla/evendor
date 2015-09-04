<?php
class Admin_AuthController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
    }
	
	
    public function indexAction()
    {
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
		if($data && $data!=null)
		{
			$this->_redirect('admin/');
		}
		
		$this->view->messages = $this->_flashMessenger->getMessages();
		//$this->_helper->layout()->disableLayout();
		$this->_helper->layout()->setLayout('adminlogin');
		
		$users = new Admin_Model_DbTable_AdminUsers();  
		
        if($this->getRequest()->isPost())
		{
			$formdata =$this->getRequest()->getPost();
			$enc_pwd = md5($formdata['user_password']);
			//print_r($formdata);exit;
			$auth = Zend_Auth::getInstance();
			$authAdapter = new Zend_Auth_Adapter_DbTable($users->getAdapter(),TBL_ADMIN);
			$authAdapter->setIdentityColumn('user_name')
						->setCredentialColumn('user_password');
			$authAdapter->setIdentity($formdata['user_name'])
						->setCredential($enc_pwd);
			$result = $auth->authenticate($authAdapter);
			if($result->isValid())
			{
				$storage = new Zend_Auth_Storage_Session('admin_type');
				$storage->write($authAdapter->getResultRowObject());
				$this->_redirect('admin/');
			} 
			else 
			{
				$this->view->errorMessage='<div class="div-error">Invalid username or password</div>';
			}
        }
    }
	
    public function logoutAction()
    {
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $storage->clear();
		$this->_flashMessenger->addMessage('<div class="div-success">You have logged out successfully</div>');
        $this->_redirect('admin/auth/');
    }
	
	public function forgotpasswordAction()
    {
	
	    $this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout()->setLayout('adminlogin'); 
		$formData=array();
		$formErrors=array();
				
        if($this->getRequest()->isPost())
		{
			$formData =$this->getRequest()->getPost();
			
			$users = new Admin_Model_DbTable_AdminUsers();
		
			$record=$users->getInfoByAdminEmail($formData['email_id']);
			
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
							$id = $record->id;
							$key = md5($record->user_email);
							$link = $this->view->serverUrl().$this->view->baseUrl()."/admin/auth/cngpwdwebadmin/id/".$id."/key/".$key."/";
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
											<p style="color:#ffffff; font-weight:bold;">To change your password please click on the below link :</p>
											<p style="color:#ffffff; font-weight:bold;"><a href="'.$link.'">Click Here To Change Password</a></p>
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
									//<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Username:</label>'.$record->user_name.'</p>
									//		<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Password:</label>'.$record->user_password.'</p>				
							
							$mail = new Zend_Mail();
							$mail->addTo($formData['email_id']);
							$mail->setSubject("Forgot Password Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
					$this->_flashMessenger->addMessage('<div class="div-success">Your login details send to your email-id</div>');
					$this->_redirect('admin/auth/forgotpassword');
							} 
							else 
							{	
					$this->_flashMessenger->addMessage('<div class="div-error">Mail could not be sent. Try again later.</div>');
					$this->_redirect('admin/auth/forgotpassword');
							}
			   
			}
			else
			{
			  $this->_flashMessenger->addMessage('<div class="div-error">Sorry! Invalid email address.</div>');
			  $this->_redirect('admin/auth/forgotpassword');
			}
	
		}
		
	}
	public function cngpwdwebadminAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();		 
		$usersAdmin = new Admin_Model_DbTable_AdminUsers();
		$this->_helper->layout()->setLayout('adminlogin'); 
		$formData=array();
		$formErrors=array();
		$request = $this->getRequest();		
		$id=$request->getParam('id');	
		$key = $request->getParam('key');		
		$this->view->idVal=$id;
		$this->view->keyVal = $key;			
		if($this->getRequest()->isPost()){				
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
					$userDataInfo = $usersAdmin->fetchRow("id='".$id_user."' and is_active='1'");
					if(md5($userDataInfo['user_email']) == $key_user)
					{
						$updateData['user_password']=md5($formData['new_password']);
						$result=$usersAdmin->update($updateData,"id='".$id_user."'");
						$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
						$this->_redirect('admin/auth/cngpwdwebadmin/');
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

