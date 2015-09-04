<?php
class Company_AccessController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelCompany=null;
	
	
	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
		
        $this->modelCompany = new Company_Model_DbTable_Companies();
		
		$this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
		$this->view->sessCompanyInfo =$this->sessCompanyInfo;
		
		$tab_ajax=$this->_request->getParam('tab_ajax',0);
		if(empty($tab_ajax))
		{
			$this->_redirect('company/');
		}
	}
	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
	}
}

