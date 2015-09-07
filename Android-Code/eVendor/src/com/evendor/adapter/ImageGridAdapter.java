package com.evendor.adapter;

import java.util.ArrayList;

import org.json.JSONArray;

import android.app.Activity;
import android.content.Context;
import android.text.Html;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.TextView;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;
import com.evendor.Modal.BooksData;

public class ImageGridAdapter extends BaseAdapter{
	
	 private int urlIndex = 0;
	 private Context context;

    private class ViewHolder {
        public ImageView imageView;
        public TextView imageGroup;
        public TextView  textView;
        public TextView  price;
        public TextView  text2;
        public TextView  text3;
        public TextView  text5;
        public RatingBar  rating;
		public TextView text001;
        
    }
    
    ImageLoader imageLoader;
    ArrayList<BooksData> mList;
    private JSONArray mLocations;
    private LayoutInflater  mInflater;
    
    public ImageGridAdapter(Activity context, ImageLoader imageLoader,ArrayList<BooksData> list) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
//        mLocations = locations;
        this.context = context;
        this.imageLoader = imageLoader;
        mList=list;
    }
    
    @Override
    public int getCount() {
    	
    	 if (mList != null) {
             return mList.size();
         }
         else
         {
         
         return 0;
         }
//        if (mLocations != null) {
//            return mLocations.length();
//        }
//        else
//        {
//        
//        return 0;
//        }
    }
    
    public void notifyData()
	{
		notifyDataSetChanged();
	}
    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        
        View       view = convertView;
        ViewHolder viewHolder;
        
        if (view == null) {
            view = mInflater.inflate(R.layout.grid_row, parent, false);
            
            viewHolder = new ViewHolder();
            viewHolder.imageView = (ImageView) view.findViewById(R.id.grid_image);
            viewHolder.imageGroup = (TextView) view.findViewById(R.id.imagegroup);
            viewHolder.imageGroup.setVisibility(View.INVISIBLE);
            viewHolder.textView  = (TextView) view.findViewById(R.id.grid_label);
            viewHolder.price  = (TextView) view.findViewById(R.id.price);
            viewHolder.text001  = (TextView) view.findViewById(R.id.textView001);
            viewHolder.text2  = (TextView) view.findViewById(R.id.textView2);
            viewHolder.text3  = (TextView) view.findViewById(R.id.textView3);
            viewHolder.text5  = (TextView) view.findViewById(R.id.textView5);
            viewHolder.rating  = (RatingBar) view.findViewById(R.id.ratingbar_default);
            viewHolder.rating.setIsIndicator(true);
            
            view.setTag(viewHolder);
        }
        else {
            viewHolder = (ViewHolder) view.getTag();
        }
        
        String ProductThumbnail = null;
        String textbook_type = null;
        String title = null;
        String price = null;
        String rate = null;
		try {
//			JSONObject jsonObject = mLocations.getJSONObject(position);
			ProductThumbnail = mList.get(position).getProductThumbnail();//jsonObject.getString("ProductThumbnail");
			textbook_type = mList.get(position).getCategory_name();//jsonObject.getString("title");
			title = mList.get(position).getTitle();//jsonObject.getString("title");
			price =mList.get(position).getPriceText();// jsonObject.getString("price");
			rate = mList.get(position).getRating();//jsonObject.getString("rating");
			//Log.e("hELLO mAGNON","LIST IS FREE"+mList.get(position).getIs_free());
			if(mList.get(position).getIs_free().equals("true")){
				 viewHolder.imageGroup.setVisibility(View.VISIBLE);
			}
			//Log.i("Categorypriceprice", price+ "++++");
		} catch (Exception e) {
			
			e.printStackTrace();
		}
        
		imageLoader.DisplayImage(ProductThumbnail, viewHolder.imageView);
		/**@author MI
		 * changed from $ 0 to Free
		 */
		try{
			if(Integer.parseInt(price.substring(price.indexOf(";")+2,price.length()))==0){
				viewHolder.price.setText("Free");
			}else{
				  viewHolder.price.setText(Html.fromHtml(price));
			}
		}catch(NumberFormatException e){
			viewHolder.price.setText(Html.fromHtml(price));
		}
		//Log.e("Categorypriceprice", ""+Integer.parseInt(price.substring(price.indexOf(";")+2,price.length()) ));
		
        viewHolder.textView.setText(title);
        viewHolder.text001.setText(textbook_type);
        viewHolder.text2.setText(mList.get(position).getGenre());
        viewHolder.text3.setText(mList.get(position).getCountry_name());
        viewHolder.text5.setText(mList.get(position).getFile_size());
        
        viewHolder.rating.setRating(Float.parseFloat(rate));
        
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
