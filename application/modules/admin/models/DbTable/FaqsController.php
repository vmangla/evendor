<?php
class Admin_FaqsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $modelFaqs=null;
	
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
    }
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		//===creating model object======
		if($this->modelFaqs==null){$this->modelFaqs=new Admin_Model_DbTable_Faqs();}
		//==============================
		
		$faqList=$this->modelFaqs->fetchAll();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($faqList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($faqList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->faqList=$paginator;
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
			if(!(isset($formData['question'])) || trim($formData['question'])=="")
			{
				$formErrors['question']='Please enter question';
			}
			
			if(count($formErrors)==0)
			{
				//===creating model object======
				if($this->modelFaqs==null){$this->modelFaqs=new Admin_Model_DbTable_Faqs();}
				//==============================
		
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				$result=$this->modelFaqs->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Faq added successfully</div>');
					$this->_redirect('admin/faqs/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add faq</div>';
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
		if($this->modelFaqs==null){$this->modelFaqs=new Admin_Model_DbTable_Faqs();}
		//==============================
					
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelFaqs->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$faqInfo=$this->modelFaqs->fetchRow('id='.$id);
			$formData=$faqInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*echo "<pre>";
				print_r($formData);
				echo "<pre>";
				exit;*/
				if(!(isset($formData['question'])) || trim($formData['question'])=="")
				{
					$formErrors['question']='Please enter question';
				}
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelFaqs->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Faq updated successfully</div>');
					$this->_redirect('admin/faqs/');
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
			$this->_redirect('admin/faqs/');
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelFaqs==null)
		{
		  $this->modelFaqs=new Admin_Model_DbTable_Faqs();
		}
		
		if($id>0 && $this->modelFaqs->isExist($id))
		{
			$success=$this->modelFaqs->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Faq deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete faq</div>');
			}
		}
		$this->_redirect('admin/faqs/');
    }
	
	
	
	public function unpublishAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelFaqs==null)
		{
		 $this->modelFaqs=new Admin_Model_DbTable_Faqs();
		}
		
		if($id>0 && $this->modelFaqs->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelFaqs->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Faq unpublished successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to unpublish faq</div>');
			}
		}
		$this->_redirect('admin/faqs/');
    }
	
	
	public function publishAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelFaqs==null)
		{
		 $this->modelFaqs=new Admin_Model_DbTable_Faqs();
		}
				
		if($id>0 && $this->modelFaqs->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelFaqs->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Faq published successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to publish faq</div>');
			}
		}
		$this->_redirect('admin/faqs/');
    }
	
}

