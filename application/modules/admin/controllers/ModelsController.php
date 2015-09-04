<?php

class Admin_ModelsController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $modelModels=null;
	
	
    public function init()
    {
		/* creating flashMessanger object to display message */
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login */
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
        if(!$data)
		{
            $this->_redirect('admin/auth/');
        }
		
        $this->view->user_name = $data->first_name.' '.$data->last_name; 

	    /* creating model object */
		$this->modelModels=new Admin_Model_DbTable_Models();
		
    }
	
	
    public function indexAction()
    {
	
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$userList=$this->modelModels->getList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($userList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($userList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->userList=$paginator;
		
    }
	
	public function viewAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelModels->isExist($id))
		{
			$formData=array();
			
			$userInfo=$this->modelModels->fetchRow('id='.$id);
			$formData=$userInfo->toArray();	
			$this->view->formData=$formData;	
			
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelModels->isExist($id))
		{
			$success=$this->modelModels->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Model deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete model</div>');
			}
		}
		$this->_redirect('admin/models/');
    }
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelModels->isExist($id))
		{
		
		    $data['profile_status']=0;
		    $success=$this->modelModels->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Model deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate model</div>');
			}
		}
		$this->_redirect('admin/models/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelModels->isExist($id))
		{
		
		    $data['profile_status']=1;
		    $success=$this->modelModels->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Model activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate model</div>');
			}
		}
		$this->_redirect('admin/models/');
    }
	
	
	/*public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelUsers->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelUsers->fetchRow('id='.$id);
			$formData=$userInfo->toArray();		
				
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelUsers->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Candidate updated successfully</div>');
					$this->_redirect('admin/candidates/');
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
			$this->_redirect('admin/users/');
		}
    }*/
	
	
}

