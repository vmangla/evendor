<?php
class Company_RegisterController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelCompany=null;
	var $modelCompanyProfiles=null;
	var $modelIndustries=null;
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelCompanyProfiles = new Company_Model_DbTable_CompanyProfiles();
		//==========
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
        if($data && $data!=null)
		{
            $this->sessCompanyInfo=$this->modelCompanyProfiles->getInfoByCompanyId($data->id);
			$this->view->sessCompanyInfo =$this->sessCompanyInfo;
        }
		
		$this->modelIndustries=new Company_Model_DbTable_Industries();
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
			if(!(isset($formData['business_name'])) || trim($formData['business_name'])=="")
			{
				$formErrors['business_name']="Please enter your business name";
			}
			if(!(isset($formData['contact_name'])) || trim($formData['contact_name'])=="")
			{
				$formErrors['contact_name']="Please enter contact name";
			}
			if(!(isset($formData['user_name'])) || trim($formData['user_name'])=="")
			{
				$formErrors['user_name']="Please enter username";
			}
			if(!(isset($formData['user_email'])) || trim($formData['user_email'])=="")
			{
				$formErrors['user_email']="Please enter your email";
			}
			if(!(isset($formData['user_password'])) || trim($formData['user_password'])=="")
			{
				$formErrors['user_password']="Please enter your password";
			}
			if(!(isset($formData['verify_user_password'])) || trim($formData['verify_user_password'])=="")
			{
				$formErrors['verify_user_password']="Please enter verify password";
			}
			if($formData['user_password']!=$formData['verify_user_password'])
			{
				$formErrors['verify_user_password']="Your password does not match";
			}
			if(!(isset($formData['agree'])) || trim($formData['agree'])=="")
			{
				$formErrors['agree']="Please check agree";
			}
			if(!(CommonFunctions::isValidEmail($formData['user_email'])))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Please enter valid email";
				}	
			}
			if($this->modelCompany->isExist('user_name="'.$formData['user_name'].'"'))
			{
				if(!(array_key_exists('user_name',$formErrors)))
				{
					$formErrors['user_name']="Username already exist";
				}
			}
			if($this->modelCompany->isExist('user_email="'.$formData['user_email'].'"'))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Email already exist";
				}
			}
			//=====================END FORM VALIDATION===================================
			
			if(count($formErrors)==0)
			{
				$sessionPost = new Zend_Session_Namespace('step1Post');
				$sessionPost->formData=$formData;
				$this->_redirect('company/register/step2');
			}
			else
			{
				 $this->view->errorMessage = '<div class="div-error">Please enter required fieild to register.</div>';
			}      
        }
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	public function step2Action()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$sessionPost = new Zend_Session_Namespace('step1Post');
		if(isset($sessionPost) && $sessionPost!=null && isset($sessionPost->formData) && count($sessionPost->formData)>0)
		{
			//print_r($sessionPost->formData);
			$step1formData=$sessionPost->formData;
			
			$formData=array();
			$formErrors=array();
			
			$formData['business_name']=$step1formData['business_name'];
			
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();

				//print_r($formData);exit;
				//=====================START FORM VALIDATION===================================
				if(!(isset($formData['business_name'])) || trim($formData['business_name'])=="")
				{
					$formErrors['business_name']="Please enter your business name";
				}
				//=====================END FORM VALIDATION===================================
				
				if(count($formErrors)==0)
				{
					//======inserting data to the candidate table===============
					$activationCode=CommonFunctions::generateGUID();
					$activationStartTime=strtotime(date('Y-m-d H:i:s'));
					$activationExpireTime=strtotime(date('Y-m-d H:i:s',strtotime("+1 days")));
					//echo "TIME::::".$activationStartTime."====TIME 2:::".strtotime(date('Y-m-d H:i:s'))."===EXPIRE TIME:::".$activationExpireTime;exit;
					
					$compData=array('user_name'=>$step1formData['user_name'],
									'user_email'=>$step1formData['user_email'],
									'user_password'=>$step1formData['user_password'],
									'added_date'=>date("Y-m-d H:i:s"),
									'updated_date'=>date("Y-m-d H:i:s"),
									'status'=>1,
									'activation_code'=>$activationCode,
									'activation_start_time'=>$activationStartTime,
									'activation_expire_time'=>$activationExpireTime);
					$lastId=$this->modelCompany->insert($compData);
					if($lastId)
					{
						//========unset the session for step1 form data====
						Zend_Session::namespaceUnset('step1Post');
						//=================================================
						
						//======inserting data to the company profile table===============
						$agree=(isset($step1formData['agree']) && $step1formData['agree']!="")?1:0;
						$signup_newsletter=(isset($step1formData['signup_newsletter']) && $step1formData['signup_newsletter']!="")?1:0;
						$notify_jobs=(isset($step1formData['notify_jobs']) && $step1formData['notify_jobs']!="")?1:0;
						
						$profileData=array('company_id'=>$lastId,
											'business_name'=>$formData['business_name'],
											'contact_name'=>$step1formData['contact_name'],
											'post_code'=>$formData['post_code'],
											'state'=>$formData['state'],
											'industry_1'=>$formData['industry_1'],
											'industry_2'=>$formData['industry_2'],
											'about_us'=>$formData['about_us'],
											'opening_hours'=>$formData['opening_hours'],
											'telephone'=>$formData['telephone'],
											'website'=>$formData['website'],
											'abn'=>$formData['abn'],
											'acn'=>$formData['acn'],
											'facebook_url'=>$formData['facebook_url'],
											'twitter_url'=>$formData['twitter_url'],
											'shifte_url'=>$formData['shifte_url'],
											'agree'=>$agree,
											'signup_newsletter'=>$signup_newsletter,
											'notify_jobs'=>$notify_jobs,
											'added_date'=>date("Y-m-d H:i:s"),
											'updated_date'=>date("Y-m-d H:i:s")
											);
						$profileId=$this->modelCompanyProfiles->insert($profileData);
						
						if($profileId>0)
						{
							/**** Uploading Logo File on Server*****/
							$upload = new Zend_File_Transfer_Adapter_Http();
							$upload->setDestination(COMPANY_UPLOAD_DIR);
							$files = $upload->getFileInfo();
							if(isset($files) && count($files)>0)
							{		
								$i=1;
								foreach($files as $file => $info) 
								{
									if($info['name']!="")
									{
										if($upload->isValid($file))
										{
											try
											{
												// upload received file(s)
												$upload->receive($file);		
											}
											catch (Zend_File_Transfer_Exception $e) 
											{
												//echo $e->getMessage();//exit;
											}
											 // so, Finally lets See the Data that we received on Form Submit
											 $name = $upload->getFileName($file);
											 $size = $upload->getFileSize($file);
											 # Returns the mimetype for the '$file' form element
											 $mimeType = $upload->getMimeType($file);
											 $renameFile = time().$i.'.jpg';
											 $fullFilePath = COMPANY_UPLOAD_DIR.$renameFile;
											 //Rename uploaded file using Zend Framework
											 $filterFileRename=new Zend_Filter_File_Rename(array('target' =>$fullFilePath,'overwrite' => true));
											 $filterFileRename->filter($name);

											$logoData=array('logo'=>$renameFile);
											$this->modelCompanyProfiles->update($logoData,'id='.$profileId);
										}
									}
									$i++;	
								}
							}	
							/*****End Uploading************/
						}
						$this->_redirect('company/register/thanks');
					}
					else
					{
						 $this->view->errorMessage = '<div class="div-error">Please enter required fieild to register.</div>';
					} 
				}					
				else
				{
					 $this->view->errorMessage = '<div class="div-error">Sorry, unable to register, please try later.</div>';
				}
			}
			
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			$this->view->industryList=$this->modelIndustries->fetchAll('status=1');
		}
		else
		{
			$this->_flashMessenger->addMessage('<div class="div-error">Please enter required fieild to register.</div>');
			$this->_redirect('company/register/');
		}
	}
	public function thanksAction()
	{
	}
}

