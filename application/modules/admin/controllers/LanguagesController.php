<?php
class Admin_LanguagesController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
	
	var $modelLanguages=null;
	
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

		$this->modelLanguages=new Admin_Model_DbTable_Languages();
		
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
		if($this->modelLanguages==null){$this->modelLanguages=new Admin_Model_DbTable_Languages();}
		//==============================
		
		$languageList=$this->modelLanguages->fetchAll();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($languageList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($languageList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->languageList=$paginator;
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
			if(!(isset($formData['language_name'])) || trim($formData['language_name'])=="")
			{
				$formErrors['language_name']='Please enter language';
			}
			
			if($this->modelLanguages->isNameExist("language_name='$formData[language_name]'"))
			{
				$formErrors['language_name']="Language name already exist.";
			}
			
			if(count($formErrors)==0)
			{
				//===creating model object======
				if($this->modelLanguages==null){$this->modelLanguages=new Admin_Model_DbTable_Languages();}
				//==============================
		
				//$formData['added_date']=date('Y-m-d H:i:s');
				//$formData['updated_date']=date('Y-m-d H:i:s');
				$result=$this->modelLanguages->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Language added successfully</div>');
					$this->_redirect('admin/languages/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add language</div>';
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
		if($this->modelLanguages==null){$this->modelLanguages=new Admin_Model_DbTable_Languages();}
		//==============================
					
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelLanguages->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$faqInfo=$this->modelLanguages->fetchRow('id='.$id);
			$formData=$faqInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*echo "<pre>";
				print_r($formData);
				echo "<pre>";
				exit;*/
				if(!(isset($formData['language_name'])) || trim($formData['language_name'])=="")
				{
					$formErrors['language_name']='Please enter language_name';
				}
				
				if(count($formErrors)==0)
				{
					//$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelLanguages->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Language updated successfully</div>');
					$this->_redirect('admin/languages/');
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
			$this->_redirect('admin/languages/');
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelLanguages==null)
		{
		  $this->modelLanguages=new Admin_Model_DbTable_Languages();
		}
		
		if($id>0 && $this->modelLanguages->isExist($id))
		{
			$success=$this->modelLanguages->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Language deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete language/div>');
			}
		}
		$this->_redirect('admin/languages/');
    }
	
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelLanguages==null)
		{
		 $this->modelLanguages=new Admin_Model_DbTable_Languages();
		}
		
		if($id>0 && $this->modelLanguages->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelLanguages->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Language deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate language</div>');
			}
		}
		$this->_redirect('admin/languages/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelLanguages==null)
		{
		 $this->modelLanguages=new Admin_Model_DbTable_Languages();
		}
				
		if($id>0 && $this->modelLanguages->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelLanguages->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Language activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to active language</div>');
			}
		}
		$this->_redirect('admin/languages/');
    }
	
}

