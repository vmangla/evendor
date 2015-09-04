<?php
class Api_IndexController extends Zend_Rest_Controller
{
	var $modelApiKey=null;

	public function init()
    {
        $this->_helper->viewRenderer->setNoRender(true);
	}

    public function indexAction()
    {
         $this->getResponse()
            ->appendBody("From indexAction() returning all articles");
    }

    public function getAction()
    {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$apiparameters=$this->view->apiparams();
		//print_r($apiparameters);
		
		if(!array_key_exists('apicall',$apiparameters))
		{
			echo"{'error':'No method was called!'}";
		}
		elseif(!empty($apiparameters['apicall']) && $apiparameters['apicall']=='UserLogin')
		{
			$request='['.json_encode($apiparameters).']';
					
			$url = $this->view->serverUrl().$this->view->baseUrl().'/api/';
			//$url ='http://localhost/projects/evendor/api';
			$params = array('jsondata'=>$request); // key value pairs
			$response = CommonFunctions::processRequest($url, $params);
			
			//echo $response;
			print_r($response);
			//print_r(json_decode($response));
			 
			//exit;	
		}
		else
		{
			if(!array_key_exists('apikey',$apiparameters))
			{
				echo"{'error':'Api Key missing!'}";
			}
			else
			{
				$db = Zend_Registry::get('db');
				$sql = 'SELECT * FROM pclive_apikeys where apikey="'.$apiparameters['apikey'].'"';
				$result = $db->query($sql);
				$record = $result->FetchAll();		
				if(count($record)==1)
				{
					$request='['.json_encode($apiparameters).']';
					
					$url = $this->view->serverUrl().$this->view->baseUrl().'/api/';
					//$url ='http://localhost/projects/evendor/api';
					$params = array('jsondata'=>$request); // key value pairs
					$response = CommonFunctions::processRequest($url, $params);
					
					//$this->getResponse()->appendBody("From getAction() returning the requested article");
					
					//echo $response;
					 
					print_r($response);
					//print_r(json_decode($response));
					 
					//exit;
					
				}
				else
				{
					echo"{'apikey_error':'Invalide Apikey'}";
					//exit;
				}
			}
		}	
	}
    
    public function postAction()
    {
	   // Database Object
	    $db = Zend_Registry::get('db');
		
		$formData=$this->getRequest()->getPost();
		//print_r($formData);			
		//exit;
		
		if(is_array($formData))
		{
			if(get_magic_quotes_gpc())
			{
				$json_data = stripslashes($formData['jsondata']);
			}
			else
			{
				$json_data = $formData['jsondata'];
			}
			
			/********** Json Object Array ************************/
			$jsonObj = json_decode($json_data);
			$jsonObj = $jsonObj[0];
			/********** Json Object Array ************************/
			
			/********** Json Array ********************/
			//$jsonArray = json_decode($json_data,true);
			/********** Json Array *******************/
			
			
			
           	switch($jsonObj->apicall)
			{
			
				case "UserRegistration":
				
					$Errorresponse="";
					if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="" || !(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="" || !(isset($jsonObj->EmailId)) || trim($jsonObj->EmailId)=="" || !(isset($jsonObj->Password)) || trim($jsonObj->Password)=="")
					{
						$Errorresponse='[{"ParameterMissing":{';
						if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="") 
						{
							$Errorresponse.='"FirstName":"Please Enter First Name",';
						}
						
						if(!(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="") 
						{
							$Errorresponse.='"LastName":"Please Enter Last Name",';
						}
						
						if(!(isset($jsonObj->EmailId)) || trim($jsonObj->EmailId)=="") 
						{
							$Errorresponse.='"EmailId":"Please Enter Email Id",';
						}
						
						if(!(isset($jsonObj->Password)) || trim($jsonObj->Password)=="") 
						{
							$Errorresponse.='"Password":"Please Enter Password",';
						}
						
						if(!(isset($jsonObj->Country)) || trim($jsonObj->Country)=="") 
						{
							$Errorresponse.='"Country":"Please Select A Country"';
						}
																
						$Errorresponse.='}}]';
					}	
					
					
					if(!empty($Errorresponse)) 
					{
					 	    //echo $Errorresponse;
						        $response = '{
								"Message":"Unsuccess",
								"error":"Parameter Missing"
								}';
								
								echo $response;
					} 
					else 
					{
					    	$sql = 'SELECT * FROM pclive_companies where user_email="'.$jsonObj->EmailId.'"';
							$result = $db->query($sql);
							$record = $result->FetchAll();	
							
							
							$sql1 = 'SELECT * FROM pclive_users where emailid="'.$jsonObj->EmailId.'"';
							$result1 = $db->query($sql1);
							$record1 = $result1->FetchAll();		
							
							if(count($record)>0 || count($record1)>0)
							{
							   
								$response = '{
								"Message":"Unsuccess",
								"error":"Email Id already exists"
								}';
										
										
							   echo $response;
							}
							else
							{
								$user_name=explode("@",$jsonObj->EmailId);
								$user_name=$user_name[0];
								
								$sql = "INSERT INTO pclive_companies (parent_id,group_id,first_name,last_name,account_type,user_name,user_email,user_password,country,status,added_date,updated_date)VALUES('0','0','".$jsonObj->FirstName."','".$jsonObj->LastName."','2','$user_name','".$jsonObj->EmailId."','".md5($jsonObj->Password)."','".$jsonObj->Country."','1', NOW(),NOW())";
															
								$result = $db->query($sql);
								
								if($result)
								{
									$apikeysql = 'SELECT * FROM pclive_apikeys ORDER BY RAND() LIMIT 0,1';
									$apikeyresult = $db->query($apikeysql);
									$apikeyrecord = $apikeyresult->FetchAll();
									
									if(count($apikeyrecord)==1)
						            {
										$response = '{
										"Message":"Success",
										"error":"false"
										}';
										
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
							
							</head>
							<body>
							
							<p>Your login details given below :<br></p>
							<p>Username:&nbsp;'.$jsonObj->EmailId.'</p>
							<p>Password:&nbsp;'.$jsonObj->Password.'</p>
							<BR />
							<br>
							<p>&nbsp;</p>
							</body>
							</html>';
														
							
							$mail = new Zend_Mail();
							$mail->addTo($jsonObj->EmailId);
							$mail->setSubject("Registration Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
								//$response ='{"Message":"Success", "Error":"False" }';
								//echo $response;	 
							} 
												
						}
									 echo $response;
									 
								}
								else
								{ 
										$response = '{
										"Message":"Unsuccess",
										"error":"Registration Failed"
										}';
							
									  echo $response;
								}
							
							}   // Check Email Exists
					}
				break;
				
				case "UserLogin":
				    if(isset($jsonObj->EmailId) && !empty($jsonObj->EmailId) && isset($jsonObj->Password)) 
					{
						$sql = 'SELECT * FROM pclive_companies where user_email="'.$jsonObj->EmailId.'" AND user_password="'.md5($jsonObj->Password).'" AND   status=1';
					 
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						  $apikeysql = 'SELECT * FROM pclive_apikeys ORDER BY RAND() LIMIT 0,1';
						  $apikeyresult = $db->query($apikeysql);
						  $apikeyrecord = $apikeyresult->FetchAll();
						   
						  if(count($apikeyrecord)==1)
						  {
							/*$response = '[{"LoginResponse":{
							"Message":"Success",
							"apikey":"'.$apikeyrecord[0]['apikey'].'"
							}}]';*/
							  
						$sql1 = "SELECT * FROM pclive_country where id='".$record[0]['country']."' ";
						$result1 = $db->query($sql1);
						$record1 = $result1->FetchAll();	
							
							$response = '{
							"Message":"Success",
							"apikey":"'.$apikeyrecord[0]['apikey'].'",
							"userid":"'.$record[0]['id'].'",
							"countryid":"'.$record[0]['country'].'",
							"Country" : "'.$record1[0]['country'].'", 
							"FirstName" : "'.$record[0]['first_name'].'",
							"LastName"	: "'.$record[0]['last_name'].'","Username":"'.$record[0]['user_email'].'","Userpassword":"'.$record[0]['user_password'].'","error":"false"
							}';
							
						
							
						  }
						  echo $response;
						}
						else
						{
						   
						 /* $response = '[{"LoginResponse":{
						  "Error":"Invalid User Id or Password"
						  }}]';*/
						  
						    $response = '{
							"Message":"Invalid username or password.",
							"apikey":"",
							"error":"true"
							}';
							
						   echo $response; 	
						}
					} 
					else 
					{
					  /*$response = '[{"LoginResponse":{
					  "Error":"User Id does not exist"
					  }}]';*/
					  
					  $response = '{
							"Message":"User Id does not exist.",
							"apikey":"",
							"error":"true"
							}';
					  
					  echo $response;   
					} 
					    
				break;
				
				
				
				
				
				case "UserLoginIphone":
				    if(isset($jsonObj->EmailId) && !empty($jsonObj->EmailId) && isset($jsonObj->Password)) 
					{
						$sql = 'SELECT * FROM pclive_companies where user_email="'.$jsonObj->EmailId.'" AND user_password="'.md5($jsonObj->Password).'" AND parent_id="0" and status=1 AND account_type<>"3"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						  $apikeysql = 'SELECT * FROM pclive_apikeys ORDER BY RAND() LIMIT 0,1';
						  $apikeyresult = $db->query($apikeysql);
						  $apikeyrecord = $apikeyresult->FetchAll();
						   
						  if(count($apikeyrecord)==1)
						  {
							/*$response = '[{"LoginResponse":{
							"Message":"Success",
							"apikey":"'.$apikeyrecord[0]['apikey'].'"
							}}]';*/
							
							$response = '{
							"Message":"Success",
							"apikey":"'.$apikeyrecord[0]['apikey'].'",
							"userid":"'.$record[0]['id'].'",
							"countryid":"'.$record[0]['country'].'","Username":"'.$record[0]['user_email'].'","Userpassword":"'.$record[0]['user_password'].'","error":"false"
							}';
						 	
						  }
						  echo $response;
						}
						else
						{
						   
						 /* $response = '[{"LoginResponse":{
						  "Error":"Invalid User Id or Password"
						  }}]';*/
						  
						    $response = '{
							"Message":"Unsuccess",
							"apikey":"",
							"error":"Invalid User Id or Password"
							}';
							
						   echo $response; 	
						}
					} 
					else 
					{
					  /*$response = '[{"LoginResponse":{
					  "Error":"User Id does not exist"
					  }}]';*/
					  
					  $response = '{
							"Message":"Unsuccess",
							"apikey":"",
							"error":"User Id does not exist"
							}';
					  
					  echo $response;  
					} 
					    
				break;
				
				
				
				
				case "UserDetail":
				    if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{
						$sql = 'SELECT * FROM pclive_companies where id="'.$jsonObj->id.'" AND parent_id="0" AND account_type<>"3"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						   if($record[0]['account_type']==1)
						   {
								$record[0]['account_type']="Company";
						   }
						   elseif($record[0]['account_type']==2)
						   {
								$record[0]['account_type']="Individual";							
						   }
						   else
						   {
								$record[0]['account_type']=$record[0]['account_type'];
						   }
						  
						  
						$sql1 = "SELECT * FROM pclive_country where id='".$record[0]['country']."' ";
						$result1 = $db->query($sql1);
						$record1 = $result1->FetchAll();	
						   
							$response = '[{"UserDetail":{
							"UserType" 	: "'.$record[0]['account_type'].'",
							"UserEmail"	: "'.$record[0]['user_email'].'",
							"FirstName" : "'.$record[0]['first_name'].'",
							"LastName"	: "'.$record[0]['last_name'].'",
							"Countryid" : "'.$record[0]['country'].'", 
							"Countryname" : "'.$record1[0]['country'].'", 
							"JoinTime"	: "'.$record[0]['added_date'].'"
							}}]';
							
							echo $response;
						}
						else
						{
						   
						  $response = '[{"Error":{"Message":"Invalid User Id."}}]';
						   echo $response; 	
						   
						}
					} 
					else 
					{
					  $response = '[{"Error":{"Message":"User Id does not exist"}}]';
						echo $response;  
					} 
					    
				break;
				
				
				
				case "UserDetailIphone":
				    if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{
						$sql = 'SELECT * FROM pclive_companies where id="'.$jsonObj->id.'" AND parent_id="0" AND account_type<>"3"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						   if($record[0]['account_type']==1)
						   {
								$record[0]['account_type']="Company";
						   }
						   elseif($record[0]['account_type']==2)
						   {
								$record[0]['account_type']="Individual";							
						   }
						   else
						   {
								$record[0]['account_type']=$record[0]['account_type'];
						   }
						  
						  
						$sql1 = "SELECT * FROM pclive_country where id='".$record[0]['country']."' ";
						$result1 = $db->query($sql1);
						$record1 = $result1->FetchAll();	
						   
							$response = '{"UserDetail":{
							"UserType" 	: "'.$record[0]['account_type'].'",
							"Username"	: "'.$record[0]['user_email'].'",
							"FirstName" : "'.$record[0]['first_name'].'",
							"LastName"	: "'.$record[0]['last_name'].'",
							"countryid" : "'.$record[0]['country'].'", 
							"Country" : "'.$record1[0]['country'].'", 
							"JoinTime"	: "'.$record[0]['added_date'].'"
							 
							},"Message"  : "Success",
							 "error"    : "false" }';
							
							echo $response;
						}
						else
						{
						   
						   $response = '{"Message":"User detail not found","error":"true"}';
						   echo $response; 	
						   
						}
					} 
					else 
					{
					  $response = '{"Message":"User detail not found","error":"true"}';
						echo $response;  
					} 
					    
				break;
				
				
				case "UserSubscriptions":
					 
				    if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{
					$sql="SELECT s.id,s.product_id,s.store_id,s.status,s.added_date,s.updated_date, p.product_type,p.publisher_id,p.author_id,p.title,p.parent_brand_id,p.edition_id,p.description,p.isbn_number,p.publisher,p.total_pages,p.cat_id,p.file_name,p.file_size, pp.country_id,pp.language_id,pp.price FROM pclive_company_subscriptions as s INNER JOIN pclive_products as p ON s.product_id=p.id INNER JOIN pclive_product_prices as pp ON s.product_id=pp.product_id WHERE s.company_id<>0 AND s.company_id='$jsonObj->id' AND s.store_id=pp.country_id ORDER BY s.id ASC";
		
					$result = $db->query($sql);
					$records = $result->FetchAll();		
						
						if(count($records)>0)
						{
							$response=array();
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
								$publisher_info_qry='SELECT first_name,last_name,emailid,publisher,country,phone from  pclive_users where id="'.$Parray['publisher_id'].'"';
								$publisher_info_result = $db->query($publisher_info_qry);
								$publisher_info_record = $publisher_info_result->FetchAll();
								$publisher_info=$publisher_info_record[0];
								
								if(!empty($Parray['parent_brand_id']))
								{
								$parent_brand_id_qry='SELECT title from pclive_products where id="'.$Parray['parent_brand_id'].'"';
								$brand_info_result = $db->query($parent_brand_id_qry);
								$brand_info_record = $brand_info_result->FetchAll();
								$brand_info=$brand_info_record[0];
								
								$parent_brand_title_qry='SELECT brand from pclive_brands where id="'.$brand_info['title'].'"';
								$parent_brand_info_result = $db->query($parent_brand_title_qry);
								$parent_brand_info_record = $parent_brand_info_result->FetchAll();
								$parent_brand_info=$parent_brand_info_record[0];
								}
															
								$store_info_qry='SELECT country from pclive_country where id="'.$Parray['store_id'].'"';
								$store_info_result = $db->query($store_info_qry);
								$store_info_record = $store_info_result->FetchAll();
								$store_info=$store_info_record[0];
								
								$genre_info_qry='SELECT genre from  pclive_genres where id="'.$Parray['product_type'].'"';
								$genre_info_result = $db->query($genre_info_qry);
								$genre_info_record = $genre_info_result->FetchAll();
								$genre_info=$genre_info_record[0];
								
								$cat_info_qry='SELECT category_name from   pclive_categories where id="'.$Parray['cat_id'].'"';
								$cat_info_result = $db->query($cat_info_qry);
								$cat_info_record = $cat_info_result->FetchAll();
								$cat_info=$cat_info_record[0];
								
								$lan_info_qry='SELECT language_name from pclive_product_language where id="'.$Parray['language_id'].'"';
								$lan_info_result = $db->query($lan_info_qry);
								$lan_info_record = $lan_info_result->FetchAll();
								$lan_info=$lan_info_record[0];
																
								if(strtolower(trim($cat_info['category_name']))==strtolower(trim('eBook')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('eBooks')))
								{
								$author_info_qry='SELECT first_name,last_name,emailid,phone from  pclive_users where id="'.$Parray['author_id'].'"';
								$author_info_result = $db->query($author_info_qry);
								$author_info_record = $author_info_result->FetchAll();
								$author_info=$author_info_record[0];
								}
								else
								{
								$edition_info_qry='SELECT edition from pclive_editions where id="'.$Parray['edition_id'].'"';
								$edition_info_result = $db->query($edition_info_qry);
								$edition_info_record = $edition_info_result->FetchAll();
								$edition_info=$edition_info_record[0];
								}
																							
								$response['Bookshelve'][$key]['ProductId']=$Parray['product_id'];
								
								if(!empty($parent_brand_info))
								{
									$title=$Parray['title']."(".ucfirst($parent_brand_info['brand']).")";
								}
								else
								{
									$title=$Parray['title'];
								}
								$response['Bookshelve'][$key]['Title']=$title;
								
								//$thumbnail='<img src="'.$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'].'" height="208" width="166">';
								
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								
								 
								
								$response['Bookshelve'][$key]['ProductThumbnail']=$thumbnail_path;
								//$response['Bookshelve'][$key]['ProductThumbnail']=$thumbnail;
								
								$response['Bookshelve'][$key]['StoreName']=$store_info['country'];
								//$response['Bookshelve'][$key]['StoreId']=$Parray['store_id'];
								$response['Bookshelve'][$key]['Category']=$cat_info['category_name'];
								if(isset($edition_info) && !empty($edition_info))
								{
								$response['Bookshelve'][$key]['Edition']=$edition_info['edition'];
								}
								$response['Bookshelve'][$key]['Genre']=$genre_info['genre'];
								$response['Bookshelve'][$key]['Language']=$lan_info['language_name'];
								$response['Bookshelve'][$key]['Price']=$Parray['price'];
								//$response['Bookshelve'][$key]['TotalDownloads']=$Parray['total_downloads'];
																							
								$response['Bookshelve'][$key]['PublisherInfo']=$publisher_info;
								if(isset($author_info) && !empty($author_info))
								{
									$response['Bookshelve'][$key]['AuthorInfo']=$author_info;
								}
								
								$response['Bookshelve'][$key]['Edition']=$edition_info['edition'];
								$response['Bookshelve'][$key]['AddedDate']=$Parray['added_date'];
								$response['Bookshelve'][$key]['UpdatedDate']=$Parray['updated_date'];
							}
											   
						   /*echo"<pre>";
						   print_r($response);
						   */
						   $response=json_encode($response);
 						   echo stripslashes($response);
 
						}
						else
						{
						   $response = '[{"Error":{"Message":"No Records Found."}}]';
						   echo $response; 	
						}
					} 
					else 
					{
					  $response = '[{"Error":{"Message":"User Id does not exist"}}]';
						echo $response;  
					} 
					    
				break;
				
				case "GetLibrary":
						$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.cat_id ASC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Library']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
								
								
								
							}
											   
						   /*echo"<pre>";
						   print_r($response);
						   */
						    
						   
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;
						
						}
						else
						//$response['Library']="No books available";
						{
						
						$response['Library'][0]['books_status']="No";	
						}
						
						//$response='{"Library":[{"id":"","product_type":"","publisher_id":"","author_id":"","title":"","edition_id":"","description":"","isbn_number":"","publisher":"","total_pages":"","cat_id":"","parent_brand_id":"","file_name":"","file_size":"","status":"","is_featured":"","admin_approve":"","publish_time":"","add_time":"","best_seller":"","category_name":"","genre":"","product_id":"","country_id":"","language_id":"","price":"","ProductThumbnail":"","Producturl":"","publisher_name":"","author_name":""}],"Allcategories":[{"id":"","parent_id":"","genre":"","added_date":"","updated_date":"","status":""}]}';
						 	
							
							/*echo "<pre>";
							print_r($response);
							exit;*/
				  
							//$response = json_encode($response);
							//$response =  str_replace("\\", '',$response);
							
							 
							//$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
							echo $response;
							
							//echo stripslashes($response);
					  
				break;
				
				
				
				
				
				
				case "GetNewarrivalsIphone":
					$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.cat_id ASC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Library']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
								
								
								
							}
				
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;
						
						}
						else
					
						{
						
						$response['Library'][0]['books_status']="No";	
						}
					
							 
							//$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
							//echo $response;
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.id DESC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.id DESC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.id DESC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Newarrivals']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['Newarrivals'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Newarrivals'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Newarrivals'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Newarrivals'][$key]['author_name']="";	
							else						
							$response['Newarrivals'][$key]['author_name']=$author_info['first_name'];	
							
							
							
								
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Newarrivals'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Newarrivals'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Newarrivals'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Newarrivals'][$key]['rating']=0;
							}
							
							
								
							$response['Newarrivals'][$key]['books_status']="Yes";	
								
								
						
								
							}
							
						
						  
						   
						}
						else
						 {
							
							
							$response['Newarrivals'][0]['books_status']="No";
						 }
						
							
							/*echo "<pre>";
							print_r($response);
							exit;*/
				  
							//$response = json_encode($response);
							//$response =  str_replace("\\", '',$response);
							
							 
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							 
							echo $response;
							
							//echo stripslashes($response);
					  
				break;
				
				
				case "GetBestsellersIphone":
					$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.cat_id ASC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Library']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
								
								
								
							}
				
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;
						
						}
						else
					
						{
						
						$response['Library'][0]['books_status']="No";	
						}
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.best_seller DESC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.best_seller DESC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.best_seller DESC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Bestsellers']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['Bestsellers'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Bestsellers'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Bestsellers'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Bestsellers'][$key]['author_name']="";	
							else						
							$response['Bestsellers'][$key]['author_name']=$author_info['first_name'];
							
							
							
							
								
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Bestsellers'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Bestsellers'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Bestsellers'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Bestsellers'][$key]['rating']=0;
							}
							
							
								
							$response['Bestsellers'][$key]['books_status']="Yes";	
								
						  
								
								
							}
							
						 
						  
						   
						}
						else
						{
							
							$response['Bestsellers'][0]['books_status']="No";
						}
							
							/*echo "<pre>";
							print_r($response);
							exit;*/
				  
							//$response = json_encode($response);
							//$response =  str_replace("\\", '',$response);
							
							 
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							 
							echo $response;
							
							//echo stripslashes($response);
					  
				break;
				
				
				
				case "GetBestsellers":
					$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.best_seller DESC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.best_seller DESC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.best_seller DESC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Bestsellers']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Bestsellers'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Bestsellers'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Bestsellers'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Bestsellers'][$key]['author_name']="";	
							else						
							$response['Bestsellers'][$key]['author_name']=$author_info['first_name'];
							
							
							
							
								
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Bestsellers'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Bestsellers'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Bestsellers'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Bestsellers'][$key]['rating']=0;
							}
							
							
								
							$response['Bestsellers'][$key]['books_status']="Yes";	
								
						  
								
								
							}
							
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;	
											   
						   /*echo"<pre>";
						   print_r($response);
						   */
						    
						  
						   
						}
						else
						{
							
							$response['Bestsellers'][0]['books_status']="No";
						}
							
							/*echo "<pre>";
							print_r($response);
							exit;*/
				  
							//$response = json_encode($response);
							//$response =  str_replace("\\", '',$response);
							
							 
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							 
							echo $response;
							
							//echo stripslashes($response);
					  
				break;
				
				case "GetNewarrivals":
					$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.id DESC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.id DESC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.id DESC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Newarrivals']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Newarrivals'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Newarrivals'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Newarrivals'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Newarrivals'][$key]['author_name']="";	
							else						
							$response['Newarrivals'][$key]['author_name']=$author_info['first_name'];	
							
							
							
								
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Newarrivals'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Newarrivals'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Newarrivals'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Newarrivals'][$key]['rating']=0;
							}
							
							
								
							$response['Newarrivals'][$key]['books_status']="Yes";	
								
								
						
								
							}
							
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;	
											   
						   /*echo"<pre>";
						   print_r($response);
						   */
						    
						  
						   
						}
						else
						 {
							
							
							$response['Newarrivals'][0]['books_status']="No";
						 }
						
							
							/*echo "<pre>";
							print_r($response);
							exit;*/
				  
							//$response = json_encode($response);
							//$response =  str_replace("\\", '',$response);
							
							 
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							 
							echo $response;
							
							//echo stripslashes($response);
					  
				break;
				
				case "GetPurchaseHistory":
					$main_array = array();
					
				
						
						
						//$sql="SELECT prod.*,c.category_name,g.genre, FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id INNER JOIN pclive_credit_history hs on prod.id =hs.bookid 	WHERE hs.userid='$jsonObj->UserId' ORDER BY prod.cat_id ASC";
						
						
						
						$sql="SELECT prod.id, prod.title,prod.description,prod.file_size,prod.publisher_id,prod.author_id,c.category_name, g.genre, hs.price,hs.userid,hs.add_date FROM pclive_products AS prod INNER JOIN pclive_categories c ON c.id = prod.cat_id INNER JOIN pclive_genres g ON prod.product_type = g.id INNER JOIN pclive_credit_history hs ON prod.id = hs.bookid WHERE hs.userid ='$jsonObj->UserId' ORDER BY prod.cat_id ASC";
						
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Purchase']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Purchase'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Purchase'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Purchase'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Purchase'][$key]['author_name']="";	
							else						
							$response['Purchase'][$key]['author_name']=$author_info['first_name'];	
							$response['Purchase'][$key]['status']="Yes";	
							
							
							
							
							$amount_qry="SELECT sum(`amount`) as total  FROM  pclive_payment_history where user_id='$jsonObj->UserId' group by user_id";
							$amount_result = $db->query($amount_qry);
							$amount_record = $amount_result->FetchAll();
							$amount_info=$amount_record[0];	
							if(count($amount_record)>0)
							$response['Amount']=$amount_info['total'];
							else
						    $response['Amount']=0;
							
								
					
							}
					
						}
						else
						//$response['Library']="No books available";
						$response['Purchase'][0]['status']="No";	
				
						$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
						echo $response;
		
				break;
				
				
				
				
				
				
				
				
				
				
				
				
				case "GetPurchaseHistoryNew":
					$main_array = array();
					
			
						
						//$sql="SELECT prod.*,c.category_name,g.genre, FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id INNER JOIN pclive_credit_history hs on prod.id =hs.bookid 	WHERE hs.userid='$jsonObj->UserId' ORDER BY prod.cat_id ASC";
						
						
						
						$sql="SELECT prod.id, prod.title,prod.description,prod.file_size,prod.publisher_id,prod.author_id,prod.publish_time,c.category_name, g.genre, hs.price,hs.userid,hs.add_date FROM pclive_products AS prod INNER JOIN pclive_categories c ON c.id = prod.cat_id INNER JOIN pclive_genres g ON prod.product_type = g.id INNER JOIN pclive_credit_history hs ON prod.id = hs.bookid WHERE hs.userid ='$jsonObj->UserId' ORDER BY prod.cat_id ASC";
						
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Purchase']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Purchase'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Purchase'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Purchase'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Purchase'][$key]['author_name']="";	
							else						
							$response['Purchase'][$key]['author_name']=$author_info['first_name'];	
							$response['Purchase'][$key]['status']="Yes";	
							
							
							
							
							$amount_qry="SELECT sum(`amount`) as total  FROM  pclive_payment_history where user_id='$jsonObj->UserId' group by user_id";
							$amount_result = $db->query($amount_qry);
							$amount_record = $amount_result->FetchAll();
							$amount_info=$amount_record[0];	
							if(count($amount_record)>0)
							$response['Amount']=$amount_info['total'];
							else
						    $response['Amount']=0;
							
								
					
							}
					
						}
						else
						//$response['Library']="No books available";
						$response['Purchase'][0]['status']="No";	
						
						
						
						
						
					$sql1="select * from pclive_payment_history where user_id='$jsonObj->UserId'";
						
					$result1 = $db->query($sql1);
					$records1 = $result1->FetchAll();
					
				     	if(count($records1)>0)
						{
							
							$response['PurchasePoint']=$records1;
							$credit=0;
							foreach($records1 as $key=>$Parray1)
							{
								
							$credit+=$Parray1['amount'];
								
							}
							
							
					$sql2="SELECT sum( `price` ) AS expense FROM pclive_credit_history where userid='$jsonObj->UserId' GROUP BY userid";
					$result2 = $db->query($sql2);
					$records2 = $result2->FetchAll();	
					$expense_info=$records2[0];
					
					$balance=$credit-$expense_info['expense'];
					$response['Balance']=$balance;
					
						}
						else
						//$response['Library']="No books available";
						$response['PurchasePoint'][0]['status']="No";	
						
						
						
						
				
						$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
						echo $response;
		
				break;
		
				
				
				
				
				
				case "GetPurchaseHistoryNew1":
					$main_array = array();
					$TotalTransaction=0;
					
						
						$sql="SELECT prod.id, prod.title,prod.description,prod.file_size,prod.publisher_id,prod.author_id,prod.publish_time,c.category_name, g.genre, hs.price,hs.userid,hs.add_date FROM pclive_products AS prod INNER JOIN pclive_categories c ON c.id = prod.cat_id INNER JOIN pclive_genres g ON prod.product_type = g.id INNER JOIN pclive_credit_history hs ON prod.id = hs.bookid WHERE hs.userid ='$jsonObj->UserId' ORDER BY prod.cat_id ASC";
						
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Purchase']=$records;
							foreach($records as $key=>$Parray)
							{
					
									
								//get store_id from book_id and userid from pclive_credit_history
								
								$retVal1=array();  
								$select1='SELECT store_id from  pclive_credit_history where bookid="'.$Parray['id'].'" and userid="'.$Parray['userid'].'" ';
								$currency_result = $db->query($select1);
								$retVal1 =$currency_result->FetchAll();
								
								
								
								$select2='SELECT  `currency_sign` from  `pclive_currency` INNER JOIN pclive_country ON pclive_currency.`currency_id`= pclive_country.`currency_id` where id="'.$retVal1[0]['store_id'].'" ';
								$currency_result1 = $db->query($select2);
								$retVal2 =$currency_result1->FetchAll();
							
								
								$response['Purchase'][$key]['price']=$retVal2[0]['currency_sign']." ".$response['Purchase'][$key]['price'];
								
								
								
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Purchase'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Purchase'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Purchase'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Purchase'][$key]['author_name']="";	
							else						
							$response['Purchase'][$key]['author_name']=$author_info['first_name'];	
							$response['Purchase'][$key]['status']="Yes";	
							
							
						//calculate total purchase
						
						
						  $TotalTransaction+=$Parray['price'];
							
							
								
					
							}
						$response['TotalTransaction']=$TotalTransaction;
					
						}
						else
						//$response['Library']="No books available";
						$response['Purchase'][0]['status']="No";	
						
						
						
						
						
					$sql1="select * from pclive_payment_history where user_id='$jsonObj->UserId'";
						
					$result1 = $db->query($sql1);
					$records1 = $result1->FetchAll();
					
				     	if(count($records1)>0)
						{
							
							$response['PurchasePoint']=$records1;
							$credit=0;
							foreach($records1 as $key=>$Parray1)
							{
								
							$credit+=$Parray1['amount'];
								
							}
							
							
					$sql2="SELECT sum( `price` ) AS expense FROM pclive_credit_history where userid='$jsonObj->UserId' GROUP BY userid";
					$result2 = $db->query($sql2);
					$records2 = $result2->FetchAll();	
					$expense_info=$records2[0];
					$balance=$credit-$expense_info['expense'];
					$response['Balance']=$balance;
					
					
					
					
					
					        $amount_qry="SELECT sum(`amount`) as total  FROM  pclive_payment_history where user_id='$jsonObj->UserId' group by user_id";
							$amount_result = $db->query($amount_qry);
							$amount_record = $amount_result->FetchAll();
							$amount_info=$amount_record[0];	
							if(count($amount_record)>0)
							$response['TotalPurchase']=$amount_info['total'];
							else
						    $response['TotalPurchase']=0;
					
					
						}
						else
						//$response['Library']="No books available";
						$response['PurchasePoint'][0]['status']="No";	
						
						
						
						
				
						$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
						echo $response;
		
				break;
				
				
				
				
				
				case "GetPurchasePoint":
					$main_array = array();
					$sql="select * from pclive_payment_history where user_id='$jsonObj->UserId'";
						
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['PurchasePoint']=$records;
							$credit=0;
							foreach($records as $key=>$Parray)
							{
								
							$credit+=$Parray['amount'];
								
							}
							
							
					$sql1="SELECT sum( `price` ) AS expense FROM pclive_credit_history where userid='$jsonObj->UserId' GROUP BY userid";
					$result1 = $db->query($sql1);
					$records1 = $result1->FetchAll();	
					$expense_info=$records1[0];
					
					$balance=$credit-$expense_info['expense'];
					$response['Balance']=$balance;
					
						}
						else
						//$response['Library']="No books available";
						$response['PurchasePoint'][0]['status']="No";	
				
						$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
						echo $response;
		
				break;
				
				
				
				
				
				
				
				case "GetFeatured":
					$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!='' and is_featured=1  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.product_type*1 ASC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and is_featured=1   ORDER BY prod.product_type*1 ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Featured']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Featured'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Featured'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Featured'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Featured'][$key]['author_name']="";	
							else						
							$response['Featured'][$key]['author_name']=$author_info['first_name'];		
				
								
							}
											   
						 
						   
						}
							
				
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));							 
							echo $response;
					  
				break;
				
				
				
				
				
				case "Categories":
					$get_all_categories = "select * from pclive_categories where status='1'";
					$res_all_categories = $db->query($get_all_categories);
					$data_categories =  $res_all_categories->FetchAll();
					$response['Allcategories'] = $data_categories;
					
					$response= json_encode($response);
					
					echo $response;	
					
				break;
				
				
				case "GetCountries":
					$get_all_countries = "select * from pclive_country where status='1' order by country";
					$res_all_countries = $db->query($get_all_countries);
					$data_countries =  $res_all_countries->FetchAll();
					$response['Allcountries'] = $data_countries;
					
					$response= json_encode($response);
					
					echo $response;	
					
				break;
				
				
				case "GetStores":
					$get_all_countries = "select id, country as store from pclive_country where is_store_status='1' and is_store=1 order by country";
					$res_all_countries = $db->query($get_all_countries);
					$data_countries =  $res_all_countries->FetchAll();
					$response['Allstores'] = $data_countries;
					
					$response= json_encode($response);
					
					echo $response;	
					
				break;
				
				
				case "StoreDownloads":
					
					if(!empty($jsonObj->userid) && $jsonObj->userid>0) 
					{

						if((!empty($jsonObj->bookid) && $jsonObj->bookid>0) ||  (!empty($jsonObj->StoreId) && $jsonObj->StoreId>0)) 
						{
							$chk_review = $db->query("select * from pclive_credit_history where userid='".$jsonObj->userid."' and bookid='".$jsonObj->bookid."'");
							$records_review = $chk_review->FetchAll();	
							
														
							if(count($records_review)==0)
							{
								$get_prname = $db->query("select * from pclive_products where id='".$jsonObj->bookid."'");
								$records_prname = $get_prname->FetchAll();	
								
								
								$sql="insert into pclive_credit_history set userid='".$jsonObj->userid."',bookid='".$jsonObj->bookid."',price='".$jsonObj->price."',book_name='".$records_prname[0]['title']."', store_id='".$jsonObj->StoreId."', add_date=now()";
							
								$result = $db->query($sql);
						
								$sql1="update pclive_products set best_seller=best_seller+1 where id=".$jsonObj->bookid."";
							
								$result1 = $db->query($sql1);
							 							 
								$response = '{"BookResponse":{
								  "error":"false","Message":"Successfully purchased."
								  }}';
								echo $response;
							}
							else
							{
								$sql1="update pclive_products set no_download=no_download+1 where id=".$jsonObj->bookid."";
							
								$result1 = $db->query($sql1);
								
								
								$response = '{"BookResponse":{"Message":"You have already purchased.", "error":"true"}}';
								echo $response; 
							}
							 
						}
						else
						{
							 $response = '{"BookResponse":{"Message":"Bookid or store does not exist.", "error":"true"}}';
							 echo $response; 	
						}
					}
					 
					else 
					{
					  $response = '{"BookResponse":{"Message":"Userid does not exist.", "error":"true"}}';
						echo $response;  
					} 
					    
				break;
						
				
				case "ReviewRating":
					 
				    if(!empty($jsonObj->userid) && $jsonObj->userid>0) 
					{

						if(!empty($jsonObj->bookid) && $jsonObj->bookid>0) 
						{
							$chk_review = $db->query("select * from pclive_review where userid='".$jsonObj->userid."' and productid='".$jsonObj->bookid."'");
							$records_review = $chk_review->FetchAll();	
							
							if(count($records_review)==0)
							{
								$sql="insert into pclive_review set userid='".$jsonObj->userid."',rating='".$jsonObj->rating."',comments='".$jsonObj->comments."',productid='".$jsonObj->bookid."',add_time=now()";
							
								$result = $db->query($sql);
						
							 
								$response = '[{"Error":{"Message":"You have rated succesfully."}}]';
								echo $response;
							}
							else
							{
								
							$sql="update pclive_review set rating='".$jsonObj->rating."', comments='".$jsonObj->comments."',add_time=now() where  userid='".$jsonObj->userid."' and productid='".$jsonObj->bookid."' ";
							
								$result = $db->query($sql);
						
							 
								$response = '[{"Error":{"Message":"You have rated succesfully."}}]';	
								
								//$response = '[{"Error":{"Message":"You have already given your review for this book."}}]';
								echo $response; 
							}
							 
						}
						else
						{
							 $response = '[{"Error":{"Message":"Book does not exists."}}]';
							 echo $response; 	
						}
					}
					 
					else 
					{
					  $response = '[{"Error":{"Message":"User Id does not exist"}}]';
						echo $response;  
					} 
					    
				break;
				
				case "UserUpdate":
				 
					if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{	
						$Errorresponse="";
						if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="" || !(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="" || !(isset($jsonObj->Country)) || trim($jsonObj->Country)=="")
						{
							$Errorresponse='[{"ParameterMissing":{';
							if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="") 
							{
								//$Errorresponse.='"FirstName":"Please Enter First Name",';
								
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter First Name" }';
								
							}
							
							if(!(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="") 
							{
								//$Errorresponse.='"LastName":"Please Enter Last Name",';
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter Last Name" }';
							}
							
							if(!(isset($jsonObj->Country)) || trim($jsonObj->Country)=="") 
							{
								//$Errorresponse.='"Country":"Please Select A Country"';
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Select A Country" }';
							}
							
							$Errorresponse.='}}]';
						}	
				
						if(!empty($Errorresponse)) 
						{
							echo $Errorresponse;
						} 
						else 
						{
							//if($jsonObj->Acounttype=="")
							//$jsonObj->Acounttype=2;
					    	
							
							$sql = "UPDATE pclive_companies SET first_name='$jsonObj->FirstName', last_name='$jsonObj->LastName', country='$jsonObj->Country'  where id='$jsonObj->id'";
							
						 
							
							$result = $db->query($sql);
														
							if($result)
							{
								  $response.='{"Message":"Success", "UpdationResponse":"Record Updated Successfully" }';
								   
								  echo $response;
							}
							else
							{
								 $response.='{"Message":"Unsuccess", "UpdationResponse":"Record cannot be modified" }';
								 
								 echo $response;
							}
						}
					}
					else 
					{
					 
					 $response.='{"Message":"Unsuccess", "Error":"User Id does not exist" }';
					 echo $response;  
					}
				
				break;
				
				
				
				
				/*
				
				case "UserUpdateIphone":
				 
					if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{	
						$Errorresponse="";
						if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="" || !(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="" || !(isset($jsonObj->Country)) || trim($jsonObj->Country)=="")
						{
							$Errorresponse='[{"ParameterMissing":{';
							if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="") 
							{
								//$Errorresponse.='"FirstName":"Please Enter First Name",';
								
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter First Name" }';
								
							}
							
							if(!(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="") 
							{
								//$Errorresponse.='"LastName":"Please Enter Last Name",';
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter Last Name" }';
							}
							
							if(!(isset($jsonObj->Country)) || trim($jsonObj->Country)=="") 
							{
								//$Errorresponse.='"Country":"Please Select A Country"';
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Select A Country" }';
							}
							
							$Errorresponse.='}}]';
						}	
				
						if(!empty($Errorresponse)) 
						{
							echo $Errorresponse;
						} 
						else 
						{
					    	
							
							$countrrep=str_replace(" ", "",$jsonObj->Country);
							//$sqlcountry="select id  from pclive_country where country='".$jsonObj->Country."'";
							$sqlcountry="select id FROM pclive_country
WHERE replace( country, ' ', '' ) = '".$countrrep."' ";
							$resultcountry = $db->query($sqlcountry);
							$data_country =  $resultcountry->FetchAll();
								
							
							//$sqlcountry="select id  from pclive_country where country='".$jsonObj->Country."'";
							$userinfo="select * FROM pclive_companies where id='$jsonObj->id'";
							$resultuserinfo = $db->query($userinfo);
							$data_userinfo =  $resultuserinfo->FetchAll();
							//if($jsonObj->Acounttype=="")
							//$jsonObj->Acounttype=2;
					    	
							
							$sql = "UPDATE pclive_companies SET first_name='$jsonObj->FirstName', last_name='$jsonObj->LastName', country='".$data_country[0]['id']."' where id='$jsonObj->id'";	
						 
							
							$result = $db->query($sql);
														
							if($result)
							{
								  $response.='{"Message":"Success", "UpdationResponse":"Record Updated Successfully" ,"UpdatedCountry":"'.$data_country[0]['id'].'","Userid":"'.$data_userinfo[0]['id'].'","Username":"'.$data_userinfo[0]['user_email'].'","Userpassword":"'.$data_userinfo[0]['user_password'].'"}';
								   
								  echo $response;
							}
							else
							{
								 $response.='{"Message":"Unsuccess", "UpdationResponse":"Record cannot be modified" }';
								 
								 echo $response;
							}
						}
					}
					else 
					{
					 
					 $response.='{"Message":"Unsuccess", "Error":"User Id does not exist" }';
					 echo $response;  
					}
				
				break;
				*/
				
				
				case "UserUpdateIphone":
				 
					if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{	
						$Errorresponse="";
						if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="" || !(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="" || !(isset($jsonObj->countryid)) || trim($jsonObj->countryid)=="")
						{
							$Errorresponse='[{"ParameterMissing":{';
							if(!(isset($jsonObj->FirstName)) || trim($jsonObj->FirstName)=="") 
							{
								//$Errorresponse.='"FirstName":"Please Enter First Name",';
								
								$Errorresponse.='{"Message":"Please Enter First Name.", "error":"true" }';
								
							}
							
							if(!(isset($jsonObj->LastName)) || trim($jsonObj->LastName)=="") 
							{
								//$Errorresponse.='"LastName":"Please Enter Last Name",';
								$Errorresponse.='{"Message":"Please Enter Last Name.", "error":"true" }';
							}
							
							if(!(isset($jsonObj->countryid)) || trim($jsonObj->countryid)=="") 
							{
								//$Errorresponse.='"Country":"Please Select A Country"';
								$Errorresponse.='{"Message":"Please Select A Country.", "error":"true" }';
							}
							
							$Errorresponse.='}}]';
						}	
				
						if(!empty($Errorresponse)) 
						{
							echo $Errorresponse;
						} 
						else 
						{
							//if($jsonObj->Acounttype=="")
							//$jsonObj->Acounttype=2;
					    	
							
							$sql = "UPDATE pclive_companies SET first_name='$jsonObj->FirstName', last_name='$jsonObj->LastName', country='$jsonObj->countryid'  where id='$jsonObj->id'";
							
						 
							
							$result = $db->query($sql);
														
							if($result)
							{
								  //$response.='{"Message":"Record Updated Successfully.", "error":"false" }';
								$sql1 = "SELECT * FROM pclive_country where id='".$jsonObj->countryid."' ";
						        $result1 = $db->query($sql1);
						        $record1 = $result1->FetchAll();	
								
						$sql = 'SELECT * FROM pclive_companies where id="'.$jsonObj->id.'" AND parent_id="0" AND account_type<>"3"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					  
								  	
							$response = '{
							"Message":"Record updated successfully.",
							"userid":"'.$jsonObj->id.'",
							"countryid":"'.$jsonObj->countryid.'",
							"Country" : "'.$record1[0]['country'].'", 
							"FirstName" : "'.$jsonObj->FirstName.'",
							"LastName"	: "'.$jsonObj->FirstName.'","Username":"'.$record[0]['user_email'].'","Userpassword":"'.$record[0]['user_password'].'","error":"false"
							}';
								   
							echo $response;
							
							}
							else
							{
								 $response.='{"Message":"Request failed. Please try again.", "error":"true" }';
								 
								 echo $response;
							}
						}
					}
					else 
					{
					 
					 $response.='{"Message":"Request failed. Please try again.", "error":"true" }';
					 echo $response;  
					}
				
				break;
				
				
				case "ChangePwd":
				
					if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{	
						$Errorresponse="";
						if(!(isset($jsonObj->NewPassword)) || trim($jsonObj->NewPassword)=="" || !(isset($jsonObj->NewConfPassword)) || trim($jsonObj->NewConfPassword)=="")
						{
							//$Errorresponse='[{"ParameterMissing":{';
							if(!(isset($jsonObj->NewPassword)) || trim($jsonObj->NewPassword)=="") 
							{
								
								
								
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter Your New Password" }';
								 
							}
							
							
							
							
							if(!(isset($jsonObj->NewConfPassword)) || trim($jsonObj->NewConfPassword)=="") 
							{ 
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter New Confirm Password" }';							 
							}
							
							if($jsonObj->NewPassword!=$jsonObj->NewConfPassword)
							{ 
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Confirm Password does not Matched with New Password" }';							 
							}
							 
							//$Errorresponse.='}}]';
						}	
						/*
					if($jsonObj->OldPassword!="") 
					{
						$sql_oldpwd = "select * from pclive_companies where id='".$jsonObj->id."'";
						 
						
						$res_oldpwd = $db->query($sql_oldpwd);
						$data_oldpwd =  $res_oldpwd->FetchAll();
 					
						 
						
						if(strcmp($data_oldpwd[0]['user_password'],$jsonObj->OldPassword)!=0)
						{
							//$Errorresponse.='"CurrentPassword":"Wrong Current Password",';
						  
							$Errorresponse.='{"Message":"Unsuccess", "Error":"Wrong Current Password" }';
							
						}
						 
					}
					*/
						if(!empty($Errorresponse)) 
						{
							echo $Errorresponse;
						} 
						else 
						{
					    	$sql = "UPDATE pclive_companies SET user_password='".md5($jsonObj->NewConfPassword)."' where id='".$jsonObj->id."'";
							$result = $db->query($sql);
							
							
						$sql_oldpwd = "select * from pclive_companies where id='".$jsonObj->id."'";
						 
						
						$res_oldpwd = $db->query($sql_oldpwd);
						$data_oldpwd =  $res_oldpwd->FetchAll();
														
							if($result)
							{
								   $response.='{"Message":"Success", "UpdationResponse":"Password Changed Successfully"  ,"UpdatedCountry":"'.$data_oldpwd[0]['country'].'","Userid":"'.$data_oldpwd[0]['id'].'","Username":"'.$data_oldpwd[0]['user_email'].'","Userpassword":"'.$data_oldpwd[0]['user_password'].'"}';
								  
								  echo $response;
							}
							else
							{
								  $response.='{"Message":"Unsuccess", "Error":"Password cannot be modified" }';
								 
								  echo $response;
							}
						}
					}
					else 
					{
					$response = '{"Message":{"Unsuccess":"User Id does not exist"}}';
					echo $response;  
					}
				
				break;
				
				
				
				case "ChangePwdIphone":
				
					if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{	
						$Errorresponse="";
						if(!(isset($jsonObj->NewPassword)) || trim($jsonObj->NewPassword)=="" || !(isset($jsonObj->NewConfPassword)) || trim($jsonObj->NewConfPassword)=="")
						{
							//$Errorresponse='[{"ParameterMissing":{';
							if(!(isset($jsonObj->NewPassword)) || trim($jsonObj->NewPassword)=="") 
							{
								
								
								
								$Errorresponse.='{"Message":"Request failed. Please try again.", "error":"true" }';
								 
							}
							
							
							
							
							if(!(isset($jsonObj->NewConfPassword)) || trim($jsonObj->NewConfPassword)=="") 
							{ 
								$Errorresponse.='{"Message":"Request failed. Please try again.",  "error":"true"  }';							 
							}
							
							if($jsonObj->NewPassword!=$jsonObj->NewConfPassword)
							{ 
								$Errorresponse.='{"Message":"Request failed. Please try again.", "error":"true"   }';							 
							}
							 
							//$Errorresponse.='}}]';
						}	
						/*
					if($jsonObj->OldPassword!="") 
					{
						$sql_oldpwd = "select * from pclive_companies where id='".$jsonObj->id."'";
						 
						
						$res_oldpwd = $db->query($sql_oldpwd);
						$data_oldpwd =  $res_oldpwd->FetchAll();
 					
						 
						
						if(strcmp($data_oldpwd[0]['user_password'],$jsonObj->OldPassword)!=0)
						{
							//$Errorresponse.='"CurrentPassword":"Wrong Current Password",';
						  
							$Errorresponse.='{"Message":"Unsuccess", "Error":"Wrong Current Password" }';
							
						}
						 
					}
					*/
						if(!empty($Errorresponse)) 
						{
							echo $Errorresponse;
						} 
						else 
						{
					    	$sql = "UPDATE pclive_companies SET user_password='".md5($jsonObj->NewConfPassword)."' where id='".$jsonObj->id."'";
							$result = $db->query($sql);
							
							
						$sql_oldpwd = "select * from pclive_companies where id='".$jsonObj->id."'";
						 
						
						$res_oldpwd = $db->query($sql_oldpwd);
						$data_oldpwd =  $res_oldpwd->FetchAll();
														
							if($result)
							{
								$sql1 = "SELECT * FROM pclive_country where id='".$data_oldpwd[0]['country']."' ";
						        $result1 = $db->query($sql1);
						        $record1 = $result1->FetchAll();
								
								   $response.='{"Message":"Password Changed Successfully.", "error":"false", "FirstName":"'.$data_oldpwd[0]['first_name'].'","LastName":"'.$data_oldpwd[0]['last_name'].'","countryid":"'.$data_oldpwd[0]['country'].'","Country":"'.$record1[0]['country'].'","userid":"'.$data_oldpwd[0]['id'].'","Username":"'.$data_oldpwd[0]['user_email'].'","Userpassword":"'.$data_oldpwd[0]['user_password'].'"}';
								  
								  echo $response;
							}
							else
							{
								  $response.='{"Message":"Request failed. Please try again.", "error":"true"  }';		
								 
								  echo $response;
							}
						}
					}
					else 
					{
					$response = '{"Message":"Request failed. Please try again.", "error":"true"  }';		
					echo $response;  
					}
				
				break;
				
				
				case "Downloads":
				    if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{
					$sql="SELECT s.id,s.product_id,s.store_id,s.total_downloads,s.downloaded_file_size,s.status,s.added_date,s.updated_date, p.product_type,p.publisher_id,p.author_id,p.title,p.file_name,p.file_size,p.parent_brand_id,p.edition_id,p.description,p.isbn_number,p.publisher,p.total_pages,p.cat_id,p.file_name,p.file_size, pp.country_id,pp.language_id,pp.price FROM pclive_company_subscriptions as s INNER JOIN pclive_products as p ON s.product_id=p.id INNER JOIN pclive_product_prices as pp ON s.product_id=pp.product_id WHERE s.company_id<>0 AND s.company_id='$jsonObj->id' AND s.store_id=pp.country_id ORDER BY s.id ASC";
		
					$result = $db->query($sql);
					$records = $result->FetchAll();		
						
						if(count($records)>0)
						{
							$response=array();
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
								$publisher_info_qry='SELECT first_name,last_name,emailid,publisher,country,phone from  pclive_users where id="'.$Parray['publisher_id'].'"';
								$publisher_info_result = $db->query($publisher_info_qry);
								$publisher_info_record = $publisher_info_result->FetchAll();
								$publisher_info=$publisher_info_record[0];
								
								if(!empty($Parray['parent_brand_id']))
								{
								$parent_brand_id_qry='SELECT title from pclive_products where id="'.$Parray['parent_brand_id'].'"';
								$brand_info_result = $db->query($parent_brand_id_qry);
								$brand_info_record = $brand_info_result->FetchAll();
								$brand_info=$brand_info_record[0];
								
								$parent_brand_title_qry='SELECT brand from pclive_brands where id="'.$brand_info['title'].'"';
								$parent_brand_info_result = $db->query($parent_brand_title_qry);
								$parent_brand_info_record = $parent_brand_info_result->FetchAll();
								$parent_brand_info=$parent_brand_info_record[0];
								}
															
								$store_info_qry='SELECT country from pclive_country where id="'.$Parray['store_id'].'"';
								$store_info_result = $db->query($store_info_qry);
								$store_info_record = $store_info_result->FetchAll();
								$store_info=$store_info_record[0];
								
								$genre_info_qry='SELECT genre from  pclive_genres where id="'.$Parray['product_type'].'"';
								$genre_info_result = $db->query($genre_info_qry);
								$genre_info_record = $genre_info_result->FetchAll();
								$genre_info=$genre_info_record[0];
								
								$cat_info_qry='SELECT category_name from   pclive_categories where id="'.$Parray['cat_id'].'"';
								$cat_info_result = $db->query($cat_info_qry);
								$cat_info_record = $cat_info_result->FetchAll();
								$cat_info=$cat_info_record[0];
								
								$lan_info_qry='SELECT language_name from pclive_product_language where id="'.$Parray['language_id'].'"';
								$lan_info_result = $db->query($lan_info_qry);
								$lan_info_record = $lan_info_result->FetchAll();
								$lan_info=$lan_info_record[0];
																
								if(strtolower(trim($cat_info['category_name']))==strtolower(trim('eBook')) || strtolower(trim($cat_info['category_name']))==strtolower(trim('eBooks')))
								{
								$author_info_qry='SELECT first_name,last_name,emailid,phone from  pclive_users where id="'.$Parray['author_id'].'"';
								$author_info_result = $db->query($author_info_qry);
								$author_info_record = $author_info_result->FetchAll();
								$author_info=$author_info_record[0];
								}
								else
								{
								$edition_info_qry='SELECT edition from pclive_editions where id="'.$Parray['edition_id'].'"';
								$edition_info_result = $db->query($edition_info_qry);
								$edition_info_record = $edition_info_result->FetchAll();
								$edition_info=$edition_info_record[0];
								}
																							
								$response['Downloads'][$key]['ProductId']=$Parray['product_id'];
								
								if(!empty($parent_brand_info))
								{
									$title=$Parray['title']."(".ucfirst($parent_brand_info['brand']).")";
								}
								else
								{
									$title=$Parray['title'];
								}
								$response['Downloads'][$key]['Title']=$title;
								$response['Downloads'][$key]['FileName']=$Parray['file_name'];
								
								$file_size_array=explode(" ",$Parray['file_size']);
								if(strtolower(trim($file_size_array[1]))==strtolower(trim('Mb')))
								{
									$file_size_array[0]=round($file_size_array[0],2);
								}
								elseif(strtolower(trim($file_size_array[1]))==strtolower(trim('Kb')))
								{
									$file_size_array[0]=round($file_size_array[0]/1024,2);
								}
								elseif(strtolower(trim($file_size_array[1]))==strtolower(trim('Bytes')))
								{
									$file_size_array[0]=round($file_size_array[0]/(1024*1024),2);
								}
								else
								{
									$file_size_array[0]=round($file_size_array[0],2);
								}
								
								
								$response['Downloads'][$key]['FileSize']=$file_size_array[0];
								$response['Downloads'][$key]['DownloadedFileSize']=$Parray['downloaded_file_size'];
								
								//$thumbnail='<img src="'.$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'].'" height="208" width="166">';
								
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								
								$response['Downloads'][$key]['ProductThumbnail']=$thumbnail_path;
								//$response['Downloads'][$key]['ProductThumbnail']=$thumbnail;
								
								$response['Downloads'][$key]['StoreName']=$store_info['country'];
								//$response['Downloads'][$key]['StoreId']=$Parray['store_id'];
								$response['Downloads'][$key]['Category']=$cat_info['category_name'];
								if(isset($edition_info) && !empty($edition_info))
								{
								$response['Downloads'][$key]['Edition']=$edition_info['edition'];
								}
								$response['Downloads'][$key]['Genre']=$genre_info['genre'];
								$response['Downloads'][$key]['Language']=$lan_info['language_name'];
								$response['Downloads'][$key]['Price']=$Parray['price'];
								$response['Downloads'][$key]['TotalDownloads']=$Parray['total_downloads'];
								$response['Downloads'][$key]['DownloadStatus']=$Parray['status'];
																							
								$response['Downloads'][$key]['PublisherInfo']=$publisher_info;
								if(isset($author_info) && !empty($author_info))
								{
									$response['Downloads'][$key]['AuthorInfo']=$author_info;
								}
								
								$response['Downloads'][$key]['Edition']=$edition_info['edition'];
								$response['Downloads'][$key]['AddedDate']=$Parray['added_date'];
								$response['Downloads'][$key]['UpdatedDate']=$Parray['updated_date'];
							}
											   
						   /*echo"<pre>";
						   print_r($response);
						   */
						   $response=json_encode($response);
						   echo stripslashes($response);
						}
						else
						{
						   $response = '[{"Error":{"Message":"No Records Found."}}]';
						   echo $response; 	
						}
					} 
					else 
					{
					  $response = '[{"Error":{"Message":"User Id does not exist"}}]';
						echo $response;  
					} 
					    
				break;
				
				case "ForgotPassword":
				      
					$xml = simplexml_load_string($formData['xmldata']);
					
				
					if($xml===false) 
					{
					    $response = '<?xml version="1.0" encoding="utf-8"?>
									<Error>
									<Message>Error : Invalid XML Format.</Message>
								   </Error>';
						echo $response; 		   				   
					} 
					else 
					{
					    
						$sql = 'SELECT * FROM pclive_users where emailid="'.$xml->Email.'"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
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
							Online & Offline
							</a></p>
							</div>
							
							<p class="title">Forgot Password Email</p>
							<p>Your login details given below :</p>
							<p>Username:&nbsp;'.$record[0]['username'].'</p>
							<p>Password:&nbsp;'.$record[0]['password'].'</p>
							
							<BR />
							<br>
							<p>&nbsp;</p>
							
							</div>
							<div id="footer">&nbsp;</div>
							</div>
							</body>
							</html>';
														
							
							$mail = new Zend_Mail();
							$mail->addTo($xml->Email);
							$mail->setSubject("Forgot Password Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
								$response = '<?xml version="1.0" encoding="utf-8"?>
								<Success>
								<Message>Message : Password send successfully!. Please check your mail.</Message>
								</Success>';
								echo $response;	 
							} 
							else 
							{
								$response = '<?xml version="1.0" encoding="utf-8"?>
								<Error>
								<Message>Error : Mail Could not be sent. Try again later!.</Message>
								</Error>';
								echo $response; 		 
							}
																	
						}
						else
						{
						   
								$response = '<?xml version="1.0" encoding="utf-8"?>
								<Error>
								<Message>Error : No record found.</Message>
								</Error>';
								echo $response; 	
						   
						}
						
				   	} // Validate XML	
					
				break;
				
				case "UserForgotPassword":
			
						
					if(isset($jsonObj->EmailId) && !empty($jsonObj->EmailId)) 
					 {
					    
						$sql = 'SELECT * FROM pclive_companies where user_email="'.$jsonObj->EmailId.'"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					    $url='http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/user/auth/cngpwd/id/';
						if(count($record)>0)
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
							
							</head>
							<body>
							
							<p>To set new password, click on the below link:<br></p>
							
							<p>URL:&nbsp;<a href='.$url.base64_encode($record[0]['id']).'/'.mt_rand().'>'.$url.base64_encode($record[0]['id']).'/'.mt_rand().'</a></p>
							<BR />
							<br>
							<p>&nbsp;</p>
							</body>
							</html>';
														
							
							$mail = new Zend_Mail();
							$mail->addTo($jsonObj->EmailId);
							$mail->setSubject("Forgot Password Email");
							$mail->setBodyHtml($message);
							$mail->setFrom(SETFROM, SETNAME);
							
							if($mail->send())
							{
								$response ='{"Message":"Success", "Error":"False" }';
								echo $response;	 
							} 
							else 
							{
							
								$response ='{"Message":"Unsuccess", "Error":"Mail Could not be sent. Try again later" }';
								echo $response;	 	 
							}
																	
						}
						else
						{
						   $response ='{"Message":"Unsuccess", "Error":"UserID does not exist"}';
						   echo $response;	
						   
						}
						
				   //	} // Validate XML	
					}
				break;
				
				case "GetAllLibrary":
				
				$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.cat_id ASC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Library']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
								
								
								
							}
				
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;
						
						}
						else
					
						{
						
						$response['Library'][0]['books_status']="No";	
						}
					
							 
							//$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
							//echo $response;
							
							
							
			//new arrivals
			
		
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.id DESC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.id DESC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.id DESC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
					
				     	if(count($records)>0)
						{
							
							$response['Newarrivals']=$records;
							
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Newarrivals'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Newarrivals'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Newarrivals'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Newarrivals'][$key]['author_name']="";	
							else						
							$response['Newarrivals'][$key]['author_name']=$author_info['first_name'];	
			
								
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Newarrivals'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Newarrivals'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Newarrivals'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Newarrivals'][$key]['rating']=0;
							}
							
							
								
							$response['Newarrivals'][$key]['books_status']="Yes";	
								
								
						
								
							}
							
					
		
						}
						else
						 {
							
							
							$response['Newarrivals'][0]['books_status']="No";
						 }
							//$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							
							
							
							
				//best seller
				if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.best_seller DESC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.best_seller DESC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.best_seller DESC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Bestsellers']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Bestsellers'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Bestsellers'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Bestsellers'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Bestsellers'][$key]['author_name']="";	
							else						
							$response['Bestsellers'][$key]['author_name']=$author_info['first_name'];
							
							
							
							
								
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Bestsellers'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Bestsellers'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Bestsellers'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Bestsellers'][$key]['rating']=0;
							}
							
							
								
							$response['Bestsellers'][$key]['books_status']="Yes";	
								
				
							}
							
			
						   
						}
						else
						{
							
							$response['Bestsellers'][0]['books_status']="No";
						}
						
							 
				//get featured
				
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!='' and is_featured=1  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.product_type*1 ASC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and is_featured=1   ORDER BY prod.product_type*1 ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Featured']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								$response['Featured'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Featured'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Featured'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Featured'][$key]['author_name']="";	
							else						
							$response['Featured'][$key]['author_name']=$author_info['first_name'];		
				
								
							}
											   
						 
						   
						}
							
				
							
							
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							echo $response;
					
				
				break;
				
				
				
				case "GetAllFeaturedLibrary":
				
				$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.cat_id ASC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Library']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								
								
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
							$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
							$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
								
								
								
							}
				
						   $get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;
						
						}
						else
					
						{
						
						$response['Library'][0]['books_status']="No";	
						}
					
							 
							//$response = stripslashes(json_encode($response, JSON_HEX_APOS)); 
							//echo $response;
							
				
							 
				//get featured
				
				
					 if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!='' and is_featured=1  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.product_type*1 ASC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and is_featured=1   ORDER BY prod.product_type*1 ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Featured']=$records;
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['Featured'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Featured'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Featured'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Featured'][$key]['author_name']="";	
							else						
							$response['Featured'][$key]['author_name']=$author_info['first_name'];		
				
								
							}
											   
						 
						   
						} 
							
				
							
							
							$response = stripslashes(json_encode($response, JSON_HEX_APOS));
							 
							echo $response;
					
				
				break;
				case "GetOnlyFeatured":
				if($jsonObj->UserId!='')
				{
					$query_user = "select * from pclive_companies where id = '".$jsonObj->UserId."'";
					$exe_user = $db->query($query_user);
					
					$result_user = $exe_user->FetchAll();

					$array_of_books = array();
						
					$sql_books = "select * from pclive_group_subscriptions where company_id='".$result_user[0]['parent_id']."' and group_id='".$result_user[0]['group_id']."'";
					
					
					$exe_books = mysql_query($sql_books);
					 
					while($result_books = mysql_fetch_array($exe_books))
					{
						 
						$array_of_books[] = $result_books['publication_id'];
						 
					}
					
					$implode_array_of_books = @implode(",",$array_of_books);
			 		
				}
				 
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!='' and is_featured=1  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.product_type*1 ASC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and is_featured=1   ORDER BY prod.product_type*1 ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['Featured']=$records;
							$ii=0;
							foreach($records as $key=>$Parray)
							{								
								//echo $Parray['id']." : - book array";
								//echo $result_books[$ii]['publication_id']." : - subscription array";
								
								
								
								$retVal=array();  
								$select='SELECT * from pclive_country where id="'.$Parray['country_id'].'"';
								
								$country_result = $db->query($select);
								$retVal =$country_result->FetchAll();
								
								
								//echo ">>>>>".$retVal['currency_id'];
								
								$retVal1=array();  
								$select1='SELECT * from pclive_currency where currency_id="'.$retVal[0]['currency_id'].'"';
								$currency_result = $db->query($select1);
								$retVal1 =$currency_result->FetchAll();
								
								// get symbol with price $retVal1[0]['currency_sign'];
							    $response['Featured'][$key]['price']=$retVal1[0]['currency_sign']." ".$response['Featured'][$key]['price'];
							
								
								
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['Featured'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Featured'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Featured'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['Featured'][$key]['author_name']="";	
							else						
							$response['Featured'][$key]['author_name']=$author_info['first_name'];

							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Featured'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Featured'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
								$response['Featured'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
								$response['Featured'][$key]['rating']=0;
							}
							
							$response['Featured'][$key]['books_status']="Yes";
							
							 if(strpos($implode_array_of_books,$Parray['id']))
							 {
									 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_user[0]['group_id']."'");
									 $result_group_name = mysql_fetch_array($get_group_name);
									 $response['Featured'][$key]['is_free']="true";
									 $response['Featured'][$key]['group_name']= $result_group_name['group_name'];
							 }
							 else
							 {
									$response['Featured'][$key]['is_free']="false";
									$response['Featured'][$key]['group_name']= "";
							 }
							
							
							$ii++;
							
							
							}
							
							 $response['Message'] = "Success.";
							 $response['error'] = "false";
						}
						else
						{
							 //$response = '{"Message":"No Featured Books found","apikey":"","error":"true"}';
							 
							 $response['Message'] = "No Featured Books found.";
							 $response['error'] = "true";
						}
						
						$response = stripslashes(json_encode($response, JSON_HEX_APOS));
						echo $response;
				break;
				
				
				case "GetOnlyBestSeller":
					if($jsonObj->UserId!='')
					{
						$query_user = "select * from pclive_companies where id = '".$jsonObj->UserId."'";
						$exe_user = $db->query($query_user);
						
						$result_user = $exe_user->FetchAll();

						$array_of_books = array();
							
						$sql_books = "select * from pclive_group_subscriptions where company_id='".$result_user[0]['parent_id']."' and group_id='".$result_user[0]['group_id']."'";
						
						
						$exe_books = mysql_query($sql_books);
						 
						while($result_books = mysql_fetch_array($exe_books))
						{
							 
							$array_of_books[] = $result_books['publication_id'];
							 
						}
						
						$implode_array_of_books = @implode(",",$array_of_books);
			 		
					}
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!='' AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.best_seller DESC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.best_seller DESC";
				 	}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['BestSeller']=$records;
							foreach($records as $key=>$Parray)
							{
								
								
								
								
								$retVal=array();  
								$select='SELECT * from pclive_country where id="'.$Parray['country_id'].'"';
								
								$country_result = $db->query($select);
								$retVal =$country_result->FetchAll();
								//echo ">>>>>".$retVal['currency_id'];
								
								$retVal1=array();  
								$select1='SELECT * from pclive_currency where currency_id="'.$retVal[0]['currency_id'].'"';
								$currency_result = $db->query($select1);
								$retVal1 =$currency_result->FetchAll();								
								// get symbol with price $retVal1[0]['currency_sign'];
							    $response['BestSeller'][$key]['price']=$retVal1[0]['currency_sign']." ".$response['BestSeller'][$key]['price'];
							
								
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['BestSeller'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['BestSeller'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['BestSeller'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							{
								$response['BestSeller'][$key]['author_name']="";	
							}
							else						
							{
								$response['BestSeller'][$key]['author_name']=$author_info['first_name'];		
							}
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['BestSeller'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['BestSeller'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
								$response['BestSeller'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
								$response['BestSeller'][$key]['rating']=0;
							}
							
							$response['BestSeller'][$key]['books_status']="Yes";	
							
							if(strpos($implode_array_of_books,$Parray['id']))
							{
									 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_user[0]['group_id']."'");
									 $result_group_name = mysql_fetch_array($get_group_name);
									 $response['BestSeller'][$key]['is_free']="true";
									 $response['BestSeller'][$key]['group_name']= $result_group_name['group_name'];
							}
							else
							{
									$response['BestSeller'][$key]['is_free']="false";
									 $response['BestSeller'][$key]['group_name']= "";
							}
							
							
							}
							$response['Message'] = "Success.";
							$response['error'] = "false";
						}
						else
						{
							// $response = '[{"Error": {"Message":"No Best Sellers Found.","error":"true"}}]';
							 $response['Message'] = "No Best Sellers Found.";
							 $response['error'] = "true";
						}
						
						$response = stripslashes(json_encode($response, JSON_HEX_APOS));
						echo $response;
				break;
				
				
				case "GetOnlyNewArrivals":
					if($jsonObj->UserId!='')
					{
						$query_user = "select * from pclive_companies where id = '".$jsonObj->UserId."'";
						$exe_user = $db->query($query_user);
						
						$result_user = $exe_user->FetchAll();

						$array_of_books = array();
							
						$sql_books = "select * from pclive_group_subscriptions where company_id='".$result_user[0]['parent_id']."' and group_id='".$result_user[0]['group_id']."'";
						
						
						$exe_books = mysql_query($sql_books);
						 
						while($result_books = mysql_fetch_array($exe_books))
						{
							 
							$array_of_books[] = $result_books['publication_id'];
							 
						}
						
						$implode_array_of_books = @implode(",",$array_of_books);
			 		
					}
				
					$date = date('Y-m-d',time()-(15*86400));
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!='' AND storeprice.country_id='$jsonObj->StoreId'  and  date(add_time) >= '$date' ORDER BY prod.add_time DESC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and  date(add_time) >= '$date' ORDER BY prod.add_time DESC";
				 	}
					 
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							
							$response['NewArrivals']=$records;
							foreach($records as $key=>$Parray)
							{
								
								$retVal=array();  
								$select='SELECT * from pclive_country where id="'.$Parray['country_id'].'"';
								
								$country_result = $db->query($select);
								$retVal =$country_result->FetchAll();
								//echo ">>>>>".$retVal['currency_id'];
								
								$retVal1=array();  
								$select1='SELECT * from pclive_currency where currency_id="'.$retVal[0]['currency_id'].'"';
								$currency_result = $db->query($select1);
								$retVal1 =$currency_result->FetchAll();								
								// get symbol with price $retVal1[0]['currency_sign'];
							    $response['NewArrivals'][$key]['price']=$retVal1[0]['currency_sign']." ".$response['NewArrivals'][$key]['price'];
								
								
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								$response['NewArrivals'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['NewArrivals'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['NewArrivals'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();
							$author_info=$author_record[0];	
							if($author_info['first_name']=="")
							$response['NewArrivals'][$key]['author_name']="";	
							else						
							$response['NewArrivals'][$key]['author_name']=$author_info['first_name'];	
							 
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['NewArrivals'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['NewArrivals'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
								$response['NewArrivals'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
								$response['NewArrivals'][$key]['rating']=0;
							}
							
							$response['NewArrivals'][$key]['books_status']="Yes";	
							
								if(strpos($implode_array_of_books,$Parray['id']))
								{
									 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_user[0]['group_id']."'");
									 $result_group_name = mysql_fetch_array($get_group_name);
									 $response['NewArrivals'][$key]['is_free']="true";
									 $response['NewArrivals'][$key]['group_name']= $result_group_name['group_name'];
								}
								else
								{
									$response['NewArrivals'][$key]['is_free']="false";
									$response['NewArrivals'][$key]['group_name']= "";
								}
							 
							}
							$response['Message'] = "Success.";
							$response['error'] = "false";
						}
						else
						{
							 //$response = '[{"Error": {"Message":"No New Arrivals Found.","error":"true"}}]';
							 $response['Message'] = "No New Arrivals";
							 $response['error'] = "true";
						}
						
						$response = stripslashes(json_encode($response, JSON_HEX_APOS));
						echo $response;
				break;
				
				case "GetAllCategories":
					 
					$get_all_categories = "select * from pclive_genres where status='1' order by genre ASC";
					$res_all_categories = $db->query($get_all_categories);
					$data_categories =  $res_all_categories->FetchAll();
					
					if(count($data_categories)>0)	
					{
						$response['Allcategories'] = $data_categories; 
						$response['Message'] = "Success.";
						$response['error'] = "false";
						
					}
					else
					{
						//$response = '[{"Error": {"Message":"No Categories.","error":"true"}}]';
						
						$response['Message'] = "No Categories Found.";
						$response['error'] = "true";
					}
					$response = stripslashes(json_encode($response, JSON_HEX_APOS));
					echo $response;
				break;
				
				case "GetFullLibrary":
					 
					if($jsonObj->UserId!='')
					{
						$query_user = "select * from pclive_companies where id = '".$jsonObj->UserId."'";
						$exe_user = $db->query($query_user);
						
						$result_user = $exe_user->FetchAll();

						$array_of_books = array();
							
						$sql_books = "select * from pclive_group_subscriptions where company_id='".$result_user[0]['parent_id']."' and group_id='".$result_user[0]['group_id']."'";
						
						
						$exe_books = mysql_query($sql_books);
						 
						while($result_books = mysql_fetch_array($exe_books))
						{
							 
							$array_of_books[] = $result_books['publication_id'];
							 
						}
						
						$implode_array_of_books = @implode(",",$array_of_books);
			 		
					}
					
					
					$main_array = array();
					
				
					if(!empty($jsonObj->StoreId) && $jsonObj->StoreId>0) 
					{
						
						
						    $store_qry='SELECT is_store,is_store_status from  pclive_country where id="'.$jsonObj->StoreId.'" order by id desc LIMIT 1';
							$store_result = $db->query($store_qry);
							$store_record = $store_result->FetchAll();
							$store_info=$store_record[0];	
							if($store_info['is_store']==1 && $store_info['is_store_status']==1)
							
						
						
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
						
						else
						//default nigeria
						
							$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1'  AND prod.file_name!=''  AND storeprice.country_id=226 ORDER BY prod.cat_id ASC";
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							  
							$response['Library']=$records;
							
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								
								
								$retVal=array();  
								$select='SELECT * from pclive_country where id="'.$Parray['country_id'].'"';
								
								$country_result = $db->query($select);
								$retVal =$country_result->FetchAll();
								
								
								//echo ">>>>>".$retVal['currency_id'];
								
								$retVal1=array();  
								$select1='SELECT * from pclive_currency where currency_id="'.$retVal[0]['currency_id'].'"';
								$currency_result = $db->query($select1);
								$retVal1 =$currency_result->FetchAll();
								
	
								
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							// get symbol with price $retVal1[0]['currency_sign'];
							$response['Library'][$key]['price']=$retVal1[0]['currency_sign']." ".$response['Library'][$key]['price'];
							
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
								$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
								$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
							if(strpos($implode_array_of_books,$Parray['id']))
								{
									 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_user[0]['group_id']."'");
									 $result_group_name = mysql_fetch_array($get_group_name);
									 $response['Library'][$key]['is_free']="true";
									 $response['Library'][$key]['group_name']= $result_group_name['group_name'];
								}
								else
								{
									$response['Library'][$key]['is_free']="false";
									$response['Library'][$key]['group_name']= "";
								}	



							
							}
							
							
							
							$response['Message'] = "Success.";
							$response['error'] = "false";
				 
						}
						else
						{
						 	 //$response = '[{"Error": {"Message":"No New Arrivals Found.","error":"true"}}]';
							 $response['Message'] = "No Books in library.";
							 $response['error'] = "true";
						 
						}
						$response = stripslashes(json_encode($response, JSON_HEX_APOS));
					
					echo $response;
				break;
				
				
				case "GetFullStoreLibrary":
					 
					if($jsonObj->UserId!='')
					{
						$query_user = "select * from pclive_companies where id = '".$jsonObj->UserId."'";
						$exe_user = $db->query($query_user);
						
						$result_user = $exe_user->FetchAll();

						$array_of_books = array();
							
						$sql_books = "select * from pclive_group_subscriptions where company_id='".$result_user[0]['parent_id']."' and group_id='".$result_user[0]['group_id']."'";
						
						
						$exe_books = mysql_query($sql_books);
						 
						while($result_books = mysql_fetch_array($exe_books))
						{
							 
							$array_of_books[] = $result_books['publication_id'];
							 
						}
						
						$implode_array_of_books = @implode(",",$array_of_books);
			 		
					}
					
					
					$main_array = array();
					
				
					if(!empty($jsonObj->Keyword)) 
					{
						
					$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and (prod.title like '%$jsonObj->Keyword%' or prod.description like '%$jsonObj->Keyword%')  ORDER BY prod.cat_id ASC";
					
						
						
					}
					
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!=''  ORDER BY prod.cat_id ASC";
					
					
					}
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							  
							$response['Library']=$records;
							
							foreach($records as $key=>$Parray)
							{
								
								
								
								$retVal=array();  
								$select='SELECT * from pclive_country where id="'.$Parray['country_id'].'"';
								
								$country_result = $db->query($select);
								$retVal =$country_result->FetchAll();
								
								
								//echo ">>>>>".$retVal['currency_id'];
								
								$retVal1=array();  
								$select1='SELECT * from pclive_currency where currency_id="'.$retVal[0]['currency_id'].'"';
								$currency_result = $db->query($select1);
								$retVal1 =$currency_result->FetchAll();
								
								
							  // get symbol with price $retVal1[0]['currency_sign'];
							   $response['Library'][$key]['price']=$retVal1[0]['currency_sign']." ".$response['Library'][$key]['price'];
								
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								
								
								
								$response['Library'][$key]['ProductThumbnail']=$thumbnail_path;
								$response['Library'][$key]['Producturl']="http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response['Library'][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response['Library'][$key]['author_name']="";	
							else						
							$response['Library'][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response['Library'][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response['Library'][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
								$response['Library'][$key]['rating']=$rs[0];	
							}
							}
							else
							{
								$response['Library'][$key]['rating']=0;
							}
							
							$response['Library'][$key]['books_status']="Yes";		
							if(strpos($implode_array_of_books,$Parray['id']))
								{
									 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_user[0]['group_id']."'");
									 $result_group_name = mysql_fetch_array($get_group_name);
									 $response['Library'][$key]['is_free']="true";
									 $response['Library'][$key]['group_name']= $result_group_name['group_name'];
								}
								else
								{
									$response['Library'][$key]['is_free']="false";
									$response['Library'][$key]['group_name']= "";
								}	



							
							}
							
							
							
							$response['Message'] = "Success.";
							$response['error'] = "false";
				 
						}
						else
						{
						 	 //$response = '[{"Error": {"Message":"No New Arrivals Found.","error":"true"}}]';
							 $response['Message'] = "No Books in library.";
							 $response['error'] = "true";
						 
						}
						$response = stripslashes(json_encode($response, JSON_HEX_APOS));
					
					echo $response;
				break;
				
				
				
				
				
					case "GetFreeLibrary":
					 
					if($jsonObj->UserId!='')
					{
						$query_user = "select * from pclive_companies where id = '".$jsonObj->UserId."' and parent_id!=0 and group_id!=0";
						$exe_user = $db->query($query_user);						
						$result_user = $exe_user->FetchAll();

					if(count($result_user)>0)
						 
			 		{
						
					    $query_group_name = "select * from pclive_companies where id = '".$jsonObj->UserId."' and parent_id!=0 and group_id!=0";
						$exe_group = $db->query($query_group_name);						
						$result_group = $exe_group->FetchAll();
						
						// Get group name from group id
						 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_group[0]['group_id']."'");
						 $result_group_name = mysql_fetch_array($get_group_name);
									
									
								
						
						
						$query_free_book = "select bookid from  pclive_credit_history where userid = '".$result_user[0]['parent_id']."' ";
						$exe_free_book = $db->query($query_free_book);						
						$result_book= $exe_free_book->FetchAll();
						
						
						$book_id_arr=array();
						foreach($result_book as $key1=>$book)
							{
								
							$book_id_arr[]=	$book['bookid'];
								
							}
						
						$bookstr=implode(",",$book_id_arr);
					
					
					if(count($result_book)>0)
					{
					
					$main_array = array();		
					$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' AND prod.file_name!='' and prod.id in($bookstr)  ORDER BY prod.cat_id ASC";
					
				
					
					$result = $db->query($sql);
					$records = $result->FetchAll();
					
				     	if(count($records)>0)
						{
							  
							$response[$result_group_name['group_name']]=$records;
							
							foreach($records as $key=>$Parray)
							{
								$book_thumb_qry='SELECT * from  pclive_product_images where product_id="'.$Parray['product_id'].'" order by id desc LIMIT 1';
								$boothumb_result = $db->query($book_thumb_qry);
								$book_thumb_record = $boothumb_result->FetchAll();
								$book_thumb_info=$book_thumb_record[0];
								
							   
								if($book_thumb_info['image_name_thumb']!='')
								{
								$thumbnail_path=$this->view->serverUrl().$this->view->baseUrl()."/".USER_UPLOAD_DIR.$book_thumb_info['image_name_thumb'];
								}
								else
								{
								$thumbnail_path= "";
								}
								
								$bookname = str_replace(" ","",$Parray['title']);
								if(is_numeric($Parray['title']))
								{
								 $bookbrand="select * from pclive_brands where id='".$Parray['title']."' ";	
								 
								$bookbrand_result = $db->query($bookbrand);
								$bookbrand_record = $bookbrand_result->FetchAll();
								$bookbrand_info=$bookbrand_record[0];	
								$bookname= str_replace(" ","",$bookbrand_info['brand']);	
								}
								
								
								
								
								$response[$result_group_name['group_name']][$key]['ProductThumbnail']=$thumbnail_path;
								$response[$result_group_name['group_name']][$key]['Producturl']="http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/download/index/apicall/Bookdownload/apikey/".$jsonObj->apikey."/bookid/".$Parray['product_id']."/bookname/".$bookname;
								
							
							
							
							//code to get publisher name from user table
							$pub_qry='SELECT publisher from  pclive_users where id="'.$Parray['publisher_id'].'" order by id desc LIMIT 1';
							$pub_result = $db->query($pub_qry);
							$pub_record = $pub_result->FetchAll();
							$pub_info=$pub_record[0];							
							$response[$result_group_name['group_name']][$key]['publisher_name']=$pub_info['publisher'];
									
							//code to get author name from user table
							$author_qry='SELECT first_name from  pclive_users where id="'.$Parray['author_id'].'" order by id desc LIMIT 1';
							$author_result = $db->query($author_qry);
							$author_record = $author_result->FetchAll();							
							$author_info=$author_record[0];	
							
							
							if($author_info['first_name']=="")
							$response[$result_group_name['group_name']][$key]['author_name']="";	
							else						
							$response[$result_group_name['group_name']][$key]['author_name']=$author_info['first_name'];	
							
							
							
							$review_qry='SELECT  avg(rating*1) as rate,`productid` FROM pclive_review where productid="'.$Parray['product_id'].'" group by productid';
							$review_result = $db->query($review_qry);
							$review_record = $review_result->FetchAll();
							$review_info=$review_record[0];
							
							if(count($review_record)>0)
							{
							$rs=explode(".",$review_info['rate']);
							if($rs[1]!='')
							{
								if(".".$rs[1]>.50)
								{
							    $response[$result_group_name['group_name']][$key]['rating']=$rs[0]+1;
								}
								else
								{
								$response[$result_group_name['group_name']][$key]['rating']=$rs[0]+0.50;	
								}
							}
							else
							{
								$response[$result_group_name['group_name']][$key]['rating']=$rs[0];	
							}
							}
							else
							{
								$response[$result_group_name['group_name']][$key]['rating']=0;
							}
							
							$response[$result_group_name['group_name']][$key]['books_status']="Yes";		
							
									 $get_group_name = mysql_query("select * from pclive_company_groups where id= '".$result_user[0]['group_id']."'");
									 $result_group_name = mysql_fetch_array($get_group_name);
									 $response[$result_group_name['group_name']][$key]['is_free']="true";
									 $response[$result_group_name['group_name']][$key]['group_name']= $result_group_name['group_name'];
								



							
							}
							
							
							
							$response['Message'] = "Success.";
							$response['error'] = "false";
				 
						}
						
					}
					else
					{
					 $response['Message'] = "No books are free.";
					 $response['error'] = "true";	
					}
						
						}
						
						else
						{
						     $response['Message'] = "You are not a group user.";
							 $response['error'] = "true";
							
						}
						
						
						
					  }
						else
						{
						 	 //$response = '[{"Error": {"Message":"No New Arrivals Found.","error":"true"}}]';
							 $response['Message'] = "User Id does not exist.";
							 $response['error'] = "true";
						 
						}
						$response = stripslashes(json_encode($response, JSON_HEX_APOS));
					
					   echo $response;
				       break;
				
				
				
				
				default:
				echo "Sorry! Invalid Method Call";
			}

		}
		else
		{
		    echo "Invalid Request Format";
		}
			
		exit;
    }
	
	
	
	
	
    
    public function putAction()
    {
        $this->getResponse()
            ->appendBody("From putAction() updating the requested article");

    }
    
    public function deleteAction()
    {
        //$this->_helper->layout->disableLayout();
		$this->getResponse()->appendBody("From deleteAction() deleting the requested article");

    }
	
	public function apicallAction()
    {
         ///$this->getResponse()->appendBody("From indexAction() returning all articles");
			
	}
	/* new work starts from here*/
	 
  /*new work ends here*/
	
}

?>