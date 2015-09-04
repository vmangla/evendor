<?php
class Admin_CandidatesController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $modelCandidates=null;
	
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
        if(!$data)
		{
            $this->_redirect('admin/auth/');
        }
        $this->view->user_name = $data->first_name.' '.$data->last_name; 

		//===creating model object======	
		$this->modelCandidates=new Admin_Model_DbTable_Candidates();
		//==============================
    }
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$userList=$this->modelCandidates->getList();
		
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
		
		if($id>0 && $this->modelCandidates->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelCandidates->fetchRow('id='.$id);
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
					$result=$this->modelCandidates->update($formData,'id='.$id);
					
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
			$this->_redirect('admin/candidates/');
		}
    }
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCandidates->isExist($id))
		{
			$success=$this->modelCandidates->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Candidate deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete candidate</div>');
			}
		}
		$this->_redirect('admin/candidates/');
    }
}

