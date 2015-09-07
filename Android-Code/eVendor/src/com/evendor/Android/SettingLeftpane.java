package com.evendor.Android;

import java.util.ArrayList;

import android.app.Activity;
import android.os.Bundle;
import android.support.v4.app.ListFragment;
import android.view.View;
import android.widget.ListView;

import com.evendor.adapter.SettingLeftAdapter;

public class SettingLeftpane extends ListFragment {
	

	OnArticleSelectedListener mListener;
	
	 ArrayList<String>  settingList;
	 
	 
	 @Override
	    public void onAttach(Activity activity) {
	        super.onAttach(activity);
	        try {
	            mListener = (OnArticleSelectedListener) activity;
	        } catch (ClassCastException e) {
	            throw new ClassCastException(activity.toString() + " must implement OnArticleSelectedListener");
	        }
	    }

   
   @Override
   public void onActivityCreated(Bundle savedInstanceState) {
       super.onActivityCreated(savedInstanceState);
       
       Activity activity = getActivity();
       
       if (activity != null) {
    	   
    	   settingList = new ArrayList<String>();
    	   settingList.add("Profile Setting");
    	   settingList.add("Change Password");
    	   settingList.add("About");
    	 //  settingList.add("Payment Information");
    	 //  settingList.add("Create Bookshelf");
    	 //   settingList.add("Add Books");
    	 //  settingList.add("User Type");
    	   settingList.add("Sign Out");
       	 
    	   getListView().setDivider(null);
    	   SettingLeftAdapter listAdapter = new SettingLeftAdapter(activity, settingList);
           setListAdapter(listAdapter);
       }
   }

   @Override
   public void onListItemClick(ListView l, View v, int position, long id) {
       Activity activity = getActivity();
       
     
       
       if (activity != null) {   
    	 //  MainListAdapter listAdapter = (MainListAdapter) getListAdapter();
           mListener.onArticleSelected(position);
           }
   }
   
   public interface OnArticleSelectedListener {
       public void onArticleSelected(int postion );
   }

      
}

	

	


