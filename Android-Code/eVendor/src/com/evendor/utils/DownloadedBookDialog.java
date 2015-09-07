package com.evendor.utils;

import android.app.Dialog;
import android.content.Context;
import android.text.Spanned;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import com.evendor.Android.ApplicationManager;
import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;

public class DownloadedBookDialog extends Dialog 
{

	public static final String DELETE = "Delete";
	public static final String RATE = "Rate";
	public static final String DOWNLOADED_BOOK = "Downloaded book";
	public static final String LOGIN_TITILE = "Log in";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	//changed from Read Book to Read date : 08/Jul/14
	public static final String READ = "Read";
	public static final String ADD_TO_SHELF = "Add to Bookshelf";
	public static final String MOVE_TO_SHELF = "Move";
	public static final String CREATE_SHELF = "Create shelf";
	public static final String YES = "Yes";
	public static final String NO = "No";
	TextView bookTitle;
	TextView pulisher;
	TextView description,publisherdate,size;
	TextView titleText,authorText;
	ImageView bookIcon;
	
	Button possitiveButton;
	Button negativeButton;
	Button neutralButton1;
	Button neutralButton2;
	Button neutralButton3;
	Button neutralButton4;
	ImageLoader imageLoader;
	
	public DownloadedBookDialog(Context context) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.downloadbook_custom_dialog);
		imageLoader = new ImageLoader(context);
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
		bookTitle = (TextView) findViewById(R.id.tv_detail_tex);
		authorText = (TextView) findViewById(R.id.textauthor);
		publisherdate = (TextView) findViewById(R.id.textViewpubdate);
		size = (TextView) findViewById(R.id.textViewsize);
		bookIcon = (ImageView) findViewById(R.id.book_icon);
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);
		neutralButton1 = (Button) findViewById(R.id.btn_cmdlg_neutral1);
		neutralButton2 = (Button) findViewById(R.id.btn_cmdlg_neutral2);
		neutralButton3 = (Button) findViewById(R.id.btn_cmdlg_neutral3);
		neutralButton4 = (Button) findViewById(R.id.btn_cmdlg_neutral4);
		description = (TextView) findViewById(R.id.description);
		imageLoader=new ImageLoader(context);
	}
	
	public Button getCancelButton( )
	{
		return neutralButton2;
	}
	
	
	public void setTitle(String title)
	{
		titleText.setText(title);
	}
	
	public void setBookTitle(String title)
	{
		bookTitle.setText(title);
	}
	
	
	
	public void setContent(String title,String bookheading,String author,String publisher,String date,String booksize,String url, Spanned spanned)
	{
		titleText.setText(bookheading);
		bookTitle.setText(publisher);
		authorText.setText(author);
		String pub_date = "";
		
		if(date != null)
		{
			Log.e("!!!!!","date-"+date);
			pub_date = ApplicationManager.getconvertdate1(date);
		}
		publisherdate.setText(pub_date);
		size.setText(booksize);
		imageLoader.DisplayImage(url, bookIcon);
		description.setText(spanned);
	}
	
	public ImageView getBookIcon()
	{
		return bookIcon;
	}
	
	public void setDescription(String message)
	{
		description.setText(message);
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
	
	public void setNeutralButton1(String text,View.OnClickListener listener)
	{
		neutralButton1.setVisibility(View.VISIBLE);
		setNeutralButtonText1(text);
		setNeutralButtonClickListener1(listener);
	}
	
	public void setNeutralButton2(String text,View.OnClickListener listener)
	{
		neutralButton2.setVisibility(View.VISIBLE);
		setNeutralButtonText2(text);
		setNeutralButtonClickListener2(listener);
	}
	
	public void setNeutralButton3(String text,View.OnClickListener listener)
	{
		neutralButton3.setVisibility(View.VISIBLE);
		setNeutralButtonText3(text);
		setNeutralButtonClickListener3(listener);
	}
	
	public void setNeutralButton4(String text,View.OnClickListener listener)
	{
		neutralButton4.setVisibility(View.GONE);
		setNeutralButtonText4(text);
		setNeutralButtonClickListener4(listener);
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
	
	public void setNeutralButtonText2(String text)
	{
		neutralButton2.setText(text);
	}
	
	public void setNeutralButtonText3(String text)
	{
		neutralButton3.setText(text);
	}
	
	public void setNeutralButtonText4(String text)
	{
		neutralButton4.setText(text);
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

	public void setNeutralButtonClickListener2(View.OnClickListener listener)
	{
		neutralButton2.setOnClickListener(listener);
	}
	
	public void setNeutralButtonClickListener3(View.OnClickListener listener)
	{
		neutralButton3.setOnClickListener(listener);
	}
	
	public void setNeutralButtonClickListener4(View.OnClickListener listener)
	{
		neutralButton4.setOnClickListener(listener);
	}
}
