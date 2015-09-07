package com.evendor.widgets;

import java.util.ArrayList;
import java.util.HashMap;

import android.content.Context;
import android.content.Intent;
import android.text.Html;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Toast;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;
import com.evendor.utils.MyIntents;

public class DownloadListAdapter extends BaseAdapter {

	private Context mContext;
	private ArrayList<HashMap<Integer, String>> dataList;
	 ImageLoader imageLoader;
	 String priceText;
	public DownloadListAdapter(Context context) {
		mContext = context;
		dataList = new ArrayList<HashMap<Integer, String>>();
		imageLoader = new ImageLoader(context);
	}

	@Override
	public int getCount() {
		return dataList.size();
	}

	@Override
	public Object getItem(int position) {
		return dataList.get(position);
	}

	@Override
	public long getItemId(int position) {
		return position;
	}

	public void addItem(String url , String iconPath , String bookTitle , String price, String priceText) {
		addItem(url, false , iconPath , bookTitle , price , priceText);
	}
	
	public void notifyData()
	{
		notifyDataSetChanged();
	}

	
	public void addItem(String url, boolean isPaused , String iconPath , String bookTitle , String price, String priceText) {
		HashMap<Integer, String> item = ViewHolder.getItemDataMap(url, null,
				null, isPaused + "", iconPath ,bookTitle, price,priceText);
		if(!dataList.contains(item))
			dataList.add(item);
		this.notifyDataSetChanged();
	}
	public void removeItem(String url) {
		String tmp;
//		AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
//		appDataSavedMethod.SaveDownloadedBook(bookTitle,bookDescription,bookUrl, bookId, iconPath,
//				bookPath, "",bookAuthor,bookAuthor,bookPublishedDate,bookSize,bookCategory);
		Log.e("dataList : ", ""+dataList.size());
		for (int i = 0; i < dataList.size(); i++) {
			
			tmp = dataList.get(i).get(ViewHolder.KEY_URL);
			Log.e("removeItem : ",url);
			Log.e("tmp : ",tmp);
			if (tmp.equals(url)) {
			
				dataList.remove(i);
				this.notifyDataSetChanged();
			}
		}
	}

	@Override
	public View getView(int position, View convertView, ViewGroup parent) {
		if (convertView == null) {
			convertView = LayoutInflater.from(mContext).inflate(
					R.layout.download_list_item, null);
		}

		HashMap<Integer, String> itemData = dataList.get(position);
		String url = itemData.get(ViewHolder.KEY_URL);
		String iconPath = itemData.get(ViewHolder.KEY_ICON_PATH);
		String bookTitle = itemData.get(ViewHolder.KEY_BOOK_TITLE);
		String bookPrice = itemData.get(ViewHolder.KEY_BOOK_PRICE);
		if(itemData.containsKey(ViewHolder.KEY_PRICE_TEXT))
		 priceText = itemData.get(ViewHolder.KEY_PRICE_TEXT);
//		Log.i("ICON_PATH", iconPath +"");
		convertView.setTag(url);
		
	

		ViewHolder viewHolder = new ViewHolder(convertView);
	//	viewHolder.setData(itemData);
        if (position % 2 == 1) {
        	
        	
        	convertView.setBackgroundResource(R.drawable.download_bg1);
        } else {
        	convertView.setBackgroundResource(R.drawable.download_bg2);
        }
	
        imageLoader.DisplayImage(iconPath, viewHolder.thumpNail);
        
        viewHolder.bookTitleText.setText(bookTitle);
        if(bookPrice.equalsIgnoreCase("Free")){
            viewHolder.bookPriceText.setText( ""+bookPrice);
        }else{
        	if(priceText!=null){
        		try{
        		viewHolder.bookPriceText.setText(Html.fromHtml(priceText));
        		}catch(Exception E){
        			E.printStackTrace();
        			viewHolder.bookPriceText.setText( "$ "+bookPrice);
        		}
        	}else
        		viewHolder.bookPriceText.setText( "$ "+bookPrice);
        
        }
		viewHolder.continueButton.setOnClickListener(new DownloadBtnListener(
				url, viewHolder,position));
		viewHolder.pauseButton.setOnClickListener(new DownloadBtnListener(url,
				viewHolder,position));
		viewHolder.deleteButton.setOnClickListener(new DownloadBtnListener(url,
				viewHolder,position));
		
		
		
		return convertView;
	}

	private class DownloadBtnListener implements View.OnClickListener {
		private String url;
		private ViewHolder mViewHolder;
		private int postion;

		public DownloadBtnListener(String url, ViewHolder viewHolder, int position) {
			this.url = url;
			this.mViewHolder = viewHolder;
			this.postion = position;
		}

		@Override
		public void onClick(View v) {
			Intent downloadIntent = new Intent(
					"com.yyxu.download.services.IDownloadService");

			switch (v.getId()) {
			case R.id.btn_continue:
				// mDownloadManager.continueTask(mPosition);
				downloadIntent.putExtra(MyIntents.TYPE,
						MyIntents.Types.CONTINUE);
				downloadIntent.putExtra(MyIntents.URL, url);
				//downloadIntent.putExtra("bookUrl", url);
				mContext.startService(downloadIntent);

				mViewHolder.continueButton.setVisibility(View.GONE);
				mViewHolder.pauseButton.setVisibility(View.VISIBLE);
				break;
			case R.id.btn_pause:
				// mDownloadManager.pauseTask(mPosition);
				downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.PAUSE);
				downloadIntent.putExtra(MyIntents.URL, url);
				mContext.startService(downloadIntent);

				mViewHolder.continueButton.setVisibility(View.VISIBLE);
				mViewHolder.pauseButton.setVisibility(View.GONE);
				break;
			case R.id.btn_delete:
//				 mDownloadManager.deleteTask(mPosition);
			/*	downloadIntent.putExtra(MyIntents.TYPE, MyIntents.Types.DELETE);
				downloadIntent.putExtra(MyIntents.URL, url);
				mContext.startService(downloadIntent);
*/
				removeItem(url);
				break;
				
			
			}
		}
	}
	
	
//	 private class DownloadBtnListener implements View.OnClickListener {
//		 private int mPosition;
//		 private ViewHolder mViewHolder;
//		
//		 public DownloadBtnListener(int position, ViewHolder viewHolder) {
//		 this.mPosition = position;
//		 this.mViewHolder = viewHolder;
//		 }
//		
//		 @Override
//		 public void onClick(View v) {
//		 switch (v.getId()) {
//		 case R.id.btn_continue:
//		 // mDownloadManager.continueTask(mPosition);
//		 mViewHolder.continueButton.setVisibility(View.GONE);
//		 mViewHolder.pauseButton.setVisibility(View.VISIBLE);
//		 break;
//		 case R.id.btn_pause:
//		 // mDownloadManager.pauseTask(mPosition);
//		 mViewHolder.continueButton.setVisibility(View.VISIBLE);
//		 mViewHolder.pauseButton.setVisibility(View.GONE);
//		 break;
//		 case R.id.btn_delete:
//		 // mDownloadManager.deleteTask(mPosition);
//		 DownloadListAdapter.this.notifyDataSetChanged();
//		 break;
//		 }
//		 }
//		 }

	
}