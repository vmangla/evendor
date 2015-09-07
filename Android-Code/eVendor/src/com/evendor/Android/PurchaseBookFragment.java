package com.evendor.Android;

import java.io.File;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ActionBar;
import android.app.Activity;
import android.app.NotificationManager;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.text.Html;
import android.text.Spanned;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.Button;
import android.widget.ListView;
import android.widget.ProgressBar;
import android.widget.TextView;

import com.activate.gcm.GCMIntentService;
import com.evender.Constant.Constant;
import com.evendor.adapter.CreditBookListAdapter;
import com.evendor.adapter.PurchaseBookListAdapter;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.utils.CMDialog;
import com.evendor.utils.LibraryEvendorDialog;
import com.evendor.utils.MyIntents;
import com.evendor.utils.StorageUtils;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class PurchaseBookFragment extends Fragment implements ServiceResultListener {
	
	UrlMakingClass urlMakingObject;
	JSONArray purchaseJsonArray;
//	JSONArray creditJsonArray;
	View rootView;
	String bookUrl = null;
    String bookId = null;
    String bookTitle = null;
    String bookTitleWithSpace = null;
    String bookCategory1 = null;
    String bookPrice = null;
    String bookPublisher = null;
    String bookDescription = null;
    String filename = null;
    String bookIconPath = null;
    String bookPath = null;
    String bookAuthor = null;
    String bookPublisherName = null;
    String bookPublishedDate = null;
    String publisherDate;
    Activity activity ;
    String bookSize;
	public static JSONObject responseInJSONObject = null;
    private ProgressBar prog;
	
	private SQLiteDatabase db;
   	private DBHelper dbHelper             =null;
   	
   	TextView amountDetail;
   	ListView fragmentList;
   	ListView fragmentList1;
	Button  purchase;
	Button  transaction;
	String userId;
	private String PriceText=null;
//	String total = "0";
//	String balance = "0";
	
	boolean tabSwitcherBoolean = true;
	private ImageLoader imageLoader;
	
	

	@Override
	public void onStart() {
		
		super.onStart();
		 IntentFilter intentFilter = new IntentFilter(Constant.NOTIFICATION_MESSAGE_ACTION);
		
		   getActivity().getApplicationContext().
		       registerReceiver(mHandleMessageReceiver, intentFilter);
	}

	@Override
	public void onStop() {
		  getActivity().getApplicationContext().unregisterReceiver(mHandleMessageReceiver); 
	   super.onStop();
	}
   	
   	public View onCreateView(LayoutInflater inflater, ViewGroup container,Bundle savedInstanceState) 
	{
		 super.onActivityCreated(savedInstanceState);
         rootView= inflater.inflate(R.layout.purchase_fragment, container, false);
//         prog=   (ProgressBar) rootView.findViewById(R.id.progressBar1);
//         prog.setVisibility(View.GONE);
		 return rootView;
	}
	 
	 @Override
	    public void onAttach(Activity activity) {
	        super.onAttach(activity);
	        try {
	           
	        	
	        	
	        } catch (ClassCastException e) {
	            throw new ClassCastException(activity.toString() + " must implement OnArticleSelectedListener");
	        }
	    }

   
   @Override
   public void onActivityCreated(Bundle savedInstanceState) {
       super.onActivityCreated(savedInstanceState);
        activity = getActivity();
       
       if (activity != null) {
    	   imageLoader = new ImageLoader(getActivity());
    	   fragmentList = (ListView) activity.findViewById(R.id.commonListView);
    	   fragmentList1 = (ListView) activity.findViewById(R.id.commonListView1);
    	   purchase =(Button) activity.findViewById(R.id.purchase); 
    	   transaction =(Button) activity.findViewById(R.id.transaction);
    	   amountDetail =(TextView) activity.findViewById(R.id.amountDetail);
//    	   urlMakingObject= new UrlMakingClass(activity);
//   		   urlMakingObject.setServiceResultListener(this);
    	   refresh();
   		   AppSettings appSetting  = new AppSettings(activity);
		   userId = appSetting.getString("UserId");
		   
//		   urlMakingObject.purchasedIService(userId);
		   
//		   try{
//		    	if (appSetting.getBoolean("hasNotification")) {
//					//appSetting.clearKey("hasNotification");
//					appSetting.clearKey("msg_count");
//					//appSetting.clearKey("f_list");
//				
//		        }
//		    	  Utils.setBadge(getActivity(), 0);
//		          NotificationManager notificationManager = (NotificationManager)getActivity().getSystemService(Context.NOTIFICATION_SERVICE);
//		          notificationManager.cancel(GcmIntentService.NOTIFICATION_ID);
//		    	}catch(Exception e ){
//		    		e.printStackTrace();
//		    		
//		    	}
   		   
   		 purchase.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				fragmentList.setVisibility(View.GONE);
				fragmentList1.setVisibility(View.VISIBLE);
				CreditBookListAdapter listAdapter = null;
				transaction.setTextColor(Color.parseColor("#00f5ff"));
				 purchase.setTextColor(Color.WHITE);
				
//				 amountDetail.setText("Balance: "+"$" + balance);
//				 if(creditJsonArray !=null)
//				 {
//				  listAdapter = new CreditBookListAdapter(getActivity(), creditJsonArray,creditJsonArray.length());
//				 }
//				 else
//				 {
//					 listAdapter = new CreditBookListAdapter(getActivity(), creditJsonArray, 0);
//				 }
				// fragmentList.removeView(child)
//				 fragmentList1.setAdapter(listAdapter);
				
				
				
//				tabSwitcherBoolean = false;
				//urlMakingObject.purchasedIService(userId);
				
			}
		});
   		
   		 transaction.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				fragmentList.setVisibility(View.VISIBLE);
				fragmentList1.setVisibility(View.GONE);
				PurchaseBookListAdapter listAdapter = null;
				purchase.setTextColor(Color.parseColor("#00f5ff"));
				 transaction.setTextColor(Color.WHITE);
			    
				 fragmentList.setDivider(null);
//				 amountDetail.setText("Total: "+"$"+ total);
				 if(purchaseJsonArray != null)
				 {
		    	  listAdapter = new PurchaseBookListAdapter(getActivity(), purchaseJsonArray,purchaseJsonArray.length(),imageLoader);
				 }
				 else
				 {
				  listAdapter = new PurchaseBookListAdapter(getActivity(), purchaseJsonArray,0,imageLoader);
				 }
		    	 fragmentList.setAdapter(listAdapter);
				 
				  tabSwitcherBoolean = true;
				//  urlMakingObject.transactionService(userId);
				
			}
		});
       	 
    	 /*  getListView().setDivider(null);
    	   PurchaseBookListAdapter listAdapter = new PurchaseBookListAdapter(activity, ApplicationManager.sLocations);
           setListAdapter(listAdapter);*/
       }
       
       
       
       fragmentList.setOnItemClickListener(new OnItemClickListener() {

           

		@Override
           public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
        	   
        	 
            	   
           		if(purchaseJsonArray != null)
           		 {
					try {
						JSONObject jsonObject = purchaseJsonArray.getJSONObject(position);
						bookTitleWithSpace  = jsonObject.getString("title");
 						bookTitle = bookTitleWithSpace.replace(" ", "");
 						bookPublisherName = jsonObject.getString("publisher_name");
 						filename = jsonObject.getString("file_name");
 						if(filename.contains(".pdf")){
 						bookPath = StorageUtils.FILE_ROOT + bookTitle +".pdf";
 						}else{
 							
 							bookPath = StorageUtils.FILE_ROOT + bookTitle +".epub";
 						}
 						
 						bookSize = jsonObject.getString("file_size");
 						bookUrl = jsonObject.getString("Producturl");
 						bookId  = jsonObject.getString("id");
 						bookIconPath  = jsonObject.getString("ProductThumbnail");
 						bookAuthor = jsonObject.getString("author_name");
 						bookPrice =  jsonObject.getString("price");
 						publisherDate = jsonObject.getString("publish_time");
 						bookCategory1 = jsonObject.getString("category_name");
 						bookDescription  = jsonObject.getString("description");
 						PriceText = jsonObject.getString("priceText");
 						bookUrl = bookUrl.replace(" ", "");
 						
 						
 						
 						int rowid = Integer.parseInt(bookId);
 						SQLiteDatabase db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
 					    DBHelper dbHelper = new DBHelper(db, false);
 							if(dbHelper.ItemExistinList(DB.TABLE_DOWNLOADED_BOOK,DB.COLUMN_BOOK_ID, rowid)==1){
 								
 								File file = new File(bookPath);
 								
 								if(file.exists()){
 									file.delete();
 								}
 								
 							}
 						
// 						Log.i("PURCHASED", isPresent(bookId) +"");
 						
 					 if(!isPresent(bookId))
                 	   {
 						showBookDetailDialog(bookTitleWithSpace,bookAuthor,bookPublisherName,publisherDate,Html.fromHtml(bookDescription ),null,bookUrl,null,null
 								,bookPrice,bookSize,bookIconPath,filename,PriceText); 

 						  
                 	   }
 	            	  else
 	            	   {
 	            		    successfullDialog("Book is already downloaded");
 	            	   }
 						
 						
					} catch (JSONException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
					
           	}

              }
              
               
           
           
       });
   }



	private void showBookDetailDialog(final String title,final String author, String publisher,final String publishedDate, Spanned spanned,final Bitmap imageurl,final String path, final String bookPkId , final String bookShelfId
			, String price,final String bookSize,String icon,final String file_name, String PriceText)
	{				
		final LibraryEvendorDialog cmDialog = new LibraryEvendorDialog(getActivity());
		//cmDialog.setRateingBar(bookRate);
		cmDialog.setTitle(title);
		String date = "";    
		imageLoader.DisplayImage(icon, cmDialog.getBookIcon());
		if(publisherDate != null)
		{
			date = ApplicationManager.getconvertdate1(publisherDate);
		}



//		cmDialog.setContent(LibraryEvendorDialog.LIBRARY_BUY_BOOK,title,author, publisher,publishedDate,spanned,price +"\n"+ date,bookSize);
		cmDialog.setContent(title,title,author, publisher,date,spanned,price ,bookSize,PriceText);


		imageLoader.DisplayImage(icon, cmDialog.getBookIcon());
		//	cmDialog.getBookIcon().setImageBitmap(imageurl);

		/*	ImageLoader imageLoader = new ImageLoader(getActivity());
		imageLoader.DisplayImage(path, cmDialog.getBookIcon());*/

		cmDialog.setPossitiveButton(LibraryEvendorDialog.YES, new OnClickListener() 
		{

			public void onClick(View v) 
			{ 



				{
					downloadTheBook();
					
				}
				cmDialog.cancel();			

			}
		});


		cmDialog.setNegativeButton(LibraryEvendorDialog.NO, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});


		cmDialog.show();
	}


@Override
public void onSuccess(final String result) {
	// TODO Auto-generated method stub
	if(result!=null && activity!=null)
		activity.runOnUiThread(new Runnable() {
    	String error = null;
    	JSONArray response = null;
    
    	CreditBookListAdapter listAdapter;
    //	PurchaseBookListAdapter listAdapter1; 
    	
			public void run() 
			{
				
				try {
					responseInJSONObject=new JSONObject(result);
					 
					if(responseInJSONObject.has("Purchase")){
					 purchaseJsonArray =responseInJSONObject.getJSONArray("Purchase");
//				     creditJsonArray =responseInJSONObject.getJSONArray("PurchasePoint");
				     purchase.setTextColor(Color.parseColor("#00f5ff"));
					 transaction.setTextColor(Color.WHITE);
				     fragmentList.setDivider(null);
//				     total = responseInJSONObject.getString("Amount");
//					 amountDetail.setText("Total: "+"$"+total );
				     
					 PurchaseBookListAdapter listAdapter = new PurchaseBookListAdapter(activity, purchaseJsonArray,purchaseJsonArray.length(),imageLoader);
			    	 fragmentList.setAdapter(listAdapter);
			    	 
					}
//					 balance = responseInJSONObject.getString("Balance");
					 
					
					
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
					
//					try {
////						balance = responseInJSONObject.getString("Balance");
//					} catch (JSONException e1) {
//						// TODO Auto-generated catch block
//						e1.printStackTrace();
//					}
					
					/*Toast.makeText(getActivity(), "CAUGHT", Toast.LENGTH_LONG).show();
						 try {
							purchaseJsonArray =responseInJSONObject.getJSONArray("Purchase");
							PurchaseBookListAdapter listAdapter = new PurchaseBookListAdapter(getActivity(), purchaseJsonArray,0);
							// fragmentList.removeView(child)
							 amountDetail.setText("Total: "+"$0");
						     fragmentList.setAdapter(listAdapter);
						} catch (JSONException e1) {
							// TODO Auto-generated catch block
							e1.printStackTrace();
						}
						*/
				}
				
				
			//	successfullDialog(error);
				
			}
		});
	
}


@Override
public void onError(final String errorMessage) {
	// TODO Auto-generated method stub
	if(errorMessage!=null && getActivity()!=null)
	getActivity().runOnUiThread(new Runnable() {
		
		public void run() {
			if(errorMessage.equals("-389"))
			{
				successfullDialog("Network error");
			}
			else if(errorMessage.equals("-390"))
			{
				Utils.ConnectionErrorDialog(getActivity());
			}
		}
	});
	
}


private void successfullDialog(String errorMsg) 
{				
	final CMDialog cmDialog = new CMDialog(getActivity());
	cmDialog.setContent("Transaction", errorMsg);
	cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
	{
		public void onClick(View v) 
		{
			cmDialog.cancel();					
		}
	});
	cmDialog.show();
}


private boolean isPresent(String bookId)
{

   db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
   dbHelper = new DBHelper(db, false);

   Cursor c= dbHelper.checkIfIdisPresent(DB.TABLE_DOWNLOADED_BOOK , bookId); 
   if(c.getCount() !=0)
   {
	   db.close();
       c.close();
	   return true;
   }
   else
   {
	   db.close();
       c.close();
	   return false;
   }
   
}



 
 private void downloadTheBook()
 { 
//	 AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
//     appDataSavedMethod.SaveDownloadedBook(bookTitleWithSpace,bookDescription,bookUrl, bookId, bookIconPath,
//     		bookPath, "",bookAuthor,bookPublisherName,publisherDate,bookSize,"");
//     
//	 Intent downloadIntent = new Intent("com.yyxu.download.services.IDownloadService");
//     downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.ADD);
//     downloadIntent.putExtra(MyIntents.URL, bookUrl);
//   //  downloadIntent.putExtra(MyIntents.BOOK_ID, bookId);
//     downloadIntent.putExtra(MyIntents.BOOK_TITLE, bookTitleWithSpace);
//     downloadIntent.putExtra(MyIntents.BOOK_PRICE, bookPrice);
//     downloadIntent.putExtra(MyIntents.BOOK_ICON_PATH, bookIconPath);
//     
//     getActivity().startService(downloadIntent);
//     
//     ApplicationManager.changeRightFragment(getActivity(), new DownloadBookFragment(),"downloadBookFragment");
//     
	 
	 Intent downloadIntent = new Intent("com.yyxu.download.services.IDownloadService");
		downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.ADD);
		downloadIntent.putExtra(MyIntents.URL, bookUrl);
		//  downloadIntent.putExtra(MyIntents.BOOK_ID, bookId);
		downloadIntent.putExtra(MyIntents.BOOK_TITLE, bookTitleWithSpace);
		downloadIntent.putExtra(MyIntents.BOOK_PRICE, bookPrice);
		downloadIntent.putExtra(MyIntents.BOOK_ICON_PATH, bookIconPath);
		downloadIntent.putExtra("bookDescription", bookDescription);
		downloadIntent.putExtra("bookUrl", bookUrl);
		downloadIntent.putExtra("bookId", bookId);
		downloadIntent.putExtra("bookPath", bookPath);
		downloadIntent.putExtra("bookAuthor", bookAuthor);
		downloadIntent.putExtra("bookPublisherName", bookPublisherName);
		downloadIntent.putExtra("bookPublishedDate", publisherDate);
		downloadIntent.putExtra("bookSize", bookSize);
		downloadIntent.putExtra("bookCategory", bookCategory1);//file_name
		downloadIntent.putExtra("filename", filename);
		downloadIntent.putExtra("priceText", PriceText);
		

		getActivity().startService(downloadIntent);
	/*	Bundle b = new Bundle();
		b.putString("priceText", PriceText);
		Fragment f = new DownloadBookFragment();
		f.setArguments(b);*/
		ApplicationManager.changeRightFragment(getActivity(), new DownloadBookFragment(),"downloadBookFragment");

	 
     StartFragmentActivity.leftPane.setVisibility(View.GONE);
     
     StartFragmentActivity.libraryFilerLayout.setVisibility(View.GONE);
     
     setUpActionBar();
	 
 }
 
 private void setUpActionBar()
	{
	     ActionBar actionBar;
		 actionBar = getActivity().getActionBar();
		 actionBar.setTitle("Downloads"); 
		 
		 ((StartFragmentActivity) getActivity()).changeTheMenuItemIcon(R.id.downloadMenuButton);

	}
 
 
 public void refresh(){
	 
//	 new Task().execute();
	  urlMakingObject= new UrlMakingClass(getActivity());
		   urlMakingObject.setServiceResultListener(this);
		   
		   AppSettings appSetting  = new AppSettings(getActivity());
	   userId = appSetting.getString("UserId");
	   
	   urlMakingObject.purchasedIService(userId);
	   
	   try{
	    	if (appSetting.getBoolean("hasNotification")) {
				//appSetting.clearKey("hasNotification");
				appSetting.clearKey("msg_count");
				//appSetting.clearKey("f_list");
			
	        }
	    	  Utils.setBadge(getActivity(), 0);
	          NotificationManager notificationManager = (NotificationManager)getActivity().getSystemService(Context.NOTIFICATION_SERVICE);
	          notificationManager.cancel(GcmIntentService.NOTIFICATION_ID);
	    	}catch(Exception e ){
	    		e.printStackTrace();
	    		
	    	}
		 
 }
 
 
// class Task extends AsyncTask<Void, Void, Void>{
//	 
//	 String resp;
//
//	 @Override
//	protected void onPreExecute() {
//		// TODO Auto-generated method stub
//		super.onPreExecute();
//		prog.setVisibility(View.VISIBLE);
//	}
//	@Override
//	protected Void doInBackground(Void... params) {
//		  AppSettings appSetting  = new AppSettings(getActivity());
//		   userId = appSetting.getString("UserId");
//		   
//		HTTP_Connection conn=new HTTP_Connection(getActivity());
//		String urlString  = UrlMakingClass.SERVICE_URL_PURCHASE +"UserId/"+userId+"/apikey/998905797b8646fd44134910d1f88c33"+"/type/user";
//		resp=conn.getresponseData(urlString);
//		return null;
//	}
//	
//	@Override
//	protected void onPostExecute(Void result) {
//		// TODO Auto-generated method stub
//		super.onPostExecute(result);
//		prog.setVisibility(View.GONE);
//		if(resp!=null){
//			
//			if(resp!=null && getActivity()!=null)
//				getActivity().runOnUiThread(new Runnable() {
//			    	String error = null;
//			    	JSONArray response = null;
//			    	JSONObject responseInJSONObject = null;
//			    	CreditBookListAdapter listAdapter;
//			    //	PurchaseBookListAdapter listAdapter1; 
//			    	
//						public void run() 
//						{
//							
//							try {
//								responseInJSONObject=new JSONObject(resp);
//								 
//								 purchaseJsonArray =responseInJSONObject.getJSONArray("Purchase");
////							     creditJsonArray =responseInJSONObject.getJSONArray("PurchasePoint");
//							     purchase.setTextColor(Color.parseColor("#00f5ff"));
//								 transaction.setTextColor(Color.WHITE);
//							     fragmentList.setDivider(null);
////							     total = responseInJSONObject.getString("Amount");
////								 amountDetail.setText("Total: "+"$"+total );
//								 PurchaseBookListAdapter listAdapter = new PurchaseBookListAdapter(getActivity(), purchaseJsonArray,purchaseJsonArray.length());
//						    	 fragmentList.setAdapter(listAdapter);
//						    	 
//						    	
////								 balance = responseInJSONObject.getString("Balance");
//								 
//								
//								
//							} catch (JSONException e) {
//								// TODO Auto-generated catch block
//								e.printStackTrace();
//								
////								try {
//////									balance = responseInJSONObject.getString("Balance");
////								} catch (JSONException e1) {
////									// TODO Auto-generated catch block
////									e1.printStackTrace();
////								}
//								
//								/*Toast.makeText(getActivity(), "CAUGHT", Toast.LENGTH_LONG).show();
//									 try {
//										purchaseJsonArray =responseInJSONObject.getJSONArray("Purchase");
//										PurchaseBookListAdapter listAdapter = new PurchaseBookListAdapter(getActivity(), purchaseJsonArray,0);
//										// fragmentList.removeView(child)
//										 amountDetail.setText("Total: "+"$0");
//									     fragmentList.setAdapter(listAdapter);
//									} catch (JSONException e1) {
//										// TODO Auto-generated catch block
//										e1.printStackTrace();
//									}
//									*/
//							}
//							
//							
//						//	successfullDialog(error);
//							
//						}
//					});
//			
//		}else{
//			
//			Toast.makeText(getActivity(), "connection error", Toast.LENGTH_SHORT).show();
//		}
//	}
//	 
//	 
// }
 
 
	//Create a broadcast receiver to get message and show on screen 
	private final BroadcastReceiver mHandleMessageReceiver = new BroadcastReceiver() {
		
		@Override
		public void onReceive(Context context, Intent intent) {
			  Log.e("onReceive mHandleMessageReceiver", "mHandleMessageReceiver");
			//String newMessage = intent.getExtras().getString(Config.EXTRA_MESSAGE);
			 if(intent.getAction().equalsIgnoreCase(Constant.NOTIFICATION_MESSAGE_ACTION)){
				 refresh();
				  
			   }
			
			/*// Waking up mobile if it is sleeping
			aController.acquireWakeLock(getActivity());
			
			// Display message on the screen
			Log.e("mHandleMessageReceiver",""+(newMessage + "\n"));			
			
		//	Toast.makeText(getActivity(), "Got Message: " + newMessage, Toast.LENGTH_LONG).show();
			
			// Releasing wake lock
			aController.releaseWakeLock();*/
		}
	};

}

	

	


