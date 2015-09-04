<?php

class ErrorController extends Zend_Controller_Action
{
    public function init()
    {
      $storage = new Zend_Auth_Storage_Session('user_type');
  		$data = $storage->read();
  		if($data && $data!=null)
  		{
  			$this->sessUser=$data;
  			$this->view->id =$this->sessUser->id;
  			$this->view->sessUser=$this->sessUser;
  			if($this->sessUser->usertype==1)
  			{
  				$this->view->contact_person =$this->sessUser->first_name;
  			}
  			if($this->sessUser->usertype==2)
  			{
  				$this->view->contact_person=$this->sessUser->contact_person;
  			}
  	   }
	  $storeSession = new Zend_Session_Namespace('storeInfo');
		if(!empty($storeSession->sid) && !empty($storeSession->sid) && !empty($storeSession->sname) && !empty($storeSession->sid) && !empty($storeSession->cid) && !empty($storeSession->cname))
		{
			$this->view->storeId=$storeSession->sid;
			$this->view->storeName=$storeSession->sname;
		}
	   $this->modelBooks = new Publisher_Model_DbTable_Books();
		$this->view->modelBooks = $this->modelBooks; 
		$allStored=$this->modelBooks->getAllStores();
		$this->view->allStored=$allStored;
    }
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
       //echo "mousumi";
	   //exit();
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
				$this->_redirect('/error/pagenotfound/');
                break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }
	public function pagenotfoundAction()
	{
		//exit();
	}

}

