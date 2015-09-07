package com.evendor.widgets;

import java.util.HashMap;

import android.text.TextUtils;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;

import com.evendor.Android.R;
import com.evendor.services.DownloadTask;
import com.evendor.utils.NetworkUtils;

public class ViewHolder {

	public static final int KEY_URL = 0;
	public static final int KEY_SPEED = 1;
	public static final int KEY_PROGRESS = 2;
	public static final int KEY_IS_PAUSED = 3;
	public static final int KEY_ICON_PATH = 4;
	public static final int KEY_BOOK_TITLE = 5;
	public static final int KEY_BOOK_PRICE = 6;
	public static final int KEY_PRICE_TEXT = 7;
	
	public TextView titleText;
	public ProgressBar progressBar;
	public TextView speedText;
	public TextView bookTitleText;
	public TextView bookPriceText;
	public ImageView pauseButton;
	public ImageView deleteButton;
	public ImageView continueButton;
	public ImageView thumpNail;;
	

	private boolean hasInited = false;

	public ViewHolder(View parentView) {
		if (parentView != null) {
			titleText = (TextView) parentView.findViewById(R.id.title);
			speedText = (TextView) parentView.findViewById(R.id.speed);
			bookTitleText = (TextView) parentView.findViewById(R.id.author);
			bookPriceText = (TextView) parentView.findViewById(R.id.price);
			progressBar = (ProgressBar) parentView
					.findViewById(R.id.progress_bar);
			pauseButton = (ImageView) parentView.findViewById(R.id.btn_pause);
			deleteButton = (ImageView) parentView.findViewById(R.id.btn_delete);
			continueButton = (ImageView) parentView
					.findViewById(R.id.btn_continue);
			thumpNail = (ImageView) parentView.findViewById(R.id.book_icon);
			
			
			hasInited = true;
		}
	}

	public static HashMap<Integer, String> getItemDataMap(String url,
			String speed, String progress, String isPaused , String iconPath , String BookTitle , String BookPrice, String priceText) {
		HashMap<Integer, String> item = new HashMap<Integer, String>();
		item.put(KEY_URL, url);
		item.put(KEY_SPEED, speed);
		item.put(KEY_PROGRESS, progress);
		item.put(KEY_IS_PAUSED, isPaused);
		item.put(KEY_ICON_PATH, iconPath);
		item.put(KEY_BOOK_TITLE, BookTitle);
		item.put(KEY_BOOK_PRICE, BookPrice);
		item.put(KEY_PRICE_TEXT, priceText);
		return item;
	}

	public void setData(HashMap<Integer, String> item) {
		if (hasInited) {
//			Log.i("HasCode", item +"");
		//	bookTitleText.setText(item.get(KEY_BOOK_TITLE));
		//	bookPriceText.setText(item.get(KEY_BOOK_PRICE));
			titleText.setText(NetworkUtils.getFileNameFromUrl(item.get(KEY_URL)));
			speedText.setText(item.get(KEY_SPEED));
			String progress = item.get(KEY_PROGRESS);
			if (TextUtils.isEmpty(progress)) {
				progressBar.setProgress(0);
			} else {
				progressBar.setProgress(Integer.parseInt(progress));
			}
			if (Boolean.parseBoolean(item.get(KEY_IS_PAUSED))) {
				onPause();
			}
		}
	}

	public void onPause() {
		if (hasInited) {
			pauseButton.setVisibility(View.GONE);
			continueButton.setVisibility(View.VISIBLE);
		}
	}

	public void setData(String url, String speed, String progress ,String iconPath , String bookTitle,String bookPrice, String priceText) {
		setData(url, speed, progress, false + "", iconPath , bookTitle , bookPrice, priceText);
	}

	public void setData(String url, String speed, String progress,
			String isPaused , String iconPath , String bookTitle,String bookPrice, String priceText) {
		if (hasInited) {
			HashMap<Integer, String> item = getItemDataMap(url, speed,
					progress, isPaused , iconPath , bookTitle , bookPrice,priceText);

			//titleText.setText(NetworkUtils.getFileNameFromUrl(item.get(KEY_URL)));
		//	bookTitleText.setText(bookTitle);
		//	bookPriceText.setText(bookPrice);
			speedText.setText(speed);
			if (TextUtils.isEmpty(progress)) {
				progressBar.setProgress(0);
			} else {
				progressBar
						.setProgress(Integer.parseInt(item.get(KEY_PROGRESS)));
			}

		}
	}

	public void bindTask(DownloadTask task) {
		if (hasInited) {
			titleText.setText(NetworkUtils.getFileNameFromUrl(task.getUrl()));
			speedText.setText(task.getDownloadSpeed() + "kbps | "
					+ task.getDownloadSize() + " / " + task.getTotalSize());
			progressBar.setProgress((int) task.getDownloadPercent());
			if (task.isInterrupt()) {
				onPause();
			}
		}
	}

}
