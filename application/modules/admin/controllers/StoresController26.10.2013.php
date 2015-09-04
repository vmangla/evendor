<?php
class Admin_StoresController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
	
	var $modelCountries=null;
	var $modelContinents=null;
	
	
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
		
		
		$this->modelCountries=new Admin_Model_DbTable_Countries();
		$this->modelContinents=new Admin_Model_DbTable_Continents();
		
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
		
		$db = Zend_Registry::get('db');
		//$get_all_countries = "select * from pclive_country where status='1' and is_store=1";
		$get_all_countries = "select * from pclive_country where is_store=1";
		$res_all_countries = $db->query($get_all_countries);
		$countryList =  $res_all_countries->FetchAll();
		
		
		
		//$countryList=$this->modelCountries->fetchAll();
		//echo "<pre>";
		//print_r($countryList);
		//echo "</pre>";
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($countryList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($countryList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->countryList=$paginator;
    }
	public function addAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		/********** Continent Data **********************/
		$continentList=$this->modelContinents->fetchAll();
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($continentList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($continentList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->continentList=$paginator;
		/********** Continent Data **********************/
		
		
		$db = Zend_Registry::get('db');
		//$get_all_countries = "select * from pclive_country where status='1' and is_store=1";
		$get_all_countries_store = "select * from pclive_country where is_store=0";
		$res_all_countries_store = $db->query($get_all_countries_store);
		$countryForStoreList =  $res_all_countries_store->FetchAll();
		
		$this->view->countryForStoreList=$countryForStoreList;
		
		$get_all_currency = "select * from pclive_currency";
		$res_all_currecy = $db->query($get_all_currency);
		$currencyForStoreList =  $res_all_currecy->FetchAll();
		$this->view->currencyForStoreList=$currencyForStoreList;
		
		$formData=array();
		$formErrors=array();
		
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			
			if(!(isset($formData['country_id'])) || trim($formData['country_id'])=="")
			{
				$formErrors['country_id']='Please select a country';
			}
			if(!(isset($formData['currency_id'])) || trim($formData['currency_id'])=="")
			{
				$formErrors['country_id']='Please select a currency';
			}
			/*
			if(!(isset($formData['country'])) || trim($formData['country'])=="")
			{
				$formErrors['country']='Please enter a store name.';
			}
			
			if($this->modelCountries->isNameExist("country='$formData[country]'"))
			{
				$formErrors['country']="Store name already exist.";
			}
			*/
			$id=$formData['country_id'];
			unset($formData['country_id']);
			$formData['is_store']=1;
			//print_r($formErrors);
			if(count($formErrors)==0)
			{
				//$result=$this->modelCountries->insert($formData);
				
				$result=$this->modelCountries->update($formData,'id='.$id);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Store added successfully</div>');
					$this->_redirect('admin/stores/');
				}
				else
				{
					$this->view->errorMessage1='<div class="div-error">Sorry!, unable to add store</div>';
				}
			}
			else
			{
				$this->view->errorMessage1='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		if(isset($sessionMsg->formData) && !empty($sessionMsg->formData))
		{
			//print_r($sessionMsg->formData);exit;
			$this->view->formData=array_merge($formData,$sessionMsg->formData);
			$this->view->formErrors=array_merge($formErrors,$sessionMsg->formErrors);
			$this->view->errorMessage=$sessionMsg->errorMessage;
			Zend_Session::namespaceUnset('step1Msg');
		}
    }
	
	public function addcontientAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$formData=array();
		$formErrors=array();
		
		if($this->getRequest()->isPost())
		{
			$formData=$this->getRequest()->getPost();
			
			if(!(isset($formData['continent'])) || trim($formData['continent'])=="")
			{
				$formErrors['continent']='Please enter continent';
			}
			
			if($this->modelContinents->isNameExist("continent='$formData[continent]'"))
			{
				$formErrors['continent']="Continent name already exist.";
			}
			
			if(count($formErrors)==0)
			{
				$formData['added_date']=date('Y-m-d H:i:s');
				$formData['updated_date']=date('Y-m-d H:i:s');
				$formData['status']=1;
				
				$result=$this->modelContinents->insert($formData);
				if($result>0)
				{
					$this->_flashMessenger->addMessage('<div class="div-success">Continent added successfully</div>');
					$this->_redirect('admin/countries/add');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Sorry!, unable to add continent</div>';
				}
			}
			else
			{
				$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
			}
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
		
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		$sessionMsg->formData=$formData;
		$sessionMsg->formErrors=$formErrors;
		$sessionMsg->errorMessage=$this->view->errorMessage;
		
		$this->_redirect('admin/countries/add');
    }
	
	public function editAction()
    {
					
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCountries->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$countryInfo=$this->modelCountries->fetchRow('id='.$id);
			$formData=$countryInfo->toArray();
			$continentList=$this->modelContinents->fetchAll();
			$db = Zend_Registry::get('db');
			$get_all_currency = "select * from pclive_currency";
			$res_all_currecy = $db->query($get_all_currency);
			$currencyForStoreList =  $res_all_currecy->FetchAll();
			$this->view->currencyForStoreList=$currencyForStoreList;
			
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				/*
				if(!(isset($formData['continent_id'])) || trim($formData['continent_id'])=="")
				{
					$formErrors['continent_id']='Please select a continent';
				}
				
				if(!(isset($formData['country'])) || trim($formData['country'])=="")
				{
					$formErrors['country']='Please enter a country name.';
				}
				
				if($this->modelCountries->isNameExist("country='$formData[country]' AND id<>$id"))
				{
					$formErrors['country']="Country name already exist.";
				}
				*/
				
				if(count($formErrors)==0)
				{
					//$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelCountries->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Store updated successfully</div>');
					$this->_redirect('admin/stores/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			$this->view->continentList=$continentList;
		}
		else
		{
			$this->_redirect('admin/stores/');
		}
    }
	
	public function editcontinentAction()
    {
					
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelContinents->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$countryInfo=$this->modelContinents->fetchRow('id='.$id);
			$formData=$countryInfo->toArray();
						
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				
				if(!(isset($formData['continent'])) || trim($formData['continent'])=="")
				{
					$formErrors['continent']='Please enter continent';
				}
				
				if($this->modelContinents->isNameExist("continent='$formData[continent]' AND id<>$id"))
				{
					$formErrors['continent']="Continent name already exist.";
				}
				
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelContinents->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Continent updated successfully</div>');
					$this->_redirect('admin/countries/add/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			$this->view->continentList=$continentList;
		}
		else
		{
			$this->_redirect('admin/countries/add/');
		}
    }
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCountries->isExist($id))
		{
			$success=$this->modelCountries->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Country deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete country/div>');
			}
		}
		$this->_redirect('admin/countries/');
    }
	
	public function deletecontinentAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelContinents->isExist($id))
		{
			$success=$this->modelContinents->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Continent deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete continent/div>');
			}
		}
		$this->_redirect('admin/countries/add/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelCountries->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelCountries->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Country deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate country</div>');
			}
		}
		$this->_redirect('admin/countries/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
					
		if($id>0 && $this->modelCountries->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelCountries->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Country activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to active country</div>');
			}
		}
		$this->_redirect('admin/countries/');
    }
	
	public function inactivecontinentAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelContinents->isExist($id))
		{
		
		    $data['status']=0;
		    $success=$this->modelContinents->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Continent deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate continent</div>');
			}
		}
		$this->_redirect('admin/countries/add/');
    }
	
	
	public function activecontinentAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
					
		if($id>0 && $this->modelContinents->isExist($id))
		{
		
		    $data['status']=1;
		    $success=$this->modelContinents->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Continent activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to active continent</div>');
			}
		}
		$this->_redirect('admin/countries/add/');
    }
	
}

