<?php
class Publisher_IndexController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	
	var $modelPublisher=null;
	
	var $modelBooks=null;
	var $modelAuthor=null;
	

	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
        if(!$data)
		{
            $this->_redirect('publisher/auth/');
        }
		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		
		$this->modelBooks 	= new Publisher_Model_DbTable_Books();
		$this->modelAuthor 	= new Publisher_Model_DbTable_Author();
		
		$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
		$this->view->sessPublisherInfo =$this->sessPublisherInfo; 
		
		$this->modelBooks = new Publisher_Model_DbTable_Books();
		$this->view->modelBooks = $this->modelBooks; 
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
		
    }
	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		
		/*************** Book Informations ********************/
		if($this->sessPublisherInfo->user_type=='Pmanager' || $this->sessPublisherInfo->user_type=='Amanager')
		{
			$publisherid = $this->sessPublisherInfo->parent_id;
		}
		else
		{
			$publisherid = $this->sessPublisherInfo->id;
		}
		
		
		$bookList=$this->modelBooks->getListByCategoryName($publisherid,'eBook','0');
		$this->view->totalBookCount=count($bookList);
		$approvedBookList=$this->modelBooks->getListByCategoryName($publisherid,'eBook','1');
		$this->view->totalApprovedBookCount=count($approvedBookList);
		/*************** Book Informations Ends Here********************/
		
		/*************** Magazines Informations ********************/
		$magazineList=$this->modelBooks->getListByCategoryName($publisherid,'Magazine','0');
		$this->view->totalMagazineCount=count($magazineList);
		
		$approvedMagazineList=$this->modelBooks->getListByCategoryName($publisherid,'Magazine','1');
		$this->view->totalApprovedMagazineCount=count($approvedMagazineList);
		/*************** Magazines Informations Ends Here********************/
		
		/*************** Newspapers Informations ********************/
		$newspaperList=$this->modelBooks->getListByCategoryName($publisherid,'Newspaper','0');
		$this->view->totalNewspaperCount=count($newspaperList);
		
		$approvedNewspaperList=$this->modelBooks->getListByCategoryName($publisherid,'Newspaper','1');
		$this->view->totalApprovedNewspaperCount=count($approvedNewspaperList);
		/*************** Newspapers Informations Ends Here********************/
		
		/*************** Brand Informations *******************/
		$brand= new Publisher_Model_DbTable_Brand();
		$brandList=$brand->getList($publisherid);
		$this->view->totalBrandCount=count($brandList);
		
		/*************** Brand Informations Ends Here********************/
		
		/*************** Group Informations ********************/
		$groupList=$this->modelPublisher->getGroupList($publisherid);
		$this->view->totalGroupCount=count($groupList);
		$publicationManagersList=$this->modelPublisher->getPublicationManagersList($publisherid);
		$this->view->totalPublicationManagersCount=count($publicationManagersList);
		$accountManagersList=$this->modelPublisher->getAccountManagersList($publisherid);
		$this->view->totalAccountManagersCount=count($accountManagersList);
		
		/*************** Group Informations Ends Here ******************/
		
		/*************** Author Informations ********************/
		$authorList=$this->modelAuthor->getAuthorList($publisherid);
		$this->view->totalAuthorCount=count($authorList);
		
		$activeAuthorsList=$this->modelAuthor->getActiveAuthorList($publisherid);	
		$this->view->totalActiveAuthorCount=count($activeAuthorsList);
		
		
	}
	
}

