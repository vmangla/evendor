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
		
		/*********************** For Stores with total publications ************************/
		
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		
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
		
		
	}
    
	public function indexAction()
    {
		$catName=$this->_getParam('publication');
		
		$allebooks=$this->_request->getParam('allebooks',0);	
		$backissue=$this->_request->getParam('backissue',0);
		$store_id=$this->_request->getParam('store',0);
		$language_id=$this->_request->getParam('lang',0);
		
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
		    if($backissue!="" && $store_id!="" && $language_id!="")
			{
			   $approvedPublicatiobList=$this->modelBooks->getBackIssue($store_id,$backissue);
			}
			else if($allebooks!="" && $store_id!="" && $language_id!="")
			{
			   $approvedPublicatiobList=$this->modelBooks->getBackIssue($store_id,$allebooks);
			}
			else
			{
			   $approvedPublicatiobList=$this->modelBooks->getPublicationIdsByCategoryName($storeId,'','1','0');
			}
		}
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($approvedPublicatiobList);
	    //$paginator->setItemCountPerPage(3);
		$paginator->setItemCountPerPage(10);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($approvedPublicatiobList);
		//$this->view->pageSize=3;
		$this->view->pageSize=10;
		$this->view->page=$page;
		$this->view->approvedPublicatiobList=$paginator;
		$this->view->storeId=$storeId;
		//$this->view->approvedPublicatiobList=$approvedPublicatiobList;
	
	}
	
	public function detailAction()
    {			
 		$id=$this->_request->getParam('id',0);
		$store_id=$this->_request->getParam('store',0);
		$language_id=$this->_request->getParam('lang',0);
		
		if($id>0 && $this->modelBooks->isPriceExist($id,$store_id,$language_id))
		{
		    // Get Publocation details
			$storePublicationDetail=$this->modelBooks->getStorePublicationDetail($id,$store_id,$language_id);
			$this->view->storePublicationDetail=$storePublicationDetail;
			
			
			#######################  best seller book #################################
			$bestsellerBook = $this->modelBooks->getBestSellerProduct($store_id,'3','1','1');
			$this->view->bestSellBookList=$bestsellerBook;
			/************************** Magazines Details *************************************/
			
			// Get catalgue details
			$publicationInfo=$this->modelBooks->fetchRow("id='$id'");
			$this->view->publicationInfo=$publicationInfo;
			
			// Get Publisher details
			$this->modelPublisher = new Model_DbTable_Users();
			$PublisherInfo = $this->modelPublisher->getInfoByUserId($publicationInfo['publisher_id']);
			$this->view->PublisherInfo=$PublisherInfo;
			
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
						
						$backIssuesList=$this->modelBooks->getBackIssue($store_id,$publicationInfo['parent_brand_id']);
						
						$cateDet = $this->modelBooks->getCategoryInfo($publicationInfo[cat_id]);
						//$similarPublicationList=$this->modelBooks->fetchAll("cat_id='$publicationInfo[cat_id]' AND admin_approve='1' and id not IN (".$id.")");
						$similarPublicationList=$this->modelBooks->getPublicationIdsByCategoryName($store_id,$cateDet['category_name'],'1','0',0,$id);
						//$similarPublicationList=$similarPublicationList->toArray();		
									
					}
					else
					{
						
						$backIssuesList="";
						$similarPublicationList=$this->modelBooks->getPublicationIdsByCategoryName($store_id,$catInfo['category_name'],'1','0',$publicationInfo['parent_brand_id'],$id);
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
			//exit;
		}
	}
	public function insertusercommentAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$postData =$this->getRequest()->getPost();
		$user_id = $postData['user_id'];
		$user_type = $postData['user_type'];
		$message = $postData['message'];
		$product_id = $postData['product_id'];
		$this->commentObj = new User_Model_DbTable_Usercomment();		
		if($message!='' && $product_id!='' )
		{
			$current_date = date("Y-m-d H:i:s");
			$data = array("userid"=>$user_id,
							"comments"=>$message,
							"productid"=>$product_id,
							"user_type"=>$user_type,
							"add_time"=>$current_date);
			$this->commentObj->insert($data);
			echo "success";
		}
		else {
			echo "Comment not post";
		}
		
	}	
}

