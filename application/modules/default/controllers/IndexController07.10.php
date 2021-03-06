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
		
		
		$this->modelCategories = new Admin_Model_DbTable_Categories();
		 
		$categoriesList=$this->modelCategories->getList();
		$categoriesList=$categoriesList->toArray();
		
		foreach($categoriesList as $key=>$catArray)
		{
			$records=$this->modelBooks->getPublicationIdsByCategoryName($storeSession->sid,$catArray['category_name'],'1','0');
		 
			$categoriesList[$key]['total_publications']=count($records);
		}
		$this->view->allcategories=$categoriesList;
		
		$this->modelGenres = new Admin_Model_DbTable_Genres();
		$query=$this->modelBooks->select();
		$query->setIntegrityCheck(false);
		//newspaper genres
		//$getnewspapers_genres =
		$db = Zend_Registry::get('db');
		/*$query->from(array("book"=>$this->modelBooks),array('DISTINCT(product_type)'));
		$query->join(array("genre"=>$this->modelGenres), "book.product_type = genre.id", array('genre.genre'));
		$query->where('book.cat_id = ?', 1);
		$query->order('genre.genre asc');*/		
		$query = "SELECT DISTINCT (prod.product_type), gen.genre FROM pclive_products prod
				  INNER JOIN pclive_genres gen ON prod.product_type = gen.id 
				  WHERE prod.cat_id =1 ORDER BY gen.genre ASC ";		
		$result = $db->query($query); 		
		$getnewspapers_genres = $result->fetchAll();	 
		//$getnewspapers_genres = $getnewspapers_genres->toArray();
		$this->view->getnewspapers_genres = $getnewspapers_genres;
		
		//magazine genres
		$query_mag = "SELECT DISTINCT (prod.product_type), gen.genre FROM pclive_products prod
				  INNER JOIN pclive_genres gen ON prod.product_type = gen.id 
				  WHERE prod.cat_id =2 ORDER BY gen.genre ASC ";		
		$result_mag = $db->query($query_mag); 		
		$getmagazine_genres = $result_mag->fetchAll();		 
		$this->view->getmagazine_genres = $getmagazine_genres;
		
		//ebook genres
		$query_ebook = "SELECT DISTINCT (prod.product_type), gen.genre FROM pclive_products prod
				  INNER JOIN pclive_genres gen ON prod.product_type = gen.id 
				  WHERE prod.cat_id =2 ORDER BY gen.genre ASC ";		
		$result_ebook = $db->query($query_ebook); 		
		$getebooks_genres = $result_ebook->fetchAll();	 
		$this->view->getebooks_genres = $getebooks_genres;
	 		
		
		/*$this->modelGenres = new Admin_Model_DbTable_Genres();		
		for($x=0;$x<count($getnewspapers_genres);$x++)
		{
			
			$genrename = $this->modelGenres->fetchAll('id="'.$getnewspapers_genres[$x]['product_type'].'"');
			
			
		}
		echo "<pre>";
			print_r($genrename->toArray());
			echo "</pre>";
		//$data = $select->fetchAll();*/
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

		#######################  best seller book #################################
		$bestsellerBook = $this->modelBooks->getBestSellerProduct($storeId,'3','1','1');
		$this->view->bestSellBookList=$bestsellerBook;
		/************************** Magazines Details *************************************/
	
		$approvedMagazineIssuesList=$this->modelBooks->getPublicationFeaturedItem($storeId,'2','1','1');
		$this->view->approvedMagazineList=$approvedMagazineIssuesList;

		/*******************************************************************/
		
		/*************************  Newspaper Details  ***************************************/
		$approvedNewLetterIssuesList=$this->modelBooks->getPublicationFeaturedItem($storeId,'1','1','1');
		$this->view->approvedNewsletterList=$approvedNewLetterIssuesList;		
		/*******************************************************************************************/
		#################  most commented products ###############################################
		//$this->commentObj = new User_Model_DbTable_Usercomment();
		
		$db = Zend_Registry::get('db');
		
		$query_mostcomment = "SELECT prod.title,prod.id as product_id,count(prod.id) as tot,rvw.* FROM pclive_products prod
				  INNER JOIN  pclive_review rvw ON prod.id = rvw.productid 
				  WHERE prod.status ='1' group by prod.id ORDER BY tot DESC,rvw.add_time DESC";		
		$result = $db->query($query_mostcomment); 		
		$most_commented = $result->fetchAll();	 
		
		//$getnewspapers_genres = $getnewspapers_genres->toArray();
		$this->view->most_commented_data = $most_commented;
		##########################################################################################
		
		
	}
}

