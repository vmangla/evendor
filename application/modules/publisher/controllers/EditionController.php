<?php
class Publisher_EditionController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $sessPublisherInfo=null;
	var $parentPublisherId=null;
	
	var $modelPublisher=null;
	var $modelEdition=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
					
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
		if($data && $data!=null)
		{
            $this->modelPublisher = new Publisher_Model_DbTable_Publishers();
			$this->modelEdition=new Publisher_Model_DbTable_Edition();
			
			$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo;
			$this->view->modelEdition=$this->modelEdition;
			
			$this->parentPublisherId = !empty($this->sessPublisherInfo->parent_id)?$this->sessPublisherInfo->parent_id:$this->sessPublisherInfo->id;
			
			$tab_ajax=$this->_request->getParam('tab_ajax',0);
			if(empty($tab_ajax))
			{
				$this->_redirect('publisher/');
			}
			
			if($data->user_type!='publisher' && $data->user_type!='Pmanager')
			{
			$this->_redirect('publisher/access/index/tab_ajax/edition/');
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
		
		$editionList=$this->modelEdition->getList($this->parentPublisherId);
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($editionList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($editionList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->editionList=$paginator;
		
		$this->view->parentPublisherId=$this->parentPublisherId;
			
	}
	public function addAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$formData=array();
		$formErrors=array();
		
		$formData =$this->getRequest()->getPost();
		
		if($this->getRequest()->isPost() && isset($formData['add_edition']) && $formData['add_edition']=='Add Edition')
		{
			
			if(trim($formData['edition'])=="")
			{
				$formErrors['edition']="Please enter edition";
			}
			
			if($this->modelEdition->isExist('edition="'.$formData['edition'].'" AND publisher_author_id="'.$this->parentPublisherId.'"'))
			{
				if(!(array_key_exists('edition',$formErrors)))
				{
					$formErrors['edition']="Edition already exist";
				}
			}
			
			if(count($formErrors)==0)
			{
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				
				$editionData=array(
								'edition'=>$formData['edition'],
								'publisher_author_id'=>$this->parentPublisherId,
								'added_date'=>$formData['added_date'],
								'updated_date'=>$formData['updated_date'],
								'status'=>1
								);
				
				$result=$this->modelEdition->insert($editionData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Edition added successfully</div>');
					$this->_redirect('publisher/edition/index/tab_ajax/edition/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add edition</div>';
				}
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		//$this->view->parenteditionList=$this->modelEdition->getParentList();
    }
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEdition->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$editionInfo=$this->modelEdition->fetchRow('id='.$id);
			$formData=$editionInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost() && isset($_REQUEST['edit_edition']) && $_REQUEST['edit_edition']=='Edit Edition')
			{
				$formData=$this->getRequest()->getPost();
				
				if(trim($formData['edition'])=="")
				{
					$formErrors['edition']="Please enter edition name";
				}
				
				if(!isset($formData['status']) || trim($formData['status'])=="")
				{
					$formErrors['status']="Please select status";
				}
				
				if($this->modelEdition->isExist('edition="'.$formData['edition'].'" AND publisher_author_id="'.$this->parentPublisherId.'" AND id<>"'.$id.'"'))
				{
					if(!(array_key_exists('edition',$formErrors)))
					{
						$formErrors['edition']="Edition already exist";
					}
				}
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					
					$editionData=array(
								'edition'=>$formData['edition'],
								'updated_date'=>$formData['updated_date'],
								'status'=>$formData['status']
								);
					
					$result=$this->modelEdition->update($editionData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Edition updated successfully</div>');
					$this->_redirect('publisher/edition/index/tab_ajax/edition/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			//$this->view->parenteditionList=$this->modelEdition->getParentList();
		}
		else
		{
			$this->_redirect('publisher/edition/index/tab_ajax/edition/');
		}
    }
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEdition->isExist($id))
		{
			//$success=$this->modelEdition->delete('id='.$id);
			
			$data['status']=0;
		    $success=$this->modelEdition->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Edition deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete edition</div>');
			}
		}
		$this->_redirect('publisher/edition/index/tab_ajax/edition/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEdition->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelEdition->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Edition deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate edition</div>');
			}
		}
		$this->_redirect('publisher/edition/index/tab_ajax/edition/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEdition->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelEdition->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Edition activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate edition</div>');
			}
		}
		$this->_redirect('publisher/edition/index/tab_ajax/edition/');
    }
	
	public function viewAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		$tab_ajax=$this->_request->getParam('tab_ajax');
		
		if($id>0 && $this->modelEdition->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$editionInfo=$this->modelEdition->fetchRow('id='.$id);
			$formData=$editionInfo->toArray();
				
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('publisher/edition/index/tab_ajax/edition/');
		}
    }
	
}

