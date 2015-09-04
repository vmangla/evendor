<?php
class Admin_GenresController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelGenres=null;
	
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
		$this->modelPublications=new Admin_Model_DbTable_Publications();
		$this->modelBrand = new Publisher_Model_DbTable_Brand();
		$this->modelGenres=new Admin_Model_DbTable_Genres();
		$this->modelIssue = new Publisher_Model_DbTable_Issues();
		$this->modelEditon = new Publisher_Model_DbTable_Edition();
		//==============================
		
		$this->view->modelPublications=$this->modelPublications;
		$this->view->modelGenre=$this->modelGenre;
		$this->view->modelIssue=$this->modelIssue;
		$this->view->modelEditon=$this->modelEditon;
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		/************* To Set The active section and links *******************/
		
		
		$moduleAccessRow=$this->modelModuleMenus->fetchRow("controller='$controller' AND action LIKE '%".$action."%'");
		
		$currentModuleId=$moduleAccessRow['id'];
		//if(!in_array($currentModuleId,$this->sessAdminInfo->modules))
		//{
		//	$this->_flashMessenger->addMessage('<div class="div-error">"'.$moduleAccessRow['menu_name'].'"   module not accessable to you</div>');
			//$this->_redirect('admin/');
		//}
		
    }
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$genreList=$this->modelGenres->getList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($genreList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($genreList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->genreList=$paginator;
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
			if(trim($formData['genre'])=="")
			{
				$formErrors['genre']="Please enter genre";
			}
			
			if($this->modelGenres->isNameExist("genre='$formData[genre]'"))
			{
				$formErrors['genre']="Genre name already exist.";
			}
			
			if(count($formErrors)==0)
			{
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				$result=$this->modelGenres->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Genre added successfully</div>');
					$this->_redirect('admin/genres/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add genre</div>';
				}
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		$this->view->parentGenreList=$this->modelGenres->getParentList();
    }
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGenres->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelGenres->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*echo "<pre>";
				print_r($formData);
				echo "<pre>";
				exit;*/
				if(trim($formData['genre'])=="")
				{
					$formErrors['genre']="Please enter genre name";
				}
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelGenres->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Genre updated successfully</div>');
					$this->_redirect('admin/genres/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			$this->view->parentGenreList=$this->modelGenres->getParentList();
		}
		else
		{
			$this->_redirect('admin/genres/');
		}
    }
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGenres->isExist($id))
		{
			$success=$this->modelGenres->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Genre deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete genre</div>');
			}
		}
		$this->_redirect('admin/genres/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGenres->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelGenres->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Genre deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate genre</div>');
			}
		}
		$this->_redirect('admin/genres/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGenres->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelGenres->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Genre activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate genre</div>');
			}
		}
		$this->_redirect('admin/genres/');
    }
	
}