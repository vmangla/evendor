package com.evendor.webservice;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpConnectionParams;
import org.apache.http.params.HttpParams;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONObject;

import android.app.ProgressDialog;
import android.content.Context;
import android.net.ConnectivityManager;
import android.util.Log;

import com.evendor.Android.R;

public class WebServiceBase 
{	
	public interface ServiceResultListener
	{
		public void onSuccess(String result);
		public void onError(String errorMessage);
	}
	
	/**@author MIPC27
	  * changed url http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/ to http://www.evendornigeria.com/ below on 30-jun-2014
	  */
	
	/*public static String BASE_URL1 = "http://miprojects2.com.php53-6.ord1-1.websitetestlink.com/";
	//public static String BASE_URL = "http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/";
	public static String BASE_URL = BASE_URL1+"projects/evendor/api/index/apicall/";*/
	public static String BASE_URL1 ="http://www.miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall";
//	public static String BASE_URL1 = "http://www.evendornigeria.com/";
	public static String BASE_URL = BASE_URL1+"/";
	//public static String BASE_URL = BASE_URL1+"api/index/apicall/";comment by arvind and add above line
	

	//public static String BASE_URL = "http://coppermobile.net/CienaEvents/webservices/";
	//public static String BASE_URL = "http://coppermobile.net/cmevents/";
	//public static String BASE_URL = "http://192.168.8.65:8888/project_from_svn/cmevents/webservices_testing/";
	public static String SOURCE = "android";
	public static String SERVICE_EXCEPTION = "-389";
	public static String NETWORK_EXCEPTION = "-390";
	private Context cContext;	
	final int CONN_WAIT_TIME = 3000;
	final int CONN_DATA_WAIT_TIME = 5000;
	
	//private ProgressDialog dialog;
	
	ServiceResultListener resultListener;
	
	String ResponseMessage;
	int ResponseCode;
	
	String urlString;
	public JSONObject responseInJSONObject;		//If response is in 
	JSONArray 	responseInJSONArray;
	
	final int timeOut = 60 * 1000;	
	HttpResponse responseStatusFromService;
	
	String serviceResponseString;
	
	
	public WebServiceBase(Context context,String urlString)
	{
		this.urlString = urlString;
		this.cContext = context;
	}
	
	public void setServiceResultListener(ServiceResultListener listener)
	{
		resultListener = listener;
	}
	
//	public String getTokenId()
//	{
//		return new AppSettings(cContext).getString(AppSettings.TOKEN_ID);
//	}
	
	public void executeGet(final String urlString)
	{

		final ProgressDialog dialog = new ProgressDialog(this.cContext);
		dialog.setMessage(cContext.getResources()
				.getString(R.string.dialog_please_wait));
		//dialog.setCancelable(false);
		dialog.show();

		if(checkInternetConnection())
		{
			new Thread(new Runnable() {
				
				public void run() 
				{
					try
					 {
							
				        	Log.d("Web service URL: ", urlString);
				        	
				        	try {
				        		HttpParams httpParams = new BasicHttpParams();      
				        		HttpConnectionParams.setConnectionTimeout(httpParams, CONN_WAIT_TIME);
				        		HttpConnectionParams.setSoTimeout(httpParams, CONN_DATA_WAIT_TIME);
				    		    HttpClient client = new DefaultHttpClient(httpParams);  
				    		    HttpGet get = new HttpGet(urlString);
				    		    responseStatusFromService = client.execute(get);  
				    		
				    		} catch (Exception e) {
				    		    e.printStackTrace();
				    		}
				       

				        	
				        	ResponseCode=responseStatusFromService.getStatusLine().getStatusCode();
					 		ResponseMessage=responseStatusFromService.getStatusLine().getReasonPhrase();
					 		
					 		Log.d("Web service ResponseMessage: ", ResponseMessage);
					 		
					 		if(responseStatusFromService.getStatusLine().getStatusCode() == HttpStatus.SC_OK)
					        {
					 			ResponseMessage="OK";		            
					 			HttpEntity resEntity = responseStatusFromService.getEntity();
					 			serviceResponseString =EntityUtils.toString(resEntity);
					        	
					 			Log.e("Web service ResponseString: ", serviceResponseString);
					 			
					 			if(resultListener != null)
					 			{
					 				resultListener.onSuccess(serviceResponseString);
					 			}
					        }
					 		else
					 		{
					 			
					 			if(resultListener != null)
						 		 {
					 				/**@author MIPC27
					 				 * changed the line from commented to below as 404 or any other error is not been added in any else 
					 				 * condition in listener onError()
					 				 */
					 				try{
					 					resultListener.onError(SERVICE_EXCEPTION);	
					 				}catch(Exception e){
					 					
					 				}
					 				
									// resultListener.onError( ResponseMessage + "-" + ResponseCode);
						 		 }
					 		}
					 		//WebServiceBase.this.dialog.dismiss();
				        
					 }		 
					 catch (Exception e)
					 {
						 //WebServiceBase.this.dialog.dismiss();
						 e.printStackTrace();
						 Log.d("Web service error: ",e.toString());
						 
						 if(resultListener != null)
				 		 {
							 resultListener.onError(SERVICE_EXCEPTION);
				 		 }
					 }
					 
					dialog.dismiss();
				}
				
			}).start();
		}
		else
		{
			dialog.dismiss();
			resultListener.onError(NETWORK_EXCEPTION);
		}
		 
	}	
	
	private void getJSONResponse(String JSONString) throws Exception
	{
		if(Character.toString( JSONString.charAt(0)).equals("["))
	 	{	 
	 		responseInJSONArray=new JSONArray(JSONString.toString() );
	  		Log.d(" web service output", responseInJSONArray.toString());
	 	}
	 	else if(Character.toString( JSONString.charAt(0)).equals("{"))
	 	{
	 		responseInJSONObject=new JSONObject(JSONString.toString() );
	 		Log.d(" web service output", responseInJSONObject.toString());
	 	}
	 	else
	 	{
	 		Log.d("web service Parsing Error", "not a Json Response");
	 	}
	}
	 
	
	
	
	
	public static class KeyValuePair
	{
		String key;
		String value;
		
		public KeyValuePair(String key, String value)
		{
			this.key=key;
			this.value=value;
		}
		
		public String keyValuePairInUTF8Encoding() throws UnsupportedEncodingException
		{
			 String keyInUTF8Encoding=URLEncoder.encode(this.key, "UTF-8");
			 String valueInUTF8Encoding=URLEncoder.encode(this.value,"UTF-8");
			 String encodedKeyValuePair=keyInUTF8Encoding+"="+valueInUTF8Encoding;
			 Log.d("Web Service keyValuePair:",encodedKeyValuePair);
			 return encodedKeyValuePair;
		}
		@Override
		public String toString()
		{
			return this.key+"="+this.value;
		}
	}
	
	private boolean checkInternetConnection() {
		ConnectivityManager conMgr = (ConnectivityManager)cContext
				.getSystemService(Context.CONNECTIVITY_SERVICE);
		if ((conMgr.getActiveNetworkInfo() != null)
				&& conMgr.getActiveNetworkInfo().isAvailable()
				&& conMgr.getActiveNetworkInfo().isConnectedOrConnecting())
			return true;
		else
			return false;
		
	}
	
}
