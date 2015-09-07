package com.evendor.Android;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLConnection;
import java.util.ArrayList;

import org.geometerplus.android.fbreader.FBReader;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.text.Html;
import android.text.Spanned;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.AdapterView.OnItemLongClickListener;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.artifex.mupdfdemo.MuPDFActivity;
import com.evendor.Android.R;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.utils.BookSelfManageDialog;
import com.evendor.utils.CMDialog;
import com.evendor.utils.CreateBookSelfDialog;
import com.evendor.utils.EvendorDialog;
import com.evendor.utils.RateDownloadedBookDialog;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;



public class BookShelveFragment extends Fragment implements ServiceResultListener {
	

	private VerticalAdapter verListAdapter;
	UrlMakingClass urlMakingObject;
	private static  final String TAG = "BookShelveFragment";
	private static final String LAYOUT_INFLATER_SERVICE = null;
	ImageLoader imageLoader;
	//ArrayList<ImageLoader> imageList = new ArrayList<ImageLoader>();
	JSONArray jsonArrayForVerticalList;
	ArrayList<ArrayList<String>> verticalList = new ArrayList<ArrayList<String>>();;
	String titleDialog ;
	private ListView mlistview;
	String publisherDialog ;
	String bookDescriptionDialog ;
	String imageurlDialog ;
	boolean  setDeleteVisibility ;
//	ArrayList<Bitmap> image =new ArrayList<Bitmap>();
	 URL url;
	 Bitmap bmp;
	 private SQLiteDatabase db;
	 private DBHelper dbHelper             =null;
	 
	 PopupWindow popupWindow;
	 boolean isPopupWindowShow;
	 
	 View headerView;
	 
	

   
   @Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {
	   imageLoader = new ImageLoader(getActivity());
	   View view = inflater.inflate(R.layout.book_shelf_new, container, false);
	   mlistview=(ListView) view.findViewById(R.id.listView1);
	   Activity activity = getActivity();
//     verticalList = new ArrayList<ArrayList<String>>();
     urlMakingObject= new UrlMakingClass(activity);
	   urlMakingObject.setServiceResultListener(this);
	   StartFragmentActivity.addself.setVisible(false);
     fetchBookShelves();
    
	  
   Button btn= (Button) view.findViewById(R.id.button1);
  btn.setOnClickListener(new OnClickListener() {
		
		@Override
		public void onClick(View v) {
			StartFragmentActivity.hideSoftKeyboard(getActivity());
			createBookSelfDialog(getActivity());	// TODO Auto-generated method stub
			
		}
	});

		     
		     if(verticalList.size() == 0)
		     {
		     
//		     headerView.setVisibility(View.GONE);
		     }
		     
		     verListAdapter = new VerticalAdapter(getActivity(), R.layout.book_shelf_row, verticalList);
		
		     mlistview.setAdapter(verListAdapter);
	   
//	 setListAdapter(verListAdapter);
//	footerView.setOnClickListener(new View.OnClickListener() {
//		
//		@Override
//		public void onClick(View v) {
//			// TODO Auto-generated method stub
//			
//			createBookSelfDialog( );
//			
//		}
//	});
	   return view;
	}


@Override
   public void onActivityCreated(Bundle savedInstanceState) {
       super.onActivityCreated(savedInstanceState);
       
//       Activity activity = getActivity();
////       verticalList = new ArrayList<ArrayList<String>>();
//       urlMakingObject= new UrlMakingClass(activity);
//	   urlMakingObject.setServiceResultListener(this);
//	   StartFragmentActivity.addself.setVisible(false);
//       fetchBookShelves();
//      
//  	  
////  	 View footerView = ((LayoutInflater) getActivity().getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.book_shelf__footer_row, null, false);
////     getListView().addFooterView(footerView);
//     headerView = ((LayoutInflater) getActivity().getSystemService(Context.LAYOUT_INFLATER_SERVICE)).inflate(R.layout.book_shelf_header, null, false);
//	 getListView().addHeaderView(headerView);
//     Button btn= (Button) headerView.findViewById(R.id.button1);
//    btn.setOnClickListener(new OnClickListener() {
//		
//		@Override
//		public void onClick(View v) {
//			createBookSelfDialog(getActivity());	// TODO Auto-generated method stub
//			
//		}
//	});
//
//		     
//		     if(verticalList.size() == 0)
//		     {
//		     
////		     headerView.setVisibility(View.GONE);
//		     }
//		     
//		     verListAdapter = new VerticalAdapter(getActivity(), R.layout.book_shelf_row, verticalList);
//		
//     
//  	   
//  	 setListAdapter(verListAdapter);
////  	footerView.setOnClickListener(new View.OnClickListener() {
////		
////		@Override
////		public void onClick(View v) {
////			// TODO Auto-generated method stub
////			
////			createBookSelfDialog( );
////			
////		}
////	});
  	
   }
   
   private class ViewHolder {
       public TextView textView;
       
   }

   private class VerticalAdapter extends ArrayAdapter<ArrayList<JSONArray>> {

		private int resource;
		TextView shelfName;
		JSONArray responseJSONArray;
		ArrayList<ArrayList<String>> verticalList;

		public VerticalAdapter(Context _context, int _ResourceId,
				ArrayList<ArrayList<String>> verticalList) {
			super(_context, _ResourceId);
			this.resource = _ResourceId;
			this.verticalList = verticalList;
			
			
		}
		
		@Override
		public void notifyDataSetChanged() {
			// TODO Auto-generated method stub
			super.notifyDataSetChanged();
		}

		@Override
		public int getCount() {
		// TODO Auto-generated method stub
		return verticalList.size();
		}

		@Override
		public View getView(int position, View convertView, ViewGroup parent) {
			View rowView = convertView ;
			 ViewHolder viewHolder;
	
			if (rowView == null) {
				viewHolder = new ViewHolder();
				rowView = LayoutInflater.from(getContext()).inflate(resource,
						null);
				
			//	shelfName = (TextView) rowView.findViewById(R.id.bookShelfName);
				
				viewHolder.textView = (TextView) rowView.findViewById(R.id.bookShelfName);
	            
				rowView.setTag(viewHolder);
				
			
				
			} else {
				  viewHolder = (ViewHolder) rowView.getTag();
				
			}
			
			if(position%2==0){
				rowView.setBackgroundColor(Color.parseColor("#585858"));
			}else {
				rowView.setBackgroundColor(Color.parseColor("#BDBDBD"));
				
			}
			
			String ShelfNameAfter = null;
			final String ShelfName =  verticalList.get(position).get(0);
			  final String selfColor = verticalList.get(position).get(1);
			
			if(!ShelfName.equals("") && !ShelfName.equals(""))
			{
				
			  if(ShelfName.length()>=11 && ShelfName.charAt(10)== ' ')
			  {
				  ShelfNameAfter = ShelfName.substring(0, 10) +"\n" + ShelfName.substring(10, ShelfName.length());
			  }
			  else if(verticalList.get(position).get(0).length()>=11)
			  {
				  ShelfNameAfter = ShelfName.substring(0, 10) +"-" +"\n" + ShelfName.substring(10, ShelfName.length());
			  }
			  else
			  {
				  ShelfNameAfter = verticalList.get(position).get(0);
			  }
			}
			
			
			//   int pos = (Integer) rowView.getTag();
			    viewHolder.textView.setText(ShelfNameAfter);
			  
			    if(selfColor.equals("Orange"))
			    	viewHolder.textView.setBackgroundDrawable(getResources().getDrawable(R.drawable.icon_orange));
			    else if(selfColor.equals("Green"))
			    	viewHolder.textView.setBackgroundDrawable(getResources().getDrawable(R.drawable.icon_green));
			    else
			    	viewHolder.textView.setBackgroundDrawable(getResources().getDrawable(R.drawable.icon_blue));
			    	
				
			ArrayList<ArrayList<String>> horizontalList;
			
			final int booSheleveId = Integer.parseInt(verticalList.get(position).get(2));
			
			
			rowView.setOnClickListener(new View.OnClickListener() {
				
				@Override
				public void onClick(View v) {
					// TODO Auto-generated method stub
					
					bookSelfManageDialog(ShelfName, false, booSheleveId, selfColor,getActivity()); 
					
				}
			});
			
			horizontalList = fetchBooksForShelves(booSheleveId);
			
			
			
			final HorizontalListView hListView = (HorizontalListView) rowView
					.findViewById(R.id.subListview);
			HorizontalAdapter horListAdapter = new HorizontalAdapter(
					getContext(), R.layout.book_shelf_item, horizontalList);
			
			
			hListView.setOnItemLongClickListener(new OnItemLongClickListener() {

				@Override
				public boolean onItemLongClick(AdapterView<?> arg0, View arg1,
						int arg2, long arg3) {
					// TODO Auto-generated method stub
					if(!setDeleteVisibility)
					setDeleteVisibility = true;
					else
					setDeleteVisibility = false;
					notifyDataSetChanged();
					return false;
				}
			});
			
	
			hListView.setOnItemClickListener(new OnItemClickListener() {
			   @Override
			   public void onItemClick(AdapterView<?> adapter, View view, int position, long arg) {
				   
				   ArrayList<ArrayList<String>> listItem = (ArrayList<ArrayList<String>>) hListView.getItemAtPosition(position);
				   
				   if(!setDeleteVisibility)
				   {
			       imageurlDialog = listItem.get(position).get(0);
			       String bookPath = listItem.get(position).get(1);
			       String bookTitle = listItem.get(position).get(2);
			       String bookAuthor = listItem.get(position).get(3);
			       String bookPublisher = listItem.get(position).get(4);
			       String bookPublisherDate = listItem.get(position).get(5);
			       String bookDescription = listItem.get(position).get(6);
			       String id = listItem.get(position).get(7);
			       String bookShelfId = listItem.get(position).get(8);
			       String bookId = listItem.get(position).get(9);
			       String bookSize = listItem.get(position).get(10);
			       
			       Log.e("ARRAY VALUE", ""+imageurlDialog);
			       
			       String descriptionHtmlString = "<font face='open sans'>"+bookDescription+"</font>" ;
			       
			       showBookDetailDialog(bookTitle,bookAuthor,bookPublisher,bookSize,bookPublisherDate,Html.fromHtml(descriptionHtmlString ),null,bookPath,id,bookShelfId,imageurlDialog,bookId,imageLoader);
			     
			   //   GetImageTask task = new GetImageTask(bookPath, bookTitle,bookAuthor,bookPublisher,bookPublisherDate, bookDescription ,id , bookShelfId,bookId);
			     //   task.execute(new String[] { imageurlDialog });
				   }
				   else
				   {
					   String id = listItem.get(position).get(7);
					   int downloadedBookPK = Integer.parseInt(id);
						//int bookShelfPk = Integer.parseInt(bookSelfId);
						new AppDataSavedMethhods(getActivity()).removeBookFromShelse(downloadedBookPK);

						notifyDataSetChanged();
					   
				   }
					} 
			});
			
			hListView.setAdapter(horListAdapter);

			return rowView;
		}
	}

	
	private class HorizontalAdapter extends ArrayAdapter<ArrayList<ArrayList<String>>> {
		
		private int resource;
		ArrayList<ArrayList<String>> BookList;
		
		public HorizontalAdapter(Context _context, int _textViewResourceId,
				ArrayList<ArrayList<String>> BookList) {
			super(_context, _textViewResourceId);
			this.resource = _textViewResourceId;
		//	imageLoader = new ImageLoader(_context);
			this.BookList = BookList;
//			if(BookList.size()!=0)
//			{
//				 headerView.setVisibility(View.VISIBLE);
//			}
		}
		
		 @Override
		public ArrayList<ArrayList<String>> getItem(int position) {
			// TODO Auto-generated method stub
			return BookList;
		}
		
		@Override
		public int getCount() {
			// TODO Auto-generated method stub
			return BookList.size();
		}

		@Override
		public View getView(final int position, View convertView, ViewGroup parent) {
			View retval = LayoutInflater.from(getContext()).inflate(
					this.resource, null);

            ImageView icon = (ImageView) retval.findViewById(R.id.icon);
            final ImageView delete = (ImageView) retval.findViewById(R.id.delete);
             TextView topText = (TextView) retval.findViewById(R.id.title);
			 TextView bottomText = (TextView) retval
					.findViewById(R.id.author);
			
			if(setDeleteVisibility)
				delete.setVisibility(View.VISIBLE);
			
			//new GetImageTask(icon).execute(new String[]{BookList.get(position).get(0)});
			              imageLoader.DisplayImage(BookList.get(position).get(0), icon);
			             // imageList.add(imageLoader);
	        return retval; 
		}
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
					showErrorDialog(error);
					
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
					showErrorDialog("Network error");
				}
				else if(errorMessage.equals("-390"))
				{
					Utils.ConnectionErrorDialog(getActivity());
				}
			}
		});
		
	}
	
	
	private void showBookDetailDialog(final String title,String author, String publisher,String bookSize,String publishedDate, Spanned spanned,final Bitmap imageurl,final String path, final String bookPkId , final String bookShelfId ,String price,final String bookid,final ImageLoader imageLoader2)
	{				
		final EvendorDialog cmDialog = new EvendorDialog(getActivity());
		String date = "";
		if(publishedDate != null)
		{
		date = ApplicationManager.getconvertdate1(publishedDate);
		}
		
		cmDialog.setContent(EvendorDialog.READ_BOOK,title,author,publisher, bookSize,date,spanned,price,imageLoader2);
		//cmDialog.setContent(EvendorDialog.READ_BOOK,title,author, date,publishedDate,spanned,price);
		
		//imageurlDialog
//		Log.e("  ","         1"+price);
		
		
		
		
//		imageLoader.DisplayImage(price, cmDialog.getBookIcon());
		
//		cmDialog.getBookIcon().setImageBitmap(imageurl);
		cmDialog.setReadButton(EvendorDialog.DELETE, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{ 
				

				Log.i("setReadButton", path +"setReadButton");
				
				   int downloadedBookPK = Integer.parseInt(bookPkId);
					//int bookShelfPk = Integer.parseInt(bookSelfId);
					new AppDataSavedMethhods(getActivity()).removeBookFromShelse(downloadedBookPK);
					
					 verListAdapter = new VerticalAdapter(getActivity(), R.layout.book_shelf_row, verticalList);
				     mlistview.setAdapter(verListAdapter);
				   
				cmDialog.cancel();	

			}
		});
		
		cmDialog.setPossitiveButton(EvendorDialog.READ, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{ 
				
//				Log.i("PATH", path +"");
				Log.i("PATH", path +"");
				if(path.contains(".pdf")){
					File f = new File(path);
					pdfView(f,title);
					}else
					{
				FBReader.openBookActivityMy(getActivity(), null, null,false,path);
					}
				cmDialog.cancel();	
//				FBReader.openBookActivityMy(getActivity(), null, null,false,path);
//				cmDialog.cancel();			
							
			}
		});
		
		
		cmDialog.setNegativeButton(EvendorDialog.CANCEL, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		
		cmDialog.setNegativeButton1(EvendorDialog.RATE, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				rateTheBookDialog(bookid);
				cmDialog.cancel();					
			}
		});
		
		cmDialog.setNeutralButton(EvendorDialog.MOVE, new OnClickListener() 
		{
			
			public void onClick(View v) 
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
			    	 int shelfId = Integer.parseInt(bookShelfId);
			    	 if(!verticalList.get(i).contains(bookShelfId)) 
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
			                    
			                    int downloadedBookPK = Integer.parseInt(bookPkId);
          					    int bookShelfPk = Integer.parseInt(bookSelfId);
          						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
          						fetchBookShelves();
          						 
          						if(verticalList.size() == 0)
          					     {
          					     
//          					     headerView.setVisibility(View.GONE);
          					     }
          						verListAdapter.notifyDataSetChanged();
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
			              					    int downloadedBookPK = Integer.parseInt(bookPkId);
			              					    int bookShelfPk = Integer.parseInt(bookSelfId);
			              						new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK,bookShelfPk);
			              						fetchBookShelves();
			              						 
			              						if(verticalList.size() == 0)
			              					     {
			              					     
//			              					     headerView.setVisibility(View.GONE);
			              					     }
			              						verListAdapter.notifyDataSetChanged();
			                                    dialog.dismiss();
			                                }
			                            });
			                    builderInner.show();*/
			                }
			            });
			    builderSingle.show();
				
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	
	private class GetImageTask extends AsyncTask<String, Void, Bitmap> {
		ImageView icon;
		String path;
		String bookTilte;
		String bookAuthor; 
		String bookPublisher; 
		String bookPublisherDate; 
		String bookDescription;
		String bookPkId;
		String bookShelfId;
		String bookId;
		final ProgressDialog dialog = new ProgressDialog(getActivity());
		
		/*GetImageTask(String path, String bookTilte,String bookAuthor,String bookPublisher,String bookPublisherDate , String bookDescription , String bookPkId ,String bookShelfId,String bookId)
		{
			
			this.path = path;
			this.bookTilte = bookTilte;
			this.bookAuthor = bookAuthor;
			this.bookPublisher = bookPublisher;
			this.bookPublisherDate = bookPublisherDate;
			this.bookDescription = bookDescription;
			this.bookPkId = bookPkId;
			this.bookShelfId = bookShelfId;
			this.bookId = bookId;
			
		}*/
		
		public GetImageTask(ImageView icon) {
			this.icon=icon;
		}

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
        	
        	if(!(result==null)){
        	
        		icon.setImageBitmap(result);
        		
        	}
        	// String descriptionHtmlString = "<b><font face='open sans'>"+bookDescription+"</font></b>" ;
        	 String descriptionHtmlString = "<font face='open sans'>"+bookDescription+"</font>" ;
        //	showBookDetailDialog(bookTilte,bookAuthor,bookPublisher,bookPublisherDate,Html.fromHtml(descriptionHtmlString ),result,path,bookPkId,bookShelfId,null,bookId);
        	
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
	
	private void fetchBookShelves()
    {
	   verticalList.clear();
       db = getActivity().openOrCreateDatabase("evendor.db", Context.MODE_PRIVATE, null);
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
	
	
	private ArrayList<ArrayList<String>> fetchBooksForShelves(int booSheleveId)
    {
       db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
       dbHelper = new DBHelper(db, false);

       Cursor c= dbHelper.fetchDownloadedBookForFKvalue(DB.TABLE_DOWNLOADED_BOOK,booSheleveId);
       
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
	       String bookAuthor    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_AUTHOR));
	       String bookPublisher    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_PUBLISHER));
	       String bookPublisherDate    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_PUBLISHED_DATE));
	       String bookDescription    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_DESCRIPTION));
	       String bookId    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_ID));
	       String bookSize    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_SIZE));
	       ArrayList<String> bookSheleveDetail = new ArrayList<String> ();
	       bookSheleveDetail.add(bookIcon);
	       bookSheleveDetail.add(bookPath);
	       bookSheleveDetail.add(bookTitle);
	       bookSheleveDetail.add(bookAuthor);
	       bookSheleveDetail.add(bookPublisher);
	       bookSheleveDetail.add(bookPublisherDate);
	       bookSheleveDetail.add(bookDescription);
	       bookSheleveDetail.add(id);
	       bookSheleveDetail.add(Integer.toString(booSheleveId));
	       bookSheleveDetail.add(bookId);
	       bookSheleveDetail.add(bookSize);
	       horizontalList.add(bookSheleveDetail);
	    	}while(c.moveToNext());
   	       }
       }

       db.close();
       c.close();
       
       return horizontalList;
   }
	
	private void fetchBookDetail(String bookPath)
    {
       db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
       dbHelper = new DBHelper(db, false);

       Cursor c= dbHelper.getDataWithColValue(DB.TABLE_DOWNLOADED_BOOK,bookPath);

       if(c!=null && c.getCount()!=0)
       {
         c.moveToFirst();
         String bookTilte    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_NAME));
         String bookDescription    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_DESCRIPTION));
       }
	  
       db.close();
       c.close();
   }
	
	
	private void bookSelfManageDialog(final String errorMsg, final boolean titleShow , final int rowId, final String selfColor,final Context context) 
	{				
		final BookSelfManageDialog cmDialog = new BookSelfManageDialog(getActivity(),titleShow);
		cmDialog.setCancelable(false);
		cmDialog.setContent(errorMsg, "");
		cmDialog.setPossitiveButton(BookSelfManageDialog.DELETE, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				    deleteDownloadedBook( rowId);
				    fetchBookShelves();
				    if(verticalList.size() == 0)
				     {
				     
//				     headerView.setVisibility(View.GONE);
				     }
			  	    verListAdapter.notifyDataSetChanged();
				    cmDialog.cancel();					
			}
		});
		
		cmDialog.setNegativeButton(BookSelfManageDialog.RENAME_SHELF, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
			
				renameBookSelf(rowId,errorMsg,selfColor,context);
				
				  cmDialog.cancel();					
			}
		});
		
		
		cmDialog.setNeutralButton(BookSelfManageDialog.CANCEL, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				  cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	private void deleteDownloadedBook(int rowId)
	{
		db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
	    dbHelper = new DBHelper(db, false);
		dbHelper.deleteItemFromList(DB.TABLE_BOOKSHELF,DB.COLUMN_ID,rowId);
		
    }
	
	 public void createBookSelfDialog(final Context context) 
		{				
			final CreateBookSelfDialog cmDialog = new CreateBookSelfDialog(context);
			cmDialog.setCancelable(false);
			cmDialog.setContent(CreateBookSelfDialog.CREATE_SELF, "");
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
	                 if(verticalList.size() == 0)
	    		     {
	    		     
//	    		     headerView.setVisibility(View.GONE);
	    		     }
	                 verListAdapter.notifyDataSetChanged();
	                 
					}
					else
					{
						showErrorDialog("All fields are mandatary");
					}
				
					cmDialog.cancel();
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
	 
	 
	 
	 private void renameBookSelf(final int rowId,final String errorMsg, String selfColor,final Context context ) 
		{				
			final CreateBookSelfDialog cmDialog = new CreateBookSelfDialog(getActivity());
			cmDialog.setContent(CreateBookSelfDialog.UPDATE_SELF+" '"+errorMsg+"'", "");
			cmDialog.setCancelable(false);
			Log.e("renameBookSelf", "renameBookSelf"+errorMsg);
			cmDialog.setComment(errorMsg);
			cmDialog.setColor(selfColor);
			
		
			
			cmDialog.setPossitiveButton(CreateBookSelfDialog.EDIT, new OnClickListener() 
			{
				
				public void onClick(View v) 
				{
					EditText name = cmDialog.geNametEditText();
					
					EditText color = cmDialog.getColorEditText();
					String booksheleveName = cmDialog.getComment();
					String boolshelveColor = cmDialog.getColor();
					
					
					if(!name.getText().toString().equals("") && !color.getText().toString().equals(""))
					{
						
  					 new AppDataSavedMethhods(getActivity()).updateBookSelf(rowId,booksheleveName,boolshelveColor);
	                 fetchBookShelves();
	                 if(verticalList.size() == 0)
	    		     {
	    		     
//	    		     headerView.setVisibility(View.GONE);
	    		     }
	                 verListAdapter.notifyDataSetChanged();
					}
					else
					{
						showErrorDialog("All fields are mandatary");
					}
					cmDialog.cancel();
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
	 
	 
	
	 
	 private void showErrorDialog(String errorMsg) 
		{				
			final CMDialog cmDialog = new CMDialog(getActivity());
			cmDialog.setContent(CMDialog.CREATE_SHELF, errorMsg);
			cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
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

	 
	 public void pdfView(File f, String title) {
			Log.i("pdf", "post intent to open file " + f);
			/*Intent intent = new Intent();
			intent.setDataAndType(Uri.fromFile(f), "application/pdf");
			intent.setClass(getActivity(), OpenFileActivity.class);
			intent.setAction("android.intent.action.VIEW");
			this.startActivity(intent);*/
			Uri uri = Uri.fromFile(f);
			Intent intentty = new Intent(getActivity(), MuPDFActivity.class);
			intentty.setAction(Intent.ACTION_VIEW);
			intentty.setData(uri);
			intentty.putExtra("bookName", title);
			intentty.putExtra("firstView", true);
				startActivity(intentty);
	    }
	 
	}

