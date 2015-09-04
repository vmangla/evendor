<?php
class Publisher_RegisterController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelMember=null;
	var $modelGroup=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelMember = new Company_Model_DbTable_Members();
		$this->modelGroup = new Publisher_Model_DbTable_Group();
		
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
        if($data && $data!=null)
		{
            $this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo;
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
			
			/*if(!(isset($formData['usertype'])) || trim($formData['usertype'])=="")
			{
				$formErrors['usertype']="Please select user type ";
			}
			*/
			if(!(isset($formData['first_name'])) || trim($formData['first_name'])=="")
			{
				$formErrors['first_name']="Please enter first name";
			}
			if(!(isset($formData['last_name'])) || trim($formData['last_name'])=="")
			{
				$formErrors['last_name']="Please enter last name";
			}
			if(!(isset($formData['emailid'])) || trim($formData['emailid'])=="")
			{
				$formErrors['emailid']="Please enter email";
			}
			if(!(isset($formData['password'])) || trim($formData['password'])=="")
			{
				$formErrors['password']="Please enter password";
			}
			if(!(isset($formData['country'])) || trim($formData['country'])=="")
			{
				$formErrors['country']="Please select country";
			}
			if(!(isset($formData['agree'])) || trim($formData['agree'])=="")
			{
				$formErrors['agree']="Please check agree";
			}
			
			if(!(CommonFunctions::isValidEmail($formData['emailid'])))
			{
				if(!(array_key_exists('emailid',$formErrors)))
				{
					$formErrors['emailid']="Please enter valid email";
				}	
			}
			if($this->modelPublisher->isExist('emailid="'.$formData['emailid'].'"') || $this->modelCompany->isExist('user_email="'.$formData['emailid'].'"') || $this->modelGroup->isExist('emailid="'.$formData['emailid'].'"') || $this->modelMember->isExist('emailid="'.$formData['emailid'].'"'))
			{
				if(!(array_key_exists('emailid',$formErrors)))
				{
					$formErrors['emailid']="Email Id already exist";
				}
			}
			
			if(count($formErrors)==0)
			{
			
				if($this->getRequest()->isPost())
				{
					 	
						$activationCode=CommonFunctions::generateGUID();
						$add_time=date('Y-m-d H:i:s');
						
						$username_array=explode("@",$formData['emailid']);
						$formData['username']=$username_array[0];
						
						$publisherData=array(
						                //'user_type'=>$formData['usertype'],
										'user_type'=>'publisher',
										'first_name'=>$formData['first_name'],
										'last_name'=>$formData['last_name'],
										'username'=>$formData['username'],
										'emailid'=>$formData['emailid'],
										'password'=>$formData['password'],
										'country'=>$formData['country'],
										'profile_status'=>'0',
										'updated_date'=>date("Y-m-d H:i:s"),
										'activation_code'=>$activationCode,
										'activation_status'=>'1',
										'add_time'=>$add_time
										 );
										
						$lastId=$this->modelPublisher->insert($publisherData);
												
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
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Username:</label>'.$formData['emailid'].'</p>
											<p style="color:#ffffff;"><label style="color:#FFBF00; font-weight:bold; display:inline-block; width:100px;">Password:</label>'.$formData['password'].'</p>
											<BR />
											<p style="color:#ffffff;">Activate your account by using given below link :</p>
											<BR />
											<p style="color:#ffffff;"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'/publisher/register/verification/'.$activationCode.'">Account Activation Link</a></p>
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
							$mail->addTo($formData['emailid']);
							$mail->setSubject("User Registration Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
							$this->_redirect('publisher/register/thanks');
							} 
							else 
							{	
							$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
							}
									
						}
				}		
			}
			else
			{
				 $this->view->errorMessage = '<div class="div-error">Please enter required fields to register.</div>';
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
			    $resultValue=$this->modelPublisher->activateUser($parameter[++$yy]);
			 
				if($resultValue==1)
				{
				   $this->_redirect('publisher/register/accountapprove');
				}
				else
				{
				   $this->_redirect('publisher/register/');
				}
			
		   }
		} 
	}
	
	
	public function accountapproveAction()
	{
	   
	}
}

