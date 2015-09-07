package com.evendor.adapter;

import java.util.List;

import android.app.Activity;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import com.evendor.Android.R;

public class TimeAdapter extends ArrayAdapter<String>{
	 private Activity context;
     List<String> data = null;

	public TimeAdapter(Context context, int textViewResourceId,
			List<String> objects) {
		super(context, textViewResourceId, objects);
		 this.context = (Activity) context;
         this.data = objects;
	}

	@Override
	public View getView(int position, View convertView, ViewGroup parent) {
		// TODO Auto-generated method stub
		return super.getView(position, convertView, parent);
	}

	@Override
	public View getDropDownView(int position, View convertView, ViewGroup parent) {
		// TODO Auto-generated method stub
		 View row = convertView;
         if(row == null)
         {
             LayoutInflater inflater = context.getLayoutInflater();
             row = inflater.inflate(R.layout.spinner_layout, parent, false);
         }

         String item = data.get(position);

         if(item != null)
         {   // Parse the data from each object and set it.
           
             TextView myCountry = (TextView) row.findViewById(R.id.countryName);
            
             if(myCountry != null)
                 myCountry.setText(item);

         }

         return row;
     }


	
	

}
