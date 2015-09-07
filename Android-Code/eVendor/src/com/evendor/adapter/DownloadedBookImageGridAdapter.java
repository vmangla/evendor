package com.evendor.adapter;

import java.util.ArrayList;

import android.app.Activity;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;

public class DownloadedBookImageGridAdapter extends BaseAdapter {
	
	 private int urlIndex = 0;
	 private Context context;
	

    private class ViewHolder {
        public ImageView imageView;
        public ImageView addedImage;
        public TextView  textView;
       // public RatingBar  rating;
    }
    
    ImageLoader imageLoader;
    
    private ArrayList<ArrayList<String>> downloadedBookList;
    private LayoutInflater  mInflater;
    
    public DownloadedBookImageGridAdapter(Activity context, ArrayList<ArrayList<String>> downloadedBookList , ImageLoader imageLoader) {
        mInflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        this.downloadedBookList = downloadedBookList;
        this.context = context;
        this.imageLoader = imageLoader;
    }
    
    @Override
    public int getCount() {
        if (downloadedBookList != null) {
            return downloadedBookList.size();
        }
        
        return 0;
    }

   

    

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        
        View       view = convertView;
        ViewHolder viewHolder;
        
        if (view == null) {
            view = mInflater.inflate(R.layout.downloaded_book_grid_row, parent, false);
            
            viewHolder = new ViewHolder();
            viewHolder.imageView = (ImageView) view.findViewById(R.id.grid_image);
            viewHolder.textView  = (TextView) view.findViewById(R.id.grid_label);
            viewHolder.addedImage  = (ImageView) view.findViewById(R.id.added_button);
          //  viewHolder.rating  = (RatingBar) view.findViewById(R.id.ratingbar_default);
          //  viewHolder.rating.setIsIndicator(true);
            
            view.setTag(viewHolder);
        }
        else {
            viewHolder = (ViewHolder) view.getTag();
        }
        
        String ProductThumbnail = null;
        String title = null;
        ProductThumbnail = downloadedBookList.get(position).get(0);
        title = downloadedBookList.get(position).get(2);
		imageLoader.DisplayImage(ProductThumbnail, viewHolder.imageView);
		
		if( downloadedBookList.get(position).get(5)== null)
		{
			viewHolder.addedImage.setVisibility(View.GONE);
		}
		else
		{
			viewHolder.addedImage.setVisibility(View.VISIBLE);
		}
       
        viewHolder.textView.setText(title);
       // viewHolder.rating.setRating((float) 5);
        
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
