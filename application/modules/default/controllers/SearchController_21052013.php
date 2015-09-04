<?php
class SearchController extends Zend_Controller_Action
{
	var $sessPublisherInfo=null;
	var $sessCompanyInfo=null;
	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelCatalogue=null;
	var $modelBooks=null;
	var $modelCategories=null;
	
	
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
		
		$this->view->headScript()->appendFile('public/css/default/js/jquery.accordion.source.js');
		
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
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		
		
		/*********************** For Stores with total publications ************************/
		if(!empty($allStored) && count($allStored)>0)
		{
			$country_array_with_total_publications=array();
			foreach($allStored as $continent_id=>$country_array)
			{
				foreach($country_array as $cid=>$cname)
				{
					$country_array_with_total_publications[$cid]['cid']=$cid;
					$country_array_with_total_publications[$cid]['cname']=$cname;
					$crecords=$this->modelBooks->getPublicationIdsByCategoryName($cid,'','1','0','0','0');
					$country_array_with_total_publications[$cid]['total_publications']=count($crecords);
				}
			}
		$this->view->allStoredWithPublications=$country_array_with_total_publications;	
		}
		
		/*********************** For Stores with total publications ************************/
		
		/************* For Ctegories with total publications *********************************/
		$this->modelCategories = new Admin_Model_DbTable_Categories();
		$categoriesList=$this->modelCategories->getList();
		$categoriesList=$categoriesList->toArray();
		
		foreach($categoriesList as $key=>$catArray)
		{
			$records=$this->modelBooks->getPublicationIdsByCategoryName($storeSession->sid,$catArray['category_name'],'1','0');
			$categoriesList[$key]['total_publications']=count($records);
		}
		$this->view->categoriesList=$categoriesList;
		
		/********** For Ctegories with total publications ************************************/
		
		/************* For Genres with total publications *********************************/
		$this->modelGenres = new Publisher_Model_DbTable_Genres();
		$genresList=$this->modelGenres->getGenreList();
		$genreid=$this->_getParam('id');
		 
		if(!isset($genreid))
		{
			foreach($genresList as $key=>$genreArray)
			{
				$records = 0;
				$records=$this->modelBooks->getPublicationIdsByCategoryName($storeSession->sid,'','1','0','0',$genreArray['id']);
				$genresList[$key]['total_publications']=count($records);
			}
		}
		else
		{
			$records=$this->modelBooks->getPublicationIdsByCategoryName($storeSession->sid,'','1','0','0',$genreid);
			$genresList[$key]['total_publications']=count($records);
		
		}
		
		
		$this->view->genresList=$genresList;
		
		/********** For Genres with total publications ************************************/
		
		/******************************************************************/
	}
    
	public function indexAction()
    {
		$postkeyword = new Zend_Session_Namespace('searchkeyword');
		
		$get_searchfield = $postkeyword->searchword;
		
		$catName=$this->_getParam('publication');
		$genreid=$this->_getParam('id');
		
		$this->view->producttype = $genreid;
		
		
		
		 
	 
		 //echo $get_searchfield;
		 //die();
	
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$storeId=$storeSession->sid;
			$storeName=$storeSession->sname;
			 
		}
		
		if(!empty($catName) && $get_searchfield=='' && $genreid!='')
		{
			if(strtolower(trim($catName))=='genre' && $this->_getParam('id')>0)
			{
			    // $storeId
				$approvedPublicatiobList=$this->modelBooks->getPublicationListByGenre($this->_getParam('id'));
			}
			elseif(strtolower(trim($catName))=='store' && $this->_getParam('id')>0)
			{
				$approvedPublicatiobList=$this->modelBooks->getPublicationListByStore($this->_getParam('id'));
			}
			else
			{
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,$catName,'1','0');
			}	
		}
		
		else if(!empty($catName) && $get_searchfield!='' && $genreid=='')
		{
			if(strtolower(trim($catName))=='genre' && $this->_getParam('id')>0)
			{
				 
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,'','1','0','0',$this->_getParam('id'));
			}
			elseif(strtolower(trim($catName))=='store' && $this->_getParam('id')>0)
			{
				 
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($this->_getParam('id'),'','1','0','0','0');
			}
			else
			{
				 
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,$catName,'1','0');
			}	
		}
		
		else if(isset($get_searchfield) && $get_searchfield!='' && empty($catName) && $genreid=='')
		{
			//echo "get_search field".$get_searchfield;
			//die();
			$approvedPublicatiobList = $this->modelBooks->getkeywordlist($get_searchfield,$storeId,'1','');
			
		 
			
		}
		
		else if(isset($get_searchfield) && $get_searchfield!='' && $genreid!='')
		{
			 
			$approvedPublicatiobList = $this->modelBooks->getkeywordlist($get_searchfield,$storeId,'1',$genreid);
			
		 
			
		}
		
		else if(!isset($get_searchfield) && $genreid!='')
		{
			$approvedPublicatiobList = $this->modelBooks->getgenrelist($storeId,'1',$genreid);
		}
		else 
		{
			$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,'','1','0');
		}
		
		
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($approvedPublicatiobList);
	    //$paginator->setItemCountPerPage(3);
		$paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($approvedPublicatiobList);
		//$this->view->pageSize=3;
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->approvedPublicatiobList=$paginator;
		
		if($catName!='')
		{
			$this->view->searchKeyword=$catName;
		}
		else if($get_searchfield!='')
		{
			$this->view->searchKeyword=$get_searchfield;	
		}
		//$this->view->approvedPublicatiobList=$approvedPublicatiobList;
	 
	}
	
	public function storeinsessionAction()
	{
		$postkeyword = new Zend_Session_Namespace('searchkeyword');
		if($_POST['search_box']!='Enter search here')
		{
			$postkeyword->searchword = $_POST['search_box'];
		}
		else
		{
			$postkeyword->searchword = "";
		}		
		$this->_redirect('/search/');
		
	}
}