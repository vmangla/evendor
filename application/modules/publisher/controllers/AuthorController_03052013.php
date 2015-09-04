<?php
class Publisher_AuthorController extends Zend_Controller_Action
{

	protected $_flashMessenger=null;
	var $sessPublisherInfo=null;
	
	var $modelPublisher=null;
	var $modelAuthor=null;
	var $parentPublisherId=null;
	
    public function init()
    {
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');
		
		$storage = new Zend_Auth_Storage_Session('publisher_type');
        $data = $storage->read();
		
		if($data && $data!=null)
		{
            $this->modelPublisher = new Publisher_Model_DbTable_Publishers();
			$this->modelAuthor = new Publisher_Model_DbTable_Author();
					
			$this->sessPublisherInfo=$this->modelPublisher->getInfoByPublisherId($data->id);
			$this->view->sessPublisherInfo =$this->sessPublisherInfo;
			
			$this->parentPublisherId = !empty($this->sessPublisherInfo->parent_id)?$this->sessPublisherInfo->parent_id:$this->sessPublisherInfo->id;
			
			$tab_ajax=$this->_request->getParam('tab_ajax',0);
			if(empty($tab_ajax))
			{
				$this->_redirect('publisher/');
			}
						
			if($data->user_type!='publisher')
			{
			$this->_redirect('publisher/access/index/tab_ajax/author/');
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
		
		$authorList=$this->modelAuthor->getAuthorList($this->parentPublisherId);
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($authorList);
	    //$paginator->setItemCountPerPage(PUBLISHER_PAGING_SIZE);
		$paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    
		$paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($authorList);
		//$this->view->pageSize=PUBLISHER_PAGING_SIZE;
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		
		$this->view->page=$page;
		$this->view->authorList=$paginator;
	}
	
	public function addAction()
	{
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		 
		$formData=array();
		$formErrors=array();
		
		$formData =$this->getRequest()->getPost();
		
        if($this->getRequest()->isPost() && isset($formData['add_author']) && $formData['add_author']=='Add Author')
		{
		  
			$formData =$this->getRequest()->getPost();
			
			if(!(isset($formData['first_name'])) || trim($formData['first_name'])=="")
			{
				$formErrors['first_name']="Please enter first name";
			}
			if(!(isset($formData['last_name'])) || trim($formData['last_name'])=="")
			{
				$formErrors['last_name']="Please enter last name";
			}
			/*if(!(isset($formData['emailid'])) || trim($formData['emailid'])=="")
			{
				$formErrors['emailid']="Please enter email";
			}*/
			
			/*if(!(isset($formData['password'])) || trim($formData['password'])=="")
			{
				$formErrors['password']="Please enter password";
			}
			*/
				
			/*if(!(CommonFunctions::isValidEmail($formData['emailid'])))
			{
				if(!(array_key_exists('emailid',$formErrors)))
				{
					$formErrors['emailid']="Please enter valid email";
				}	
			}*/
			
			/*if($this->modelAuthor->isExist('username="'.$formData['username'].'"'))
			{
				if(!(array_key_exists('username',$formErrors)))
				{
					$formErrors['username']="Username already exist";
				}
			}
			*/
			if($formData['emailid']!='')
			{
				if($this->modelAuthor->isExist('emailid="'.$formData['emailid'].'"'))
				{
					if(!(array_key_exists('emailid',$formErrors)))
					{
						$formErrors['emailid']="Email already exist";
					}
				}
			}
			if(count($formErrors)==0)
			{
			  if($this->getRequest()->isPost())
				{
					$activationCode=CommonFunctions::generateGUID();
					$add_time=date('Y-m-d H:i:s');
					$formData['password']=CommonFunctions::getRandomNumberPassword(8);
					$formData['parent_publisher_id']=(!empty($this->sessPublisherInfo->id))?$this->sessPublisherInfo->id:0;
					$username_array=explode("@",$formData['emailid']);
					$formData['username']=$username_array[0];
					
									
					$authorData=array(
					                'user_type'=>'author',
									'parent_id'=>$formData['parent_publisher_id'],
									'username'=>$formData['username'],
									'emailid'=>$formData['emailid'],
									'password'=>$formData['password'],
									'first_name'=>$formData['first_name'],
									'last_name'=>$formData['last_name'],
									'phone'=>$formData['phone'],
									'profile_status'=>'1',
									'updated_date'=>date("Y-m-d H:i:s"),
									'add_time'=>$add_time
									);
						
					print_r($authorData);
						
						$lastId=$this->modelAuthor->insert($authorData);
					 
						if($lastId>0)
						{
						
						    $mailhost= SMTP_SERVER; 
							
							$mailconfig = array(
							'ssl' => SMTP_SSL, 
							'port' => SMTP_PORT, 
							'auth' => SMTP_AUTH, 
							'username' => SMTP_USERNAME, 
							'password' => SMTP_PASSWORD);
							
							$transport = new Zend_Mail_Transport_Smtp ($mailhost, $mailconfig);
							Zend_Mail::setDefaultTransport($transport);
							
							
							$message ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
							<html xmlns="http://www.w3.org/1999/xhtml">
							<head>
							<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
							<title>Electronic Vendor Ltd</title>
							<style type="text/css">
							body{
							margin:0;
							padding:0px;
							}
							#container{
							width:700px;
							margin:0 auto;
							}
							#header{
							width:700px;
							float:left;
							padding:40px 0 10px 0;
							font-family:Arial, Helvetica, sans-serif;
							color:#3A3B3F;
							text-align:center;
							font-size:11px;
							
							}
							#header a{
							color:#3A3B3F;
							font-weight:bold;
							text-decoration:none;
							}
							#header a:hover{
							color:#40BBE3;
							}
							#logopart
							{
							border:0px solid red;
							width:698px;
							height:140px;
							background-color:#1B75BB;
							margin-left:0px;
							}
							#content{
							width:698px;
							float:left;
							padding:0px 0px 10px 0px;
							font-family:Arial, Helvetica, sans-serif;
							color:#3A3B3F;
							border:1px solid #D6D6D6;
							font-size:12px;
							}
							#content p{
							margin:0px 20px;
							padding:0px 0 20px 0;
							font-family:Arial, Helvetica, sans-serif;
							font-size:12px;
							color:#3A3B3F;
							}
							#content p.logo{
							margin:0px;
							padding:15px 0 0 20px;
							height:77px
							}
							#content p.title{
							margin:0px;
							font-size:20px;
							font-family:Arial, Helvetica, sans-serif;
							border-bottom:3px solid #D6D6D6;
							padding:0px 0 13px 0;
							margin:25px 20px 14px 20px;
							color:#3A3B3F;
							}
							#content p a{
							color:#40BBE3;
							text-decoration:none;
							}
							#content p a:hover{
							color:#3A3B3F;
							text-decoration:underline;
							}
							#content h2{
							margin:0px;
							padding:0 0 14px 0;
							font-size:14px;
							font-family:Arial, Helvetica, sans-serif;
							font-weight:bold;
							}
							#footer{
							width:700px;
							float:left;
							}
							#footer p{
							margin:0 0 0 0;
							padding:0 0 0 0;
							font-family:Arial, Helvetica, sans-serif;
							font-size:11px;
							color:#78797E;
							}
							#footer p.disclamer{
							margin: 0 0 0 0;
							padding:16px 6px 10px 6px;
							text-align: justify;
							border-bottom:1px solid #3A3B3F;
							color:#78797E;
							}
							#footer p.notice{
							margin: 0 0 15px 0;
							padding:16px 6px 10px 6px;
							text-align: justify;
							color:#78797E;
							}
							</style>
							</head>
							<body>
							
							<div id="container">
							<div id="header"></div>
							<div id="content">
							
							<div id="logopart">
							<p class="logo"><a href="'.$this->view->serverUrl().$this->view->baseUrl().'" target="_blank">
							Electronic Vendor Ltd
							</a></p>
							</div>
							
							<p class="title">Author Registration Email</p>
							<p>Your login details given below :</p>
							<p>Username:&nbsp;'.$formData['username'].'</p>
							<p>Password:&nbsp;'.$formData['password'].'</p>
							<BR />
							<p>&nbsp;</p>
							
							</div>
							<div id="footer">&nbsp;</div>
							</div>
							</body>
							</html>';
														
							
							$mail = new Zend_Mail();
							$mail->addTo($formData['emailid']);
							$mail->setSubject("Author Registration Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
							$this->_flashMessenger->addMessage('<div class="div-success">Author added successfully</div>');
							} 
							else 
							{	
							$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
							}
							$this->_redirect('publisher/author/index/tab_ajax/author/');		
						}
				}		
			}
			else
			{
				 $this->view->errorMessage = '<div class="div-error">Please enter required fields to register.</div>';
			}      
        }
		else
		{
			$formData['user_type']="";
			$formData['first_name']="";
			$formData['last_name']="";
			$formData['emailid']="";
			$formData['password']="";
		}
		$this->view->formData=$formData;
		$this->view->formErrors=$formErrors;
	}
	



	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAuthor->isExist($id))
		{
			$success=$this->modelAuthor->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Author deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete author</div>');
			}
			
		}
		
		$this->_redirect('publisher/author/index/tab_ajax/author/');
    }
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAuthor->isExist($id))
		{
		
		    $data['profile_status']=0;
		    $success=$this->modelAuthor->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Author deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate author</div>');
			}
		}
		$this->_redirect('publisher/author/index/tab_ajax/author/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelAuthor->isExist($id))
		{
		
		    $data['profile_status']=1;
		    $success=$this->modelAuthor->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Author activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate author</div>');
			}
		}
		$this->_redirect('publisher/author/index/tab_ajax/author/');
    }
	
	
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		$id=$this->_request->getParam('id',0);
				
		if($id>0 && $this->modelAuthor->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelAuthor->fetchRow('id='.$id);
			$formData=$userInfo->toArray();		
			if($this->getRequest()->isPost() && isset($_REQUEST['edit_author']) && $_REQUEST['edit_author']=='Edit Author')
			{
				$formData=$this->getRequest()->getPost();
				//print_r($formData);exit;
				if(count($formErrors)==0)
				{
					$username_array=explode("@",$formData['emailid']);
					$formData['username']=$username_array[0];
									
					$authorData=array(
					                'username'=>$formData['username'],
									'emailid'=>$formData['emailid'],
									'password'=>$formData['password'],
									'first_name'=>$formData['first_name'],
									'last_name'=>$formData['last_name'],
									'phone'=>$formData['phone'],
									'profile_status'=>'1',
									'updated_date'=>date("Y-m-d H:i:s")
								);
					
					$result=$this->modelAuthor->update($authorData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Author updated successfully</div>');
					$this->_redirect('publisher/author/index/tab_ajax/author/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('publisher/author/index/tab_ajax/author/');
		}
    }
	
	public function viewAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->_helper->layout->disableLayout();
		
		$id=$this->_request->getParam('id',0);
		$tab_ajax=$this->_request->getParam('tab_ajax');
		
		if($id>0 && $this->modelAuthor->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelAuthor->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
				
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
		}
		else
		{
			$this->_redirect('publisher/author/index/tab_ajax/author/');
		}
    }
	
}

