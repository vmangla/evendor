<?php
class PublisherController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$storage = new Zend_Auth_Storage_Session('user_type');
		$data = $storage->read();
		if($data && $data!=null)
		{
			$modelPublisher = new Model_DbTable_Users();
			//$modelPublisherProfiles = new Model_DbTable_UserProfiles();

			$this->view->sessUserInfo=$modelPublisher->getInfoByUserId($data->id);
			//$this->view->sessUserInfo=$modelPublisherProfiles->getInfoByUserId($data->id);
		}
		else
		{
			$this->_redirect('auth/');
		}
	}
	
    public function indexAction()
    {
	
    }
	
}

