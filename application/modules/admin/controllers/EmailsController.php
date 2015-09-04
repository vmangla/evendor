<?php
class Admin_EmailsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;	
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelEmails=null;
	
    public function init()
    {
	
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
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
		
		$this->modelEmails=new Admin_Model_DbTable_Emails();
		
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
		
		$emailList=$this->modelEmails->getList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($emailList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($emailList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->emailList=$paginator;
    }
	
	public function addAction()
    {
	
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$formData=array();
		$formErrors=array();
		
		if($this->getRequest()->isPost())
		{
			$formDatatemp=$this->getRequest()->getPost();
	
			if(trim($formDatatemp['template_name'])=="" && trim($formDatatemp['template_new'])=="")
			{
				$formErrors['template_name']="Please select or enter template name";
			}
			
			if(trim($formDatatemp['template_description'])=="")
			{
				$formErrors['template_description']="Please enter template content";
			}
			
			if(count($formErrors)==0)
			{
			    if($formDatatemp['template_name']!="")
				{
			    $formData['template_name']=$formDatatemp['template_name'];
				}
				else
				{
				$formData['template_name']=$formDatatemp['template_new'];
				}
				
				$formData['template_description']=$formDatatemp['template_description'];
				
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				
				$result=$this->modelEmails->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Email template added successfully</div>');
					$this->_redirect('admin/emails/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add email template</div>';
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
	
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEmails->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$emailInfo=$this->modelEmails->fetchRow('id='.$id);
			$formData=$emailInfo->toArray();
			
			if($this->getRequest()->isPost())
			{
				
				$formDatatemp=$this->getRequest()->getPost();
	
				if(trim($formDatatemp['template_name'])=="" && trim($formDatatemp['template_new'])=="")
				{
					$formErrors['template_name']="Please select or enter template name";
				}
				
				if(trim($formDatatemp['template_description'])=="")
				{
					$formErrors['template_description']="Please enter template content";
				}
				
		
				if(count($formErrors)==0)
				{
					if($formDatatemp['template_name']!="")
					{
					$formData['template_name']=$formDatatemp['template_name'];
					}
					else
					{
					$formData['template_name']=$formDatatemp['template_new'];
					}
					
					$formData['template_description']=$formDatatemp['template_description'];
				   
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelEmails->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Email template updated successfully</div>');
					$this->_redirect('admin/emails/');
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
			$this->_redirect('admin/emails/');
		}
    }
	
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEmails->isExist($id))
		{
			$success=$this->modelEmails->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Template deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete template</div>');
			}
		}
		$this->_redirect('admin/emails/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEmails->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelEmails->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Template deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate template</div>');
			}
		}
		$this->_redirect('admin/emails/');
    }
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelEmails->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelEmails->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Template activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate template</div>');
			}
		}
		$this->_redirect('admin/emails/');
    }
}

