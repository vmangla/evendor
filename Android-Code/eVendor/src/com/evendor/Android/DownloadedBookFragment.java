package com.evendor.Android;

import java.io.File;
import java.util.ArrayList;

import org.geometerplus.android.fbreader.FBReader;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ActionBar;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.text.Editable;
import android.text.Html;
import android.text.Spanned;
import android.text.TextWatcher;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.ArrayAdapter;
import android.widget.EditText;
import android.widget.GridView;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import com.artifex.mupdfdemo.MuPDFActivity;
import com.evendor.adapter.DownloadedBookImageGridAdapter;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.model.ListModel;
import com.evendor.utils.CMDialog;
import com.evendor.utils.CreateBookSelfDialog;
import com.evendor.utils.DownloadedBookDialog;
import com.evendor.utils.RateDownloadedBookDialog;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;



public class DownloadedBookFragment extends Fragment implements ServiceResultListener{
	TextView filters;
	UrlMakingClass urlMakingObject;
    private GridView                 mGridView;
    private DownloadedBookImageGridAdapter mGridAdapter;
    private int urlIndex = 0;
   
    private TextView all;
	private TextView books;
	private TextView magazines;
	private TextView newspaper;
	private TextView flyer;
	private TableRow tablerow;
	private EditText search;
    private SQLiteDatabase db;
	private DBHelper dbHelper             =null;
    ListModel[] sLocationsUsed = ApplicationManager.gridModel;
    ArrayList<ArrayList<String>> downloadedbookList;
    ArrayList<ArrayList<String>> newdownloadedbookList;
    ArrayList<ArrayList<String>> newdownloadedbookList1;
    ArrayList<ArrayList<String>> verticalList;
    ImageLoader imageLoader;
    
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.downloaded_grid, container, false);
        mGridView = (GridView) view.findViewById(R.id.grid_view);
  
        all=(TextView) view.findViewById(R.id.all);
		magazines=(TextView) view.findViewById(R.id.magazine);
		newspaper=(TextView) view.findViewById(R.id.newspaper);
		books=(TextView) view.findViewById(R.id.book);
		flyer=(TextView)view.findViewById(R.id.flyer);
		tablerow=(TableRow)view.findViewById(R.id.tableRow1);
		search= (EditText) view.findViewById(R.id.downloadedsearch);
		all.setOnClickListener(m_click);
		magazines.setOnClickListener(m_click);
		newspaper.setOnClickListener(m_click);
		books.setOnClickListener(m_click);
		flyer.setOnClickListener(m_click);
		
		search.addTextChangedListener(new TextWatcher() {
			
			@Override
			public void onTextChanged(CharSequence s, int start, int before, int count) {
				// TODO Auto-generated method stub
				
			}
			
			@Override
			public void beforeTextChanged(CharSequence s, int start, int count,
					int after) {
				// TODO Auto-generated method stub
				
			}
			
			@Override
			public void afterTextChanged(Editable s) {
				// TODO Auto-generated method stub
				searchByName(s.toString());
			}
		});
        
        return view;
    }
    
    @Override
    public void onActivityCreated(Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        imageLoader = new ImageLoader(getActivity());
        Activity activity = getActivity();
        if (activity != null) {
        	verticalList = new ArrayList<ArrayList<String>>();
            downloadedbookList = fetchBooksForShelves();
            newdownloadedbookList = fetchBooksForShelves();
            newdownloadedbookList1 = fetchBooksForShelves();
            
        	urlMakingObject= new UrlMakingClass(activity);
    		urlMakingObject.setServiceResultListener(this);
    		
   		 mGridAdapter = new DownloadedBookImageGridAdapter(activity, downloadedbookList,imageLoader);
        	
        	if (mGridView != null) {
                mGridView.setAdapter(mGridAdapter);
            }
            
            // Setup our onItemClickListener to emulate the onListItemClick() method of ListFragment.
            mGridView.setOnItemClickListener(new OnItemClickListener() {

                @Override
                public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                    onGridItemClick((GridView) parent, view, position, id);
                    fetchBookShelves();
                    String bookUrl = downloadedbookList.get(position).get(0);
                    String bookPath = downloadedbookList.get(position).get(1);
                    String bookName = downloadedbookList.get(position).get(2);
                    String bookid = downloadedbookList.get(position).get(3);
                    String pkId = downloadedbookList.get(position).get(4);
                    String FKId = downloadedbookList.get(position).get(5);
                    String category = downloadedbookList.get(position).get(6);
                    String author = downloadedbookList.get(position).get(7);
                    String pub = downloadedbookList.get(position).get(8);
                    String date = downloadedbookList.get(position).get(9);
                    String booksize = downloadedbookList.get(position).get(10);
                   String bookDescription = downloadedbookList.get(position).get(11);
                   String descriptionHtmlString = "<font face='open sans'>"+bookDescription+"</font>" ;
                    showDeleteRateDialog(bookUrl,bookid,pkId,bookPath,bookName,FKId,author,pub,date,booksize,Html.fromHtml(descriptionHtmlString ));
                    
                    
//                    Intent in =new Intent(getActivity(), ChooseFileActivity.class);
//                    startActivity(in);
                    
                }
                
            });
        }
        
        selectTab(R.id.all);
    }
    
    
    public void onGridItemClick(GridView g, View v, int position, long id) {
        Activity activity = getActivity();
        if (activity != null) {
            ListModel locationModel = (ListModel) mGridAdapter.getItem(position);

        }
    }
    
    
    private ArrayList<ArrayList<String>> fetchBooksForShelves()
    {
       db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
       dbHelper = new DBHelper(db, false);

       Cursor c= dbHelper.fetchFromTable(DB.TABLE_DOWNLOADED_BOOK);
       
       ArrayList<ArrayList<String>> horizontalList = new ArrayList<ArrayList<String>>();

       if(c!=null && c.getCount()!=0)
       {
    	   if(c.moveToFirst())
	    	{
	    	do{
           String id  = Integer.toString(c.getInt(c.getColumnIndex(DB.COLUMN_ID)));
	       String bookIcon    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_ICON));
	       String bookPath    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_PATH));
	       String bookTitle    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_NAME));
	       String bookDescription    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_DESCRIPTION));
	       String bookId    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_ID));
	       String bookFK    = c.getString(c.getColumnIndex(DB.COLUMN_FK));
	       String bookCategory = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_CATEGORY));//COLUMN_BOOK_PUBLISHER
	       String bookpublisher = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_PUBLISHER));
	       String bookAuthor = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_AUTHOR));
	       String publishdate = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_PUBLISHED_DATE));
	       String book_size = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_SIZE));
	       //COLUMN_BOOK_AUTHOR
	       ArrayList<String> bookSheleveDetail = new ArrayList<String> ();
	       bookSheleveDetail.add(bookIcon);
	       bookSheleveDetail.add(bookPath);
	       bookSheleveDetail.add(bookTitle);
	       bookSheleveDetail.add(bookId);
	       bookSheleveDetail.add(id);
	       
	       bookSheleveDetail.add(bookFK);
	       bookSheleveDetail.add(bookCategory);
	       bookSheleveDetail.add(bookAuthor);
	       bookSheleveDetail.add(bookpublisher);
	       bookSheleveDetail.add(publishdate);
	       bookSheleveDetail.add(book_size);
	       bookSheleveDetail.add(bookDescription);
	   //    
	      
	       horizontalList.add(bookSheleveDetail);
	    	}while(c.moveToNext());
   	       }
       }

       db.close();
       c.close();
       
       return horizontalList;
   }
    
    
    private void fetchBookShelves()
    {
	   verticalList.clear();
       db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
       dbHelper = new DBHelper(db, false);

       Cursor c= dbHelper.fetchFromTable(DB.TABLE_BOOKSHELF); 

       if(c!=null && c.getCount()!=0)
       {
    	   if(c.moveToFirst())
	    	{
	    	do{
        
	       String shelveName   = c.getString(c.getColumnIndex(DB.COLUMN_SHELF_NAME));
	       String shelveColor  = c.getString(c.getColumnIndex(DB.COLUMN_SHELF_COLOR));
	       String id    = Integer.toString(c.getInt(c.getColumnIndex(DB.COLUMN_ID)));
           ArrayList<String> bookSheleveDetail = new ArrayList<String> ();
	       bookSheleveDetail.add(shelveName);
	       bookSheleveDetail.add(shelveColor);
	       bookSheleveDetail.add(id);
           verticalList.add(bookSheleveDetail);
           
	    	}while(c.moveToNext());
	    	}

       }
	  
       db.close();
       c.close();
   }
    
    
    private void showDeleteRateDialog(String bookUrl,final String bookid , final String pkId ,final String bookPath, final String bookName , 
    		final String FKid,final String author,final String publisher,final String date,final String booksize, Spanned spanned)
	{				
		final DownloadedBookDialog cmDialog = new DownloadedBookDialog(getActivity());
		cmDialog.setContent(DownloadedBookDialog.DOWNLOADED_BOOK,bookName,author,publisher,date,booksize,bookUrl,spanned);
		
		imageLoader.DisplayImage(bookUrl, cmDialog.getBookIcon());
		
//		Log.i(" FK ", bookPath  + "   VALUE");
		cmDialog.setPossitiveButton(DownloadedBookDialog.DELETE, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				if(bookPath!=null)
				deleteDownloadedBook(pkId , bookPath);
				cmDialog.cancel();			
							
			}
		});
		
		
		cmDialog.setNegativeButton(DownloadedBookDialog.RATE, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				rateTheBookDialog(bookid); 
				cmDialog.cancel();					
			}
		});
		
		cmDialog.setNeutralButton1(DownloadedBookDialog.READ, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				
				if(bookPath.contains(".pdf")){
				File f = new File(bookPath);
				pdfView(f,bookName);
				}else{
				FBReader.openBookActivityMy(getActivity(), null, null,false,bookPath);
				
				}
				cmDialog.cancel();
			}
		});
		
		if(FKid == null)
		{	
		
		cmDialog.setNeutralButton3(DownloadedBookDialog.ADD_TO_SHELF, new OnClickListener() 
		{	
			public void onClick(View v) 
			{
				
				if(verticalList.size() != 0)
				{
                 final ArrayList<String> bookShelfIdList = new ArrayList<String>();
				
				AlertDialog.Builder builderSingle = new AlertDialog.Builder(
			            getActivity());
			    builderSingle.setIcon(R.drawable.evendor_logo02);
			    builderSingle.setTitle("Select bookshelf");
			    final ArrayAdapter<String> arrayAdapter = new ArrayAdapter<String>(
			    		getActivity(),
			            android.R.layout.select_dialog_singlechoice);
			    for(int i = 0;i<verticalList.size();i++) 
			    {
			    //	 int shelfId = Integer.parseInt(bookShelfId);
			    //	 if(!verticalList.get(i).contains(bookShelfId)) 
			    	 {
			           arrayAdapter.add(verticalList.get(i).get(0));
			           bookShelfIdList.add(verticalList.get(i).get(2));
			    	 }
			    }
			    builderSingle.setPositiveButton("AddShelf",
			            new DialogInterface.OnClickListener() {

			                @Override
			                public void onClick(DialogInterface dialog, int which) {
			                	createBookSelfDialog(getActivity(),pkId);
			                    dialog.dismiss();
			                }
			            });
			    
			    builderSingle.setNegativeButton("Cancel",
			            new DialogInterface.OnClickListener() {

			                @Override
			                public void onClick(DialogInterface dialog, int which) {
			                    dialog.dismiss();
			                }
			            });

			    builderSingle.setAdapter(arrayAdapter,
			            new DialogInterface.OnClickListener() {

			                @Override
			                public void onClick(DialogInterface dialog, int which) {
			                    String strName = arrayAdapter.getItem(which);
			                    final String bookSelfId = bookShelfIdList.get(which);
			                    
			                    
			                	// String id = listItem.get(position).get(4);
          					    int downloadedBookPK = Integer.parseInt(pkId);
          					    int bookShelfPk = Integer.parseInt(bookSelfId);
          					    
          						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
//          						fetchBookShelves();
//          						verListAdapter.notifyDataSetChanged();
          						
          						 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//          						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
          						 StartFragmentActivity.leftPane.setVisibility(View.GONE);
          						 setUpActionBar();
                                dialog.dismiss();
			                    
			                  /*  AlertDialog.Builder builderInner = new AlertDialog.Builder(
			                    		getActivity());
			                    builderInner.setMessage(strName);
			                    builderInner.setTitle("Your Selected shelf is");
			                    builderInner.setPositiveButton("Ok",
			                            new DialogInterface.OnClickListener() {

			                                @Override
			                                public void onClick(
			                                        DialogInterface dialog,
			                                        int which) {
			                                	
			                                	// String id = listItem.get(position).get(4);
			              					    int downloadedBookPK = Integer.parseInt(pkId);
			              					    int bookShelfPk = Integer.parseInt(bookSelfId);
			              					    
			              						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
//			              						fetchBookShelves();
//			              						verListAdapter.notifyDataSetChanged();
			              						
			              						 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//			              						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
			              						 StartFragmentActivity.leftPane.setVisibility(View.GONE);
			              						 setUpActionBar();
			                                    dialog.dismiss();
			                                }
			                            });
			                    builderInner.show();*/
			                }
			            });
			    builderSingle.show();
			    
				}
				else
				{
					noBookSelfDialog("There is no shelf created.Do you want to create?",pkId);
				}
				
				
				cmDialog.cancel();					
			}
			
			
		});
		
		}else {
			
			
			cmDialog.setNeutralButton3(DownloadedBookDialog.MOVE_TO_SHELF, new OnClickListener() 
			{	
				public void onClick(View v) 
				{
					
					if(verticalList.size() != 0)
					{
	                 final ArrayList<String> bookShelfIdList = new ArrayList<String>();
					
					AlertDialog.Builder builderSingle = new AlertDialog.Builder(
				            getActivity());
				    builderSingle.setIcon(R.drawable.evendor_logo02);
				    builderSingle.setTitle("Select bookshelf");
				    final ArrayAdapter<String> arrayAdapter = new ArrayAdapter<String>(
				    		getActivity(),
				            android.R.layout.select_dialog_singlechoice);
				    for(int i = 0;i<verticalList.size();i++) 
				    {
				    	 int shelfId = Integer.parseInt(FKid);
				    	 if(!verticalList.get(i).contains(FKid)) 
				    	 {
				           arrayAdapter.add(verticalList.get(i).get(0));
				           bookShelfIdList.add(verticalList.get(i).get(2));
				    	 }
				    }
				    builderSingle.setNegativeButton("Cancel",
				            new DialogInterface.OnClickListener() {

				                @Override
				                public void onClick(DialogInterface dialog, int which) {
				                    dialog.dismiss();
				                }
				            });

				    builderSingle.setAdapter(arrayAdapter,
				            new DialogInterface.OnClickListener() {

				                @Override
				                public void onClick(DialogInterface dialog, int which) {
				                    String strName = arrayAdapter.getItem(which);
				                    final String bookSelfId = bookShelfIdList.get(which);
				                    
				                    int downloadedBookPK = Integer.parseInt(pkId);
              					    int bookShelfPk = Integer.parseInt(bookSelfId);
              					    
              						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
//              						fetchBookShelves();
//              						verListAdapter.notifyDataSetChanged();
              						
              						 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//              						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
              						 StartFragmentActivity.leftPane.setVisibility(View.GONE);
              						 setUpActionBar();
                                    dialog.dismiss();
				                    /*AlertDialog.Builder builderInner = new AlertDialog.Builder(
				                    		getActivity());
				                    builderInner.setMessage(strName);
				                    builderInner.setTitle("Your Selected shelf is");
				                    builderInner.setPositiveButton("Ok",
				                            new DialogInterface.OnClickListener() {

				                                @Override
				                                public void onClick(
				                                        DialogInterface dialog,
				                                        int which) {
				                                	
				                                	// String id = listItem.get(position).get(4);
				              					    int downloadedBookPK = Integer.parseInt(pkId);
				              					    int bookShelfPk = Integer.parseInt(bookSelfId);
				              					    
				              						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
//				              						fetchBookShelves();
//				              						verListAdapter.notifyDataSetChanged();
				              						
				              						 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//				              						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
				              						 StartFragmentActivity.leftPane.setVisibility(View.GONE);
				              						 setUpActionBar();
				                                    dialog.dismiss();
				                                }
				                            });
				                    builderInner.show();*/
				                }
				            });
				    builderSingle.show();
				    
					}
					else
					{
						noBookSelfDialog("There is no shelf created.Do you want to create?",pkId);
					}
					
					
					cmDialog.cancel();					
				}
				
				
			});
			
			
		}
		
		
		cmDialog.setNeutralButton4(DownloadedBookDialog.CREATE_SHELF, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
					 StartFragmentActivity.leftPane.setVisibility(View.GONE);
					 setUpActionBar();
				cmDialog.cancel();					
			}
		});
		
		cmDialog.setNeutralButton2(DownloadedBookDialog.CANCEL, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				
         
				cmDialog.cancel();					
			}
		});
		
		cmDialog.show();
		
	}
    
    private void rateTheBookDialog(final String bookid) 
	{				
		final RateDownloadedBookDialog cmDialog = new RateDownloadedBookDialog(getActivity());
		cmDialog.setContent(RateDownloadedBookDialog.RATE_BOOK, "");
		cmDialog.setPossitiveButton(RateDownloadedBookDialog.RATE, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				AppSettings appSetting  = new AppSettings(getActivity());
				String userId = appSetting.getString("UserId");
				urlMakingObject.ratingTheBook(userId, bookid, cmDialog.getRate(), cmDialog.getComment());
				cmDialog.cancel();
			}
		});
		
		cmDialog.setNegativeButton(RateDownloadedBookDialog.CANCEL, new OnClickListener() 
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
		
    getActivity().runOnUiThread(new Runnable() {
    	String error = null;
    	JSONArray response = null;
    	JSONObject responseInJSONObject = null;
			public void run() 
			{
				
				try {
					 response = new JSONArray(result);
					 responseInJSONObject=response.getJSONObject(0);
					 error = responseInJSONObject.getJSONObject("ReviewResponse").getString("Success");
					
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				
				try {
					 response = new JSONArray(result);
					 responseInJSONObject=response.getJSONObject(0);
					 error = responseInJSONObject.getJSONObject("Error").getString("Message");
					 } catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
				successfullDialog(error);
				
			}
		});
		
	}
	
	
	private void deleteDownloadedBook(String id , String bookPath)
	{
		
		File file = new File(bookPath);
		
		int rowId = Integer.parseInt(id);
		String filename=file.getName();
		Log.e("booknaem-delet",""+rowId+" "+"filae"+file.getName());
		db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
	    dbHelper = new DBHelper(db, false);
		dbHelper.deleteItemFromList(DB.TABLE_DOWNLOADED_BOOK,DB.COLUMN_ID,rowId);
		if(file.exists())
		{
		Log.e("booknaem-delet","filae"+file.getName());
		try{
		file.delete();
		}catch(Exception e){
			e.printStackTrace();
		}
		}
		downloadedbookList = fetchBooksForShelves();
	
		
		mGridAdapter = new DownloadedBookImageGridAdapter(getActivity(), downloadedbookList,imageLoader);
    	
    	if (mGridView != null) {
            mGridView.setAdapter(mGridAdapter);
        }
		
	//mGridAdapter.notifyDataSetChanged();
		
	}

	@Override
	public void onError(final String errorMessage) {
		// TODO Auto-generated method stub
		
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
		cmDialog.setContent("Rate book", errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	
	
	private void noBookSelfDialog(String errorMsg,final String pkid) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
		cmDialog.setContent("Create shelf", errorMsg);
		cmDialog.setPossitiveButton(CMDialog.YES, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				createBookSelfDialog(getActivity(),pkid);

              			    
				
//				ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//				StartFragmentActivity.leftPane.setVisibility(View.GONE);
//					 setUpActionBar();
				cmDialog.cancel();					
			}
		});
		
		
		cmDialog.setNegativeButton(CMDialog.NO, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	
	private void setUpActionBar()
	{
		ActionBar actionBar = getActivity().getActionBar();
		 actionBar.setTitle("BookShelf"); 
		 
		 ((StartFragmentActivity) getActivity()).changeTheMenuItemIcon(R.id.bookshelveMenuButton);

	}
	
	private void resetColor(int id){

		switch (id) {
		case R.id.all:
			//			all.setBackgroundColor(Color.parseColor("#000000"));
			all.setBackgroundResource(R.drawable.roundright);
			all.setTextColor(Color.parseColor("#00f5ff"));
			break;
		case R.id.book:
			books.setBackgroundResource(R.drawable.cell_shape);
			books.setTextColor(Color.parseColor("#00f5ff"));
			break;

		case R.id.newspaper:
			newspaper.setBackgroundResource(R.drawable.roundleft);
			newspaper.setTextColor(Color.parseColor("#00f5ff"));
			break;

		case R.id.magazine:
			magazines.setBackgroundResource(R.drawable.cell_shape);
			magazines.setTextColor(Color.parseColor("#00f5ff"));
			break;
		case R.id.flyer:
			flyer.setBackgroundResource(R.drawable.cell_shape);
			//			flyer.setBackgroundColor(Color.parseColor("#000000"));
			flyer.setTextColor(Color.parseColor("#00f5ff"));
			break;

		}
	}
	
	private OnClickListener m_click=new OnClickListener() {

		@Override
		public void onClick(View v) {
			switch (v.getId()) {
			case R.id.all:
				selectTab(R.id.all);
				searchByCategoryName("");

				break;
			case R.id.book:
				selectTab(R.id.book);
				searchByCategoryName("eBook");
				break;

			case R.id.newspaper:
				selectTab(R.id.newspaper);
				searchByCategoryName("Newspapers");

				break;

			case R.id.magazine:
				selectTab(R.id.magazine);
				searchByCategoryName("Magazines");

				break;
			case R.id.flyer:
				selectTab(R.id.flyer);
				searchByCategoryName("Brochures");

				break;

			

			}

		}
	};
	
	private void selectTab(int id){

		switch (id) {
		case R.id.all:
			all.setBackgroundResource(R.drawable.rev_roundright);
			//all.setBackgroundColor(Color.parseColor("#00f5ff"));
			all.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.newspaper);
			resetColor(R.id.magazine);
			resetColor(R.id.flyer);
			
			break;
		case R.id.book:
			books.setBackgroundColor(Color.parseColor("#00f5ff"));
			books.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.all);
			resetColor(R.id.newspaper);
			resetColor(R.id.magazine);
			resetColor(R.id.flyer);
			break;

		case R.id.newspaper:
			newspaper.setBackgroundResource(R.drawable.rev_round_left);
//			newspaper.setBackgroundColor(Color.parseColor("#00f5ff"));
			newspaper.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.all);
			resetColor(R.id.magazine);
			resetColor(R.id.flyer);
			break;

		case R.id.magazine:
			magazines.setBackgroundColor(Color.parseColor("#00f5ff"));
			magazines.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.newspaper);
			resetColor(R.id.all);
			resetColor(R.id.flyer);
			break;
		case R.id.flyer:
//			flyer.setBackgroundResource(R.drawable.rev_roundright);
			flyer.setBackgroundColor(Color.parseColor("#00f5ff"));
			flyer.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.newspaper);
			resetColor(R.id.magazine);
			resetColor(R.id.all);
//			showListPopupbyDMT(flyer);
			break;

		}


	}
	
	private void searchByCategoryName(String CategoryName){

	
		{

			newdownloadedbookList1.clear();
			if(downloadedbookList!=null)
			for (int i = 0; i < downloadedbookList.size(); i++){


				String catname = null;

				try {
					catname = downloadedbookList.get(i).get(6);
				} catch (Exception e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}


				if ((catname.toLowerCase()).contains(CategoryName
						.toLowerCase())){
					
					newdownloadedbookList1.add(downloadedbookList.get(i));

				}
				
			}
		}
		
mGridAdapter = new DownloadedBookImageGridAdapter(getActivity(), newdownloadedbookList1,imageLoader);
    	
    	if (mGridView != null) {
            mGridView.setAdapter(mGridAdapter);
        }
    	/**@author MI
		 * added dated 10 April,2014
		 */
		if(newdownloadedbookList1.size()<1){
				
					if(CategoryName.equals("")){
						noBookDialog("You have no publications.", "eVendor", getActivity());
					}else{
							noBookDialog("You have no "+CategoryName+".", "eVendor",  getActivity());
					}
			}	
	}

	public  void noBookDialog(String errorMsg,String title , Context ctx) 
	{				
		final CMDialog cmDialog = new CMDialog(ctx);
		cmDialog.setContent(title, errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});

		cmDialog.show();
	}

	private void searchByName(String CategoryName){

		
		{

			downloadedbookList.clear();
			if(newdownloadedbookList!=null)
			for (int i = 0; i < newdownloadedbookList.size(); i++){


				String catname = null;

				try {
					catname = newdownloadedbookList.get(i).get(2);
				} catch (Exception e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}


				if ((catname.toLowerCase()).contains(CategoryName
						.toLowerCase())){

					downloadedbookList.add(newdownloadedbookList.get(i));
					//newdownloadedbookList1.add(newdownloadedbookList.get(i));

				}

			}

			
		}
		
		selectTab(R.id.all);
mGridAdapter = new DownloadedBookImageGridAdapter(getActivity(), downloadedbookList,imageLoader);
    	
    	if (mGridView != null) {
            mGridView.setAdapter(mGridAdapter);
        }
		
	
		
	}
	
	 public void createBookSelfDialog(final Context context,final String pkid) 
		{				
			final CreateBookSelfDialog cmDialog = new CreateBookSelfDialog(context);
			cmDialog.setContent(CreateBookSelfDialog.CREATE_SELF, "");
			cmDialog.setCancelable(false);
			
			cmDialog.setPossitiveButton(CreateBookSelfDialog.CREATE, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					EditText name = cmDialog.geNametEditText();
					
					EditText color = cmDialog.getColorEditText();
					
					String booksheleveName = cmDialog.getComment();
					String boolshelveColor = cmDialog.getColor();
					
					if(!name.getText().toString().equals("") && !color.getText().toString().equals(""))
					{
					 AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (context);
	                 appDataSavedMethod.SaveBookShelve(booksheleveName, boolshelveColor);
	                 
	                 fetchBookShelves();
	                 
	                 
	                 
//	                 if(verticalList.size() == 0)
//	    		     {
//	    		     
////	    		     headerView.setVisibility(View.GONE);
//	    		     }
//	                 verListAdapter.notifyDataSetChanged();
	                 
					
				
					
					  final ArrayList<String> bookShelfIdList = new ArrayList<String>();
						
						AlertDialog.Builder builderSingle = new AlertDialog.Builder(
					            getActivity());
					    builderSingle.setIcon(R.drawable.evendor_logo02);
					    builderSingle.setTitle("Select bookshelf");
					    final ArrayAdapter<String> arrayAdapter = new ArrayAdapter<String>(
					    		getActivity(),
					            android.R.layout.select_dialog_singlechoice);
					    for(int i = 0;i<verticalList.size();i++) 
					    {
					    //	 int shelfId = Integer.parseInt(bookShelfId);
					    //	 if(!verticalList.get(i).contains(bookShelfId)) 
					    	 {
					           arrayAdapter.add(verticalList.get(i).get(0));
					           bookShelfIdList.add(verticalList.get(i).get(2));
					    	 }
					    }
					    builderSingle.setNegativeButton("Cancel",
					            new DialogInterface.OnClickListener() {

					                @Override
					                public void onClick(DialogInterface dialog, int which) {
					                    dialog.dismiss();
					                }
					            });

					    builderSingle.setAdapter(arrayAdapter,
					            new DialogInterface.OnClickListener() {

					                @Override
					                public void onClick(DialogInterface dialog, int which) {
					                    String strName = arrayAdapter.getItem(which);
					                    final String bookSelfId = bookShelfIdList.get(which);
					                    int downloadedBookPK = Integer.parseInt(pkid);
	              					    int bookShelfPk = Integer.parseInt(bookSelfId);
	              					    
	              						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
//	              						fetchBookShelves();
//	              						verListAdapter.notifyDataSetChanged();
	              						
	              						 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//	              						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
	              						 StartFragmentActivity.leftPane.setVisibility(View.GONE);
	              						 setUpActionBar();
					                   /* AlertDialog.Builder builderInner = new AlertDialog.Builder(
					                    		getActivity());
					                    builderInner.setMessage(strName);
					                    builderInner.setTitle("Your Selected shelf is");
					                    builderInner.setPositiveButton("Ok",
					                            new DialogInterface.OnClickListener() {

					                                @Override
					                                public void onClick(
					                                        DialogInterface dialog,
					                                        int which) {
					                                	
					                                	// String id = listItem.get(position).get(4);
					              					    int downloadedBookPK = Integer.parseInt(pkid);
					              					    int bookShelfPk = Integer.parseInt(bookSelfId);
					              					    
					              						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
//					              						fetchBookShelves();
//					              						verListAdapter.notifyDataSetChanged();
					              						
					              						 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//					              						 MainFragmentActivity.leftPane.setVisibility(View.GONE);
					              						 StartFragmentActivity.leftPane.setVisibility(View.GONE);
					              						 setUpActionBar();
					                                    dialog.dismiss();
					                                }
					                            });
					                    builderInner.show();*/
					                }
					            });
					    builderSingle.show();

				
					cmDialog.cancel();
				
					}	else
					{
						Toast.makeText(getActivity(), "All fields are mandatary",Toast.LENGTH_SHORT).show();
//						showErrorDialog("All fields are mandatary");
					}
			}
			
			});
			
			cmDialog.setNegativeButton(CreateBookSelfDialog.CANCEL, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					InputMethodManager imm = (InputMethodManager)context.getSystemService(
						      Context.INPUT_METHOD_SERVICE);
						imm.hideSoftInputFromWindow(v.getWindowToken(), 0);
					cmDialog.cancel();
							
				}
			});
			cmDialog.show();
		}
	 
	 public void pdfView(File f, String bookName) {
//			Log.i("pdf", "post intent to open file " + f);
			/*Intent intent = new Intent();
			intent.setDataAndType(Uri.fromFile(f), "application/pdf");
			intent.setClass(getActivity(), OpenFileActivity.class);
			intent.setAction("android.intent.action.VIEW");
			this.startActivity(intent);
			*/
		 	
			Uri uri = Uri.fromFile(f);
			Intent intentty = new Intent(getActivity(), MuPDFActivity.class);
			intentty.setAction(Intent.ACTION_VIEW);
			intentty.setData(uri);
			intentty.putExtra("bookName", bookName);
			intentty.putExtra("firstView", true);
				startActivity(intentty);
				
	    }

}
