<?php
class IndexController extends Zend_Controller_Action
{
    var $sessPublisherInfo=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelBooks=null;
	var $modelJD=null;
	
	public function init()
    {
		/*********** To check for publisher session data *********************/
		$storage_publisher = new Zend_Auth_Storage_Session('publisher_type');
		$publisher_data = $storage_publisher->read();
		
		$storage_company = new Zend_Auth_Storage_Session('company_type');
		$company_data = $storage_company->read();
				
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
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		$this->view->class_active='class="active"';
		/************* To Set The active section and links *******************/
		
		$this->view->headScript()->appendFile($this->view->serverUrl().$this->view->baseUrl().'/public/css/default/js/banner_and_slider.js');
		
		
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
		
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$this->view->modelBooks = $this->modelBooks; 
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		
		/******************************************************************/
		
	}
    
	public function indexAction()
    {
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$storeId=$storeSession->sid;
			$storeName=$storeSession->sname;
		}
		
		/*******************************************************************/
	/*	$approvedBookList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,'eBook','1','0');
		$this->view->approvedBookList=$approvedBookList;
		
		$approvedAndFeaturedBookList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,'eBook','1','1');
		$this->view->approvedAndFeaturedBookList=$approvedAndFeaturedBookList;*/
		
		$approvedAndFeaturedBookList=$this->modelBooks->getPublicationFeaturedItem($storeId,'3','1','1');
		$this->view->approvedAndFeaturedBookList=$approvedAndFeaturedBookList;

		/*******************************************************************/
				
		/************************** Magazines Details *************************************/
	
		$approvedMagazineIssuesList=$this->modelBooks->getPublicationFeaturedItem($storeId,'2','1','1');
		$this->view->approvedMagazineList=$approvedMagazineIssuesList;

		/*******************************************************************/
		
		/*************************  Newspaper Details  ***************************************/
		$approvedNewLetterIssuesList=$this->modelBooks->getPublicationFeaturedItem($storeId,'1','1','1');
		$this->view->approvedNewsletterList=$approvedNewLetterIssuesList;
		
		/*******************************************************************************************/
		
	}
}

