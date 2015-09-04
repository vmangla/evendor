<?php
class Company_PurchasepointsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;
 
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		$storage = new Zend_Auth_Storage_Session('company_type');
		$this->modelCompanies = new Company_Model_DbTable_Companies();
		$data = $storage->read();
		if($data && $data!=null)
		{
			$this->sessCompanyInfo=$this->modelCompanies->getInfoByCompanyId($data->id);
			$this->view->sessCompanyInfo =$this->sessCompanyInfo;
		}
		else
		{
			$this->view->sessCompanyInfo =$data; 
        }
		
	}
		
	public function indexAction()
	{
		$this->_helper->layout()->disableLayout();
		$points = new Admin_Model_DbTable_Points();
		$points_data = $points->fetchAll('status="1"','sort_order asc');
		$this->view->pointsdata = $points_data; 
		
		
	}
	public function historyAction()
	{
	 
	  $this->_helper->layout()->disableLayout();	
	  $this->credit_history = new User_Model_DbTable_Chistory();
	  
	  $this->credithistory = $this->credit_history->fetchAll('userid="'.$this->sessCompanyInfo['id'].'"');
	  $this->view->creditdata = $this->credithistory; 
	  
	  
	  $this->payment_history = new User_Model_DbTable_Phistory();
	  $this->paymenthistory = $this->payment_history->fetchAll('user_id="'.$this->sessCompanyInfo['id'].'"');
	  $this->view->paymentdata = $this->paymenthistory;
	  
	 
	  $UserInfo=$this->modelCompanies->fetchRow('id='.$this->sessCompanyInfo['id']);
	  $this->view->userdetails = $UserInfo; 
	  
 
	  
	  
		$select = $this->payment_history->select()
						 ->from('pclive_payment_history', new Zend_Db_Expr('SUM(amount)'))
						 ->where('user_id =' . $this->sessCompanyInfo['id']);

		$this->timequery = $this->payment_history->fetchAll($select);
		
		
		$select_remaining = $this->credit_history->select()
						 ->from('pclive_credit_history', new Zend_Db_Expr('SUM(price)'))
						 ->where('userid =' . $this->sessCompanyInfo['id']);

		$this->timequery_remaining = $this->credit_history->fetchAll($select_remaining);
		
	  
		$this->view->totalcredits = $this->timequery[0]['SUM(amount)'];
		
		$this->view->remainingcredits = $this->timequery[0]['SUM(amount)'] - $this->timequery_remaining[0]['SUM(price)'];
	 
	}
	
	 
	
}

