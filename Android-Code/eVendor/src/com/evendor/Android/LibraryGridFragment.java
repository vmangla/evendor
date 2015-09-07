package com.evendor.Android;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLConnection;
import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ActionBar;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.Configuration;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.text.Html;
import android.text.Spanned;
import android.util.Log;
import android.view.Display;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.EditText;
import android.widget.GridView;
import android.widget.TableRow;
import android.widget.TextView;

import com.evender.Comprator.MComprater;
import com.evender.Comprator.SortByPriceAcs;
import com.evender.Comprator.SortByPriceDesc;
import com.evendor.Android.R;
import com.evendor.Modal.BooksData;
import com.evendor.adapter.ImageGridAdapter;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.model.ListModel;
import com.evendor.utils.CMDialog;
import com.evendor.utils.LibraryEvendorDialog;
import com.evendor.utils.MyIntents;
import com.evendor.utils.StorageUtils;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class LibraryGridFragment extends Fragment implements ServiceResultListener{
	TextView filters;
	UrlMakingClass urlMakingObject;
	private GridView                 mGridView;
	private ImageGridAdapter mGridAdapter;
	private int urlIndex = 0;
	ActionBar actionBar;
	public static EditText portraitSearch;

	private TextView all;
	private TextView books;
	private TextView magazines;
	private TextView newspaper;
	private TextView flyer;
	private TableRow tablerow;
	private SQLiteDatabase db;
   	private DBHelper dbHelper=null;
	String bookUrl = null;
	String bookId = null;
	String bookTitle = null;
	String bookTitleWithSpace = null;
	String country_id = null;
	String bookPrice = null;
	String bookPublisher = null;
	String bookDescription = null;
	String bookIconPath = null;
	String bookPath = null;
	String bookAuthor = null;
	String bookCategory= null;
	String bookPublisherName = null;
	String bookPublishedDate = null;
	String publisherDate;
	String bookRate;
	String bookSize;
	String file_name;
	ArrayList<BooksData> dataList=new ArrayList<BooksData>();
	ListModel[] sLocationsUsed = ApplicationManager.gridModel;
	ImageLoader imageLoader;
	JSONArray categoryWiseSortedjSONArray;
	public  JSONArray sortedjSonArrayList;



	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
		View view = inflater.inflate(R.layout.grid_fragment, container, false);
		                 
		mGridView = (GridView) view.findViewById(R.id.grid_view);
  if(StartFragmentActivity.sheduleremoveflag){
	  searchOfBooks(StartFragmentActivity.booksdata);
  }else
  {
		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		try {
			getcurrent(timeFormat.parse(getcurrentDate()));
		} catch (ParseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
  }
//		try {
//			getcurrent(timeFormat.parse(getcurrentDate()));
//		} catch (ParseException e) {
//			// TODO Auto-generated catch block
//			e.printStackTrace();
//		}
//		searchOfBooks(StartFragmentActivity.booksdata);

		return view;
	}

	@Override
	public void onActivityCreated(Bundle savedInstanceState) {
		super.onActivityCreated(savedInstanceState);
		Activity activity = getActivity();

		int  newConfig =  getscrOrientation();


		Log.i("lIBRARY_ORIENTATION", newConfig + "");

		//		if (newConfig == Configuration.ORIENTATION_LANDSCAPE) {
		//			portraitSearch.setVisibility(View.INVISIBLE);
		//			
		//	            
		//	        } else if (newConfig== Configuration.ORIENTATION_PORTRAIT){
		//	        	portraitSearch.setVisibility(View.VISIBLE);
		//	            
		//	        }

		if (activity != null) {


			String Title = null;

			try {

				if(StartFragmentActivity.booksdata != null)
				{
					// Title = MainFragmentActivity.recoveryTask.getCategoryLibraryJsonArray().getJSONObject(0).getString("genre");
				}
				else
				{
					if(!ApplicationManager.showNoBookDialog)
					{
						ApplicationManager.showNoBookDialog = false;
						//noBookDialog("No book is available");
					}
				}
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}


			Log.i("TITLE", Title +"");

			//        	notifyDataChange(Title);


			mGridView.setOnItemClickListener(new OnItemClickListener() {

				private String free;
				private String bookpriceText;

				@Override
				public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
				
					onGridItemClick((GridView) parent, view, position, id);
//
//					String bookUrl = null;
//					String bookId = null;
//					String bookTitle = null;
//					String bookPrice = null;
//					String bookPublisher = null;
//					String bookDescription = null;
//					String bookIconPath = null;
//					String bookPath = null;
//					String bookAuthor = null;
//					String bookPublisherName = null;
//					String bookPublishedDate = null;
//

					int rowid = Integer.parseInt(dataList.get(position).getId());
					SQLiteDatabase db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
				    DBHelper dbHelper = new DBHelper(db, false);
						if(dbHelper.ItemExistinList(DB.TABLE_DOWNLOADED_BOOK,DB.COLUMN_BOOK_ID, rowid)==1){
							
							File file = new File(StorageUtils.FILE_ROOT
									+dataList.get(position).getFile_name()+".pdf" );
							
							if(file.exists()){
								file.delete();
							}
							
						}
					
					
					try {
						//						JSONObject jsonObject = sortedjSonArrayList.getJSONObject(position);


						bookTitleWithSpace  =dataList.get(position).getTitle();//jsonObject.getString("title");
						bookTitle = bookTitleWithSpace.replace(" ", "");
						if(dataList.get(position).getFile_name().contains(".pdf")){
							bookPath = StorageUtils.FILE_ROOT + bookTitle +".pdf";	
						}else
						{
						bookPath = StorageUtils.FILE_ROOT + bookTitle +".epub";
						}
						bookAuthor = dataList.get(position).getAuthor_name();//jsonObject.getString("author_name");
						bookPublisherName =  dataList.get(position).getPublisher_name();  //jsonObject.getString("publisher_name");
						bookSize =  dataList.get(position).getFile_size();  //jsonObject.getString("file_size");
						bookUrl = dataList.get(position).getProducturl(); //jsonObject.getString("Producturl");
						bookId  = dataList.get(position).getId(); // jsonObject.getString("id");
						bookRate = dataList.get(position).getRating(); //  jsonObject.getString("rating"); 
						bookCategory = dataList.get(position).getCategory_name();
						bookDescription  = dataList.get(position).getDescription(); // jsonObject.getString("description");
						bookIconPath  = dataList.get(position).getProductThumbnail(); //  jsonObject.getString("ProductThumbnail");
						bookPrice = dataList.get(position).getPrice(); //  jsonObject.getString("price");
						publisherDate = dataList.get(position).getPublish_time(); // jsonObject.getString("publish_time");
						file_name = dataList.get(position).getFile_name();
						country_id = dataList.get(position).getCountry_id();
						free=dataList.get(position).getIs_free();
						bookUrl = bookUrl.replace(" ", "");
						bookpriceText=dataList.get(position).getPriceText();
						
						//Log.i("BOOK_URL", bookPath +"");
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
					//Log.e("BOOK_PRICE----", bookPrice +"******");
					if(bookPrice.equals("0")){
						bookPrice="Free";
					}
					Log.e("messssssssssssssssssssss","pricw00000"+Html.fromHtml(bookPrice));
					String descriptionHtmlString = "<font face='open sans'>"+bookDescription+"</font>" ;
					
					showBookDetailDialog(bookTitleWithSpace,bookAuthor,bookPublisherName,publisherDate,Html.fromHtml(descriptionHtmlString ),null,bookUrl,bookId,null,bookPrice,bookSize,bookIconPath,file_name,country_id,free,bookpriceText); 

					//     String htmlString = "Do you want to purchase the book? "+"<br>"+"</br>"+"<b><i>"+bookTitle+" price  $" + bookPrice +"</i></b>" ;

					//   GetImageTask task = new GetImageTask(bookPath);
					//   task.execute(new String[] { bookIconPath });

				}

			});
		}
	}


	public void onGridItemClick(GridView g, View v, int position, long id) {
		Activity activity = getActivity();

		if (activity != null) {
			ListModel locationModel = (ListModel) mGridAdapter.getItem(position);

		}
	}
	//    
	public void notifyDataChange(String Title)
	{


		if(StartFragmentActivity.booksdata !=null)
		{


			dataList.clear();
			for(int i =0;i < StartFragmentActivity.booksdata.size() ; i++)
			{
				if(StartFragmentActivity.booksdata.get(i).getGenre().equalsIgnoreCase(Title)){


					dataList.add(StartFragmentActivity.booksdata.get(i));
				}



			}


			//    	sortedjSonArrayList = categoryWiseSortedjSONArray ;
//			portraitSearch.setText("");

			mGridAdapter = new ImageGridAdapter(getActivity(), imageLoader ,dataList);

			if (mGridView != null) {
				mGridView.setAdapter(mGridAdapter);
			}

		}

		//mGridAdapter.notifyDataSetChanged();
	}

	@Override
	public void onSuccess(final String result) {
		// TODO Auto-generated method stub


		getActivity().runOnUiThread(new Runnable() {

			public void run() 
			{
				try
				{
					JSONObject responseInJSONObject=new JSONObject(result);

					JSONObject messageJSONObject = responseInJSONObject.getJSONObject("BookResponse");
					//Log.e("Success", messageJSONObject.getString("Message"));

//					if(messageJSONObject.getString("Message").equalsIgnoreCase("Success"))
					{

						AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
						appDataSavedMethod.SaveDownloadedBook(bookTitleWithSpace,bookDescription,bookUrl, bookId, bookIconPath,
								bookPath, "",bookAuthor,bookPublisherName,bookPublishedDate,bookSize,bookCategory);


						Intent downloadIntent = new Intent("com.yyxu.download.services.IDownloadService");
						downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.ADD);
						downloadIntent.putExtra(MyIntents.URL, bookUrl);
						//  downloadIntent.putExtra(MyIntents.BOOK_ID, bookId);
						downloadIntent.putExtra(MyIntents.BOOK_TITLE, bookTitleWithSpace);
						downloadIntent.putExtra(MyIntents.BOOK_PRICE, bookPrice);
						downloadIntent.putExtra(MyIntents.BOOK_ICON_PATH, bookIconPath);

						getActivity().startService(downloadIntent);

						ApplicationManager.changeRightFragment(getActivity(), new DownloadBookFragment(),"downloadBookFragment");

						StartFragmentActivity.leftPane.setVisibility(View.GONE);

						StartFragmentActivity.libraryFilerLayout.setVisibility(View.GONE);
						
						StartFragmentActivity.llcatagory.setVisibility(View.GONE);

						setUpActionBar();


					}
//					else if(messageJSONObject.getString("Message").equalsIgnoreCase("Unsuccess"))
//					{
//						noBookDialog("Book is already purchased"); 
//					}

				}



				catch (JSONException e) 
				{
					e.printStackTrace();
				}

			}
		});

	}

	@Override
	public void onError(final String errorMessage) {
		// TODO Auto-generated method stub

		getActivity().runOnUiThread(new Runnable() {

			public void run() {
				if(errorMessage.equals("-389"))
				{
					noBookDialog("Network error");
				}
				else if(errorMessage.equals("-390"))
				{
					Utils.ConnectionErrorDialog(getActivity());
				}
			}
		});

	}

	private void setUpActionBar()
	{
		actionBar = getActivity().getActionBar();
		actionBar.setTitle("Downloads"); 

		((StartFragmentActivity) getActivity()).changeTheMenuItemIcon(R.id.downloadMenuButton);

	}


	public  void sortByAlphabetically( String filter,ArrayList<BooksData> list)
	{
		//		ArrayList<JSONObject> list = new ArrayList<JSONObject>();     

		//		if (categoryWiseSortedjSONArray != null) { 
		//		   int len = categoryWiseSortedjSONArray.length();
		//		   for (int i=0;i<len;i++){ 
		//			   
		//			   JSONObject jsonObject = null;
		//			   
		//			   try {
		//				 jsonObject = (JSONObject) categoryWiseSortedjSONArray.get(i);
		//			} catch (JSONException e) {
		//				// TODO Auto-generated catch block
		//				e.printStackTrace();
		//			}
		//		    list.add(jsonObject);
		//		   } 
		//		} 

		if(filter.equals("filterOne") || filter.equals("filterTwo"))
		{

			Collections.sort(list, new MComprater());
			if(filter.equals("filterTwo"))
			{
				Collections.reverse(list);
			}
		}
		else if(filter.equals("filterThree") || filter.equals("filterFour"))
		{
			//Collections.sort(list, new SortByPriceAcs());
			if(filter.equals("filterFour"))
			{
				Collections.sort(list, new SortByPriceDesc());
				//Collections.reverse(list);
			}else if(filter.equals("filterThree")){
				Collections.sort(list, new SortByPriceAcs());
			}
		}

		//		sortedjSonArrayList = new JSONArray(list);

		//Log.i("SORTED_ARRAY", sortedjSonArrayList+"");

		mGridAdapter = new ImageGridAdapter(getActivity(),imageLoader,list );
		dataList=list;
		mGridView.setAdapter(mGridAdapter);
	}



	//	public  void searchOfBooks( JSONArray SearchJsonArray)
	public  void searchOfBooks(ArrayList<BooksData> list)
	{
		
		//if(list!=null)
		dataList=list;
		//Log.e("searchOfBooks-searchOfBooks",""+list.size());
//		setList();
//		for(int i=0;i<list.size();i++){
//			StartFragmentActivity.searchListByCat.add(list.get(i));
//		}
//        StartFragmentActivity.searchListByCat=dataList;
		imageLoader = new ImageLoader(getActivity());
		mGridAdapter = new ImageGridAdapter(getActivity() ,imageLoader,list);
		mGridView.setAdapter(mGridAdapter);
	}


	public  class CustomComparator implements Comparator<JSONObject> {
		@Override
		public int compare(JSONObject o1, JSONObject o2) {
			String bookName1 = null;
			String bookName2 = null;
			try {
				bookName1 = o1.getString("title");
				bookName2 = o2.getString("title");
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}

			return bookName1.compareTo(bookName2);

		}
	}


	public  class CustomComparatorForPrice implements Comparator<JSONObject> {
		@Override
		public int compare(JSONObject o1, JSONObject o2) {
			String bookPrice1 = null;
			String bookPrice2 = null;
			Integer price1 = null;
			Integer price2 = null;
			try {
				bookPrice1 = o1.getString("price");
				price1 = Integer.parseInt(bookPrice1);
				bookPrice2 = o2.getString("price");
				price2 = Integer.parseInt(bookPrice2);
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}

			return price1.compareTo(price2);

		}
	}



	private void showBookDetailDialog(String title,String author, String publisher,final String publishedDate, Spanned spanned,final Bitmap imageurl,final String path, final String bookPkId , final String bookShelfId 
			, String price,final String bookSize,String icon,final String file_name, final String country_id, 
			final String free, final String priceText)
	{				
		final LibraryEvendorDialog cmDialog = new LibraryEvendorDialog(getActivity());
		cmDialog.setRateingBar(bookRate);
		cmDialog.setTitle(title);
		String date = "";    
		imageLoader.DisplayImage(icon, cmDialog.getBookIcon());
		if(publisherDate != null)
		{
			date = ApplicationManager.getconvertdate1(publisherDate);
		}



//		cmDialog.setContent(LibraryEvendorDialog.LIBRARY_BUY_BOOK,title,author, publisher,publishedDate,spanned,price +"\n"+ date,bookSize);
		cmDialog.setContent(title,title,author, publisher,date,spanned,price ,bookSize,priceText);


		imageLoader.DisplayImage(icon, cmDialog.getBookIcon());
		//	cmDialog.getBookIcon().setImageBitmap(imageurl);

		/*	ImageLoader imageLoader = new ImageLoader(getActivity());
		imageLoader.DisplayImage(path, cmDialog.getBookIcon());*/

		cmDialog.setPossitiveButton(LibraryEvendorDialog.YES, new OnClickListener() 
		{

			public void onClick(View v) 
			{ 

				Log.e("bookURL-NOW", bookUrl);
//				urlMakingObject= new UrlMakingClass(getActivity());
//				urlMakingObject.setServiceResultListener(LibraryGridFragment.this);
//
//				AppSettings appSetting  = new AppSettings(getActivity());
//				String userId = appSetting.getString("UserId");
//
//				urlMakingObject.purchasedItemService(userId, bookId, bookPrice);

				{

//					AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
//					appDataSavedMethod.SaveDownloadedBook(bookTitleWithSpace,bookDescription,bookUrl, bookId, bookIconPath,
//							bookPath, "",bookAuthor,bookPublisherName,bookPublishedDate,bookSize,bookCategory);
					
					if(free.equalsIgnoreCase("false")){
						Log.e("bookURL-NOW", bookUrl);
						BookBuyDialog("You have to purchase from website.",bookId,country_id);
					}else{
						if(isPresent(bookPkId))
	                 	   {
							 noBookDialog("This publication is already downloaded");
	 						  
	                 	   }
	 	            	  else
	 	            	   {
	 	            		 
	 	            	   
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
					downloadIntent.putExtra("bookCategory", bookCategory);
					downloadIntent.putExtra("country_id", country_id);
					downloadIntent.putExtra("priceText", priceText);
					downloadIntent.putExtra("filename", file_name);
					getActivity().startService(downloadIntent);
					
					ApplicationManager.changeRightFragment(getActivity(), new DownloadBookFragment(),"downloadBookFragment");

					StartFragmentActivity.leftPane.setVisibility(View.GONE);

					StartFragmentActivity.libraryFilerLayout.setVisibility(View.GONE);
					
					StartFragmentActivity.llcatagory.setVisibility(View.GONE);

					setUpActionBar();


				}
					}
					
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

	private void BookBuyDialog(String Message, final String bookId2, final String country_id2) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
		cmDialog.setContent("Buy", Message);
		cmDialog.setPossitiveButton("Buy Now", new OnClickListener() 
		{
			public void onClick(View v) 
			{
				/**@author MIPC27
				 * changed URL from  WebServiceBase.BASE_URL1+"projects/evendor/catalogue/detail/id/"+bookId2+"/store/"+country_id2+"/lang/1"
				 * to 
				 */
				String url = WebServiceBase.BASE_URL1+"catalogue/detail/id/"+bookId2+"/store/"+country_id2+"/lang/1";
				Log.e("Hello buy book", "url--"+url);
				
				Intent i = new Intent(Intent.ACTION_VIEW);
				i.setData(Uri.parse(url));
				startActivity(i);
				cmDialog.dismiss();
			}
			
		});
		cmDialog.setNegativeButton(CMDialog.CANCEL, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
//		cmDialog.setPossitiveButton(CMDialog.CANCEL, new OnClickListener() 
//		{
//			public void onClick(View v) 
//			{
//				cmDialog.cancel();					
//			}
//		});
		cmDialog.show();
	}
	



	
	
	private int getscrOrientation() {
		Display getOrient = getActivity().getWindowManager().getDefaultDisplay();

		int orientation = getOrient.getOrientation();

		// Sometimes you may get undefined orientation Value is 0
		// simple logic solves the problem compare the screen
		// X,Y Co-ordinates and determine the Orientation in such cases
		if (orientation == Configuration.ORIENTATION_UNDEFINED) {

			Configuration config = getResources().getConfiguration();
			orientation = config.orientation;

			if (orientation == Configuration.ORIENTATION_UNDEFINED) {
				// if height and width of screen are equal then
				// it is square orientation
				if (getOrient.getWidth() == getOrient.getHeight()) {
					orientation = Configuration.ORIENTATION_SQUARE;
					//	orientation = Configuration.ORIENTATION_PORTRAIT;
				} else { // if width is less than height than it is portrait
					if (getOrient.getWidth() < getOrient.getHeight()) {
						orientation = Configuration.ORIENTATION_PORTRAIT;
					} else { // if it is not any of the above it will definitely
						// be landscape
						orientation = Configuration.ORIENTATION_LANDSCAPE;
					}
				}
			}
		}
		return orientation; // return value 1 is portrait and 2 is Landscape
		// Mode
	}



	private class GetImageTask extends AsyncTask<String, Void, Bitmap> {
		String path;

		GetImageTask(String path)
		{

			this.path = path;


		}


		final ProgressDialog dialog = new ProgressDialog(getActivity());

		@Override
		protected void onPreExecute() {
			// TODO Auto-generated method stub
			super.onPreExecute();

			dialog.setMessage(getActivity().getResources()
					.getString(R.string.dialog_please_wait));
			//dialog.setCancelable(false);
			dialog.show();
		}

		@Override
		protected Bitmap doInBackground(String... urls) {

			Bitmap map = null;
			for (String url : urls) {

				map = downloadImage(url);
			}
			return map;
		}

		@Override
		protected void onPostExecute(Bitmap result) {
			dialog.dismiss();
			// String descriptionHtmlString = "<b><font face='open sans'>"+bookDescription+"</font></b>" ;
			String descriptionHtmlString = "<font face='open sans'>"+bookDescription+"</font>" ;

//			showBookDetailDialog(bookTitleWithSpace,bookAuthor,bookPublisherName,bookPublishedDate,Html.fromHtml(descriptionHtmlString ),result,bookUrl,null,null,bookPrice,bookSize,bookIconPath); 

		}

		private Bitmap downloadImage(String url) {

			Bitmap bitmap = null;
			InputStream stream = null;
			BitmapFactory.Options bmOptions = new BitmapFactory.Options();
			bmOptions.inSampleSize = 1;

			try {
				stream = getHttpConnection(url);
				bitmap = BitmapFactory.
						decodeStream(stream, null, bmOptions);
				if(stream != null)
					stream.close();
			} catch (IOException e1) {
				e1.printStackTrace();
			}
			return bitmap;
		}

		private InputStream getHttpConnection(String urlString)
				throws IOException {
			InputStream stream = null;
			URL url = new URL(urlString);
			URLConnection connection = url.openConnection();

			try {
				HttpURLConnection httpConnection = (HttpURLConnection) connection;
				httpConnection.setRequestMethod("GET");
				httpConnection.connect();
				if (httpConnection.getResponseCode() == HttpURLConnection.HTTP_OK) {
					stream = httpConnection.getInputStream();
				}
			} catch (Exception ex) {
				ex.printStackTrace();
			}
			return stream;
		}
	}


	private void noBookDialog(String errorMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
		/**@author MIPC27
		 * changed "Library book" to "Store book"
		 */
		cmDialog.setContent("Store book", errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
//		cmDialog.setPossitiveButton(CMDialog.CANCEL, new OnClickListener() 
//		{
//			public void onClick(View v) 
//			{
//				cmDialog.cancel();					
//			}
//		});
		cmDialog.show();
	}

	
	private void getcurrent(Date curdate){
		Log.e("------getcurrent--------","'''getcurrent'''" );
		Calendar c = Calendar.getInstance();
		c.setTime(curdate);


		//		 Calendar c1 = Calendar.getInstance();
		//		 c1.setTime(curdate);

	//ArrayList<BooksData> searchListByCat=new ArrayList<BooksData>();
		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		if(StartFragmentActivity.searchListByCat!=null)
		StartFragmentActivity.searchListByCat.clear();
if(StartFragmentActivity.booksdata!=null)
	
		for(int i=0;i<StartFragmentActivity.booksdata.size();i++){

			try {
				String finalDate = getcurrentDate();
				Calendar c1 = Calendar.getInstance();
				c1.setTime(timeFormat.parse(StartFragmentActivity.booksdata.get(i).getPublish_time()));

				/*if(c.get(Calendar.YEAR)==c1.get(Calendar.YEAR)){
					if(c.get(Calendar.MONTH)==c1.get(Calendar.MONTH)){
						Log.e("---------------","'''''''''''''" +finalDate);
						StartFragmentActivity.searchListByCat.add(StartFragmentActivity.booksdata.get(i));
					}
				}*/
				StartFragmentActivity.searchListByCat.add(StartFragmentActivity.booksdata.get(i));
				
			} catch (ParseException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			//  System.out.println(day);

		}
		if(StartFragmentActivity.searchListByCat!=null)
			searchOfBooks(StartFragmentActivity.searchListByCat);

	}

	public void getcurrent1(Date curdate){
		Log.e("------getcurrent--------","'''getcurrent'''" );
		Calendar c = Calendar.getInstance();
		c.setTime(curdate);


		//		 Calendar c1 = Calendar.getInstance();
		//		 c1.setTime(curdate);

	ArrayList<BooksData> searchListByCat=new ArrayList<BooksData>();
		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		
if(StartFragmentActivity.searchListByCat!=null)
	
		for(int i=0;i<StartFragmentActivity.searchListByCat.size();i++){

			try {
				String finalDate = getcurrentDate();
				Calendar c1 = Calendar.getInstance();
				if(StartFragmentActivity.searchListByCat.get(i).getPublish_time()!=null)
				c1.setTime(timeFormat.parse(StartFragmentActivity.searchListByCat.get(i).getPublish_time()));

				/*if(c.get(Calendar.YEAR)==c1.get(Calendar.YEAR)){
					if(c.get(Calendar.MONTH)==c1.get(Calendar.MONTH)){
						searchListByCat.add(StartFragmentActivity.searchListByCat.get(i));
					}
				}*/
				searchListByCat.add(StartFragmentActivity.searchListByCat.get(i));
			} catch (ParseException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			//  System.out.println(day);

		}
		if(searchListByCat!=null){
			searchOfBooks(searchListByCat);
			StartFragmentActivity.searchListByCat=searchListByCat;
		}

	}
	
	private String getcurrentDate(){

		DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");
		//get current date time with Date()
		Date date = new Date();
		Log.e("*************************8", "  "+dateFormat.format(date));
		return dateFormat.format(date);
	}
	
	
	public void searchofmonth(){
		
		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		try {
			getcurrent(timeFormat.parse(getcurrentDate()));
		} catch (ParseException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
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
}
