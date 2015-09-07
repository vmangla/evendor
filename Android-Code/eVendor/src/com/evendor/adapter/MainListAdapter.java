package com.evendor.adapter;


import java.util.ArrayList;

import org.json.JSONArray;

import android.content.Context;
import android.graphics.Color;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.evendor.Android.ApplicationManager;
import com.evendor.Android.R;
import com.evendor.Modal.Allcategories;

public class MainListAdapter extends BaseAdapter {

    private class ViewHolder {
        public TextView textView;
    }
    
    private JSONArray mLocations;
    private LayoutInflater  mInflater;
    ArrayList<Allcategories> mList;
    
//    public MainListAdapter(Context context, JSONArray locations) {
//        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
//        mLocations = locations;
//    }
    
    public MainListAdapter(Context context, ArrayList<Allcategories> list) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
//        mLocations = locations;
        mList=list;
    }
    
    @Override
    public int getCount() {
        if (mList != null) {
            return mList.size();//mLocations.length();
        }
        
        return 0;
    }

//    @Override
//    public Object getItem(int position) {
//        if (mLocations != null && position >= 0 && position < getCount()) {
//            return mLocations[position];
//        }
//        
//        return null;
//    }

//    @Override
//    public long getItemId(int position) {
//        if (mLocations != null && position >= 0 && position < getCount()) {
//            return mLocations[position].id;
//        }
//        
//        return 0;
//    }

    @Override
    public View getView(final int position, View convertView, ViewGroup parent) {
        
        View       view = convertView;
        ViewHolder viewHolder;
        
        if (view == null) {
            view = mInflater.inflate(R.layout.list_row, parent, false);
            
            viewHolder = new ViewHolder();
            viewHolder.textView = (TextView) view.findViewById(R.id.list_label);
            
            view.setTag(viewHolder);
        }
        else {
            viewHolder = (ViewHolder) view.getTag();
        }
        
        
        if(ApplicationManager.POSITION==position){
        	
        	viewHolder.textView.setTextColor(Color.parseColor("#ffffff"));
        }else{
        	viewHolder.textView.setTextColor(Color.parseColor("#00f5ff"));
        }
        
        String CategoryName = null;
		try {
			//JSONObject jsonObject = mLocations.getJSONObject(position);
			 CategoryName =mList.get(position).getGenre();// jsonObject.getString("genre");
			 
			 Log.i("CategoryName", CategoryName + "");
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
        
        
        
       
        
        viewHolder.textView.setText(CategoryName);
        
        return view;
    }

@Override
public Object getItem(int position) {
	// TODO Auto-generated method stub
	return null;
}

@Override
public long getItemId(int position) {
	// TODO Auto-generated method stub
	return 0;
}

}
