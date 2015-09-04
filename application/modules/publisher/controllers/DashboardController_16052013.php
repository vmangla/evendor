<?php
class Publisher_DashboardController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	
	var $modelPublisher=null;
	
	var $modelBooks=null;
	var $modelGroup=null;
	var $modelAuthor=null;
	

	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
        if($data && $data!=null)
		{
			$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
			$this->modelBooks 	= new Publisher_Model_DbTable_Books();
			$this->modelGroup 	= new Publisher_Model_DbTable_Group();
			$this->modelAuthor 	= new Publisher_Model_DbTable_Author();
			
			$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo; 
			
			$tab_ajax=$this->_request->getParam('tab_ajax',0);
			if(empty($tab_ajax))
			{
				$this->_redirect('publisher/');
			}
		}
		else
		{
            $this->_redirect('publisher/auth/');
        }
	}
	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		
		/*************** Book Informations ********************/
		$bookList=$this->modelBooks->getListByCategoryName($this->sessPublisherInfo->id,'eBook','0');
		$this->view->totalBookCount=count($bookList);
		$approvedBookList=$this->modelBooks->getListByCategoryName($this->sessPublisherInfo->id,'eBook','1');
		$this->view->totalApprovedBookCount=count($approvedBookList);
		/*************** Book Informations Ends Here********************/
		
		/*************** Magazines Informations ********************/
		$magazineList=$this->modelBooks->getListByCategoryName($this->sessPublisherInfo->id,'Magazine','0');
		$this->view->totalMagazineCount=count($magazineList);
		
		$approvedMagazineList=$this->modelBooks->getListByCategoryName($this->sessPublisherInfo->id,'Magazine','1');
		$this->view->totalApprovedMagazineCount=count($approvedMagazineList);
		/*************** Magazines Informations Ends Here********************/
		
		/*************** Newspapers Informations ********************/
		$newspaperList=$this->modelBooks->getListByCategoryName($this->sessPublisherInfo->id,'Newspaper','0');
		$this->view->totalNewspaperCount=count($newspaperList);
		
		$approvedNewspaperList=$this->modelBooks->getListByCategoryName($this->sessPublisherInfo->id,'Newspaper','1');
		$this->view->totalApprovedNewspaperCount=count($approvedNewspaperList);
		/*************** Newspapers Informations Ends Here********************/
		
		/*************** Brand Informations *******************/
		$brand= new Publisher_Model_DbTable_Brand();
		$brandList=$brand->getList($this->sessPublisherInfo->id);
		$this->view->totalBrandCount=count($brandList);
		
		/*************** Brand Informations Ends Here********************/
		
		/*************** Group Informations ********************/
		$groupList=$this->modelPublisher->getGroupList($this->sessPublisherInfo->id);
		$this->view->totalGroupCount=count($groupList);
		$publicationManagersList=$this->modelPublisher->getPublicationManagersList($this->sessPublisherInfo->id);
		$this->view->totalPublicationManagersCount=count($publicationManagersList);
		$accountManagersList=$this->modelPublisher->getAccountManagersList($this->sessPublisherInfo->id);
		$this->view->totalAccountManagersCount=count($accountManagersList);
		
		/*************** Group Informations Ends Here ******************/
		
		/*************** Author Informations ********************/
		$authorList=$this->modelAuthor->getAuthorList($this->sessPublisherInfo->id);
		$this->view->totalAuthorCount=count($authorList);
		
		$activeAuthorsList=$this->modelAuthor->getActiveAuthorList($this->sessPublisherInfo->id);	
		$this->view->totalActiveAuthorCount=count($activeAuthorsList);
	}
	
}

