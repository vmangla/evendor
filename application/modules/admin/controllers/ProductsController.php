<?php
class Admin_ProductsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelProducts=null;
	
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
		$this->modelProducts=new Admin_Model_DbTable_Products();
		//==============================
		
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
		
		$prodList=$this->modelProducts->getList();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($prodList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($prodList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->prodList=$paginator;
    }
	
	public function viewAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelProducts->isExist($id))
		{
			$formData=array();
			
			$productInfo=$this->modelProducts->fetchRow('id='.$id);
			$formData=$productInfo->toArray();	
			$this->view->formData=$formData;	
		}
    }
	
	/*
	public function addAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$formData=array();
		$formErrors=array();
		
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();

			if(trim($formData['category_name'])=="")
			{
				$formErrors['category_name']="Please enter category name";
			}
			
			if(count($formErrors)==0)
			{
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				$result=$this->modelProducts->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Category added successfully</div>');
					$this->_redirect('admin/products/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add category</div>';
				}
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		$this->view->parentCategoryList=$this->modelProducts->getParentList();
    }
	
	
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelProducts->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelProducts->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				
				if(trim($formData['category_name'])=="")
				{
					$formErrors['category_name']="Please enter category name";
				}
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelProducts->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Category updated successfully</div>');
					$this->_redirect('admin/categories/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			$this->view->parentCategoryList=$this->modelProducts->getParentList();
		}
		else
		{
			$this->_redirect('admin/products/');
		}
    }
	*/
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelProducts->isExist($id))
		{
			$success=$this->modelProducts->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Product deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete product</div>');
			}
		}
		$this->_redirect('admin/products/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelProducts->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelProducts->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Product deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate product</div>');
			}
		}
		$this->_redirect('admin/products/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelProducts->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelProducts->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Product activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate product</div>');
			}
		}
		$this->_redirect('admin/products/');
    }
	
	public function deleteimageAction()
    {
	
		$id=$this->_request->getParam('id',0);
		
		$modelImage = new Admin_Model_DbTable_ProductImages();
		$imageDetail=$modelImage->getImageInfo($id);
		
		unlink(USER_UPLOAD_DIR.$imageDetail['image_name']);
		unlink(USER_UPLOAD_DIR.$imageDetail['image_name_thumb']);
	
		if($id>0 && $modelImage->isExist($id))
		{
			$success=$modelImage->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Image deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete image</div>');
			}
			
		}
	
		$this->_redirect('admin/products/');
			
	}
	
	
	public function deletepriceAction()
    {
		$id=$this->_request->getParam('id',0);
		
		$modelPrice = new Admin_Model_DbTable_ProductPrices();
		
		if($id>0 && $modelPrice->isExist($id))
		{
			$success=$modelPrice->delete('id='.$id);
			
			if($success)
			{	
				$this->_flashMessenger->addMessage('<div class="div-success">Price deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete price</div>');
			}
		}
		
		$this->_redirect('admin/products/');
		
	}
	
	public function deleteepubfileAction()
    {
	
		$id=$this->_request->getParam('id',0);
	
		$bookDetail=$this->modelProducts->getInfoByPublisherId($id);
		
		unlink(EPUB_UPLOAD_DIR.$bookDetail['file_name']);
	
		if($id>0 && $this->modelProducts->isExist($id))
		{
		   
		    $Filedata=array('file_name'=>'',
						    'file_size'=>'');
		
			$result=$this->modelProducts->update($Filedata,'id='.$id);  
			
			if($result)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Epub file deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete epub file</div>');
			}
			
		}
	
		$this->_redirect('admin/products/');
			
	}
	
	public function downloadpubfileAction()
    {
		    $id=$this->_request->getParam('id',0);
			
			$bookDetail=$this->modelProducts->getInfoByPublisherId($id);
			
			if($id>0 && $this->modelProducts->isExist($id))
			{
			  
					$filename = EPUB_UPLOAD_DIR.$bookDetail['file_name']; 
					
					header('Pragma: public');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Cache-Control: private', false); 
					header('Content-Type: application/epub');
					header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
					header('Content-Transfer-Encoding: binary');
					header('Content-Length: ' . filesize($filename));
					readfile($filename);
					exit;
			
			}
				
	}
	
}

