<?php
class Company_TransactionhistoryController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessCompanyInfo=null;	
	var $modelPublisher=null;
	var $modelCompany=null;
	var $modelGroup=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');		
		$this->modelCompany = new Company_Model_DbTable_Companies();
		$this->modelGroup = new Company_Model_DbTable_Groups();		
		$this->modelPublisher = new Publisher_Model_DbTable_Publishers();
		$creditHistoryObj = new User_Model_DbTable_Chistory();	
		$transactionHistoryObj = new User_Model_DbTable_Transactionhistory();
						
		$storage = new Zend_Auth_Storage_Session('company_type');
        $data = $storage->read();
		
        if($data && $data!=null)
		{
            $this->sessCompanyInfo=$this->modelCompany->getInfoByCompanyId($data->id);
			$this->view->sessCompanyInfo =$this->sessCompanyInfo;
        }
	}
		
	public function indexAction()
	{
		$creditHistoryObj = new User_Model_DbTable_Chistory();
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		if($this->sessCompanyInfo->parent_id!=0)
		{
			$this->_redirect('company/access/index/tab_ajax/transaction/');
		}	
		//echo "kkk".$this->sessCompanyInfo->id;
		$sql = "select * from pclive_credit_history where userid='".$this->sessCompanyInfo->id."' group by order_id order by credit_id desc";
		$MemberList = $creditHistoryObj->getAdapter()->fetchAll($sql);	
	    //$MemberList = $transactionHistoryObj->getOrderHistory($this->sessCompanyInfo->id);		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($MemberList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		//$paginator->setItemCountPerPage(2);
	    
		$paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($MemberList);
		$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		//$this->view->pageSize=2;
		
		$this->view->page=$page;
		$this->view->MemberList=$paginator;
		$this->view->modelGroup=$this->modelGroup;
		$sessionMsg = new Zend_Session_Namespace('step1Msg');
		if(isset($sessionMsg) && !empty($sessionMsg))
		{
			$this->view->formData=$sessionMsg->formData;
			$this->view->formErrors=$sessionMsg->formErrors;
			$this->view->errorMessage=$sessionMsg->errorMessage;
			
			Zend_Session::namespaceUnset('step1Msg');
		}
	}	
	public function viewAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$creditHistoryObj = new User_Model_DbTable_Chistory();
		$order_id =$this->_request->getParam('orderid',0);
		$sql = "select cr.*,tch.* from pclive_credit_history as cr,pclive_transaction_history as tch where cr.order_id=tch.orderId and cr.order_id='".$order_id."' order by credit_id desc";
		$MemberList = $creditHistoryObj->getAdapter()->fetchAll($sql);
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($MemberList);
	    $paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		$this->view->page=$page;
		$this->view->MemberList=$paginator;
		$this->view->oderId = $order_id;
		
	}	
	function printtransactionAction()
	{
		$this->_helper->layout->disableLayout();
		$trans_id = $this->_request->getParam('transid');
		$modelBooks = new Publisher_Model_DbTable_Books();
		$sql='SELECT * from  pclive_products as p inner join pclive_credit_history as pc on p.id=pc.bookid where pc.transaction_id="'.$trans_id.'" order by credit_id desc';		
		$order_list = $modelBooks->getAdapter()->fetchAll($sql);
		$trans_history_data = $modelBooks->getAllProductTransactionById($trans_id);
		$this->view->trans_history_data =$trans_history_data;
		$this->view->trans_id =$trans_id;
		$this->view->order_list =$order_list;
	}
	function viewxmlAction()
	{
		$this->_helper->layout->disableLayout();
		$trans_id = $this->_request->getParam('transid');
		$modelBooks = new Publisher_Model_DbTable_Books();		
		$trans_history_data = $modelBooks->getAllProductTransactionById($trans_id);
		/*if($trans_history_data[0]['transaction_xml']!='')
		{
			echo $trans_history_data[0]['transaction_xml'];		
		}
		else
		{
			echo "No Data";			
		}
		exit;*/
		$this->view->trans_history_data =$trans_history_data;
		$this->view->trans_id =$trans_id;
		
	}
}

