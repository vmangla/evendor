<?php
class User_RegisterController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessUserInfo=null;
	
	var $modelCandidates=null;
	var $modelCandidateProfiles=null;
	var $modelCandidateEducationHistory=null;
	var $modelCandidateEmploymentHistory=null;
	var $modelCandidateSkillsSpecializations=null;
	var $modelCandidateJobPrefernces=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelCandidates = new User_Model_DbTable_Candidates();
		$this->modelCandidateProfiles = new User_Model_DbTable_CandidateProfiles();
		//==========
		$storage = new Zend_Auth_Storage_Session('user_type');
        $data = $storage->read();
        if($data && $data!=null)
		{
			$this->_redirect('user/');
           // $this->sessUserInfo=$this->modelCandidateProfiles->getInfoByUserId($data->id);
			//$this->view->sessUserInfo =$this->sessUserInfo;
        }
		
		$this->modelCandidateEducationHistory=new User_Model_DbTable_CandidateEducationHistory();
		$this->modelCandidateEmploymentHistory=new User_Model_DbTable_CandidateEmploymentHistory();
		$this->modelCandidateSkillsSpecializations=new User_Model_DbTable_CandidateSkillsSpecializations();
		$this->modelCandidateJobPrefernces=new User_Model_DbTable_CandidateJobPrefernces();
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
			if(!(isset($formData['user_name'])) || trim($formData['user_name'])=="")
			{
				$formErrors['user_name']="Please enter user name";
			}
			if(!(isset($formData['user_email'])) || trim($formData['user_email'])=="")
			{
				$formErrors['user_email']="Please enter your email";
			}
			if(!(isset($formData['user_password'])) || trim($formData['user_password'])=="")
			{
				$formErrors['user_password']="Please enter your password";
			}
			if(!(isset($formData['user_password'])) || trim($formData['user_password'])=="")
			{
				$formErrors['user_password']="Please enter your password";
			}
			if(!(isset($formData['verify_user_password'])) || trim($formData['verify_user_password'])=="")
			{
				$formErrors['verify_user_password']="Please enter verify password";
			}
			if(!(isset($formData['agree'])) || trim($formData['agree'])=="")
			{
				$formErrors['agree']="Please check agree";
			}
			if($formData['user_password']!=$formData['verify_user_password'])
			{
				$formErrors['verify_user_password']="Your password does not match";
			}
			if(!(CommonFunctions::isValidEmail($formData['user_email'])))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Please enter valid email";
				}	
			}
			if($this->modelCandidates->isExist('user_name="'.$formData['user_name'].'"'))
			{
				if(!(array_key_exists('user_name',$formErrors)))
				{
					$formErrors['user_name']="Username already exist";
				}
			}
			if($this->modelCandidates->isExist('user_email="'.$formData['user_email'].'"'))
			{
				if(!(array_key_exists('user_email',$formErrors)))
				{
					$formErrors['user_email']="Email already exist";
				}
			}
			//=====================END FORM VALIDATION===================================
			
			if(count($formErrors)==0)
			{
				//======inserting data to the candidate table===============
				$activationCode=CommonFunctions::generateGUID();
				$activationStartTime=strtotime(date('Y-m-d H:i:s'));
				$activationExpireTime=strtotime(date('Y-m-d H:i:s',strtotime("+1 days")));
				//echo "TIME::::".$activationStartTime."====TIME 2:::".strtotime(date('Y-m-d H:i:s'))."===EXPIRE TIME:::".$activationExpireTime;exit;
				
				$candidateData=array('user_name'=>$formData['user_name'],
								'user_email'=>$formData['user_email'],
								'user_password'=>$formData['user_password'],
								'added_date'=>date("Y-m-d H:i:s"),
								'updated_date'=>date("Y-m-d H:i:s"),
								'status'=>0,
								'activation_code'=>$activationCode,
								'activation_start_time'=>$activationStartTime,
								'activation_expire_time'=>$activationExpireTime);
				$lastId=$this->modelCandidates->insert($candidateData);
				if($lastId)
				{
					//======inserting data to the candidate profile table===============
					$agree=(isset($formData['agree']) && $formData['agree']!="")?1:0;
					$signup_newsletter=(isset($formData['signup_newsletter']) && $formData['signup_newsletter']!="")?1:0;
					$notify_jobs=(isset($formData['notify_jobs']) && $formData['notify_jobs']!="")?1:0;
						
					$profileData=array('user_id'=>$lastId,
								'first_name'=>$formData['first_name'],
								'last_name'=>$formData['last_name'],
								'agree'=>$agree,
								'signup_newsletter'=>$signup_newsletter,
								'notify_jobs'=>$notify_jobs,
								'added_date'=>date("Y-m-d H:i:s"),
								'updated_date'=>date("Y-m-d H:i:s")
								);
					$this->modelCandidateProfiles->insert($profileData);
					
					$sessionPost = new Zend_Session_Namespace('step1Post');
					$sessionPost->user_id=$lastId;
					$this->_redirect('user/register/step2');
				}
				else
				{
					 $this->view->errorMessage = '<div class="div-error">Sorry, unable to register, please try later.</div>';
				}
			}
			else
			{
				 $this->view->errorMessage = '<div class="div-error">Please enter email required field to register.</div>';
			}      
        }
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	public function step2Action()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$sessionPost = new Zend_Session_Namespace('step1Post');
		if(isset($sessionPost) && $sessionPost!=null && isset($sessionPost->user_id) && $sessionPost->user_id!="")
		{
			$formData=array();
			$formErrors=array();
				
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();

				//print_r($formData);exit;
				//=====================START FORM VALIDATION===================================
				if(!(isset($formData['location'])) || trim($formData['location'])=="")
				{
					$formErrors['location']="Please enter your location";
				}
				if(!(isset($formData['day'])) || trim($formData['day'])=="")
				{
					$formErrors['day']="Please select your date of birth";
				}
				if(!(isset($formData['month'])) || trim($formData['month'])=="")
				{
					$formErrors['month']="Please select your date of birth";
				}
				if(!(isset($formData['year'])) || trim($formData['year'])=="")
				{
					$formErrors['year']="Please select your date of birth";
				}
				//=====================END FORM VALIDATION===================================
				
				if(count($formErrors)==0)
				{
					$dob=$formData['year'].'-'.$formData['month'].'-'.$formData['day'];
					
					$profileData=array('location'=>$formData['location'],'date_of_birth'=>$dob,'about_me'=>$formData['about_me'],'updated_date'=>date("Y-m-d H:i:s"));
					$this->modelCandidateProfiles->update($profileData,'user_id='.$sessionPost->user_id);
					
					$jobPrefData=array('user_id'=>$sessionPost->user_id,'distance'=>$formData['distance'],'post_code'=>$formData['post_code'],
									'available_type'=>$formData['available_type'],'days'=>$formData['days'],'hours'=>$formData['hours'],
									'industry_1'=>$formData['industry_1'],'industry_2'=>$formData['industry_2'],
									'added_date'=>date("Y-m-d H:i:s"),'updated_date'=>date("Y-m-d H:i:s"));
					$this->modelCandidateJobPrefernces->insert($jobPrefData);
					
					/**** Uploading Logo File on Server*****/
					$upload = new Zend_File_Transfer_Adapter_Http();
					$upload->setDestination(USER_UPLOAD_DIR);
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
									 $fullFilePath = USER_UPLOAD_DIR.$renameFile;
									 //Rename uploaded file using Zend Framework
									 $filterFileRename=new Zend_Filter_File_Rename(array('target' =>$fullFilePath,'overwrite' => true));
									 $filterFileRename->filter($name);

									$logoData=array('profile_picture'=>$renameFile);
									$this->modelCandidateProfiles->update($logoData,'user_id='.$sessionPost->user_id);
								}
							}
							$i++;	
						}
					}	
					/*****End Uploading************/
						
					$this->_redirect('user/register/step3');
				}					
				else
				{
					 $this->view->errorMessage = '<div class="div-error">Sorry, unable to register, please try later.</div>';
				}
			}
			
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_flashMessenger->addMessage('<div class="div-error">Please enter required fieild to register.</div>');
			$this->_redirect('user/register/');
		}
	}	
	public function step3Action()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$sessionPost = new Zend_Session_Namespace('step1Post');
		if(isset($sessionPost) && $sessionPost!=null && isset($sessionPost->user_id) && $sessionPost->user_id!="")
		{
			$formData=array();
			$formErrors=array();
				
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				//print_r($formData);exit;
				//=====================START FORM VALIDATION===================================
		
				//=====================END FORM VALIDATION===================================
				
				if(count($formErrors)==0)
				{
					//=========insert education history========================
					if(count($formData['education_type'])>0 && count($formData['school_name'])>0 && count($formData['years_attended'])>0)
					{
						for($e=0;$e<count($formData['education_type']);$e++)
						{
							$education_type=$formData['education_type'][$e];
							$school_name=$formData['school_name'][$e];
							$years_attended=$formData['years_attended'][$e];
							if((isset($education_type) && $education_type!="") && (isset($school_name) && $school_name!="") && (isset($years_attended) && $years_attended!=""))
							{
								$educationData=array('user_id'=>$sessionPost->user_id,'education_type'=>$education_type,'school_name'=>$school_name,'years_attended'=>$years_attended,
													'added_date'=>date("Y-m-d H:i:s"),'updated_date'=>date("Y-m-d H:i:s"));
								$this->modelCandidateEducationHistory->insert($educationData);
							}		
						}
					}
					//=========insert employment history========================
					if(count($formData['employment_company'])>0)
					{
					
						for($em=0;$em<count($formData['employment_company']);$em++)
						{
							$employment_company=$formData['employment_company'][$em];
							$employment_role=$formData['employment_role'][$em];
							$employment_location=$formData['employment_location'][$em];
							$employment_month=$formData['employment_month'][$em];
							$employment_year=$formData['employment_year'][$em];
							$employment_responsibilities=$formData['employment_responsibilities'][$em];
							$employment_achievements=$formData['employment_achievements'][$em];
							//$employment_file_references=$formData['employment_file_references'][$em];
							if((isset($employment_company) && $employment_company!=""))
							{
								$employmentData=array('user_id'=>$sessionPost->user_id,'employment_company'=>$employment_company,'employment_role'=>$employment_role,
													'employment_location'=>$employment_location,'employment_month'=>$employment_month,
													'employment_year'=>$employment_year,'employment_responsibilities'=>$employment_responsibilities,
													'employment_achievements'=>$employment_achievements,
													'added_date'=>date("Y-m-d H:i:s"),'updated_date'=>date("Y-m-d H:i:s"));
								$empId=$this->modelCandidateEmploymentHistory->insert($employmentData);
								
								if(isset($empId) && $empId>0)
								{
									/**** Uploading File on Server*****/
									$upload = new Zend_File_Transfer_Adapter_Http();
									$upload->setDestination(USER_UPLOAD_DIR);
									$files = $upload->getFileInfo('employment_file_references');
									if(isset($files) && count($files)>0)
									{	
										$i=1;
										foreach($files as $file => $info) 
										{
											//echo "<pre>";
											//print_r($file);
											//echo "</pre>";
											//exit;
											if($file=='employment_file_references_'.$em.'_' && $info['name']!="")
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
													 $renameFile = time().$i.$info['name'];
													 //echo $renameFile;exit;
													 $fullFilePath = USER_UPLOAD_DIR.$renameFile;
													 //Rename uploaded file using Zend Framework
													 $filterFileRename=new Zend_Filter_File_Rename(array('target' =>$fullFilePath,'overwrite' => true));
													 $filterFileRename->filter($name);

													$logoData=array('employment_file_references'=>$renameFile);
													$this->modelCandidateEmploymentHistory->update($logoData,'id='.$empId);
												}
											}
											$i++;	
										}
									}	
									/*****End Uploading************/
								}
							}		
						}
					}
					//=========insert skills and specialization========================
					if(isset($formData['specializations']) && count($formData['specializations'])>0)
					{
						$specializations=implode(",",$formData['specializations']);
						$skills=implode(",",$formData['skills']);
						
							$skillsData=array('user_id'=>$sessionPost->user_id,'specializations'=>$specializations,
												'skills'=>$skills,'driving_license'=>$driving_license,	
												'added_date'=>date("Y-m-d H:i:s"),'updated_date'=>date("Y-m-d H:i:s"));
							$this->modelCandidateSkillsSpecializations->insert($skillsData);
					}
					
					//========unset the session of step1 form data====
					Zend_Session::namespaceUnset('step1Post');
					//=================================================
					$this->_redirect('user/register/thanks');
				}					
				else
				{
					 $this->view->errorMessage = '<div class="div-error">Sorry, unable to add, please try later.</div>';
				}
			}
			
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_flashMessenger->addMessage('<div class="div-error">Please enter required field to register.</div>');
			$this->_redirect('user/register/');
		}
	}
	public function thanksAction()
	{
	}
}

