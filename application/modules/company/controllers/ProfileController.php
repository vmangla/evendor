<?php
class Company_ProfileController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelCompany=null;
	
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
        if(!$data)
		{
            $this->_redirect('company/auth/');
        }
		$this->sessCompanyInfo=$data;
        
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
		$this->view->sessCompanyInfo =$this->sessCompanyInfo; 
    }
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$CompanyInfo=$this->modelCompany->fetchRow('id='.$this->sessCompanyInfo->id);
		$profileInfo=$CompanyInfo->toArray();
		//print_r($profileInfo);exit;
		$formData=$this->getRequest()->getPost();
		//print_r($formData);exit;
		$this->view->profileInfo=$profileInfo;
		$this->view->formData=$formData;
	
    }
	public function changepasswordAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$formData=array();
		$formErrors=array();
		
		$CompanyInfo=$this->modelCompany->fetchRow('id='.$this->sessCompanyInfo->id);
		
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			if(!empty($formData['change_password']) && $formData['change_password']=='Update Password')
			{
			/*echo "<pre>";
			print_r($formData);
			echo "<pre>";
			exit;*/
				if(!(isset($formData['password'])) || trim($formData['password'])=="")
				{
					$formErrors['password']='Please enter your current password';
				}
				if(!(isset($formData['new_password'])) || trim($formData['new_password'])=="")
				{
					$formErrors['new_password']='Please enter your new password';
				}
				if(!(isset($formData['conf_new_password'])) || trim($formData['conf_new_password'])=="")
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				if(md5($formData['password'])!=$CompanyInfo->user_password)
				{
					$formErrors['password']='Invalid current password';
				}
				if($formData['new_password']!=$formData['conf_new_password'])
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				
				if(count($formErrors)==0)
				{
					$updateData['user_password']=md5($formData['new_password']);
					$result=$this->modelCompany->update($updateData,'id='.$this->sessCompanyInfo->id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
					$this->_redirect('company/profile/changepassword/');
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
	
	public function updateprofileAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$formData=array();
		$formErrors=array();
		
		$CompanyInfo=$this->modelCompany->fetchRow('id='.$this->sessCompanyInfo->id);
		$formData=$CompanyInfo->toArray();
		//print_r($formData);exit;
			
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			$tab_ajax=$formData['tab_ajax'];
			if(!empty($formData['update_profile']) && $formData['update_profile']=='Update Profile')
			{
			
				/*if(!(isset($formData['user_type'])) || trim($formData['user_type'])=="")
				{
					$formErrors['user_type']="Please select user type ";
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
				
				
				/*if(!(isset($formData['location'])) || trim($formData['location'])=="")
				{
					$formErrors['location']="Please enter location";
				}
				if(!(isset($formData['city'])) || trim($formData['city'])=="")
				{
					$formErrors['city']="Please enter city";
				}
				if(!(isset($formData['state'])) || trim($formData['state'])=="")
				{
					$formErrors['state']="Please enter state";
				}
				*/
				if(!(isset($formData['country'])) || trim($formData['country'])=="")
				{
					$formErrors['country']="Please enter country";
				}
				if(!(isset($formData['name'])) || trim($formData['name'])=="")
				{
					$formErrors['name']="Please enter company name";
				}
				
				/*if(!(isset($formData['pincode'])) || trim($formData['pincode'])=="")
				{
					$formErrors['pincode']="Please enter pincode";
				}
				*/
			
				if(count($formErrors)==0)
				{
					$CompanyData=array(
										//'user_type'=>$formData['user_type'],
										'name'=>$formData['name'],
										'first_name'=>$formData['first_name'],
										'last_name'=>$formData['last_name'],
										'country'=>$formData['country'],
										'phone'=>$formData['phone'],
										'updated_date'=>date("Y-m-d H:i:s"),
										);
					$result=$this->modelCompany->update($CompanyData,'id='.$this->sessCompanyInfo->id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Profile updated successfully</div>');
					$this->_redirect('company/profile/updateprofile/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			else
			{
				$CompanyInfo=$this->modelCompany->fetchRow('id='.$this->sessCompanyInfo->id);
				
				$formData=$CompanyInfo->toArray();
				$formData['tab_ajax']=$tab_ajax;
				//print_r($formData);exit;
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	
}

