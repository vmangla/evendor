<?php
class Publisher_ProfileController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	
	var $modelPublishers=null;
	
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
        if($data && $data!=null)
		{
            $this->modelPublishers = new Publisher_Model_DbTable_Publishers();
			$this->sessPublisherInfo=$this->modelPublishers->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo; 
			
			$tab_ajax=$this->_request->getParam('tab_ajax',0);
			if(empty($tab_ajax))
			{
				$this->_redirect('publisher/');
			}
		}
		else
		{
			$this->_redirect('publisher/auth/');
		}
	}
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$publisherInfo=$this->modelPublishers->fetchRow('id='.$this->sessPublisherInfo->id);
		$profileInfo=$publisherInfo->toArray();
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
		
		$publisherInfo=$this->modelPublishers->fetchRow('id='.$this->sessPublisherInfo->id);
		
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
				if(md5($formData['password'])!=$publisherInfo->password)
				{
					$formErrors['password']='Invalid current password';
				}
				if($formData['new_password']!=$formData['conf_new_password'])
				{
					$formErrors['conf_new_password']='Confirm password does not match';
				}
				
				if(count($formErrors)==0)
				{
					$updateData['password']=md5($formData['new_password']);
					$result=$this->modelPublishers->update($updateData,'id='.$this->sessPublisherInfo->id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Password changed successfully</div>');
					$this->_redirect('publisher/profile/changepassword/tab_ajax/profile/');
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
		
		$publisherInfo=$this->modelPublishers->fetchRow('id='.$this->sessPublisherInfo->id);
		$formData=$publisherInfo->toArray();
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
				if(!(isset($formData['pub_name'])) || trim($formData['pub_name'])=="")
				{
					$formErrors['pub_name']="Please enter name.";
				}
				if(!(isset($formData['first_name'])) || trim($formData['first_name'])=="")
				{
					$formErrors['first_name']="Please enter first name.";
				}
				if(!(isset($formData['last_name'])) || trim($formData['last_name'])=="")
				{
					$formErrors['last_name']="Please enter last name.";
				}
				if(!(isset($formData['publisher'])) || trim($formData['publisher'])=="")
				{
					$formErrors['publisher']="Please enter publisher name.";
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
				if(!(isset($formData['c_country'])) || trim($formData['country'])=="")
				{
					$formErrors['c_country']="Please enter country.";
				}
				
				if(!(isset($formData['c_state'])) || trim($formData['c_state'])=="")
				{
					$formErrors['c_state']="Please enter state.";
				}
				if(!(isset($formData['c_city'])) || trim($formData['c_city'])=="")
				{
					$formErrors['c_city']="Please enter city";
				}
				if(!(isset($formData['account_no'])) || trim($formData['account_no'])=="")
				{
					$formErrors['account_no']="Please enter account no.";
				}
				if(!(isset($formData['account_holder_name'])) || trim($formData['account_holder_name'])=="")
				{
					$formErrors['account_holder_name']="Please enter account holder name.";
				}
				/*if(!(isset($formData['pincode'])) || trim($formData['pincode'])=="")
				{
					$formErrors['pincode']="Please enter pincode";
				}
				*/
			
				if(count($formErrors)==0)
				{
					$publisherData=array(
										//'user_type'=>$formData['user_type'],
										'first_name'=>$formData['first_name'],
										'last_name'=>$formData['last_name'],
										'publisher'=>$formData['publisher'],
										'location'=>$formData['location'],
										'city'=>$formData['city'],
										'state'=>$formData['state'],
										'country'=>$formData['country'],
										'pincode'=>$formData['pincode'],
										'phone'=>$formData['phone'],
										
										'name'=>$formData['pub_name'],
										'c_country'=>$formData['c_country'],
										'c_state'=>$formData['c_state'],
										'c_city'=>$formData['c_city'],
										'c_phone'=>$formData['c_phone'],
										'account_no'=>$formData['account_no'],
										'account_holder_name'=>$formData['account_holder_name'],
										'banker'=>$formData['banker'],
										'routing_number'=>$formData['routing_number'],
										'updated_date'=>date("Y-m-d H:i:s"),
										);
					$result=$this->modelPublishers->update($publisherData,'id='.$this->sessPublisherInfo->id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Profile updated successfully</div>');
					$this->_redirect('publisher/profile/updateprofile/tab_ajax/profile/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			else
			{
				$publisherInfo=$this->modelPublishers->fetchRow('id='.$this->sessPublisherInfo->id);
				
				$formData=$publisherInfo->toArray();
				$formData['tab_ajax']=$tab_ajax;
				//print_r($formData);exit;
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	
}

