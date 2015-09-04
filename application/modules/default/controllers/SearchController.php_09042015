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
					$crecords=$this->modelBooks->getPublicationIdsByCategoryNameFront($cid,'','1','0','0','0');
					$country_array_with_total_publications[$cid]['total_publications']=count($crecords);
				}
			}
		$this->view->allStoredWithPublications=$country_array_with_total_publications;	
		}
		
		/*********************** For Stores with total publications ************************/
		
		/************* For Ctegories with total publications *********************************/
		$this->modelCategories = new Admin_Model_DbTable_Categories();
		$categoriesList=$this->modelCategories->getListFront();
		$categoriesList=$categoriesList->toArray();
		
		foreach($categoriesList as $key=>$catArray)
		{
			
			$records=$this->modelBooks->getPublicationIdsByCategoryNameFront($storeSession->sid,$catArray['category_name'],'1','0');
			$recordsCat = $this->modelBooks->getPublicationIdsByCategoryNameCountFront($storeSession->sid,$catArray['category_name'],'1','0');
			$categoriesList[$key]['total_publications']=count($recordsCat);
		}
		//echo "<pre>";
		//print_r($categoriesList);
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
				$records=$this->modelBooks->getPublicationIdsByCategoryNameFront($storeSession->sid,'','1','0','0',$genreArray['id']);
				$genresList[$key]['total_publications']=count($records);
			}
		}
		else
		{
			$records=$this->modelBooks->getPublicationIdsByCategoryNameFront($storeSession->sid,'','1','0','0',$genreid);
			$genresList[$key]['total_publications']=count($records);
		
		}
		
		
		$this->view->genresList=$genresList;
		
		/********** For Genres with total publications ************************************/
		
		/******************************************************************/
	}
    
	public function indexAction()
    {
		$genreObj = new Publisher_Model_DbTable_Genres();
		$postkeyword = new Zend_Session_Namespace('searchkeyword');
		$get_searchfield = $postkeyword->searchword;
		
		//echo $postkeyword->searchword;
		//echo $postkeyword->storSearchId;
		
		$catName=$this->_getParam('publication');
		$genreid=$this->_getParam('id');
		//$from_page=$this->_getParam('from');
		$cat_id=$this->_getParam('cat_id');
		
		$this->view->CateName = $catName;
		$this->view->keyWord = $get_searchfield;		
		$this->view->producttype = $genreid;
		
		
		if($catName == 'store')
		{
			$countryname = $this->modelBooks->getCountryName($genreid);
			$this->view->CateName = ucwords($countryname['country']);
		}
		elseif($catName == 'genre')
		{
			$generDet = $genreObj->getGenreInfo($genreid);
			$this->view->CateName = ucwords($generDet['genre']);
		}
		else
		{
			$this->view->CateName = ucwords($catName);
		}
		$storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$storeId=$storeSession->sid;
			$storeName=$storeSession->sname;			 
		}
		if($postkeyword->storSearchId!='')
		{
			$storeId=$postkeyword->storSearchId;
		}
		else {
			$storeId=$storeId;
		}
		if($storeId == "all")
		{
			$storeId='';
		}
		if(!empty($catName) && $get_searchfield=='' && $genreid!='')
		{
			
			if(strtolower(trim($catName))=='genre' && $this->_getParam('id')>0)
			{
			    // $storeId
				if($cat_id!='')
				{
					$approvedPublicatiobList=$this->modelBooks->getPublicationListByGenreByStoreFront($this->_getParam('id'),$storeId,'1',$cat_id);
				}
				else
				{
					//echo "kkk";
					$approvedPublicatiobList=$this->modelBooks->getPublicationListByGenreFront($this->_getParam('id'),$storeSession->sid);
				}				
			}
			elseif(strtolower(trim($catName))=='store' && $this->_getParam('id')>0)
			{
				$approvedPublicatiobList=$this->modelBooks->getPublicationListByStoreFront($this->_getParam('id'));
			}
			else
			{
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryNameFront($storeId,$catName,'1','0');
			}	
		}
		else if(!empty($catName) && $get_searchfield!='' && $genreid=='')
		{
			
			if(strtolower(trim($catName))=='genre' && $this->_getParam('id')>0)
			{
				 
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryNameFront($storeId,'','1','0','0',$this->_getParam('id'));
			}
			elseif(strtolower(trim($catName))=='store' && $this->_getParam('id')>0)
			{				 
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryNameFront($this->_getParam('id'),'','1','0','0','0');
			}
			else
			{	
				$postkeyword = new Zend_Session_Namespace('searchkeyword');
				 $postkeyword->searchword = '';
				 $postkeyword->storSearchId = '';
				$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryNameCountFront($storeSession->sid,$catName,'1','0');
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
			//$approvedPublicatiobList = $this->modelBooks->getkeywordlist($get_searchfield,$storeId,'1',$genreid);
			 $approvedPublicatiobList=$this->modelBooks->getPublicationListByGenreFront($this->_getParam('id'));
			 $postkeyword = new Zend_Session_Namespace('searchkeyword');
			 $postkeyword->searchword = '';
			 $postkeyword->storSearchId = '';
		}		
		else if(!isset($get_searchfield) && $genreid!='')
		{
			$approvedPublicatiobList = $this->modelBooks->getgenrelistFront($storeId,'1',$genreid);
		}
		else 
		{			
			$approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryNameFront($storeId,$catName,'1','0');
		}
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($approvedPublicatiobList);
	    //$paginator->setItemCountPerPage(3);
		$paginator->setItemCountPerPage(16);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($approvedPublicatiobList);
		//$this->view->pageSize=3;
		$this->view->pageSize=16;
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
		if($postkeyword->searchword!='Enter search here')
		{
			$this->view->storSearchId=$postkeyword->storSearchId;
		}
		//$this->view->approvedPublicatiobList=$approvedPublicatiobList;
	 
	}
	
	public function storeinsessionAction()
	{
		$postkeyword = new Zend_Session_Namespace('searchkeyword');
		if($_POST['search_box']!='Enter search here')
		{
			$postkeyword->searchword = $_POST['search_box'];
			$postkeyword->storSearchId = $_POST['country_id'];
		}
		else
		{
			$postkeyword->searchword = "";
			$postkeyword->storSearchId="";
		}		
		$this->_redirect('/search/');
		
	}
}