<?php
class Admin_CompaniesController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
	
	var $modelCompanies=null;
	
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
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
		
		$this->modelModuleMenus = new Admin_Model_DbTable_ModulesMenus();
		$this->view->modelModuleMenus = $this->modelModuleMenus->getModuleMenusList();

		//===creating model object======	
		$this->modelCompanies=new Admin_Model_DbTable_Companies();
		//==============================
		
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
		
		$userList=$this->modelCompanies->getList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($userList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($userList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->userList=$paginator;
    }
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompanies->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelCompanies->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*echo "<pre>";
				print_r($formData);
				echo "<pre>";
				exit;*/
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelCompanies->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Company updated successfully</div>');
					$this->_redirect('admin/companies/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('admin/companies/');
		}
    }
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCompanies->isExist($id))
		{
			$success=$this->modelCompanies->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Company deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete company</div>');
			}
		}
		$this->_redirect('admin/companies/');
    }
}

