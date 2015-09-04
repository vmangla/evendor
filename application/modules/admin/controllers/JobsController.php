<?php
class Admin_JobsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	
	var $modelJobs=null;
	
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
        if(!$data)
		{
            $this->_redirect('admin/auth/');
        }
        $this->view->user_name = $data->first_name.' '.$data->last_name; 

		//===creating model object======	
		//$this->modelJobs=new Admin_Model_DbTable_Jobs();
		//==============================
    }
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
    }
	public function postedAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
    }
	public function appliedAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
    }
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelJobs->isExist($id))
		{
			$success=$this->modelJobs->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Job deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete job</div>');
			}
		}
		$this->_redirect('admin/jobs/');
    }
}

