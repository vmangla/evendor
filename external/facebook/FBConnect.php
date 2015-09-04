<?php
/**
 * This class is developed by Talib Aziz based on facebook connect example.
 */
require 'src/facebook.php';
class FBConnect
{
	var $_fbAppId='475338292476238';
	var $_fbAppSecret='ad671a0434d9647966f7dce7b1f18295';
	var $_fbLoginRedirectUrl='login.php';
	var $_fbLogoutRedirectUrl='logout.php';
	var $_fbUser=false;
	var $_fbUserProfile=false;
	var $_fbLoginUrl=false;
	var $_fbLogoutUrl=false;
	
	public function FBConnect($pAppId='',$pAppSecret='',$pLoginRedirectUrl='',$pLogoutRedirectUrl='')
	{
		if(isset($pAppId) && $pAppId!=""){$this->_fbAppId=$pAppId;}
		if(isset($pAppSecret) && $pAppSecret!=""){$this->_fbAppSecret=$pAppSecret;}
		if(isset($pAppSecret) && $pAppSecret!=""){$this->_fbAppSecret=$pAppSecret;}
		if(isset($pLoginRedirectUrl) && $pLoginRedirectUrl!=""){$this->_fbLoginRedirectUrl=$pLoginRedirectUrl;}
		if(isset($pLogoutRedirectUrl) && $pLogoutRedirectUrl!=""){$this->_fbLogoutRedirectUrl=$pLogoutRedirectUrl;}
	}
	public function call()
	{
		//echo $this->_fbLoginRedirectUrl;exit;
		// Create our Application instance (replace this with your appId and secret).
		$facebook = new Facebook(array('appId'=>$this->_fbAppId,'secret' => $this->_fbAppSecret,'cookie'=>false,));
		// Get User ID
		$this->_fbUser = $facebook->getUser();
		// We may or may not have this data based on whether the user is logged in.
		// If we have a $user id here, it means we know the user is logged into
		// Facebook, but we don't know if the access token is valid. An access
		// token is invalid if the user logged out of Facebook.
		if($this->_fbUser)
		{
			try 
			{
				// Proceed knowing you have a logged in user who's authenticated.
				$this->_fbUserProfile=$facebook->api('/me');
			} 
			catch(FacebookApiException $e) 
			{
				error_log($e);
				$this->_fbUser = null;
			}
		}
		// Login or logout url will be needed depending on current user state.
		if($this->_fbUser)
		{
		  $this->_fbLogoutUrl=$facebook->getLogoutUrl(array("next"=>$this->_fbLogoutRedirectUrl));
		} 
		else 
		{
			$this->_fbLoginUrl= $facebook->getLoginUrl(array('redirect_uri'=>$this->_fbLoginRedirectUrl));
			//echo $this->_fbLoginUrl;exit;
		}
	}
	public function clearSession()
	{
		$this->_fbUser=false;
		$this->_fbUserProfile=false;
		$this->_fbLoginUrl=false;
		$this->_fbLogoutUrl=false;
		unset($_SESSION['fb_'.$this->_fbAppId.'_code']);
		unset($_SESSION['fb_'.$this->_fbAppId.'_access_token']);
		unset($_SESSION['fb_'.$this->_fbAppId.'_user_id']);
		unset($_SESSION['fb_'.$this->_fbAppId.'_state']);
	}
}
?>
