<?php
class Publisher_BrandController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $sessPublisherInfo=null;
	var $parentPublisherId=null;
	
	var $modelPublisher=null;
	var $modelBrand=null;
	
    public function init()
    {
		$this->categories = new Admin_Model_DbTable_Categories();
		
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
					
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
		if($data && $data!=null)
		{
            $this->modelPublisher = new Publisher_Model_DbTable_Publishers();
			$this->modelBrand=new Publisher_Model_DbTable_Brand();
			
			$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo;
			
			$this->parentPublisherId = !empty($this->sessPublisherInfo->parent_id)?$this->sessPublisherInfo->parent_id:$this->sessPublisherInfo->id;
			
			$tab_ajax=$this->_request->getParam('tab_ajax',0);
			if(empty($tab_ajax))
			{
				$this->_redirect('publisher/');
			}
			
			if($data->user_type!='publisher' && $data->user_type!='Pmanager' && $data->user_type!='Amanager' )
			{
			$this->_redirect('publisher/access/index/tab_ajax/brand/');
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
		
		$brandList=$this->modelBrand->getList($this->parentPublisherId);
		
		
		 
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($brandList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($brandList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->brandList=$paginator;
	
		
		$this->view->parentPublisherId=$this->parentPublisherId;
	}
	public function addAction()
    {
 		
		$allcategories=$this->categories->getList();
		$this->view->catlist = $allcategories;

		 
		
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$formData=array();
		$formErrors=array();
		
		$formData =$this->getRequest()->getPost();
		
		if($this->getRequest()->isPost() && isset($formData['add_brand']) && $formData['add_brand']=='Add Brand')
		{
			
			if(trim($formData['brand'])=="")
			{
				$formErrors['brand']="Please enter brand";
			}
			
			if($this->modelBrand->isExist('brand="'.$formData['brand'].'" AND publisher_author_id="'.$this->parentPublisherId.'" and category="'.$formData['category'].'"'))
			{
				if(!(array_key_exists('brand',$formErrors)))
				{
					$formErrors['brand']="Brand already exist";
				}
			}
			
			if(count($formErrors)==0)
			{
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				
				$brandData=array(
								'brand'=>$formData['brand'],
								'category'=>$formData['category'],
								'publisher_author_id'=>$this->parentPublisherId,
								'added_date'=>$formData['added_date'],
								'updated_date'=>$formData['updated_date'],
								'status'=>1
								);
				
				$result=$this->modelBrand->insert($brandData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Brand added successfully</div>');
					$this->_redirect('publisher/brand/index/tab_ajax/brand/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add brand</div>';
				}
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		//$this->view->parentBrandList=$this->modelBrand->getParentList();
    }
	public function editAction()
    {			
 		$allcategories=$this->categories->getList();
		$this->view->catlist = $allcategories;
		
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBrand->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$brandInfo=$this->modelBrand->fetchRow('id='.$id);
			$formData=$brandInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost() && isset($_REQUEST['edit_brand']) && $_REQUEST['edit_brand']=='Edit Brand')
			{
				$formData=$this->getRequest()->getPost();
				
				if(trim($formData['category'])=="")
				{
					$formErrors['category']="Please select category";
				}
				
				if(trim($formData['brand'])=="")
				{
					$formErrors['brand']="Please enter brand name";
				}
				
				if(!isset($formData['status']) || trim($formData['status'])=="")
				{
					$formErrors['status']="Please select status";
				}
				
				if($this->modelBrand->isExist('brand="'.$formData['brand'].'" AND category="'.$formData['category'].'" AND publisher_author_id="'.$this->parentPublisherId.'" AND id<>"'.$id.'"'))
				{
					if(!(array_key_exists('brand',$formErrors)))
					{
						$formErrors['brand']="Brand already exist";
					}
				}
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					
					$brandData=array(
								'brand'=>$formData['brand'],
								'category'=>$formData['category'],
								'updated_date'=>$formData['updated_date'],
								'status'=>$formData['status']
								);
					
					$result=$this->modelBrand->update($brandData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Brand updated successfully</div>');
					$this->_redirect('publisher/brand/index/tab_ajax/brand/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			//$this->view->parentbrandList=$this->modelBrand->getParentList();
		}
		else
		{
			$this->_redirect('publisher/brand/index/tab_ajax/brand/');
		}
    }
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBrand->isExist($id))
		{
			//$success=$this->modelBrand->delete('id='.$id);
			$data['status']=0;
		    $success=$this->modelBrand->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Brand deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete brand</div>');
			}
		}
		$this->_redirect('publisher/brand/index/tab_ajax/brand/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBrand->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelBrand->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Brand deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate brand</div>');
			}
		}
		$this->_redirect('publisher/brand/index/tab_ajax/brand/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelBrand->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelBrand->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Brand activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate brand</div>');
			}
		}
		$this->_redirect('publisher/brand/index/tab_ajax/brand/');
    }
	
	public function viewAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		$tab_ajax=$this->_request->getParam('tab_ajax');
		
		if($id>0 && $this->modelBrand->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$brandInfo=$this->modelBrand->fetchRow('id='.$id);
			$formData=$brandInfo->toArray();
				
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('publisher/brand/index/tab_ajax/brand/');
		}
    }
	
}
