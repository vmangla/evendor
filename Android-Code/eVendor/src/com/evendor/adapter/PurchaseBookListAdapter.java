package com.evendor.adapter;


import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.content.Context;
import android.text.Html;
import android.text.Spanned;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.evendor.Android.ApplicationManager;
import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;

public class PurchaseBookListAdapter extends BaseAdapter {
	Context mContext;
    private class ViewHolder {
        public TextView category;
        public TextView title;
        public TextView publisher;
        public TextView date;
        public TextView price;
        public ImageView icon;
        String bookId;
        
    }
    
    ImageLoader imageLoader;
    private JSONArray mLocations;
    private LayoutInflater  mInflater;
    private int  lengh;
    
    
    public PurchaseBookListAdapter(Context context, JSONArray locations , int lengh,ImageLoader imageLoader) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        mLocations = locations;
        mContext = context;
        this.lengh = lengh;
        this.imageLoader = imageLoader;
    }
    
    @Override
    public int getCount() {
    	if (mLocations != null) {
            return lengh;
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
            view = mInflater.inflate(R.layout.purchase_list_row, parent, false);
            
            viewHolder = new ViewHolder();
            viewHolder.category = (TextView) view.findViewById(R.id.category);
            viewHolder.title = (TextView) view.findViewById(R.id.title);
            viewHolder.publisher = (TextView) view.findViewById(R.id.society);
            viewHolder.date = (TextView) view.findViewById(R.id.date);
            viewHolder.price = (TextView) view.findViewById(R.id.price);
            viewHolder.icon = (ImageView) view.findViewById(R.id.icon);
            
            
            
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
        Spanned price = null;
        
		try {
			JSONObject jsonObject = mLocations.getJSONObject(position);
			ProductThumbnail = jsonObject.getString("ProductThumbnail");
			title = jsonObject.getString("title");
			/**@see
			 * changed the two attributes.
			 */
			category = jsonObject.getString("genre");
			//category = jsonObject.getString("category_name");
			publisher = jsonObject.getString("publisher_name");
			price = Html.fromHtml(jsonObject.getString("priceText"));
			date = ApplicationManager.getconvertdate1(jsonObject.getString("add_date"));
			viewHolder.bookId = jsonObject.getString("id");
		//	date = jsonObject.getString("add_date");
			
			Log.i("DATE", jsonObject.getString("add_date"));
			
			 Log.i("CategoryName", ProductThumbnail + "");
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
        
		imageLoader.DisplayImage(ProductThumbnail, viewHolder.icon);
       
        viewHolder.title.setText(title);
        viewHolder.category.setText(category);
        viewHolder.publisher.setText(publisher);
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
