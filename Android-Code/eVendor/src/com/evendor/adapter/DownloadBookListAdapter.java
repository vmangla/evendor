package com.evendor.adapter;


import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.content.Context;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;

public class DownloadBookListAdapter extends BaseAdapter {
	Context mContext;
	ImageLoader imageLoader;
	
    private class ViewHolder {
    	public ImageView bookIcon;
        public TextView title;
        public TextView author;
        public TextView society;
        public TextView date;
        public TextView price;
        
        
    }
    
    private JSONArray downloadJsonArray;
    private LayoutInflater  mInflater;
    
    public DownloadBookListAdapter(Context context, JSONArray downloadJsonArray) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        this.downloadJsonArray = downloadJsonArray;
        mContext = context;
        imageLoader = new ImageLoader(context);
    }
    
    
    

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        
        View       view = convertView;
        ViewHolder viewHolder;
        
        if (view == null) {
            view = mInflater.inflate(R.layout.download_row, parent, false);
            
            viewHolder = new ViewHolder();
           
            viewHolder.bookIcon  = (ImageView) view.findViewById(R.id.book_icon);
            viewHolder.title  = (TextView) view.findViewById(R.id.title);
            viewHolder.author  = (TextView) view.findViewById(R.id.author);
            viewHolder.society  = (TextView) view.findViewById(R.id.society);
            viewHolder.date  = (TextView) view.findViewById(R.id.date);
            viewHolder.price  = (TextView) view.findViewById(R.id.price);
            
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
        
        String bookicon = null;
        String title = null;
        String category = null;
        String society = null;
        String genre = null;
        String date = null;
        String price = null;
		try {
			JSONObject jsonObject = downloadJsonArray.getJSONObject(position);
			bookicon = jsonObject.getString("ProductThumbnail");
			title = jsonObject.getString("Title");
			category = jsonObject.getString("Category");
			society = jsonObject.getString("StoreName");
			genre = jsonObject.getString("Genre");
			price = jsonObject.getString("Price");
			 Log.e("----------","price-"+price);
			 
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
        
		imageLoader.DisplayImage(bookicon, viewHolder.bookIcon);
        viewHolder.title.setText(title);
        viewHolder.author.setText(category);
        viewHolder.society.setText(society);
        viewHolder.price.setText(price);
        
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
		return downloadJsonArray.length();
	}

}
