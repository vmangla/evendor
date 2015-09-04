<?php
class Admin_AdminUsersController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $sessAdminInfo=null;
	
	var $modelUsers=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
	
    public function init()
    {
		/* creating flashMessanger object to display message */
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login */
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
		
		if($data)
		{
		$this->modelAdmiUsers = new Admin_Model_DbTable_AdminUsers();
		$this->sessAdminInfo=$this->modelAdmiUsers->fetchRow("id=".$data->id);
		}
		
		if(!$this->sessAdminInfo)
		{
            $this->_redirect('admin/auth/');
        }
		
		$this->sessAdminInfo->modules=explode(",",$this->sessAdminInfo->modules);
		$this->view->sessAdminInfo =$this->sessAdminInfo;
			
		/* creating model object */
		$this->modelModuleMenus = new Admin_Model_DbTable_ModulesMenus();
		$this->view->modelModuleMenus = $this->modelModuleMenus->getModuleMenusList();
		$this->modelUsers=new Admin_Model_DbTable_Users();
		$this->modelAdminUsers=new Admin_Model_DbTable_SubAdminUsers();
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		/************* To Set The active section and links *******************/
		$moduleAccessRow=$this->modelModuleMenus->fetchRow("controller='$controller' AND action LIKE '%".$action."%'");
		
		$currentModuleId=$moduleAccessRow['id'];
		if(!in_array($currentModuleId,$this->sessAdminInfo->modules))
		{
			$this->_flashMessenger->addMessage('<div class="div-error">"'.$moduleAccessRow['menu_name'].'"   module not accessable to you</div>');
			$this->_redirect('admin/');
		}
		
    }
	
	
    public function indexAction()
    {
	   
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$formData=array();
		$formErrors=array();		
		$formDataSave=array();
		
		if($this->getRequest()->isPost())
		{
		    $formData=$this->getRequest()->getPost();
			if(isset($formData['modulename']))
			{
			
				$module="";
							
				for($ii=0;$ii<count($formData['modulename']);$ii++)
				{
				   $module.=$formData['modulename'][$ii].",";
				}
				
				$modules = substr($module, 0, -1);
			
				
				if(!isset($modules) || trim($modules)=="")
				{
					$formErrors['modulename']="Please select atleact one module";
				}
				if(!isset($formData['firstname']) || trim($formData['firstname'])=="")
				{
					$formErrors['firstname']="Please enter first name";
				}
				if(!(isset($formData['lastname'])) || trim($formData['lastname'])=="")
				{
					$formErrors['lastname']="Please enter last name";
				}
				if(!(isset($formData['username'])) || trim($formData['username'])=="")
				{
					$formErrors['username']="Please enter username";
				}
				if(!(isset($formData['password'])) || trim($formData['password'])=="")
				{
					$formErrors['password']="Please enter password";
				}
				if(!(isset($formData['email'])) || trim($formData['email'])=="")
				{
					$formErrors['email']="Please enter email";
				}
								
				if(!(CommonFunctions::isValidEmail($formData['email'])))
				{
					if(!(array_key_exists('email',$formErrors)))
					{
						$formErrors['email']="Please enter valid email";
					}	
				}
				
				
				if(count($formErrors)==0)
				{
				
					$formDataSave['first_name']   =$formData['firstname'];
					$formDataSave['last_name']    =$formData['lastname'];
					$formDataSave['user_email']   =$formData['email'];
					$formDataSave['user_name']    =$formData['username'];
					$formDataSave['user_password']=$formData['password'];
					$formDataSave['modules']      =$modules;
					$formDataSave['is_active']    =1;
					
					$result=$this->modelAdminUsers->insert($formDataSave);
					
					if($result>0)
					{
						$this->_flashMessenger->addMessage('<div class="div-success">Sub-Admin user added successfully</div>');
						$this->_redirect('admin/adminusers/');
					}
					else
					{
						$this->view->errorMessage='<div class="div-error">Sorry!, unable to add sub admin user</div>';
					}
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required fields properly.</div>';
				}
			}	
		}
		$this->view->formData=$formData;
		
		if(isset($formData['status']) && trim($formData['status'])!="")
		{
		  $status=trim($formData['status']);
		  
		  $whrcls="";
		  $whrcls.=" id!='1' AND is_active='".$status."'";
		   
		  $userList=$this->modelAdminUsers->fetchAll($whrcls);
		}
		else
		{
			$userList=$this->modelAdminUsers->getList();
		}
		$modleMenusList = $this->modelModuleMenus->getModuleMenusList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($userList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($userList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->userList=$paginator;
		
		$this->view->modleMenusList=$modleMenusList;
	}
	
	

	/*public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAdminUsers->isExist($id))
		{
			$success=$this->modelAdminUsers->delete('id='.$id);
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Sub-Admin user deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete User</div>');
			}
			
		}
		$this->_redirect('admin/adminusers/');
    }
	*/
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAdminUsers->isExist($id))
		{
		
		    $data['is_active']=0;
		    $success=$this->modelAdminUsers->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">User deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate user</div>');
			}
		}
		$this->_redirect('admin/adminusers/');
    }
	
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAdminUsers->isExist($id))
		{
		
		    $data['is_active']=1;
		    $success=$this->modelAdminUsers->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">User activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate user</div>');
			}
		}
		$this->_redirect('admin/adminusers/');
    }
	
	
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAdminUsers->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			$formDataSave=array();
		
			$userInfo=$this->modelAdminUsers->fetchRow('id='.$id);
			$formData=$userInfo->toArray();		
				
			if($this->getRequest()->isPost())
			{
				    $formData=$this->getRequest()->getPost();
				
					$module="";
					
					for($ii=0;$ii<count($formData['modulename']);$ii++)
					{
					$module.=$formData['modulename'][$ii].",";
					}
					
					$modules = substr($module, 0, -1);
					
				if(!isset($modules) || trim($modules)=="")
				{
					$formErrors['modulename']="Please select atleact one module";
				}
				if(!isset($formData['firstname']) || trim($formData['firstname'])=="")
				{
					$formErrors['firstname']="Please enter first name";
				}
				if(!(isset($formData['lastname'])) || trim($formData['lastname'])=="")
				{
					$formErrors['lastname']="Please enter last name";
				}
				if(!(isset($formData['username'])) || trim($formData['username'])=="")
				{
					$formErrors['username']="Please enter username";
				}
				if(!(isset($formData['password'])) || trim($formData['password'])=="")
				{
					$formErrors['password']="Please enter password";
				}
				if(!(isset($formData['email'])) || trim($formData['email'])=="")
				{
					$formErrors['email']="Please enter email";
				}
								
				if(!(CommonFunctions::isValidEmail($formData['email'])))
				{
					if(!(array_key_exists('email',$formErrors)))
					{
						$formErrors['email']="Please enter valid email";
					}	
				}
				
				if(count($formErrors)==0)
				{
				
				
					$formDataSave['first_name']   =$formData['firstname'];
					$formDataSave['last_name']    =$formData['lastname'];
					$formDataSave['user_email']   =$formData['email'];
					$formDataSave['user_name']    =$formData['username'];
					$formDataSave['user_password']=$formData['password'];
					$formDataSave['modules']      =$modules;
					
					$result=$this->modelAdminUsers->update($formDataSave,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Sub-Admin user updated successfully</div>');
					$this->_redirect('admin/adminusers/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required fields properly.</div>';
				}	
					
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('admin/adminusers/');
		}
    }
	
	
}

