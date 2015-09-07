package com.evendor.Android;

import android.app.Activity;
import android.os.Bundle;
import android.support.v4.app.ListFragment;
import android.view.View;
import android.widget.ListView;
import android.widget.TextView;

import com.evendor.Android.R;
import com.evendor.adapter.MainListAdapter;

public class LibraryLeftpane extends ListFragment {

	OnLibraryRowSelected mRowListener;
	TextView bookCategory;
	public static MainListAdapter listAdapter;
   // ListFragment is a very useful class that provides a simple ListView inside of a Fragment.
   // This class is meant to be sub-classed and allows you to quickly build up list interfaces
   // in your app.
	 
	 @Override
	    public void onAttach(Activity activity) {
	        super.onAttach(activity);
	        try {
	        	mRowListener = (OnLibraryRowSelected) activity;
	        } catch (ClassCastException e) {
	            throw new ClassCastException(activity.toString() + " must implement OnLibraryRowSelected");
	        }
	    }

   
   @Override
   public void onActivityCreated(Bundle savedInstanceState) {
       super.onActivityCreated(savedInstanceState);
       
       Activity activity = getActivity();
       
       if (activity != null) {
    	   
    	   bookCategory = (TextView) activity.findViewById(R.id.library_title);
    	   
    	   String title =null;
    		try {
    			if(StartFragmentActivity.catList != null)
    			{
    			//title = MainFragmentActivity.recoveryTask.getCategoryLibraryJsonArray().getJSONObject(0).getString("genre");
    			}
    		} catch (Exception e) {
    			// TODO Auto-generated catch block
    			e.printStackTrace();
    		}
    	   
    	   bookCategory.setText(title);
           getListView().setDivider(null);
         listAdapter = new MainListAdapter(activity, StartFragmentActivity.catList);
           setListAdapter(listAdapter);
       }
   }

   @Override
   public void onListItemClick(ListView l, View v, int position, long id) {
       Activity activity = getActivity();
       
       ApplicationManager.POSITION = position;
       
       String title =null;
	try {
		title = StartFragmentActivity.catList.get(position).getGenre();//recoveryTask.getCategoryLibraryJsonArray().getJSONObject(position).getString("genre");
	} catch (Exception e) {
		// TODO Auto-generated catch block
		e.printStackTrace();
	}
     //  bookCategory.setText(title);
       
       if (activity != null) {   
    	   listAdapter.notifyDataSetChanged();
    	//   MainListAdapter listAdapter = (MainListAdapter) getListAdapter();
    	   mRowListener.onRowSelected(position);
           }
   }
   
   
   public interface OnLibraryRowSelected {
       public void onRowSelected(int postion );
   }

   
   
   
      
}

	

	


