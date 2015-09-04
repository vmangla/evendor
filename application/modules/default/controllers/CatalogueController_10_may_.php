<?php
class CatalogueController extends Zend_Controller_Action
{
	var $sessPublisherInfo=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelCatalogue=null;
	var $modelBooks=null;
	
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
		
		//$this->modelCatalogue = new Publisher_Model_DbTable_Books();
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$title=$this->_getParam('title');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		$this->view->currentTitle=$title;
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
		
		
		/***************** Continents And Countries ***********************/
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		$this->view->modelBooks=$this->modelBooks;
		/***************** Continents And Countries ***********************/
	}
    
	public function indexAction()
    {
		$catName=$this->_getParam('publication');
		
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$storeId=$storeSession->sid;
			$storeName=$storeSession->sname;
		}
		
		if(!empty($catName))
		{
		$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,$catName,'1','0');
		}
		else
		{
			$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,'','1','0');
		}
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($approvedPublicatiobList);
	    //$paginator->setItemCountPerPage(3);
		$paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($approvedPublicatiobList);
		//$this->view->pageSize=3;
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->approvedPublicatiobList=$paginator;
		//$this->view->approvedPublicatiobList=$approvedPublicatiobList;
	
	}
	
	public function detailAction()
    {			
 		$id=$this->_request->getParam('id',0);
		$store_id=$this->_request->getParam('store',0);
		$language_id=$this->_request->getParam('lang',0);
		
		if($id>0 && $this->modelBooks->isPriceExist($id,$store_id,$language_id))
		{
			$storePublicationDetail=$this->modelBooks->getStorePublicationDetail($id,$store_id,$language_id);
			$this->view->storePublicationDetail=$storePublicationDetail;
			
			$publicationInfo=$this->modelBooks->fetchRow("id='$id'");
			
			if(!empty($publicationInfo) && count($publicationInfo)>0)
			{
				if($publicationInfo['cat_id']>0)
				{
					$catInfo=$this->modelBooks->getCategoryInfo($publicationInfo['cat_id']);
				}
				
				if(!empty($catInfo) && count($catInfo)>0)
				{
					if($publicationInfo['parent_brand_id']>0)
					{
						$backIssuesList=$this->modelBooks->getPublicationIdsByCategoryName($store_id,$catInfo['category_name'],'1','0',$publicationInfo['parent_brand_id']);
						
						$similarPublicationList=$this->modelBooks->fetchAll("cat_id='$publicationInfo[cat_id]' AND parent_brand_id='0' AND admin_approve='1'");
						$similarPublicationList=$similarPublicationList->toArray();
					}
					else
					{
						$backIssuesList="";
						$similarPublicationList=$this->modelBooks->getPublicationIdsByCategoryName($store_id,$catInfo['category_name'],'1','0',$publicationInfo['parent_brand_id']);
					}
				$this->view->backIssuesList=$backIssuesList;
				$mainCatList[$catInfo['category_name']]=$similarPublicationList;
				
				$this->view->similarPublicationList=$mainCatList;
				$this->view->currentIssueId=$id;
				}
			}
			
		}
		else
		{
			$this->_redirect('catalogue/');
		}
	}
	
}

