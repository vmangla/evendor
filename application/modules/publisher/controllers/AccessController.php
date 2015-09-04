<?php
class Publisher_AccessController extends Zend_Controller_Action
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
		
        $this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		
		$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
		$this->view->sessPublisherInfo =$this->sessPublisherInfo;
		
		$tab_ajax=$this->_request->getParam('tab_ajax',0);
		if(empty($tab_ajax))
		{
			$this->_redirect('publisher/');
		}
	}
	
	public function indexAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
	}
}

