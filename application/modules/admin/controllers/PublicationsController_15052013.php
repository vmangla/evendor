<?php
class Admin_PublicationsController extends Zend_Controller_Action
{
	protected $_flashMessenger=null;
	var $sessAdminInfo=null;
	var $modelAdmiUsers=null;
	var $modelModuleMenus=null;
		
	var $modelPublications=null;
	var $modelBrand=null;
	var $modelGenre=null;
	var $modelIssue=null;
	var $modelEditon=null;
		
    public function init()
    {
		/*creating flashMessanger object to display message*/
		$this->_flashMessenger =$this->_helper->getHelper('FlashMessenger');	
		
		/* check for admin login*/
		$storage = new Zend_Auth_Storage_Session('admin_type');
        $data = $storage->read();
        
		if($data)
		{
		$this->modelAdmiUsers = new Admin_Model_DbTable_AdminUsers();
		$this->sessAdminInfo=$this->modelAdmiUsers->fetchRow("id=".$data->id);
		}
		
		if(!$this->sessAdminInfo)
		{
            $this->_redirect('admin/auth/');
        }
		$this->sessAdminInfo->modules=explode(",",$this->sessAdminInfo->modules);
		$this->view->sessAdminInfo =$this->sessAdminInfo;
		
		$this->modelModuleMenus = new Admin_Model_DbTable_ModulesMenus();
		$this->view->modelModuleMenus = $this->modelModuleMenus->getModuleMenusList();
		
		$this->modelPublications=new Admin_Model_DbTable_Publications();
		$this->modelBrand = new Publisher_Model_DbTable_Brand();
		$this->modelGenre = new Publisher_Model_DbTable_Genres();
		$this->modelIssue = new Publisher_Model_DbTable_Issues();
		$this->modelEditon = new Publisher_Model_DbTable_Edition();
		
		$this->view->modelPublications=$this->modelPublications;
		$this->view->modelGenre=$this->modelGenre;
		$this->view->modelIssue=$this->modelIssue;
		$this->view->modelEditon=$this->modelEditon;
		
		/************* To Set The active section and links *******************/
		$controller=$this->_getParam('controller');
		$action=$this->_getParam('action');
		$this->view->currentController=$controller;
		$this->view->currentAction=$action;
		/************* To Set The active section and links *******************/
		$moduleAccessRow=$this->modelModuleMenus->fetchRow("controller='$controller' AND action LIKE '%".$action."%'");
		
		$currentModuleId=$moduleAccessRow['id'];
		if(!in_array($currentModuleId,$this->sessAdminInfo->modules))
		{
			$this->_flashMessenger->addMessage('<div class="div-error">"'.$moduleAccessRow['menu_name'].'"   module not accessable to you</div>');
			$this->_redirect('admin/');
		}
	}
	
	
    public function indexAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$formData=$this->getRequest()->getPost();
		
						
		if(isset($formData['searchword']) && (trim($formData['searchword'])!="" || trim($formData['booktype'])!="" || trim($formData['searchisbn'])!=""))
		{
		  $bookname=trim($formData['searchword']);
		  $booktype=trim($formData['booktype']);
		  $isbnnumber=trim($formData['searchisbn']);
		  
		  $getBrandIds=$this->modelBrand->getBrandIds($bookname);
		  	  
		  $whrcls="";
		  if($bookname!="")
		  {
		    $whrcls.=" title LIKE '%".$bookname."%'";
			
			if(!empty($getBrandIds))
			{
				$whrcls.=" OR title in(".$getBrandIds.")";
			}
		  }
		  if($booktype!="")
		  {
		    if($bookname!="")
		    {
			$whrcls.=" AND product_type LIKE '%".$booktype."%'";
			}
			else
			{
		    $whrcls.=" product_type LIKE '%".$booktype."%'";
			}
		  }
		  
		  if(!empty($isbnnumber))
		  {
			if($bookname!="")
		    {
				$whrcls.=" AND isbn_number LIKE '%".$isbnnumber."%'";
			}
			elseif($booktype!="")
			{
				$whrcls.=" AND isbn_number LIKE '%".$isbnnumber."%'";
			}
			else
			{
				$whrcls.=" isbn_number LIKE '%".$isbnnumber."%'";
			}
		  }
		    
		  $prodList=$this->modelPublications->getListWhere($whrcls);
		}
		else
		{
		  $prodList=$this->modelPublications->getList();
		}
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($prodList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($prodList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->prodList=$paginator;
		
		$this->view->formData=$formData;
		
	}
	
	public function viewAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		$parent_publication_id=$this->_request->getParam('parentid',0);
		if(!empty($parent_publication_id))
		{
		$parent_publication_info=$this->modelIssue->getParentPublicationInfo($parent_publication_id);
		$parent_brand_info=$this->modelIssue->getParentBrandInfo($parent_publication_info['title']);
		$this->view->ParentPublicationInfo=$parent_publication_info;
		$this->view->ParentBrandInfo=$parent_brand_info;
		}
		
		$formDataValue=array();
		$formDataValue=$this->getRequest()->getPost();
			
		if($id>0 && $this->modelPublications->isExist($id))
		{
		  
			$formData=array();
			
			$productInfo=$this->modelPublications->fetchRow('id='.$id);
			
			$publisherInfo=$this->modelPublications->getPublisherInfo($productInfo['publisher_id']);
			
			if($this->getRequest()->isPost() && (!empty($formDataValue['sendemail']) && $formDataValue['sendemail']=='Send Mail'))
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
									<p class="logo"><a href="'.SVN_URL.'" target="_blank">
									Electronic Vendor Ltd
									</a></p>
									</div>
									
									<p class="title">New Admin Message Email</p>
									<p>You have new message from admin. </p>
									<p>Book :&nbsp;'.$formDataValue['searchword'].'</p>
									<BR />
									<p>&nbsp;</p>
									
									</div>
									<div id="footer">&nbsp;</div>
									</div>
									</body>
									</html>';
									
									/*echo $publisherInfo['emailid'];
									exit;*/
									$mail = new Zend_Mail();
									$mail->addTo($publisherInfo['emailid']);
									$mail->setSubject("New Admin Message Email");
									$mail->setBodyHtml($message);
									$mail->setFrom(SETFROM, SETNAME);
									
									if($mail->send())
									{
									echo "<script>alert('Message send successfully.');</script>";
									//$this->_flashMessenger->addMessage('<div class="div-success">Message send successfully</div>');
									} 
									else 
									{	
									echo "<script>alert('Mail could not be sent. Try again later.');</script>";
									//$this->view->errorMessage='<div class="div-error">Mail could not be sent. Try again later.</div>';
									}
		    }
			
			$formData=$productInfo->toArray();	
			$this->view->formData=$formData;
		}
    }
	
		
	/*
	public function editAction()
    {			
 		$this->view->messages = $this->_flashMessenger->getMessages();
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublications->isExist($id))
		{
			$formData=array();
			$formErrors=array();
			
			$userInfo=$this->modelPublications->fetchRow('id='.$id);
			$formData=$userInfo->toArray();
			//print_r($formData);exit;
			
			if($this->getRequest()->isPost())
			{
				$formData=$this->getRequest()->getPost();
				
				if(trim($formData['category_name'])=="")
				{
					$formErrors['category_name']="Please enter category name";
				}
				if(count($formErrors)==0)
				{
					$formData['updated_date']=date('Y-m-d H:i:s');
					$result=$this->modelPublications->update($formData,'id='.$id);
					
					$this->_flashMessenger->addMessage('<div class="div-success">Category updated successfully</div>');
					$this->_redirect('admin/categories/');
				}
				else
				{
					$this->view->errorMessage='<div class="div-error">Please enter required field properly.</div>';
				}
			}
			$this->view->formData=$formData;
			$this->view->formErrors=$formErrors;
			$this->view->parentCategoryList=$this->modelPublications->getParentList();
		}
		else
		{
			$this->_redirect('admin/publications/');
		}
    }
	*/
	
	public function issuesAction()
    {
 		$this->view->messages = $this->_flashMessenger->getMessages();
		
		$parent_publication_id=$this->_request->getParam('parentid',0);
		$parent_publication_info=$this->modelIssue->getParentPublicationInfo($parent_publication_id);
		$parent_brand_info=$this->modelIssue->getParentBrandInfo($parent_publication_info['title']);
		//print_r($parent_brand_info);
				
		$formData=$this->getRequest()->getPost();
						
		if(isset($formData['searchword']) && (trim($formData['searchword'])!="" || trim($formData['edition'])!="" || trim($formData['status'])!=""))
		{
		  $issuname=trim($formData['searchword']);
		  $edition=trim($formData['edition']);
		  $status=trim($formData['status']);
		  
		  $whrcls="";
		  if($issuname!="")
		  {
		    $whrcls.=" title LIKE '%".$issuname."%'";
		  }
		  if($edition!="")
		  {
		    if($issuname!="")
		    {
			$whrcls.=" AND edition_id LIKE '%".$edition."%'";
			}
			else
			{
		    $whrcls.=" edition_id LIKE '%".$edition."%'";
			}
		  }
		  
		  if($status!="")
		  {
			if($issuname!="")
		    {
				$whrcls.=" AND admin_approve='".$status."'";
			}
			elseif($edition!="")
			{
				$whrcls.=" AND admin_approve='".$status."'";
			}
			else
			{
				$whrcls.=" admin_approve='".$status."'";
			}
		  }
		    
		  $issueList=$this->modelIssue->getListWhere($whrcls." AND parent_brand_id='$parent_publication_id'");
		}
		else
		{
		  $issueList=$this->modelIssue->getList(0,$parent_publication_id);
		}
		//$issueList=$this->modelIssue->getList(0,$parent_publication_id);
		
		$page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($issueList);
	    $paginator->setItemCountPerPage(ADMIN_PAGING_SIZE);
	    $paginator->setCurrentPageNumber($page);
		
		$this->view->totalCount=count($issueList);
		$this->view->pageSize=ADMIN_PAGING_SIZE;
		$this->view->page=$page;
		$this->view->issueList=$paginator;
		
		$this->view->ParentPublicationInfo=$parent_publication_info;
		$this->view->ParentBrandInfo=$parent_brand_info;
		$this->view->formData=$formData;
	}
		
	
	public function deleteAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublications->isExist($id))
		{
			$success=$this->modelPublications->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Product deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete product</div>');
			}
		}
		$this->_redirect('admin/publications/');
    }
	
	
	public function inactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublications->isExist($id))
		{
		
		    $data['admin_approve']=0;
		    $success=$this->modelPublications->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Product deactivated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to deactivate product</div>');
			}
		}
		$this->_redirect('admin/publications/');
    }
	
	
	public function activeAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublications->isExist($id))
		{
		
		    $data['admin_approve']=1;
		    $success=$this->modelPublications->update($data,'id="'.$id.'"');
			
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Product activated successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to activate product</div>');
			}
		}
		$this->_redirect('admin/publications/');
    }
	
	public function deleteimageAction()
    {
	
		$id=$this->_request->getParam('id',0);
		
		$modelImage = new Admin_Model_DbTable_ProductImages();
		$imageDetail=$modelImage->getImageInfo($id);
		
		unlink(USER_UPLOAD_DIR.$imageDetail['image_name']);
		unlink(USER_UPLOAD_DIR.$imageDetail['image_name_thumb']);
	
		if($id>0 && $modelImage->isExist($id))
		{
			$success=$modelImage->delete('id='.$id);
			if($success)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Image deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete image</div>');
			}
			
		}
	
		$this->_redirect('admin/publications/');
			
	}
	
	
	public function deletepriceAction()
    {
		$id=$this->_request->getParam('id',0);
		
		$modelPrice = new Admin_Model_DbTable_ProductPrices();
		
		if($id>0 && $modelPrice->isExist($id))
		{
			$success=$modelPrice->delete('id='.$id);
			
			if($success)
			{	
				$this->_flashMessenger->addMessage('<div class="div-success">Price deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete price</div>');
			}
		}
		
		$this->_redirect('admin/publications/');
		
	}
	
	public function deleteepubfileAction()
    {
	
		$id=$this->_request->getParam('id',0);
	
		$bookDetail=$this->modelPublications->getInfoByPublisherId($id);
		
		unlink(EPUB_UPLOAD_DIR.$bookDetail['file_name']);
	
		if($id>0 && $this->modelPublications->isExist($id))
		{
		   
		    $Filedata=array('file_name'=>'',
						    'file_size'=>'');
		
			$result=$this->modelPublications->update($Filedata,'id='.$id);  
			
			if($result)
			{
				$this->_flashMessenger->addMessage('<div class="div-success">Epub file deleted successfully</div>');
			}
			else
			{
				$this->_flashMessenger->addMessage('<div class="div-error">Sorry!, unable to delete epub file</div>');
			}
			
		}
	
		$this->_redirect('admin/publications/');
			
	}
	
	public function downloadpubfileAction()
    {
		    $id=$this->_request->getParam('id',0);
			
			$bookDetail=$this->modelPublications->getInfoByPublisherId($id);
			
			if($id>0 && $this->modelPublications->isExist($id))
			{
			  
					$filename = EPUB_UPLOAD_DIR.$bookDetail['file_name']; 
					
					header('Pragma: public');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Cache-Control: private', false); 
					header('Content-Type: application/epub');
					header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
					header('Content-Transfer-Encoding: binary');
					header('Content-Length: ' . filesize($filename));
					readfile($filename);
					exit;
			
			}
				
	}
	
	
	public function featuredinactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublications->isExist($id))
		{
		
		    $data['is_featured']=0;
		    $success=$this->modelPublications->update($data,'id="'.$id.'"');
		}
		$this->_redirect('admin/publications/');
    }
	
	
	public function featuredactiveAction()
    {
 		
		$id=$this->_request->getParam('id',0);
		
		if($id>0 && $this->modelPublications->isExist($id))
		{
		
		    $data['is_featured']=1;
		    $success=$this->modelPublications->update($data,'id="'.$id.'"');
		}
		$this->_redirect('admin/publications/');
    }
	
}

