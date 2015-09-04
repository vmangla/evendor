<?php
class FaqsController extends Zend_Controller_Action
{
	var $sessPublisherInfo=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelFaqs=null;

	
    public function init()
    {
        /*********** To check for publisher session data *********************/
		$storage_publisher = new Zend_Auth_Storage_Session('publisher_type');
		$publisher_data = $storage_publisher->read();
		
		$storage_company = new Zend_Auth_Storage_Session('company_type');
		$company_data = $storage_company->read();
				
		$storage = new Zend_Auth_Storage_Session('account_type');		
        $userSessData = $storage->read();
		
		if($publisher_data && $publisher_data!=null)
		{
			$this->modelPublisher = new Model_DbTable_Users();
			$this->sessPublisherInfo = $this->modelPublisher->getInfoByUserId($publisher_data->id);
			
			$this->view->sessPublisherInfo=$this->sessPublisherInfo;
		}
		elseif($company_data && $company_data!=null)
		{
			$this->modelCompany = new Model_DbTable_Companies();
			$this->sessCompanyInfo = $this->modelCompany->getInfoByUserId($company_data->id);
			
			$this->view->sessCompanyInfo = $this->sessCompanyInfo;
		}
		elseif($userSessData && $userSessData!=null)
		{
			$this->modelCompanies = new Company_Model_DbTable_Companies();
			$this->sessUserInfo=$this->modelCompanies->getInfoByCompanyId($userSessData->id);
			$this->view->sessUserInfo =$this->sessUserInfo;
		}
		$this->modelFaqs=new Model_DbTable_Faqs();
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$title=$this->_getParam('title');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		$this->view->currentTitle=$title;
		$this->view->class_active='class="active"';
		/************* To Set The active section and links *******************/
		
		$this->view->headScript()->appendFile($this->view->serverUrl().$this->view->baseUrl().'/public/css/default/js/jquery.accordion.source.js');
		
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
		
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
		
		/******************************************************************/
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$this->view->modelBooks = $this->modelBooks; 
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		
    }
    
	public function indexAction()
    {
		$this->view->faqInfo=$this->modelFaqs->getFaqList();
		//$this->view->faqInfo=$this->modelFaqs->fetchAll('status=1 order by  sort_order asc');
	}
}

