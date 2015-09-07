package com.evendor.webservice;

import java.io.IOException;
import java.util.ArrayList;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.ParseException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import com.evender.parser.Parser;
import com.evendor.Android.ApplicationManager;
import com.evendor.Android.RegistrationScreen;
import com.evendor.Modal.Allcategories;
import com.evendor.Modal.BooksData;
import com.evendor.appsetting.AppSettings;
import com.evendor.utils.Utils;

import android.content.Context;
import android.net.ConnectivityManager;
import android.os.AsyncTask;
import android.util.Log;

public class CommonAsynTask extends AsyncTask<Void, Void, Boolean>
{

	UrlMakingClass urlMakingObject;
	String ResponseMessage;
	int ResponseCode;
	String urlDecidedString;
	String urlStringToHit;
	String makingJsonArrayDecidingString;
	public JSONObject responseInJSONObject;		//If response is in 
	JSONArray 	responseInJSONArray;

	final int timeOut = 60 * 1000;	
	HttpResponse responseStatusFromService;

	String serviceResponseString;

	JSONArray libraryJsonArray;
	JSONArray CategoryLibraryJsonArray;
	
	ArrayList<BooksData> bookDataList=null;
	ArrayList<Allcategories>  allCatList=null;


	private android.app.ProgressDialog progressDialog;
	private OnResponseListener responder;
	private int responseCode;
	Context context;
	JSONObject jsonObject;
	String CountryId;

	/** execute params:
	 * <ul>
	 * <li>Param1: Http Post request url</li>
	 * <li>Param2: Desired response code. Default: 200</li>
	 * <li>Param3: Attempts count. Default: 1 </li>
	 * </ul>
	 */
	public CommonAsynTask(android.app.ProgressDialog progressDialog)
	{
		this.progressDialog = progressDialog;
		this.responder = responder;
	}

	public CommonAsynTask(Context context , String urlDecidedString1 , String countryStoreId)
	{

		this.progressDialog = new ProgressDialog(context, "Please wait...");
		//this.responder = responder;
		this.jsonObject = jsonObject;
		this.context = context;
		this.urlDecidedString = urlDecidedString1;
		AppSettings appSetting  = new AppSettings(context);
		if(!appSetting.getString("CountryId").equals(""))
		{
			CountryId = appSetting.getString("CountryId");
		}
		else
		{
			CountryId = "226";
		}

		if(countryStoreId != null)
		{
			CountryId = countryStoreId ;
		}


		//	urlMakingObject= new UrlMakingClass(context);

		if(urlDecidedString.equals("default"))
		{
			makingJsonArrayDecidingString = "Library";
			//urlStringToHit ="http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetLibrary/StoreId/"+ CountryId +"/apikey/998905797b8646fd44134910d1f88c33";
			urlStringToHit =WebServiceBase.BASE_URL+"GetLibrary/StoreId/"+ CountryId +"/apikey/998905797b8646fd44134910d1f88c33";
		}
		else if(urlDecidedString.equals("NewArrivals"))
		{

			makingJsonArrayDecidingString = "Newarrivals";
			//urlStringToHit = "http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetNewarrivals/StoreId/"+ CountryId +"/apikey/998905797b8646fd44134910d1f88c33";
			urlStringToHit = WebServiceBase.BASE_URL+"GetNewarrivals/StoreId/"+ CountryId +"/apikey/998905797b8646fd44134910d1f88c33";
		}else if(urlDecidedString.equals("Featured"))
		{

			makingJsonArrayDecidingString = "Featured";
			//urlStringToHit = "http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetOnlyFeatured/apikey/998905797b8646fd44134910d1f88c33/StoreId/"+ CountryId +"/UserId/104";
			urlStringToHit = WebServiceBase.BASE_URL+"GetOnlyFeatured/apikey/998905797b8646fd44134910d1f88c33/StoreId/"+ CountryId +"/UserId/104";
		}
		else if(urlDecidedString.equals("TopSellers"))
		{

			makingJsonArrayDecidingString = "Bestsellers";
			//urlStringToHit = "http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetBestsellers/StoreId/"+ CountryId +"/apikey/998905797b8646fd44134910d1f88c33";
			urlStringToHit = WebServiceBase.BASE_URL+"GetBestsellers/StoreId/"+ CountryId +"/apikey/998905797b8646fd44134910d1f88c33";
		}

	}




	@Override
	protected void onPreExecute() 
	{
		//	progressDialog.setCancelable(true);
		progressDialog.show();
	}

	@Override
	protected Boolean doInBackground(Void... params) {
		int desiredCode = 200;
		int attemptsCount;
		responseCode = 0;

		if(checkInternetConnection())
		{

			try {
				HttpClient client = new DefaultHttpClient();  
				HttpGet get = new HttpGet(urlStringToHit);
				responseStatusFromService = client.execute(get);  

			} catch (Exception e) {
				e.printStackTrace();
			}
			ResponseCode=responseStatusFromService.getStatusLine().getStatusCode();
			ResponseMessage=responseStatusFromService.getStatusLine().getReasonPhrase();
			Log.d("Web service ResponseMessage: ", ResponseMessage);
			if(ResponseCode == HttpStatus.SC_OK)
			{
				ResponseMessage="OK";		            
				HttpEntity resEntity = responseStatusFromService.getEntity();
				try {
					serviceResponseString =EntityUtils.toString(resEntity);
				} catch (ParseException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

				Log.d("Web service ResponseString: ", serviceResponseString);
				JSONObject responseInJSONObject=null;

				try {
					responseInJSONObject=new JSONObject(serviceResponseString);
					if(makingJsonArrayDecidingString.equals("Library"))
					{
						libraryJsonArray =responseInJSONObject.getJSONArray("Library");
					}
					else if(makingJsonArrayDecidingString.equals("Newarrivals"))
					{
						libraryJsonArray =responseInJSONObject.getJSONArray("Newarrivals");
					}
					else if(makingJsonArrayDecidingString.equals("Featured"))
					{
						libraryJsonArray =responseInJSONObject.getJSONArray("Featured");
					}
					else if(makingJsonArrayDecidingString.equals("Bestsellers"))
					{
						libraryJsonArray =responseInJSONObject.getJSONArray("Bestsellers");
					}
					
					
//					CategoryLibraryJsonArray =responseInJSONObject.getJSONArray("Allcategories");
					bookDataList=Parser.getArrayBooks(libraryJsonArray);
					allCatList=Parser.getAllCategory(CategoryLibraryJsonArray);




				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}


			}

			return true;

		}
		else
		{

			if (this.progressDialog.isShowing()) {
				this.progressDialog.dismiss();
			}


			return false;
		}



	}

	@Override
	protected void onPostExecute(Boolean result) {
		if (this.progressDialog.isShowing()) {
			this.progressDialog.dismiss();
		}
		if(result)
		{

			responder.onAsynTaskSuccess();
		}

		else
		{
			Utils.ConnectionErrorDialog(context);
			responder.onAsynTaskFailure(Integer.toString(responseCode));
		}
	}


	public void setListener(OnResponseListener listener){
		responder = listener;
	}

	public class ProgressDialog extends android.app.ProgressDialog
	{
		public ProgressDialog(Context context, String progressMessage) 
		{
			super(context);
			setCancelable(false);
			setMessage(progressMessage);
		}

	}


	public JSONArray getLibraryJsonArray()
	{
		return libraryJsonArray;
	}

	public JSONArray getCategoryLibraryJsonArray()
	{
		return CategoryLibraryJsonArray;
	}



	public ArrayList<BooksData> getBooksData(){

		return bookDataList;

	}

	public ArrayList<Allcategories> getAllCetagory(){

		return allCatList;

	}

	private boolean checkInternetConnection() {
		ConnectivityManager conMgr = (ConnectivityManager)context
				.getSystemService(Context.CONNECTIVITY_SERVICE);
		if ((conMgr.getActiveNetworkInfo() != null)

				&& conMgr.getActiveNetworkInfo().isAvailable()
				&& conMgr.getActiveNetworkInfo().isConnectedOrConnecting())
			return true;
		else
			return false;

	}
}