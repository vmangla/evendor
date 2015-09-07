package com.evendor.utils;

import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.view.inputmethod.InputMethodManager;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;


public class BookSelfManageDialog extends Dialog 
{

	public static final String DELETE = "Delete";
	public static final String RATE = "Rate";
	public static final String BOOK_SHELF = "Book shelf";
	public static final String RENAME_SHELF = "Edit ";
	public static final String LOGIN_TITILE = "Log in";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	public static final String READ = "Read";
	public static final String ADD_TO_SHELF = "Add to self";
	public static final String YES = "Yes";
	public static final String NO = "No";
	
	Context context;
	TextView bookTitle;
	TextView pulisher;
	TextView description;
	TextView titleText;
	ImageView bookIcon;
	
	Button possitiveButton;
	Button negativeButton;
	Button neutralButton1;
	boolean Show=true;
	View v;
	ImageLoader imageLoader;
	
	public BookSelfManageDialog(Context context,boolean Show) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.bookself_manage_custom_dialog);
		imageLoader = new ImageLoader(context);
		this.context=context;
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
		bookTitle = (TextView) findViewById(R.id.tv_detail_tex);
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);
		neutralButton1 = (Button) findViewById(R.id.btn_cmdlg_neutral1);
		this.Show=Show;
		v=bookTitle;
	}
	
	
	
	
	public void setTitle(String title)
	{
		if(titleText.isShown())
		titleText.setText(title);
	}
	
	public void setBookTitle(String title)
	{
		bookTitle.setText(title);
	}
	
	
	
	public void setContent(String title,String bookheading)
	{
		if(Show==false){
			titleText.setVisibility(View.GONE);
		bookTitle.setText(title);
		}else{
		titleText.setText(title);
		bookTitle.setText(bookheading);
		
		}
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
	
	public void setNeutralButton(String text,View.OnClickListener listener)
	{
		neutralButton1.setVisibility(View.VISIBLE);
		setNeutralButtonText1(text);
		setNeutralButtonClickListener1(listener);
	}
	
	
	
	
	
	public void setPossitiveButtonText(String text)
	{
		possitiveButton.setText(text);
	}
	
	public void setNegativeButtonText(String text)
	{
		negativeButton.setText(text);
	}
	
	
	public void setNeutralButtonText1(String text)
	{
		neutralButton1.setText(text);
	}
	
	/*
	 public void onDismiss(DialogInterface dialogInterface)
	  {
		 Log.e("Errrrrrrrrrrrrrrr", "dddddddddddddddddd");
		 InputMethodManager imm = (InputMethodManager)context.getSystemService(
			      Context.INPUT_METHOD_SERVICE);
			imm.hideSoftInputFromWindow(v.getWindowToken(), 0);
	  }*/
	
	
	@Override
	public void dismiss() {
		// TODO Auto-generated method stub
		super.dismiss();
		 InputMethodManager imm = (InputMethodManager)context.getSystemService(
			      Context.INPUT_METHOD_SERVICE);
			imm.hideSoftInputFromWindow(v.getWindowToken(), 0);
	}




	public void setPossitiveButtonClickListener(View.OnClickListener listener)
	{
		possitiveButton.setOnClickListener(listener);
	}
	
	public void setNegativeButtonClickListener(View.OnClickListener listener)
	{
		negativeButton.setOnClickListener(listener);
	}
	
	public void setNeutralButtonClickListener1(View.OnClickListener listener)
	{
		neutralButton1.setOnClickListener(listener);
	}

	
	
	
}
