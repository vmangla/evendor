<?php

class Admin_PublishersController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelPublishers=null;
		
    public function init()
    {
		/* creating flashMessanger object to display message */
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login */
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
		
		
	    /* creating model object */
		$this->modelPublishers=new Admin_Model_DbTable_Publishers();
		
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
		
		$userList=$this->modelPublishers->getList();
		
		$formData=array();
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			
			if(isset($formData['status']) && trim($formData['status'])!="")
			{
			  $status=trim($formData['status']);
			  $whrcls="";
			  $whrcls.=" profile_status='".$status."' AND user_type='publisher'";
			  $userList=$this->modelPublishers->fetchAll($whrcls);
			}
		}
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($userList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($userList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->userList=$paginator;
		$this->view->formData=$formData;
	}
	############### export users as csv format #######################################
	public function exportusercsvAction()
	{
			$this->modelUsers=new Admin_Model_DbTable_Users();
			$type = $this->_request->getParam('type');
			if($type!='')
			{
				if($type=='1')
				{
					$file = "active_publisher";
				}
				else {
					$file = "inactive_publisher";
				}
				$where = " where profile_status ='".$type."' and user_type='publisher'";
			}
			else {
				$file = "all_publisher";
				$where = '';
			}
			$separator=","; // separate column in csv				
			$sql = "select first_name,last_name,username,country,emailid,publisher,phone from  pclive_users $where ";		
			//$request_list=$this->modelconnections->fetchAll("intro_id='".$_SESSION['Zend_Auth']['storage']->user_id."' and conn_linkedin_id!=''");
			$request_list = $this->modelPublishers->getAdapter()->fetchAll($sql);
			if(count($request_list)>0)
			{
				$csv_output="";				
				$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."publisher".$separator."Email".$separator."Phone\n";
				$content='<table class="table-list" cellpadding="0" cellspacing="0" width="100%">
				<tr>
				  <th>First Name</th>
				  <th>Last Name</th>
				  <th>User Name</th>
				  <th>Country</th>				 
				  <th>Email</th>
				  <th>Phone</th>
				</tr>';
				
				foreach($request_list as $req)
				{
					//$req['country'];		
					$countryName = $this->modelUsers->getCountryInfo($req['country']); 		
					 $content.='<tr>
					 			<td>'.$req['first_name'].'</td>
				 				<td>'.$req['last_name'].'</td>
				  				<td>'.$req['username'].'</td>
				 				<td>'.$countryName['country'].'</td>				 			
				 				<td>'.$req['emailid'].'</td>
				  				<td>'.$req['phone'].'</td>
					   		</tr>';	
					
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['username']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['publisher']).$separator.str_replace(",","",$req['emailid']).$separator.str_replace(",","",$req['phone'])."\n";
				}
			}
			else {
				$content.='<tr><td colspan="8" class="list-not-found">Data Not Found</td></tr>';
			}
			$content.='</table>';
			$filename = $file."_".time().".xls";			
			$test=$content;
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=$filename");
			echo $test;
			exit;
			/*
			$filename = $file."_".time();
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-type: text/x-csv");
			header("Content-type: text/csv");
			header("Content-type: application/csv");
			//header("Content-disposition: excel".time().".csv");
			header("Content-Disposition: attachment; filename=".$filename.".csv");
			header( "Content-disposition: filename=".$filename.".csv");
			print $csv_output;
			exit;*/
		
	}
	public function viewAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublishers->isExist($id))
		{
			$formData=array();
			
			$userInfo=$this->modelPublishers->fetchRow('id='.$id);
			$formData=$userInfo->toArray();	
			$this->view->formData=$formData;	
			
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublishers->isExist($id))
		{
			$success=$this->modelPublishers->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Publisher deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete publisher</div>');
			}
		}
		$this->_redirect('admin/publishers/');
    }
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublishers->isExist($id))
		{
		
		    $data['profile_status']=0;
		    $success=$this->modelPublishers->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Publisher deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate publisher</div>');
			}
		}
		$this->_redirect('admin/publishers/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublishers->isExist($id))
		{
		
		    $data['profile_status']=1;
		    $success=$this->modelPublishers->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Publisher activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate publisher</div>');
			}
		}
		$this->_redirect('admin/publishers/');
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

