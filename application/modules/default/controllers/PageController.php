<?php
class PageController extends Zend_Controller_Action
{
	var $sessPublisherInfo=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelPage=null;
	var $modelEnquiry=null;
	
    public function init()
    {
        $this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		/*********** To check for publisher session data *********************/
		$storage_publisher = new Zend_Auth_Storage_Session('publisher_type');
		$publisher_data = $storage_publisher->read();
		
		$storage_company = new Zend_Auth_Storage_Session('company_type');
		$company_data = $storage_company->read();
		
		$storage = new Zend_Auth_Storage_Session('account_type');		
        $userSessData = $storage->read();
				
		if($publisher_data && $publisher_data!=null)
		{
			$this->modelPublisher = new Model_DbTable_Users();
			$this->sessPublisherInfo = $this->modelPublisher->getInfoByUserId($publisher_data->id);
			
			$this->view->sessPublisherInfo=$this->sessPublisherInfo;
		}
		elseif($company_data && $company_data!=null)
		{
			$this->modelCompany = new Model_DbTable_Companies();
			$this->sessCompanyInfo = $this->modelCompany->getInfoByUserId($company_data->id);
			
			$this->view->sessCompanyInfo = $this->sessCompanyInfo;
		}
		elseif($userSessData && $userSessData!=null)
		{
			$this->modelCompanies = new Company_Model_DbTable_Companies();
			$this->sessUserInfo=$this->modelCompanies->getInfoByCompanyId($userSessData->id);
			$this->view->sessUserInfo =$this->sessUserInfo;
		}
		$this->modelPage=new Model_DbTable_Page();
		$this->modelEnquiry=new Admin_Model_DbTable_Enquirys();
				
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$title=$this->_getParam('title');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		$this->view->currentTitle=$title;
		$this->view->class_active='class="active"';
		/************* To Set The active section and links *******************/
		
		if($title=='contact-us')
		{
		$captcha = new Zend_Captcha_Image();
		$captcha->setImgDir('public/css/captcha/images/');
		$captcha->setImgUrl($this->view->serverUrl.$this->view->baseUrl().'/public/css/captcha/images/');
		$captcha->setFont('public/css/captcha/fonts/times_new_yorker.ttf');
		$captcha->setWordlen(5);
		$captcha->setFontSize(28);
		$captcha->setLineNoiseLevel(3);
		$captcha->setWidth(161);
		$captcha->setHeight(75);
		$captcha->generate();
		
		/*$captcha= new Zend_Form_Element_Captcha('captcha', array(
                    'label' => "Please verify you're a human",

                    'captcha' => array(
                        'captcha' => 'image',

                        'font'=>'public/css/captcha/fonts/times_new_yorker.ttf',
                        'imgDir'=>'public/css/captcha/images/',
                        'imgUrl'=>$this->view->serverUrl.$this->view->baseUrl().'/public/css/captcha/images/',
                        'wordLen' => 4,
                        'fsize'=>20,
                        'height'=>60,
                        'width'=>250,
                        'gcFreq'=>50,
                        'expiration' => 300
                    )
                ));
				*/
		
		$this->view->captcha = $captcha;
		
		}
		
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$this->view->modelBooks = $this->modelBooks; 
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		
    }
    public function indexAction()
    {
		$this->view->messages = $this->_flashMessenger->getMessages();
		$pageTitle=$this->_request->getParam('title',0);
		
		if($this->modelPage->isExist($pageTitle))
		{
			$this->view->pageInfo=$this->modelPage->fetchRow('url_friendly_title="'.$pageTitle.'"');
			
			$formData=array();
			$formErrors=array();
			if($this->getRequest()->isPost() && $pageTitle=='contact-us')
			{
				$formData =$this->getRequest()->getPost();
				//print_r($formData);exit;
				if(isset($formData['cid']))
				{	
					$capId = trim($formData['cid']);
					$capSession = new Zend_Session_Namespace('Zend_Form_Captcha_'.$capId);
					if ($formData['captcha'] == $capSession->word)
					{
						if(!(isset($formData['contact_name'])) || trim($formData['contact_name'])=="")
						{
							$formErrors['contact_name']="Please enter first name";
						}
						if(!(isset($formData['contact_email'])) || trim($formData['contact_email'])=="")
						{
							$formErrors['contact_email']="Please enter email";
						}
						if(!(isset($formData['contact_message'])) || trim($formData['contact_message'])=="")
						{
							$formErrors['contact_message']="Please enter your message";
						}
						
						if(!(CommonFunctions::isValidEmail($formData['contact_email'])))
						{
							if(!(array_key_exists('contact_email',$formErrors)))
							{
								$formErrors['contact_email']="Please enter valid email";
							}	
						}
					}
					else
					{
						$formErrors['security_code']="You have entered wrong security code.";
					}
				}
				else
				{
					$formErrors['security_code']="Please enter security code properly.";
				}
						
				if(count($formErrors)==0)
				{
					if($this->getRequest()->isPost())
					{
						$add_time=date('Y-m-d H:i:s');
						$user_permission=(isset($formData['agree']) && !empty($formData['agree']) && $formData['agree']==1)?1:0;
						
						$enquiryData=array(
										'name'=>$formData['contact_name'],
										'email'=>$formData['contact_email'],
										'phone'=>$formData['contact_phone'],
										'suggestion'=>$formData['contact_message'],
										'user_permission'=>$user_permission,
										'status'=>'pending',
										'add_time'=>$add_time
										);
						$lastId=$this->modelEnquiry->insert($enquiryData);
											
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
												<p style="color:#FFBF00; font-weight:bold;">Customer Enquiry Email</p>
												<p style="color:#ffffff; font-weight:bold;">Your enquiry has been sent successfully. Evendor admin will contact you soon</p>
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
								$mail->addTo($formData['contact_email']);
								$mail->setSubject("Customer Enquiry Email");
								$mail->setBodyHtml($message);
								$mail->setFrom(SETFROM, SETNAME);
								if($mail->send())
								{
									$this->_flashMessenger->addMessage('<div class="div-success">Your enquiry has been sent successfully. Our representative will get back to you soon.</div>');
									
									//$this->_flashMessenger->addMessage('<div class="div-success">You  have an email from Evendor.</div>');
									$this->_redirect('page/index/title/contact-us/');
								} 
								else 
								{	
									$this->view->errorMessage='<div class="div-error">Evendor could not sent any confirmation mail to you but Admin will contact you soon.</div>';
								}
						}
					}		
				}
				else
				{
					 $this->view->errorMessage = '<div class="div-error">Please enter required fields properly.</div>';
				}      
			}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;	
		}
		else
		{
			$this->_redirect('/');
		}
	}
}

