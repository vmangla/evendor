<?php
class Company_DashboardController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
	
	var $modelCompany=null;
	//var $modelCompanyProfiles=null;
	//var $modelMember=null;
	var $modelSubdcription=null;
	
	public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
			
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
        if(!$data)
		{
            $this->_redirect('company/auth/');
        }
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelSubdcription = new Company_Model_DbTable_Subscriptions();
		//$this->modelMember = new Company_Model_DbTable_Members();
		
		$this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
		$this->view->sessCompanyInfo =$this->sessCompanyInfo;

		
    }
	public function indexAction()
	{
		$this->_helper->layout->disableLayout();
		
		/*************** Subscriptions Information ********************/
		$all_subscriptions=$this->modelSubdcription->getAllTotalSubscription($this->sessCompanyInfo->id);
		$this->view->all_subscriptions=$all_subscriptions;
		/*************** Subscriptions Information ********************/
		
		/*************** Member Informations ********************/
		$memberList=$this->modelCompany->getMemberList($this->sessCompanyInfo->id);
		$this->view->totalMemberCount=count($memberList);
		/*************** Member Informations Ends Here ******************/
	}
}

