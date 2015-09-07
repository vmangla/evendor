package com.evendor.Android;

import java.io.File;
import java.io.IOException;

import android.app.ActionBar;
import android.app.Activity;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.database.sqlite.SQLiteDatabase;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.text.TextUtils;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;

import com.evendor.Android.R;
import com.evendor.adapter.DownloadBookListAdapter;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.services.TrafficCounterService;
import com.evendor.utils.MyIntents;
import com.evendor.utils.NetworkUtils;
import com.evendor.utils.StorageUtils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;
import com.evendor.widgets.DownloadListAdapter;
import com.evendor.widgets.ViewHolder;

public class DownloadBookFragment extends Fragment implements ServiceResultListener {
	private ListView downloadList;
	private Button addButton;
	private Button pauseButton;
	private Button deleteButton;
	private Button trafficButton;
	private int urlIndex = 0;
	private DownloadListAdapter downloadListAdapter;
	private MyReceiver mReceiver;
	String bookId;
	String bookTitle;
	String bookIcon;
	String bookPath;

	String priceText= null;
    
	UrlMakingClass urlMakingObject;
	private static  final String TAG = "DownloadBookFragment";
	DownloadBookListAdapter listAdapter ;
	
	
	@Override
	public void onAttach(Activity activity) {
		super.onAttach(activity);
		try {

		} catch (ClassCastException e) {
			throw new ClassCastException(activity.toString() + " must implement OnArticleSelectedListener");
		}
	}

	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onActivityCreated(savedInstanceState);

		if (container == null) {
			return null;
		}
		
		
		Bundle b = getArguments();
		if(b!=null){
			if(b.containsKey("priceText")){
				 priceText = b.getString("priceText");
			}
		}
		View view = inflater.inflate(R.layout.download_list_activity, container, false);

		downloadList = (ListView) view.findViewById(R.id.download_list);
		addButton = (Button) view.findViewById(R.id.btn_add);
		pauseButton = (Button) view.findViewById(R.id.btn_pause_all);
		deleteButton = (Button) view.findViewById(R.id.btn_delete_all);
		trafficButton = (Button) view.findViewById(R.id.btn_traffic);
		return view;

	}

	@Override
	public void onActivityCreated(Bundle savedInstanceState) {
		super.onActivityCreated(savedInstanceState);

		Activity activity = getActivity();

		if (!StorageUtils.isSDCardPresent()) {

			return;
		}

		if (!StorageUtils.isSdCardWrittenable()) {

			return;
		}

		try {
			StorageUtils.mkdir();
		} catch (IOException e) {
			e.printStackTrace();
		}


		if (activity != null) {

			downloadListAdapter = new DownloadListAdapter(getActivity());
			downloadList.setAdapter(downloadListAdapter);
			/*addButton.setOnClickListener(new View.OnClickListener() {

               @Override
               public void onClick(View v) {

                   // downloadManager.addTask(Utils.url[urlIndex]);
                   Intent downloadIntent = new Intent("com.yyxu.download.services.IDownloadService");
                   downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.ADD);
                   downloadIntent.putExtra(MyIntents.URL, Utils.url[urlIndex]);
                   getActivity().startService(downloadIntent);

                   urlIndex++;
                   if (urlIndex >= Utils.url.length) {
                       urlIndex = 0;
                   }
               }
           });*/


			// downloadManager.startManage();
			Intent trafficIntent = new Intent(getActivity(), TrafficCounterService.class);
			getActivity().startService(trafficIntent);
			Intent downloadIntent = new Intent("com.yyxu.download.services.IDownloadService");
			downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.START);
			getActivity().startService(downloadIntent);
			mReceiver = new MyReceiver();
			IntentFilter filter = new IntentFilter();
			filter.addAction("com.yyxu.download.activities.DownloadListActivity");
			getActivity().registerReceiver(mReceiver, filter);
			
//			Intent intent = new Intent();
//			intent.setAction("com.yyxu.download.activities.DownloadListActivity");
//			getActivity().sendBroadcast(intent); 
		}
	}

	@Override
	public void onSuccess(String result) {
		Toast.makeText(getActivity(), "success", Toast.LENGTH_SHORT).show();

	}

	@Override
	public void onError(String errorMessage) {
		
		Toast.makeText(getActivity(), "error", Toast.LENGTH_SHORT).show();

	}




	public class MyReceiver extends BroadcastReceiver {

		@Override
		public void onReceive(Context context, Intent intent) {
			Log.e("MyReceiver", ""+intent.getAction());
			handleIntent(intent);

		}

		private void handleIntent(Intent intent) {

			if (intent != null
					&& intent.getAction().equals(
							"com.yyxu.download.activities.DownloadListActivity")) {
				int type = intent.getIntExtra(MyIntents.TYPE, -1);
				String url;
				String iconPath = null;
				String bookTitle = null;
				String bookPrice;
				
				String bookDescription = null;
				String bookUrl= null;
				String bookId= null;
				String bookAuthor = null;
				String bookPublisherName=null;
				String bookPublishedDate= null;
				String bookSize = null;
				String store_id = null;
				String bookCategory = null;
                String country_id = null;
				String filename = null;//				String bookPrice;
//				String bookPrice;
				


				switch (type) {
				case MyIntents.Types.ADD:
					url = intent.getStringExtra(MyIntents.URL);
					iconPath = intent.getStringExtra(MyIntents.BOOK_ICON_PATH);
					bookTitle = intent.getStringExtra(MyIntents.BOOK_TITLE);
					bookPrice = intent.getStringExtra(MyIntents.BOOK_PRICE);
					bookDescription=intent.getStringExtra("bookDescription");
					bookUrl=intent.getStringExtra("bookUrl");
					bookId=intent.getStringExtra("bookId");
					bookPath=intent.getStringExtra("bookPath");
					bookAuthor=intent.getStringExtra("bookAuthor");
					bookPublisherName=intent.getStringExtra("bookPublisherName");
					bookPublishedDate=intent.getStringExtra("bookPublishedDate");
					bookSize=intent.getStringExtra("bookSize");
					country_id=intent.getStringExtra("country_id");
					//("country_id", country_id);
					bookCategory=intent.getStringExtra("bookCategory");
					filename=intent.getStringExtra("filename");
					priceText=intent.getStringExtra("priceText");
				

					//   Log.i("ICON PATH", intent.getStringExtra(MyIntents.BOOK_ICON_PATH) +"");

					/* bookId = intent.getStringExtra("BOOK_ID");
                    bookTitle = intent.getStringExtra("BOOK_TITLE");
                    bookIcon = intent.getStringExtra("BOOK_ICON");
                    bookPath = StorageUtils.SDCARD_ROOT + StorageUtils.FILE_ROOT + bookTitle +".epub";

                    AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
                    appDataSavedMethod.SaveDownloadedBook(bookTitle, bookId, bookIcon, bookPath, "");
					 */

					Log.e("MyIntents.Types.ADD: : ",bookUrl);
					boolean isPaused = intent.getBooleanExtra(MyIntents.IS_PAUSED, false);
					if (!TextUtils.isEmpty(bookUrl)) {
					//	if(iconPath!=null && !bookTitle.isEmpty() && !bookPrice.isEmpty())
						
							downloadListAdapter.addItem(bookUrl, isPaused , iconPath , bookTitle , bookPrice,priceText);
					}
					break;
				case MyIntents.Types.COMPLETE:
					if(intent.hasExtra("Error")){
						url = intent.getStringExtra(MyIntents.URL);
						Log.e("COMPLETE", ""+intent.getStringExtra("bookId"));
						if(intent.hasExtra("bookId")){
							bookId=intent.getStringExtra("bookId");
							String FileName=null;
							try{
								int rowid = Integer.parseInt(bookId);
								SQLiteDatabase db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
							    DBHelper dbHelper = new DBHelper(db, false);
									if(dbHelper.ItemExistinList(DB.TABLE_DOWNLOADED_BOOK,DB.COLUMN_BOOK_ID, rowid)==0){
								    dbHelper.deleteItemFromList(DB.TABLE_DOWNLOADED_BOOK,DB.COLUMN_BOOK_ID,rowid);
									filename= url.substring(url.lastIndexOf("/")+1,url.length());
									Log.e("COMPLETE_filename", ""+filename);
									File file = new File(StorageUtils.FILE_ROOT
											+filename+".pdf" );
									try{
										Log.e(".pdf--", ""+file.getName());
									if(file.exists())
									{
									file.delete();
									}else{
										Log.e(".epub--", ""+file.getName());
										File file2 = new File(StorageUtils.FILE_ROOT
												+filename+".epub" );
										if(file2.exists())
										{
											file2.delete();
										}
									}
									}catch(Exception e){
										e.printStackTrace();
									}
									
									}
							
							}catch (Exception e) {
								e.printStackTrace();
							}
							
						}
						if (!TextUtils.isEmpty(url)) {
							downloadListAdapter.removeItem(url);
							downloadListAdapter.notifyData();
							
						}
					}else{
							url = intent.getStringExtra(MyIntents.URL);
							iconPath = intent.getStringExtra(MyIntents.BOOK_ICON_PATH);
							bookTitle = intent.getStringExtra(MyIntents.BOOK_TITLE);
							bookPrice = intent.getStringExtra(MyIntents.BOOK_PRICE);
							bookDescription=intent.getStringExtra("bookDescription");
							bookUrl=intent.getStringExtra("bookUrl");
							bookId=intent.getStringExtra("bookId");
							bookPath=intent.getStringExtra("bookPath");
							bookAuthor=intent.getStringExtra("bookAuthor");
							bookPublisherName=intent.getStringExtra("bookPublisherName");
							bookPublishedDate=intent.getStringExtra("bookPublishedDate");
							bookSize=intent.getStringExtra("bookSize");
							bookCategory=intent.getStringExtra("bookCategory");
							store_id=intent.getStringExtra("country_id");
							filename=intent.getStringExtra("filename");
							Log.e("filename))))))))))))))))))", "((("+intent.getStringExtra("country_id"));
						/*	AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
							appDataSavedMethod.SaveDownloadedBook(bookTitle,bookDescription,bookUrl, bookId, iconPath,
									bookPath, "",bookAuthor,bookPublisherName,bookPublishedDate,bookSize,bookCategory);*/
		
								Log.e("qqqqqqqqqq", "*****************************88 "+bookId);
					
								UrlMakingClass um = new UrlMakingClass(getActivity());
								 AppSettings appSetting  = new AppSettings(getActivity());
								 
								 Log.e("qqqqqqqqqq", "*****************************88 "+appSetting.getString("UserId"));
								um.purchasedItemService(appSetting.getString("UserId"), store_id, bookId);
								
					if (!TextUtils.isEmpty(url)) {
						downloadListAdapter.removeItem(url);
						ApplicationManager.showReadButton = true;
						downloadListAdapter.notifyData();
						
											ApplicationManager.changeRightFragment(getActivity(), new DownloadedBookFragment(),"downloaded Books");
						// 						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
						StartFragmentActivity.leftPane.setVisibility(View.GONE);
						setUpActionBar();
					}
					}
					break;
				case MyIntents.Types.PROCESS: 
					url = intent.getStringExtra(MyIntents.URL);
					iconPath = intent.getStringExtra(MyIntents.BOOK_ICON_PATH);
					bookTitle = intent.getStringExtra(MyIntents.BOOK_TITLE);
					bookPrice = intent.getStringExtra(MyIntents.BOOK_PRICE);
					priceText = intent.getStringExtra(MyIntents.BOOK_PRICE_TEXT);
					View taskListItem = downloadList.findViewWithTag(url);
					ViewHolder viewHolder = new ViewHolder(taskListItem);
					viewHolder.setData(url, intent.getStringExtra(MyIntents.PROCESS_SPEED),
							intent.getStringExtra(MyIntents.PROCESS_PROGRESS) , iconPath , bookTitle , bookPrice, priceText);
					break;
				case MyIntents.Types.ERROR:
					url = intent.getStringExtra(MyIntents.URL);
					//downloadListAdapter.notifyData();
					// int errorCode =
					// intent.getIntExtra(MyIntents.ERROR_CODE,
					// DownloadTask.ERROR_UNKONW);
					// handleError(url, errorCode);
					break;
				default:
					break;
				}
			}else if (intent != null
					&& intent.getAction().equals("com.yyxu.download.error")) {
				Log.e("com.yyxu.download.error : ","com.yyxu.download.error");
				
			}
		}
	}
	private void setUpActionBar()
	{
		ActionBar actionBar = getActivity().getActionBar();
		actionBar.setTitle("Downloaded Books"); 

		//	 ((MainFragmentActivity) getActivity()).changeTheMenuItemIcon(R.id.downloadedMenuButton);
		((StartFragmentActivity) getActivity()).changeTheMenuItemIcon(R.id.downloadedMenuButton);

	}

	@Override
	public void onDestroy() {
		// TODO Auto-generated method stub

		getActivity().unregisterReceiver(mReceiver);
		super.onDestroy();
	}
}






