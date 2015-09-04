<?php
class LoginController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		/**********************************************************/
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		//$this->modelPublishers = new Publisher_Model_DbTable_Publishers();
		
		$storage1 = new Zend_Auth_Storage_Session('company_type');
        $data1 = $storage1->read();
		//$this->modelCompanies = new Company_Model_DbTable_Companies();
		
		if(!empty($data) || !empty($data1))
		{
			$this->_redirect('/');
		}
		
		/******************************************************************/
		//$storeId=$this->getRequest()->getCookie('sid');
		//$storeName=$this->getRequest()->getCookie('sname');
		
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
		else
		{
			//$modelCountry = new Admin_Model_DbTable_Countries();
			//$country_info = $modelCountry->fetchRow('country="Nigeria"');
			//$this->_redirect('/auth/storechange/storeid/'.$country_info->id);
			$this->_redirect('/auth/storechange/');
		}
		
		$modelBooks = new Publisher_Model_DbTable_Books();
		$allStored=$modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		
		/******************************************************************/
		
    }
	
	
    public function indexAction()
    {
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		if(isset($sessionMsg->formData) && !empty($sessionMsg->formData))
		{
			$this->view->formData=$sessionMsg->formData;
			$this->view->formErrors=$sessionMsg->formErrors;
			$this->view->errorMessage=$sessionMsg->errorMessage;
			Zend_Session::namespaceUnset('step1Msg');
		}
		
		$companySessionMsg = new Zend_Session_Namespace('companyStep1Msg');
		if(isset($companySessionMsg->formData) && !empty($companySessionMsg->formData))
		{
			$this->view->cformData=$companySessionMsg->formData;
			$this->view->cformErrors=$companySessionMsg->formErrors;
			$this->view->cerrorMessage=$companySessionMsg->errorMessage;
			Zend_Session::namespaceUnset('companyStep1Msg');
		}
		
	}

}	


