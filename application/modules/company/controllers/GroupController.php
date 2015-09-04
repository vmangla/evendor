<?php
class Company_GroupController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelCompany=null;
	var $modelGroup=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$this->modelGroup = new Company_Model_DbTable_Groups();
		$this->modelCompany = new Company_Model_DbTable_Companies();
				
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
		
        if($data && $data!=null)
		{
            $this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
			$this->view->sessCompanyInfo =$this->sessCompanyInfo;
        }
	}
		
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/group/');
		}
		
		$GroupList=$this->modelGroup->getGroupList($this->sessCompanyInfo->id);
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($GroupList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		//$paginator->setItemCountPerPage(2);
	    
		$paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($GroupList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		//$this->view->pageSize=2;
		
		$this->view->page=$page;
		$this->view->GroupList=$paginator;
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		if(isset($sessionMsg) && !empty($sessionMsg))
		{
			$this->view->formData=$sessionMsg->formData;
			$this->view->formErrors=$sessionMsg->formErrors;
			$this->view->errorMessage=$sessionMsg->errorMessage;
			
			Zend_Session::namespaceUnset('step1Msg');
		}
	}
	
	public function createAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/group/');
		}
		
		
		$formData=array();
		$formErrors=array();
				
		$formData =$this->getRequest()->getPost();
		//print_r($formData);exit;
        if($this->getRequest()->isPost() && isset($formData['create_group']) && $formData['create_group']=='Create')
		{
		  
			if(!(isset($formData['group_name'])) || trim($formData['group_name'])=="")
			{
				$formErrors['group_name']="Please enter group name";
			}
			
			if($this->modelGroup->isExist('group_name="'.$formData['group_name'].'" AND company_id="'.$this->sessCompanyInfo->id.'"'))
			{
				if(!(array_key_exists('group_name',$formErrors)))
				{
					$formErrors['group_name']="Group name already exist";
				}
			}
			
				
			if(count($formErrors)==0)
			{
			
				if($this->getRequest()->isPost())
				{
					$add_time=date('Y-m-d H:i:s');
					$formData['company_id']=(!empty($this->sessCompanyInfo->parent_id))?$this->sessCompanyInfo->parent_id:$this->sessCompanyInfo->id;
					$GroupData=array(
							    'group_name'=>$formData['group_name'],
								'company_id'=>$formData['company_id'],
								'status'=>'1',
								'created_date'=>$add_time,
								'updated_date'=>date("Y-m-d H:i:s")
								);
										
						$lastId=$this->modelGroup->insert($GroupData);
						
						if($lastId>0)
						{
							$this->_flashMessenger->addMessage('<div class="div-success">Group created successfully</div>');
						}
						else
						{
							$this->view->errorMessage='<div class="div-error">Sorry group cannot be created. Try again later.</div>';
						}
					$this->_redirect('company/group/');		
				}
			}
			else
			{
				 //$this->view->errorMessage = '<div class="div-error">Please enter required fields properly.</div>';
				 //$this->_redirect('company/group/');
			}      
        }
		else
		{
			$formData['group_name']="";
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		$sessionMsg->formData=$formData;
		$sessionMsg->formErrors=$formErrors;
		$sessionMsg->errorMessage=$this->view->errorMessage;
		
		$this->_redirect('company/group/');
	}
	



	public function deleteAction()
    {
 		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/group/');
		}
		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGroup->isExist($id))
		{
			$success=$this->modelGroup->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Group deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete group</div>');
			}
			
		}
		
		$this->_redirect('company/group/');
    }
	
	public function inactiveAction()
    {
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/group/');
		}
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGroup->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelGroup->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Group deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate group</div>');
			}
		}
		$this->_redirect('company/group/');
    }

	public function activeAction()
    {
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/group/');
		}
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelGroup->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelGroup->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Group activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate group</div>');
			}
		}
		$this->_redirect('company/group/');
    }
		
	public function editAction()
    {			
 		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/group/');
		}
		
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
				
		if($id>0 && $this->modelGroup->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelGroup->fetchRow('id='.$id);
			$formData=$userInfo->toArray();		
			if($this->getRequest()->isPost() && isset($_REQUEST['edit_group']) && $_REQUEST['edit_group']=='Edit Group')
			{
				$formData=$this->getRequest()->getPost();
				//print_r($formData);exit;
				
				if(!(isset($formData['group_name'])) || trim($formData['group_name'])=="")
				{
					$formErrors['group_name']="Please enter group name";
				}
				
				if($this->modelGroup->isExist('group_name="'.$formData['group_name'].'" AND company_id="'.$this->sessCompanyInfo->id.'" AND id<>"'.$id.'"'))
				{
					if(!(array_key_exists('group_name',$formErrors)))
					{
						$formErrors['group_name']="Group name already exist";
					}
				}
								
				if(count($formErrors)==0)
				{
					$GroupData=array(
					                'group_name'=>$formData['group_name'],
									//'status'=>$formData['status'],
									'updated_date'=>date("Y-m-d H:i:s")
								);
					
					$result=$this->modelGroup->update($GroupData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Group updated successfully</div>');
					$this->_redirect('company/group/');
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
			$this->_redirect('company/group/');
		}
    }
	
	public function viewAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		$tab_ajax=$this->_request->getParam('tab_ajax');
		
		if($id>0 && $this->modelGroup->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelGroup->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
				
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('company/group/');
		}
    }
	
}

