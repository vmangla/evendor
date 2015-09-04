<?php
class Publisher_RegisterController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	
	var $modelPublisher=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
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
			if($this->modelPublisher->isExist('emailid="'.$formData['emailid'].'"'))
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
							
							<p class="title">User Registration Email</p>
							<p>Your login details given below :</p>
							<p>Username:&nbsp;'.$formData['username'].'</p>
							<p>Password:&nbsp;'.$formData['password'].'</p>
							<BR />
							<p>Activate your account by using given below link :</p>
							<BR />
							<p><a href="'.SVN_URL.'publisher/register/verification/'.$activationCode.'">Account Activation Link</a></p>
							<br>
							<p>&nbsp;</p>
							
							</div>
							<div id="footer">&nbsp;</div>
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

