<?php
class Admin_PagesController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelPages=null;
	
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
		//===creating model object======
		if($this->modelPages==null){$this->modelPages=new Admin_Model_DbTable_Pages();}
		//==============================
		$pageList=$this->modelPages->fetchAll();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($pageList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($pageList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->pageList=$paginator;
    }
	public function addAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$formData=array();
		$formErrors=array();
		
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			/*echo "<pre>";
			print_r($formData);
			echo "<pre>";
			exit;*/
			if(!(isset($formData['title'])) || trim($formData['title'])=="")
			{
				$formErrors['title']='Please enter page title';
			}
			
			if(count($formErrors)==0)
			{
				//===creating model object======
				if($this->modelPages==null){$this->modelPages=new Admin_Model_DbTable_Pages();}
				//==============================
		
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				$result=$this->modelPages->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Page added successfully</div>');
					$this->_redirect('admin/pages/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add page</div>';
				}
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
    }
	
	
	public function editAction()
    {
		//===creating model object======
		if($this->modelPages==null){$this->modelPages=new Admin_Model_DbTable_Pages();}
		//==============================
					
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPages->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$pagInfo=$this->modelPages->fetchRow('id='.$id);
			$formData=$pagInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*echo "<pre>";
				print_r($formData);
				echo "<pre>";
				exit;*/
				if(!(isset($formData['title'])) || trim($formData['title'])=="")
				{
					$formErrors['title']='Please enter page title';
				}
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelPages->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Page updated successfully</div>');
					$this->_redirect('admin/pages/');
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
			$this->_redirect('admin/pages/');
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelPages==null)
		{
		  $this->modelPages=new Admin_Model_DbTable_Pages();
		}
		
		if($id>0 && $this->modelPages->isExist($id))
		{
			$success=$this->modelPages->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Page deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete page</div>');
			}
		}
		$this->_redirect('admin/pages/');
    }
	
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelPages==null)
		{
		 $this->modelPages=new Admin_Model_DbTable_Pages();
		}
		
		if($id>0 && $this->modelPages->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelPages->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Page deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate page</div>');
			}
		}
		$this->_redirect('admin/pages/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelPages==null)
		{
		 $this->modelPages=new Admin_Model_DbTable_Pages();
		}
				
		if($id>0 && $this->modelPages->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelPages->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Page activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate page</div>');
			}
		}
		$this->_redirect('admin/pages/');
    }
	
}

