package com.evendor.webservice;

import android.content.Context;
import android.util.Log;

public class UrlMakingClass extends WebServiceBase 
{
	private final static String SERVICE_URL= BASE_URL ;
	private final static String SERVICE_URL_LOGIN= BASE_URL +"UserLogin/";// "UserLoginIphone/"; //UserLogin
	private final static String SERVICE_URL_REGISTRATION= BASE_URL + "UserRegistration/" ;
	private final static String SERVICE_URL_BOOKSHELVE= BASE_URL + "UserSubscriptions/" ;
	private final static String SERVICE_URL_LIBRARY= BASE_URL + "GetLibrary/" ;
	private final static String SERVICE_URL_DOWNLOAD= BASE_URL + "Downloads/" ;
	private final static String SERVICE_URL_UPDATE_PROFILE= BASE_URL + "UserUpdateIphone/" ;
	private final static String SERVICE_URL_CHANGE_PASSWORD= BASE_URL + "ChangePwd/" ;
	private final static String SERVICE_URL_COUNTRY= BASE_URL + "GetStores/" ;
	private final static String SERVICE_URL_STRORE= BASE_URL + "GetStores/" ;
	private final static String SERVICE_URL_RATING= BASE_URL + "ReviewRating/" ;
	private final static String SERVICE_URL_PROFILE_DETAIL= BASE_URL + "UserDetailIphone/" ;
	private final static String SERVICE_URL_FORGOTPASSWORD= BASE_URL + "UserForgotPassword/" ;
	private final static String SERVICE_URL_FEATUREDBOOKS= BASE_URL + "GetFeatured/" ;
	private final static String SERVICE_URL_TRANSACTION= BASE_URL + "GetPurchaseHistory/" ;
	private final static String SERVICE_URL_PURCHASEITEM= BASE_URL + "StoreDownloads/" ;
	public final static String SERVICE_URL_PURCHASE= BASE_URL + "GetPurchaseHistoryNew1/";//"GetPurchaseHistoryNew/" ;
	
	
	//http://miprojects.com.php5-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetCountries/apikey/998905797b8646fd44134910d1f88c33

	public UrlMakingClass(Context context) 
	{
		super(context, SERVICE_URL);
	}
	
	public void login(String emailId, String password,String imei,String deviceID)
	{
		//String urlString  = SERVICE_URL_LOGIN +"EmailId/"+emailId+"/Password/"+password+"/apikey/998905797b8646fd44134910d1f88c33/DeviceId/"+imei+"/reg_id/"+deviceID+"/os_type/0";
		String urlString  = SERVICE_URL_LOGIN +"EmailId/"+emailId+"/Password/"+password+"/apikey/998905797b8646fd44134910d1f88c33/DeviceId/"+imei+"/reg_id/"+deviceID+"/os_type/2";
		Log.e("-----------", "  "+urlString);
		executeGet(urlString);
	}
	
	public void registration(String firstName,String lastName,String emailId, String password,String country)
	{
		String urlString  = SERVICE_URL_REGISTRATION +"FirstName/"+firstName+"/LastName/"+lastName+"/EmailId/"+emailId+"/Password/"+password+"/Country/"+country+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	
	public void bookSheleve(String Id)
	{
		String urlString  = SERVICE_URL_BOOKSHELVE +"id/"+Id+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void library()
	{
		String urlString  = SERVICE_URL_LIBRARY +"apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	
	public void download(String Id)
	{
		String urlString  = SERVICE_URL_DOWNLOAD +"id/"+Id+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void updateProfile(String Id,String firstName, String lastName, String country)
	{
		
//		apikey/998905797b8646fd44134910d1f88c33/countryid/226/FirstName/ABCD/LastName/XYZ/id/104
		String urlString  = SERVICE_URL_UPDATE_PROFILE +"apikey/998905797b8646fd44134910d1f88c33/"+"countryid/"+country+ "/FirstName/"+firstName+"/LastName/"+lastName+"/id/"+Id;
		executeGet(urlString);
	}
	
	public void changePassword(String Id,String currentPassword, String newPassword )
	{
		String urlString  = SERVICE_URL_CHANGE_PASSWORD +"id/"+Id+ "/NewPassword/"+currentPassword+"/NewConfPassword/"+newPassword +"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
		
	}
	
	public void countryName()
	{
		String urlString  = SERVICE_URL_COUNTRY +"apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void stroreName()
	{
		String urlString  = SERVICE_URL_STRORE +"apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	
	public void ratingTheBook(String userId,String bookId,String rate,String commentText)
	{
		/**@author MIPC27
		 * added a replace line as it crashed url.
		 */
		String comment_temp = commentText.replace(" ", "%20");
				
		String urlString  = SERVICE_URL_RATING +"userid/"+userId+"/bookid/"+bookId+"/rating/"+rate+"/comments/"+comment_temp+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void profileSetting(String userId)
	{
		String urlString  = SERVICE_URL_PROFILE_DETAIL +"id/"+userId+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void forgotPasswordApiCall(String emailId)
	{
		String urlString  = SERVICE_URL_FORGOTPASSWORD +"EmailId/"+emailId+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void featuredBooks(String userId , String storeId)
	{
		String urlString  = SERVICE_URL_FEATUREDBOOKS +"StoreId/"+storeId+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void transactionService(String userId)
	{
		String urlString  = SERVICE_URL_TRANSACTION +"UserId/"+userId+"/apikey/998905797b8646fd44134910d1f88c33";
		executeGet(urlString);
	}
	
	public void purchasedItemService(String userId , String store_id , String bookid)
	{
		String urlString  = SERVICE_URL_PURCHASEITEM +"userid/"+userId+"/StoreId/"+store_id+"/bookid/"+bookid+"/apikey/998905797b8646fd44134910d1f88c33";
		
		 Log.e("qqqqqqqqqq", "*************fgfdgdf****************88 "+urlString);
		executeGet(urlString);
	}
	
	public void purchasedIService(String userId)
	{
		String urlString  = SERVICE_URL_PURCHASE +"UserId/"+userId+"/apikey/998905797b8646fd44134910d1f88c33"+"/type/user";
		executeGet(urlString);
	}
	
	
	

	
	
}
