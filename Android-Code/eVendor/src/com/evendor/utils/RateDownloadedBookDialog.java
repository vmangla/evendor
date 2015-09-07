package com.evendor.utils;

import android.app.Dialog;
import android.content.Context;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.widget.Button;
import android.widget.EditText;
import android.widget.RatingBar;
import android.widget.TextView;

import com.evendor.Android.R;

public class RateDownloadedBookDialog extends Dialog 
{

	public static final String RATE_BOOK = "Rate book";
	public static final String RATE = "Rate";
	public static final String LOGIN_TITILE = "Log in";
	public static final String PROFILE_UPDATE = "Profile update";
	public static final String CREATE_SHELF = "Bookshelf";
	public static final String ADD_BOOK = "Add book";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	public static final String EDIT ="Edit";
	public static final String YES = "Yes";
	public static final String NO = "No";
	public static final String CHANGE_PASSWORD = "Change password";
	
	TextView titleText;
	RatingBar ratingBar_default;
	TextView contentText;
	EditText comment;
	String rate;
	Button possitiveButton;
	Button negativeButton;
	
	public RateDownloadedBookDialog(Context context) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.rate_downloaded_book_dialog);
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
		ratingBar_default = (RatingBar)findViewById(R.id.ratingbar_default);
		
		comment = (EditText) findViewById(R.id.comment);
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);
		
		ratingBar_default.setOnRatingBarChangeListener(new RatingBar.OnRatingBarChangeListener(){ 
	    	    @Override
	    	    public void onRatingChanged(RatingBar ratingBar, float rating,
	    	      boolean fromUser) {
	    	    	
	    	    	Log.i("RATE THE VALUE", String.valueOf(rating));
	    	    	
	    	    	setRate(String.valueOf(rating));
	    	     // TODO Auto-generated method stub 
	    	//     text.setText("Rating: "+String.valueOf(rating)); 
	    	    }}); 
	}
	
	public void setTitle(String title)
	{
		titleText.setText(title);
	}
	
	public void setContent(String title, String message)
	{
		titleText.setText(title);
		
	}
	
	public void setRate(String rating)
	{
		rate = rating;
	}
	
	public String getComment()
	{
		return comment.getText().toString();
	}
	
	public String getRate()
	{
		if(rate != null)
		return rate;
		else
		return "";	
	}
	
	public void setPossitiveButton(String text,View.OnClickListener listener)
	{
		possitiveButton.setVisibility(View.VISIBLE);
		setPossitiveButtonText(text);
		setPossitiveButtonClickListener(listener);
	}
	
	public void setNegativeButton(String text,View.OnClickListener listener)
	{
		negativeButton.setVisibility(View.VISIBLE);
		setNegativeButtonText(text);
		setNegativeButtonClickListener(listener);
	}
	
	public void setPossitiveButtonText(String text)
	{
		possitiveButton.setText(text);
	}
	
	public void setNegativeButtonText(String text)
	{
		negativeButton.setText(text);
	}
	
	public void setPossitiveButtonClickListener(View.OnClickListener listener)
	{
		possitiveButton.setOnClickListener(listener);
	}
	
	public void setNegativeButtonClickListener(View.OnClickListener listener)
	{
		negativeButton.setOnClickListener(listener);
	}

}
