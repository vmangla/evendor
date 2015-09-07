package com.evendor.adapter;


import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;

import com.evendor.Android.ApplicationManager;
import com.evendor.Android.R;

public class CreditBookListAdapter extends BaseAdapter {
	Context mContext;
    private class ViewHolder {
        public TextView category;
        public TextView title;
      
        public TextView date;
        public TextView price;
       
        String bookId;
        
    }
    
//    ImageLoader imageLoader;
    private JSONArray mLocations;
    private LayoutInflater  mInflater;
    private int  length;
    
    
    public CreditBookListAdapter(Context context, JSONArray locations , int length) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        mLocations = locations;
        mContext = context;
        this.length = length;
//        imageLoader = new ImageLoader(context);
    }
    
    @Override
    public int getCount() {
    	if (mLocations != null) {
            return length;
        }
    	else
    	{
        
        return 0;
    	}
    }

   

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        
        View       view = convertView;
        final ViewHolder viewHolder;
        
        if (view == null) {
            view = mInflater.inflate(R.layout.purchase_credit_list_row, parent, false);
            
            viewHolder = new ViewHolder();
            viewHolder.category = (TextView) view.findViewById(R.id.category);
            viewHolder.title = (TextView) view.findViewById(R.id.title);
           
            viewHolder.date = (TextView) view.findViewById(R.id.date);
            viewHolder.price = (TextView) view.findViewById(R.id.price);
            
            
            
            
            view.setTag(viewHolder);
        }
        else {
            viewHolder = (ViewHolder) view.getTag();
        }
        
        if (position % 2 == 1) {
        	
        	
            view.setBackgroundResource(R.drawable.download_bg1);
        } else {
        	view.setBackgroundResource(R.drawable.download_bg2);
        }
        
        
        String ProductThumbnail = null;
        String category = null;
        String title = null;
        String publisher = null;
        String date = null;
        String price = null;
        
		try {
			JSONObject jsonObject = mLocations.getJSONObject(position);
			
			title = "Payment Id : "+ jsonObject.getString("payment_id");
			category = "Transaction Id : "+jsonObject.getString("transaction_id");
			price = "$"+jsonObject.getString("amount");
			date = ApplicationManager.getconvertdate1(jsonObject.getString("purchase_date"));
			
			
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
     
       
        viewHolder.title.setText(title);
        viewHolder.category.setText(category);
       
        viewHolder.date.setText(date);
        viewHolder.price.setText(price);
        
        
       /* view.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				
				if(isPresent(viewHolder.bookId));
				
			}
		});
        */
       
        
        return view;
    }
    
    
    @Override
	public Object getItem(int arg0) {
		// TODO Auto-generated method stub
		return null;
	}

	@Override
	public long getItemId(int arg0) {
		// TODO Auto-generated method stub
		return 0;
	}
	
	
	
	
	



}
