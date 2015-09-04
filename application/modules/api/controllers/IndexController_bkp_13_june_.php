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
							
							if(count($record)>0)
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
								
								$sql = "INSERT INTO pclive_companies (parent_id,group_id,first_name,last_name,account_type,user_name,user_email,user_password,country,status,added_date,updated_date)VALUES('0','0','".$jsonObj->FirstName."','".$jsonObj->LastName."','2','$user_name','".$jsonObj->EmailId."','".$jsonObj->Password."','".$jsonObj->Country."','1', NOW(),NOW())";
															
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
						$sql = 'SELECT * FROM pclive_companies where user_email="'.$jsonObj->EmailId.'" AND user_password="'.$jsonObj->Password.'" AND parent_id="0" and status=1 AND account_type<>"3"';
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
							"error":"false"
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
						$sql="SELECT prod.*,c.category_name,g.genre,storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id=storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id  WHERE prod.admin_approve='1' AND storeprice.country_id='$jsonObj->StoreId' ORDER BY prod.cat_id ASC";
					}
					else
					{
						$sql="SELECT prod.*,c.category_name,g.genre, storeprice.product_id, storeprice.country_id, storeprice.language_id, storeprice.price FROM pclive_products as prod INNER JOIN pclive_product_prices as storeprice ON prod.id = storeprice.product_id INNER JOIN pclive_categories c on c.id=prod.cat_id INNER JOIN pclive_genres g on prod.product_type=g.id WHERE prod.admin_approve='1' ORDER BY prod.cat_id ASC";
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
								
							}
											   
						   /*echo"<pre>";
						   print_r($response);
						   */
						    
						   
						   $get_all_categories = "select * from pclive_genres where status='1'";
						   $res_all_categories = $db->query($get_all_categories);
						   $data_categories =  $res_all_categories->FetchAll();
						   $response['Allcategories'] = $data_categories;
						   
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
				
				case "Categories":
					$get_all_categories = "select * from pclive_categories where status='1'";
					$res_all_categories = $db->query($get_all_categories);
					$data_categories =  $res_all_categories->FetchAll();
					$response['Allcategories'] = $data_categories;
					
					$response= json_encode($response);
					
					echo $response;	
					
				break;
				
				
				case "GetCountries":
					$get_all_countries = "select * from pclive_country where status='1'";
					$res_all_countries = $db->query($get_all_countries);
					$data_countries =  $res_all_countries->FetchAll();
					$response['Allcountries'] = $data_countries;
					
					$response= json_encode($response);
					
					echo $response;	
					
				break;
				
				
				case "StoreDownloads":
					
					if(!empty($jsonObj->userid) && $jsonObj->userid>0) 
					{

						if(!empty($jsonObj->bookid) && $jsonObj->bookid>0) 
						{
							$chk_review = $db->query("select * from pclive_credit_history where userid='".$jsonObj->userid."' and bookid='".$jsonObj->bookid."'");
							$records_review = $chk_review->FetchAll();	
							
							if(count($records_review)==0)
							{
								$get_prname = $db->query("select * from pclive_products where id='".$jsonObj->bookid."'");
								$records_prname = $get_prname->FetchAll();	
								
								
								$sql="insert into pclive_credit_history set userid='".$jsonObj->userid."',bookid='".$jsonObj->bookid."',price='".$jsonObj->price."',book_name='".$records_prname[0]['title']."',add_date=now()";
							
								$result = $db->query($sql);
						
							 
								$response = '[{"BookResponse":{
								  "Success":"Book downloaded Successfully"
								  }}]';
								echo $response;
							}
							else
							{
								$response = '[{"Error":{"Message":"You have already downloaded this book."}}]';
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
						
							 
								$response = '[{"ReviewResponse":{
								  "Success":"Review Added Successfully"
								  }}]';
								echo $response;
							}
							else
							{
								$response = '[{"Error":{"Message":"You have already given your review for this book."}}]';
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
							
							$sql = "UPDATE pclive_companies SET first_name='$jsonObj->FirstName', last_name='$jsonObj->LastName', country='".$data_country[0]['id']."'  where id='$jsonObj->id'";
							
						 
							
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
				
				
				
				
				
				case "ChangePwd":
				
					if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{	
						$Errorresponse="";
						if(!(isset($jsonObj->OldPassword)) || trim($jsonObj->OldPassword)=="" || !(isset($jsonObj->NewPassword)) || trim($jsonObj->NewPassword)=="")
						{
							//$Errorresponse='[{"ParameterMissing":{';
							if(!(isset($jsonObj->OldPassword)) || trim($jsonObj->OldPassword)=="") 
							{
								
								
								
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter Your Current Password" }';
								 
							}
							
							
							
							
							if(!(isset($jsonObj->NewPassword)) || trim($jsonObj->NewPassword)=="") 
							{ 
								$Errorresponse.='{"Message":"Unsuccess", "Error":"Please Enter New Password" }';							 
							}
							 
							//$Errorresponse.='}}]';
						}	
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
						if(!empty($Errorresponse)) 
						{
							echo $Errorresponse;
						} 
						else 
						{
					    	$sql = "UPDATE pclive_companies SET user_password='$jsonObj->NewPassword' where id='".$jsonObj->id."'";
							$result = $db->query($sql);
														
							if($result)
							{
								   $response.='{"Message":"Success", "UpdationResponse":"Password Changed Successfully" }';
								  
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
					$response = '[{"Error":{"Message":"User Id does not exist"}}]';
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
							
							<p>Your login details given below :<br></p>
							<p>Username:&nbsp;'.$record[0]['user_email'].'</p>
							<p>Password:&nbsp;'.$record[0]['user_password'].'</p>
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
	
}

?>