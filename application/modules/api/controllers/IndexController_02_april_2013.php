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
		$apiparameters=$this->view->apiparams();
		//print_r($apiparameters);
		
		if(!array_key_exists('apicall',$apiparameters))
		{
			echo"{'error':'No method was called!'}";
		}
		elseif(!array_key_exists('apikey',$apiparameters))
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
				
				echo "<pre>";
				print_r($response);
				//print_r(json_decode($response));
				echo "</pre>";
				//exit;
				
			}
			else
			{
				echo"{'apikey_error':'Invalide Apikey'}";
				//exit;
			}
		}	
	}
    
    public function postAction()
    {
	   
	    // Database Object
	    $db = Zend_Registry::get('db');
		
		$formData=$this->getRequest()->getPost();
		//print_r($formData);			
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
				
					$xml = simplexml_load_string($formData['xmldata']);
				
					if($xml===false) 
					{
					  
						 $response = '<?xml version="1.0" encoding="utf-8"?>
								<Error>
									<Message>Error : Invalid XML Format</Message>
								</Error>';
								
					     echo $response;
						
					} 
					else 
					{
					    
						$sql = 'SELECT * FROM pclive_users where username="'.$xml->Username.'"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						  
						    $response = '<?xml version="1.0" encoding="utf-8"?>
								<Error>
									<Message>Error : Username already exists</Message>
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
							   
								$response = '<?xml version="1.0" encoding="utf-8"?>
								<Error>
									<Message>Error : Email already exists</Message>
								</Error>';
								
					            echo $response;
								
							}
							else
							{   
								$sql = "INSERT INTO pclive_users set 
								user_type    =	'".$xml->Usertype."', 
								username     =	'".$xml->Username."',
								emailid      =	'".$xml->Email."',
								password     =	'".$xml->Password."',
								first_name   =	'".$xml->Firstname."',
								last_name    =	'".$xml->Lastname."',
								location     =	'".$xml->Location."',
								city         =	'".$xml->City."',
								state        =	'".$xml->State."',
								country      =	'".$xml->Country."',
								pincode      =	'".$xml->Pincode."',
								phone        =	'".$xml->Phone."',
								profile_status ='".$xml->Status."',  
								add_time     = '".$xml->Jointime."'";
								$result = $db->query($sql);
								
								if($result)
								{
									  $response = '<?xml version="1.0" encoding="utf-8"?>
									   <Success>
										<Message>Message : New user registered successfully!.</Message>
									   </Success>';
									
									   echo $response; 
								}
								else
								{
								       $response = '<?xml version="1.0" encoding="utf-8"?>
									    <Error>
										<Message>Error : Error occur in registration. Try again later.</Message>
									   </Error>';
									
									   echo $response; 
								}
							
							}   // Check Email Exists
							
						}   // Check User Exists
				
					} // Validate XML
					
				break;
				
				case "UserDetail":
				    if(!empty($jsonObj->id) && $jsonObj->id>0) 
					{
						$sql = 'SELECT * FROM pclive_users where id="'.$jsonObj->id.'"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						   
							$response = '[{"UserDetail":{
							"Usertype" 	: "'.$record[0]['user_type'].'",
							"Username" 	: "'.$record[0]['username'].'",
							"Email"	   	: "'.$record[0]['emailid'].'",
							"Password" 	: "'.$record[0]['password'].'",
							"Firstname" : "'.$record[0]['first_name'].'",
							"Lastname"	: "'.$record[0]['last_name'].'",
							"Jointime"	: "'.$record[0]['add_time'].'"
							}}]';
							
							echo $response;
						}
						else
						{
						   
						  $response = '{"Error":{"Message":"Invalid User Id."}}';
						   echo $response; 	
						   
						}
					} 
					else 
					{
					  $response = '{"Error":{"Message":"Invalid User Id."}}';
						echo $response;  
					} // Validate XML	
					    
				break;
				
				case "UserDetailsUpdate":
				
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
					    
						$sql = 'SELECT * FROM pclive_users where id="'.$xml->Userid.'"';
						$result = $db->query($sql);
						$record = $result->FetchAll();		
					
						if(count($record)>0)
						{
						    $sql = "update pclive_users set 
								user_type    =	'".$xml->Usertype."', 
								first_name   =	'".$xml->Firstname."',
								last_name    =	'".$xml->Lastname."',
								location     =	'".$xml->Location."',
								city         =	'".$xml->City."',
								state        =	'".$xml->State."',
								country      =	'".$xml->Country."',
								pincode      =	'".$xml->Pincode."',
								phone        =	'".$xml->Phone."' where id='".$xml->Userid."'";
								$result = $db->query($sql);
								
								if($result)
								{
									  $response = '<?xml version="1.0" encoding="utf-8"?>
									   <Success>
										<Message>Message : User information updated successfully!.</Message>
									   </Success>';
									
									   echo $response; 
								}
								else
								{
								       $response = '<?xml version="1.0" encoding="utf-8"?>
									    <Error>
										<Message>Error : Error occur in infomation update. Try again later.</Message>
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