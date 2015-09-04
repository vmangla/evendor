<?php
class Admin_PointsSystemController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelPoints=null;
	
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
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		/************* To Set The active section and links *******************/
		$moduleAccessRow=$this->modelModuleMenus->fetchRow("controller='$controller'");
		
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
		if($this->modelPoints==null){$this->modelPoints=new Admin_Model_DbTable_Points();}
		//==============================
		
		$pointsList=$this->modelPoints->fetchAll();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($pointsList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($pointsList);
		//echo "Total count : - ".$this->view->totalCount;
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->pointsList=$paginator;
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
			if(!(isset($formData['points'])) || trim($formData['points'])=="")
			{
				$formErrors['points']='Please enter points';
			}
			if(isset($formData['points']) && trim($formData['points'])!="")
			{
				 if (!is_numeric ($formData['points'])) 
				 {
					$formErrors['points']='Please enter numeric value for points.';
				 }
			}
			
			
			if(count($formErrors)==0)
			{
				//===creating model object======
				if($this->modelPoints==null){$this->modelPoints=new Admin_Model_DbTable_Points();}
				//==============================
				$chk_points = $this->modelPoints->fetchAll('points="'.$formData['points'].'"');
				$count_points = count($chk_points);
				if($count_points==0)
				{
					$formData['add_date']=date('Y-m-d H:i:s');
					$formData['mod_date']=date('Y-m-d H:i:s');
					$result=$this->modelPoints->insert($formData);
					if($result>0)
					{
						$this->_flashMessenger->addMessage('<div class="div-success">Points added successfully</div>');
						$this->_redirect('admin/pointssystem/');
					}
					else
					{
						$this->view->errorMessage='<div class="div-error">Sorry!, unable to add points</div>';
					}
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">These points already exists.</div>';
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
		if($this->modelPoints==null){$this->modelPoints=new Admin_Model_DbTable_Points();}
		//==============================
					
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$point_id=$this->_request->getParam('id',0);
		  
		if($point_id>0 && $this->modelPoints->isExist($point_id))
		{
			$formData=array();
			$formErrors=array();
			
			$PointsInfo=$this->modelPoints->fetchRow('point_id='.$point_id);
			$formData=$PointsInfo->toArray();
			 
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*echo "<pre>";
				print_r($formData);
				echo "<pre>";
				exit;*/
				if(!(isset($formData['points'])) || trim($formData['points'])=="")
				{
					$formErrors['points']='Please enter points';
				}
				if(isset($formData['points']) && trim($formData['points'])!="")
				{
					 if (!is_numeric ($formData['points'])) 
					 {
						$formErrors['points']='Please enter numeric value for points.';
					 }
				}
				
				if(count($formErrors)==0)
				{
					$formData['mod_date']=date('Y-m-d H:i:s');
					$result=$this->modelPoints->update($formData,'point_id='.$point_id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Points updated successfully</div>');
					$this->_redirect('admin/pointssystem/');
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
			$this->_redirect('admin/pointssystem/');
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelPoints==null)
		{
		  $this->modelPoints=new Admin_Model_DbTable_Points();
		}
		
		if($id>0 && $this->modelPoints->isExist($id))
		{
			$success=$this->modelPoints->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Points deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete Points</div>');
			}
		}
		$this->_redirect('admin/pointssystem/');
    }
	
	
	
	public function unpublishAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelPoints==null)
		{
		 $this->modelPoints=new Admin_Model_DbTable_Points();
		}
		
		if($id>0 && $this->modelPoints->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelPoints->update($data,'point_id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Points unpublished successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to unpublish Points</div>');
			}
		}
		$this->_redirect('admin/pointssystem/');
    }
	
	
	public function publishAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($this->modelPoints==null)
		{
		 $this->modelPoints=new Admin_Model_DbTable_Points();
		}
				
		if($id>0 && $this->modelPoints->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelPoints->update($data,'point_id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Points published successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to publish Points</div>');
			}
		}
		$this->_redirect('admin/pointssystem/');
    }
	
	public function setsortorderAction()
    {
		$id=$this->_request->getParam('id',0);
		$setorder=$this->_request->getParam('setorder',0);
		
		if($this->modelPoints==null)
		{
		 $this->modelPoints=new Admin_Model_DbTable_Points();
		}
				
		if($id>0 && $this->modelPoints->isExist($id))
		{
		
		    $data['sort_order']=!empty($setorder)?$setorder:0;
			
		    $success=$this->modelPoints->update($data,'point_id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Points order has been set successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to set sort order</div>');
			}
		}
		$this->_redirect('admin/pointssystem/');
		exit;
	}
	
	
}

