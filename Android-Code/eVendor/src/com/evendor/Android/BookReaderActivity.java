package com.evendor.Android;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.List;

import nl.siegmann.epublib.domain.Book;
import nl.siegmann.epublib.domain.Resource;
import nl.siegmann.epublib.domain.Spine;
import nl.siegmann.epublib.domain.SpineReference;
import nl.siegmann.epublib.epub.EpubReader;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.res.AssetManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.webkit.WebView;
import com.evendor.Android.R;



	public class BookReaderActivity extends Activity {
		  /** Called when the activity is first created. */
		
		WebView webview;
		Book book;
		String line;
	    String line1;
	    String finalstr;
	    int i;
	    
	     ProgressDialog dialog;
	     
		  @Override
		  public void onCreate(Bundle savedInstanceState) {
		        super.onCreate(savedInstanceState);
		        setContentView(R.layout.book_reader);
		        webview = (WebView)findViewById(R.id.webView);
		        webview.getSettings().setJavaScriptEnabled(true);
		        
//		        dialog = new ProgressDialog(this);
//				dialog.setMessage(getResources()
//						.getString(R.string.dialog_please_wait));
//				//dialog.setCancelable(false);
//				dialog.show();
		        
		        AssetManager assetManager = getAssets();
		        try {
		         
		          InputStream epubInputStream = assetManager
		              .open("testBook.epub");

		         
		           book = (new EpubReader()).readEpub(epubInputStream);
		           
		         
		           new ShowDialogAsyncTask().execute();
		   
		           
		         

		        } catch (IOException e) {
		          Log.e("epublib", e.getMessage());
		        }
		        
		        
		        

		        
		        
		        
		        
		    }
		 
		  
		  
		  public String getEntireBook()
		    {
			  

		        String line, linez = null;
		        Spine spine = book.getSpine();
		        Resource res;
		        List<SpineReference> spineList = spine.getSpineReferences() ;

		        int count = spineList.size();
		        int start = 0;

		        StringBuilder string = new StringBuilder();
		        for (int i = start; count > i; i = i +1) {
		            res = spine.getResource(i);

		                try {
		                    InputStream is = res.getInputStream();
		                    BufferedReader reader = new BufferedReader(new InputStreamReader(is));
		                    try {
		                        while ((line = reader.readLine()) != null) {
		                            linez =   string.append(line + "\n").toString();
		                        }

		                    } catch (IOException e) {e.printStackTrace();}
		                } catch (IOException e) {
		                    e.printStackTrace();
		                }
		        }
		        
		       //  dialog.dismiss();
		        
		     //   webview.loadDataWithBaseURL(" ", linez, "text/html", "UTF-8", "");
		        return linez;
		    }
		  
		  
		 
		  
		  private class ShowDialogAsyncTask extends AsyncTask<Void, Integer, String>{
			  
			     int progress_status;
			      
			     @Override
			  protected void onPreExecute() {
			   // update the UI immediately after the task is executed
			   super.onPreExecute();
			    
			  
			    dialog = new ProgressDialog(BookReaderActivity.this);
				dialog.setMessage("Loading...");
				dialog.setCancelable(false);
				dialog.show();
			    
			    
			  }
			      
			  @Override
			  protected String doInBackground(Void... params) {
			    
				  String lineNumber = getEntireBook();
			   return lineNumber;
			  }
			  
			  @Override
			  protected void onProgressUpdate(Integer... values) {
			   super.onProgressUpdate(values);
			    
			
			   
			    
			  }
			   
			  @Override
			  protected void onPostExecute(String result) {
			   super.onPostExecute(result);
			    
			  
			   webview.loadDataWithBaseURL(" ", result, "text/html", "UTF-8", "");
			   
			   dialog.dismiss();
			    
			  }
			    
		  }
		  
		 
		  
		  }

