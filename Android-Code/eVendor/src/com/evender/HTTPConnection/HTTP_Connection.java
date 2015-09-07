package com.evender.HTTPConnection;

import java.io.IOException;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.ParseException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;

import android.content.Context;
import android.net.ConnectivityManager;

public class HTTP_Connection {
	String ResponseMessage;
	int ResponseCode;
	Context mContext;
	String response=null;
	
	public HTTP_Connection(Context cntxt){
		mContext=cntxt;
		
	}
	public String getresponseData(String url){
		
		HttpResponse responseStatusFromService;

		try {
			HttpClient client = new DefaultHttpClient();  
			HttpGet get = new HttpGet(url);
			responseStatusFromService = client.execute(get);  
			
			ResponseCode=responseStatusFromService.getStatusLine().getStatusCode();
			ResponseMessage=responseStatusFromService.getStatusLine().getReasonPhrase();
			//Log.d("Web service ResponseMessage: ", ResponseMessage);
			
			if(ResponseCode == HttpStatus.SC_OK)
			{
				ResponseMessage="OK";		            
				HttpEntity resEntity = responseStatusFromService.getEntity();
				try {
					response =EntityUtils.toString(resEntity);
				} catch (ParseException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				return response;
			}

		} catch (Exception e) {
			e.printStackTrace();
		}
		
		return response;
		
	}
	
	private boolean checkInternetConnection() {
		ConnectivityManager conMgr = (ConnectivityManager)mContext
				.getSystemService(Context.CONNECTIVITY_SERVICE);
		if ((conMgr.getActiveNetworkInfo() != null)

				&& conMgr.getActiveNetworkInfo().isAvailable()
				&& conMgr.getActiveNetworkInfo().isConnectedOrConnecting())
			return true;
		else
			return false;

	}
}
