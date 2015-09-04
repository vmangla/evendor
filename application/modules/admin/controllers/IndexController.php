<?php
class Admin_IndexController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $sessAdminInfo=null;
	
	var $modelUsers=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
	
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
			
		/* creating model object */
		$this->modelModuleMenus = new Admin_Model_DbTable_ModulesMenus();
		$this->view->modelModuleMenus = $this->modelModuleMenus->getModuleMenusList();
				
		$this->modelUsers=new Admin_Model_DbTable_Users();
		$this->modelAdminUsers=new Admin_Model_DbTable_SubAdminUsers();
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		/************* To Set The active section and links *******************/
	}
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$modleMenusList = $this->modelModuleMenus->getModuleMenusList();
		
    }

}

