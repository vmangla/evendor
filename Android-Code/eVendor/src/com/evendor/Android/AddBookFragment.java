package com.evendor.Android;

import java.util.ArrayList;

import org.json.JSONObject;

import android.app.ActionBar;
import android.app.Activity;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.ListAdapter;
import android.widget.ListPopupWindow;
import android.widget.TextView;

import com.evendor.Android.R;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.utils.CMDialog;

public class AddBookFragment extends Fragment  {
	
	View rootView;
	EditText booksheleveName;
	EditText boolshelveColor;
	Button create;
	ActionBar actionBar;
	private SQLiteDatabase db;
	private DBHelper dbHelper             =null;
	private boolean editTextEntryCheck ;
	private ArrayList<ArrayList<String>> listToPopUp;
	String DownloadBookTablerowIds; 
	String bookSelfId;
	ListPopupWindow popup;

	@Override
	public void onCreate(Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onCreate(savedInstanceState);
		}
	
	public View onCreateView(LayoutInflater inflater, ViewGroup container,Bundle savedInstanceState) 
	{
		
		 super.onActivityCreated(savedInstanceState);
         rootView= inflater.inflate(R.layout.add_book_setting, container, false);
 
		 return rootView;
	}

	public void onActivityCreated(Bundle savedInstanceState) 
     {
		 super.onActivityCreated(savedInstanceState);
		 
		 Activity activity =getActivity();
		 
		 listToPopUp = new ArrayList<ArrayList<String>>();

		 booksheleveName  = (EditText) activity.findViewById(R.id.shelve_name);
		 
		 boolshelveColor  = (EditText) activity.findViewById(R.id.shelve_color);
		 
		 booksheleveName.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				
				listToPopUp = fetchBooksShelvesName();
				if(listToPopUp.size() !=0)
				{
				editTextEntryCheck = true;
				showListPopup(booksheleveName);
				}
				else
				{
					showErrorDialog("No bookshelf is created");
				}
			}
		});
		 
		 boolshelveColor.setOnClickListener(new View.OnClickListener() {
				
				@Override
				public void onClick(View v) {
					// TODO Auto-generated method stub
					
					listToPopUp = fetchBooksForShelves();
					if(listToPopUp.size() !=0)
					{
					editTextEntryCheck = false;
					showListPopup(boolshelveColor);
					}
					else
					{
						showErrorDialog("There is no book to add");
					}
				}
			});
		
		 
		 create = (Button) activity.findViewById(R.id.create);

		 create.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				
				if(!booksheleveName.getText().toString().equals("") && !boolshelveColor.getText().toString().equals(""))
				{
					int downloadedBookPK = Integer.parseInt(DownloadBookTablerowIds);
					int bookShelfPk = Integer.parseInt(bookSelfId);
					new AppDataSavedMethhods(getActivity()).updateDownloadedBook(downloadedBookPK, bookShelfPk);
					showSuccessDialog("Book Sucessfully Added");
				
				/* AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
                 appDataSavedMethod.SaveBookShelve(booksheleveName.getText().toString(), boolshelveColor.getText().toString());
                 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
				 MainFragmentActivity.leftPane.setVisibility(View.GONE);
				 setUpActionBar();*/
				}
				else
				{
					showErrorDialog("All fields are mandatary");
				}
			}
		});
		 	
	}
	
	@Override
	public void onDetach() {
		// TODO Auto-generated method stub
		super.onDetach();
	}
	
	private void showErrorDialog(String errorMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
		cmDialog.setContent(CMDialog.ADD_BOOK, errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	private void showSuccessDialog(String successMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
		cmDialog.setContent(CMDialog.ADD_BOOK, successMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				booksheleveName.setText("");
				boolshelveColor.setText("");
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	
	private void setUpActionBar()
	{
		 actionBar = getActivity().getActionBar();
		 actionBar.setTitle("BookShelve"); 

	}
	
	public void showListPopup(View anchor) {
        popup = new ListPopupWindow(getActivity());
        popup.setAnchorView(anchor);

        ListAdapter adapter = new MyAdapter(getActivity());
        popup.setAdapter(adapter);

        popup.show();
    }

    public  class MyAdapter extends BaseAdapter implements ListAdapter {
       
    	private final String[] list = new String[] {"Blue","Green","Orange"};
        private Activity activity;
        private static final int textid = 1234;
        
        JSONObject jsonObject = null;
        String country = null;

        public MyAdapter(Activity activity) {
            this.activity = activity;
        }

        @Override
        public int getCount() {
            return listToPopUp.size();
        }

        @Override
        public Object getItem(int position) {
            return listToPopUp.get(position);
        }

        @Override
        public long getItemId(int position) {
            return position;
        }

        
        @Override
        public View getView(final int position, View convertView, ViewGroup parent) {
            TextView text = null;
            if (convertView == null) {
                LinearLayout layout = new LinearLayout(activity);
                layout.setOrientation(LinearLayout.HORIZONTAL);

                text = new TextView(activity);
                LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, 30);
                llp.setMargins(5, 0, 0, 0);
                text.setLayoutParams(llp);
                text.setId(textid);

                layout.addView(text);

                convertView = layout;
            }
            else {
                text = (TextView)convertView.findViewById(textid);
            }
            text.setText(listToPopUp.get(position).get(0));
            
            convertView.setOnClickListener(new View.OnClickListener() {
				
				@Override
				public void onClick(View v) {
					// TODO Auto-generated method stub
					if(editTextEntryCheck)
					{
					booksheleveName.setText(listToPopUp.get(position).get(0));
					bookSelfId = listToPopUp.get(position).get(1);
					}
					else
					{
					boolshelveColor.setText(listToPopUp.get(position).get(0));
					DownloadBookTablerowIds = listToPopUp.get(position).get(1);
					}
					
					
					popup.dismiss();
					
				}
			});
            return convertView;
        }
    }
    
    
    
    
    private ArrayList<ArrayList<String>> fetchBooksForShelves()
    {
       db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
       dbHelper = new DBHelper(db, false);
       Cursor c= dbHelper.fetchDownloadedBookWhenFKBlank(DB.TABLE_DOWNLOADED_BOOK);
       
       Log.i("CURSOR_COUNT", c.getCount() +"");
       ArrayList<ArrayList<String>> downloadBooks = new ArrayList<ArrayList<String>> ();
       

       if(c!=null && c.getCount()!=0)
       {
    	   if(c.moveToFirst())
	    	{
	    	do{
	       ArrayList<String> arrayList = new ArrayList<String> ();
	       String bookTitle    = c.getString(c.getColumnIndex(DB.COLUMN_BOOK_NAME));
	       String id    = Integer.toString(c.getInt(c.getColumnIndex(DB.COLUMN_ID)));
	       arrayList.add(bookTitle);
	       arrayList.add(id);
	       downloadBooks.add(arrayList);
	    	}while(c.moveToNext());
   	       }
    	   
    	   
       }

       db.close();
       c.close();
       
       return downloadBooks;
   }
    
    
    private ArrayList<ArrayList<String>> fetchBooksShelvesName()
    {
       db = getActivity().openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
       dbHelper = new DBHelper(db, false);

       Cursor c= dbHelper.fetchFromTable(DB.TABLE_BOOKSHELF);
       
       ArrayList<ArrayList<String>> bookShelevesName = new ArrayList<ArrayList<String>> ();
       
       

       if(c!=null && c.getCount()!=0)
       {
    	   if(c.moveToFirst())
	    	{
	    	do{
	       ArrayList<String> arrayList = new ArrayList<String> ();
	       String bookTitle    = c.getString(c.getColumnIndex(DB.COLUMN_SHELF_NAME));
	       String id    = Integer.toString(c.getInt(c.getColumnIndex(DB.COLUMN_ID)));
	       arrayList.add(bookTitle);
	       arrayList.add(id);
	       bookShelevesName.add(arrayList);
	    	}while(c.moveToNext());
   	       }
    	  
       }

       db.close();
       c.close();
       
       return bookShelevesName;
   }
      
}

	

	


