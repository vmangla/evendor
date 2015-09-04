<?php

class Admin_UsersController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
	
	var $modelUsers=null;
	    
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

	    /* creating model object */
		$this->modelUsers=new Admin_Model_DbTable_Users();
		
		$this->modelModuleMenus = new Admin_Model_DbTable_ModulesMenus();
		$this->view->modelModuleMenus = $this->modelModuleMenus->getModuleMenusList();
		
		
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
		
		$userList=$this->modelUsers->getList();
		
		$formData=array();
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			
			if(isset($formData['status']) && trim($formData['status'])!="")
			{
			  $status=trim($formData['status']);
			  $whrcls="";
			  $whrcls.=" status='".$status."' AND (account_type='1' OR account_type='2')";
			  $userList=$this->modelUsers->fetchAll($whrcls);
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
			$type = $this->_request->getParam('type');
			if($type!='')
			{
				if($type=='1')
				{
					$file = "active_user";
				}
				else {
					$file = "inactive_user";
				}
				$where = " where status ='".$type."' and (account_type='1' OR account_type='2')";
			}
			else {
				$file = "all_user";
				$where = '';
			}
			$separator=","; // separate column in csv				
			$sql = "select first_name,last_name,user_name,country,user_email,phone from pclive_companies $where ";		
			//$request_list=$this->modelconnections->fetchAll("intro_id='".$_SESSION['Zend_Auth']['storage']->user_id."' and conn_linkedin_id!=''");
			$request_list = $this->modelUsers->getAdapter()->fetchAll($sql);
			if(count($request_list)>0)
			{
				$csv_output="";				
				//$csv_output.="First Name".$separator."Last Name".$separator."User Name".$separator."Country".$separator."Email".$separator."Phone\n";
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
				  				<td>'.$req['user_name'].'</td>
				 				<td>'.$countryName['country'].'</td>
				 				<td>'.$req['user_email'].'</td>
				  				<td>'.$req['phone'].'</td>
					   		</tr>';
									
					//$csv_output .= str_replace(",","",$req['first_name']).$separator.str_replace(",","",$req['last_name']).$separator.str_replace(",","",$req['user_name']).$separator.str_replace(",","",$countryName['country']).$separator.str_replace(",","",$req['user_email']).$separator.str_replace(",","",$req['phone'])."\n";
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
			
			/*header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
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
		
		if($id>0 && $this->modelUsers->isExist($id))
		{
			$formData=array();
			
			$userInfo=$this->modelUsers->fetchRow('id='.$id);
			$formData=$userInfo->toArray();	
			$this->view->formData=$formData;	
			
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelUsers->isExist($id))
		{
			$success=$this->modelUsers->delete('id='.$id);
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">User deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete User</div>');
			}
			
		}
		$this->_redirect('admin/users/');
    }
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelUsers->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelUsers->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">User deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate user</div>');
			}
		}
		$this->_redirect('admin/users/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelUsers->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelUsers->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">User activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate user</div>');
			}
		}
		$this->_redirect('admin/users/');
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
