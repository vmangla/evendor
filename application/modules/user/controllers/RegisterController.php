<?php
class User_RegisterController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessUserInfo=null;
	var $modelUsers=null;
	 
	
	var $modelPublisher=null;
	var $modelGroup=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		$this->modelUsers = new User_Model_DbTable_Users();
		 
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelGroup = new Model_DbTable_Users();
		
		$storage = new Zend_Auth_Storage_Session('user_type');
        $data = $storage->read();
        if($data && $data!=null)
		{
            $this->sessUserInfo=$this->modelUsers->getInfoByUserId($data->id);
			$this->view->sessUserInfo =$this->sessUserInfo;
			 $this->_redirect('user/');
        }
		
	}
	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		 
		$formData=array();
		$formErrors=array();		
        if($this->getRequest()->isPost())
		{
			$formData =$this->getRequest()->getPost();
			//print_r($formData);exit;
			//=====================START FORM VALIDATION===================================
			if(!(isset($formData['first_name'])) || trim($formData['first_name'])=="")
			{
				$formErrors['first_name']="Please enter your first name";
			}
			if(!(isset($formData['last_name'])) || trim($formData['last_name'])=="")
			{
				$formErrors['last_name']="Please enter your last name";
			}
			if(!(isset($formData['user_email'])) || trim($formData['user_email'])=="")
			{
				$formErrors['user_email']="Please enter your email";
			}
			if(!(isset($formData['user_password'])) || trim($formData['user_password'])=="")
			{
				$formErrors['user_password']="Please enter your password";
			}
		 
			if(!(isset($formData['agree'])) || trim($formData['agree'])=="")
			{
				$formErrors['agree']="Please check the checkbox if you agree the terms and conditions.";
			}
			if(!(CommonFunctions::isValidEmail($formData['user_email'])))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Please enter valid email";
				}	
			}
			if($this->modelPublisher->isExist('emailid="'.$formData['user_email'].'"') || $this->modelUsers->isExist('emailid="'.$formData['user_email'].'"') || $this->modelCompany->isExist('user_email="'.$formData['user_email'].'"'))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Email already exist";
				}
			}
			//=====================END FORM VALIDATION===================================
			
			if(count($formErrors)==0)
			{
				//$sessionPost = new Zend_Session_Namespace('step1Post');
				//$sessionPost->formData=$formData;
				//$this->_redirect('company/register/step2');
				
				$activationCode=CommonFunctions::generateGUID();
							
				$username_array=explode("@",$formData['user_email']);
				$formData['username']=$username_array[0];
				$user_pwd = md5($formData['user_password']);
				$add_time=date('Y-m-d H:i:s');
				$userData=array(
						                //'user_type'=>$formData['usertype'],
										'account_type'=>'2',
										'first_name'=>$formData['first_name'],
										'last_name'=>$formData['last_name'],
										'user_name'=>$formData['user_email'],
										'user_email'=>$formData['user_email'],
										'user_password'=>$user_pwd,
										'country'=>$formData['country'],
										'status'=>'0',
										'added_date'=>date("Y-m-d H:i:s"),
										'updated_date'=>date("Y-m-d H:i:s"),
										'activation_code'=>$activationCode
										 );
								
				$lastId=$this->modelCompany->insert($userData);
				
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
											<p style="color:#FFBF00; font-weight:bold;">User Registration Email</p>
											<p style="color:#ffffff; font-weight:bold;">Your login details given below :</p>
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Username:</label>'.$formData['user_email'].'</p>
											<BR />
											<p style="color:#ffffff;">Activate your account by using given below link :</p>
											<BR />
											<p style="color:#ffffff;"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'/user/register/verification/'.$activationCode.'">Account Activation Link</a></p>
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
									//<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Password:</label>'.$formData['user_password'].'</p> 
							
							$mail = new Zend_Mail();
							$mail->addTo($formData['user_email']);
							$mail->setSubject("User Registration Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
								$this->_redirect('user/register/thanks');
							} 
							else 
							{	
								$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
							}
									
						}
				
			}
 
        }
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	
	public function thanksAction()
	{
	}
	
	public function verificationAction()
	{
	
	    $fullUrl = $this->getRequest()->getHttpHost() . $this->view->url();
		$parameter =explode("/",$fullUrl);
		 
	
		$stepup=0;
		
		for($yy;$yy<count($parameter);$yy++)
		{
		   if($parameter[$yy]=="verification")
		   { 
			    
				$resultValue=$this->modelCompany->activateUser($parameter[++$yy]);
			 
				if($resultValue==1)
				{
				   $this->_redirect('user/register/accountapprove');
				}
				else
				{
				   $this->_redirect('user/register/');
				}
			
		   }
		} 
	}
	public function verificationwithgroupAction()
	{
	
	    $fullUrl = $this->getRequest()->getHttpHost() . $this->view->url();
		$parameter =explode("/",$fullUrl);
		 
	
		$stepup=0;
		
		for($yy;$yy<count($parameter);$yy++)
		{
		   if($parameter[$yy]=="verificationwithgroup")
		   { 
			    
				$resultValue=$this->modelCompany->activateMemberWithGroup($parameter[++$yy]);
			 
				if($resultValue==1)
				{
				   $this->_redirect('user/register/accountapprove');
				}
				else
				{
				   $this->_redirect('user/register/');
				}
			
		   }
		} 
	}
	public function groupverificationAction()
	{
	
		$groupverification=$this->_request->getParam('code',0);
	    $varify_group_id=$this->_request->getParam('id',0);
		$resultValue=$this->modelCompany->activateGroupUser($groupverification,$varify_group_id);
		if($resultValue == 1)
		{
			$this->_redirect('company/register/accountapprove');
		}
		else
		{
			$this->_redirect('company/register/');
		}
		/*for($yy;$yy<count($parameter);$yy++)
		{
		   if($parameter[$yy]=="verification")
		   { 
			    $resultValue=$this->modelCompany->activateUser($parameter[++$yy]);
			 
				if($resultValue==1)
				{
				   $this->_redirect('company/register/accountapprove');
				}
				else
				{
				   $this->_redirect('company/register/');
				}
			
		   }
		} */
	}
	public function accountapproveAction()
	{
	   
	}
	
	
}

