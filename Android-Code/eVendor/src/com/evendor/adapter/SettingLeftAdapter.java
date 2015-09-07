package com.evendor.adapter;


import java.util.ArrayList;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.evendor.Android.R;

public class SettingLeftAdapter extends BaseAdapter {

    private class ViewHolder {
        public TextView textView;
    }
    
   
   
    private ArrayList<String>  settingList;
    private LayoutInflater  mInflater;
    
    public SettingLeftAdapter(Context context, ArrayList<String>  settingList) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        this.settingList = settingList;
    }
    
    



    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        
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
        
        viewHolder.textView.setText(settingList.get(position));
        
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



@Override
public int getCount() {
	// TODO Auto-generated method stub
	return settingList.size();
}

}
