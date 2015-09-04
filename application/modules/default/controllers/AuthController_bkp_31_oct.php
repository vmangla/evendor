<?php
class AuthController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
    }
	
	
    public function indexAction()
    {
		$this->view->messages = $this->_flashMessenger->getMessages();
		
		//$this->_helper->layout()->setLayout('adminlogin');
		
		$users = new Model_DbTable_Users();  
        if($this->getRequest()->isPost())
		{
			$formdata =$this->getRequest()->getPost();
			//print_r($formdata);exit;
			$auth = Zend_Auth::getInstance();
			$authAdapter = new Zend_Auth_Adapter_DbTable($users->getAdapter(),TBL_USERS);
			$authAdapter->setIdentityColumn('username')
						->setCredentialColumn('password');
			
			$authAdapter->setIdentity($formdata['user_name'])
						->setCredential($formdata['user_password']);
			$result = $auth->authenticate($authAdapter);
			if($result->isValid())
			{
				$storage = new Zend_Auth_Storage_Session('user_type');
				$storage->write($authAdapter->getResultRowObject());
				$this->_redirect('publisher/');
			} 
			else 
			{
				$this->view->errorMessage='<div class="div-error">Invalid username or password</div>';
			}
        }
    }
	
    public function logoutAction()
    {
		$storage = new Zend_Auth_Storage_Session('user_type');
        $storage->clear();
		$this->_flashMessenger->addMessage('<div class="div-success">You have logged out successfully</div>');
        $this->_redirect('/');
    }
	
	public function storechangeAction()
    {
		
		/***********************************************************/
		$modelCountry = new Admin_Model_DbTable_Countries();
		$storeId=$this->_getParam('storeid');
		if(!empty($storeId))
		{
			$country_info = $modelCountry->fetchRow("id='$storeId'");
			$storeId=$this->getRequest()->getCookie('sid');
			$storeName=$this->getRequest()->getCookie('sname');
			$curId=$this->getRequest()->getCookie('curId');
			
			if(!empty($storeId) && !empty($storeName))
			{
				setcookie("sid", $country_info->id);
				setcookie("sname", $country_info->country);
				setcookie("curId", $country_info->currency_id);
				
				$storeSession = new Zend_Session_Namespace('storeInfo');
				$storeSession->sid=$country_info->id;
				$storeSession->sname=$country_info->country;
				$storeSession->curId=$country_info->currency_id;
			}
			else
			{
				setcookie("sid", $country_info->id);
				setcookie("sname", $country_info->country);
				setcookie("curId", $country_info->currency_id);
				
				$storeSession = new Zend_Session_Namespace('storeInfo');
				$storeSession->sid=$country_info->id;
				$storeSession->sname=$country_info->country;
				$storeSession->curId=$country_info->currency_id;
				
				//$publication_store = new Zend_Auth_Storage_Session('store_type');
				//$publication_store->write($country_info);
			}
		//exit;			
		}
		else
		{
			$storeId=$this->getRequest()->getCookie('sid');
			$storeName=$this->getRequest()->getCookie('sname');
			$curId=$this->getRequest()->getCookie('curId');
			
			if(empty($storeId) && empty($storeName))
			{
				$country_info = $modelCountry->fetchRow('country="Nigeria"');
				setcookie("sid", $country_info->id);
				setcookie("sname", $country_info->country);
				setcookie("curname", $country_info->currency_id);
				
				$storeSession = new Zend_Session_Namespace('storeInfo');
				$storeSession->sid=$country_info->id;
				$storeSession->sname=$country_info->country;
				$storeSession->cid=$country_info->id;
				$storeSession->cname=$country_info->country;
				$storeSession->curId=$country_info->currency_id;
				
			}
			else
			{
				$storeSession = new Zend_Session_Namespace('storeInfo');
				$storeSession->sid=$storeId;
				$storeSession->sname=$storeName;
				$storeSession->cid=$storeId;
				$storeSession->cname=$storeName;
				$storeSession->curId=$curId;
			}
		//exit;	
		}
		/*
		$modelCurrency = new Admin_Model_DbTable_Currencies();
		$currency_info = $modelCurrency->fetchRow("id='$storeSession->curId'");
		echo ">>>>>".$currency_info->currency_name;
		$storeSession->curname=$currency_info->currency_name;
		echo ">>>>>".$storeSession->curname;
		exit();
		*/
		
		/********************************************************************/
		$this->_redirect('/');
	}
	
}

