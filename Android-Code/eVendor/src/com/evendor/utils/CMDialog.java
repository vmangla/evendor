package com.evendor.utils;

import android.app.Dialog;
import android.content.Context;
import android.view.View;
import android.view.Window;
import android.widget.Button;
import android.widget.TextView;

import com.evendor.Android.R;

public class CMDialog extends Dialog 
{

	public static final String REGISTER_TITLE = "Register new account";
	public static final String ACCEPT = "Please accept";
	public static final String LOGIN_TITILE = "Log in";
	public static final String LIBRARY_BOOK = "Library book";
	public static final String FORGOTPASSWORD_TITILE = "Forgot password";
	public static final String PROFILE_UPDATE = "Profile update";
	public static final String CREATE_SHELF = "Bookshelf";
	public static final String DELETE ="Delete";
	public static final String ADD_BOOK = "Add book";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	public static final String EDIT ="Edit";
	public static final String YES = "Yes";
	public static final String NO = "No";
	public static final String CHANGE_PASSWORD = "Change password";
	
	
	TextView titleText;
	TextView contentText;
	
	Button possitiveButton;
	Button negativeButton;
	
	public CMDialog(Context context) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.cm_dialog);
		
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
		contentText = (TextView) findViewById(R.id.tv_detail_tex);
		
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);
	}
	
	public void setTitle(String title)
	{
		titleText.setText(title);
	}
	
	public void setMessage(String message)
	{
		contentText.setText(message);
	}
	
	public void setContent(String title, String message)
	{
		titleText.setText(title);
		 contentText.setText(message);
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
