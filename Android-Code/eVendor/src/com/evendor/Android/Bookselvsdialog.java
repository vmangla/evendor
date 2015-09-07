package com.evendor.Android;

import android.app.Dialog;
import android.content.Context;
import android.text.Spanned;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import com.evendor.Android.R;

public class Bookselvsdialog extends Dialog 
{

	public static final String READ = "Read";
	public static final String MOVE = "Move";
	public static final String READ_BOOK = "Read book";
	public static final String LOGIN_TITILE = "Log in";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	public static final String RATE = "Rate";
	public static final String YES = "Yes";
	public static final String NO = "No";
	
	
	TextView bookTitle;
	TextView bookAuthor;
	TextView pulisher;
	TextView publishedDate;
	TextView description;
	TextView titleText;
	TextView bookPrice;
	ImageView bookIcon;
	
	Button btn_read;
	Button btn_rate;
	Button btn_move;
	Button btn_cancel;
	ImageLoader loader;
	Context mctx;
	
	
	public Bookselvsdialog(Context context) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.bookshelvsdialog);
		
		
		titleText = (TextView) findViewById(R.id.tv_dialog_title1);
		bookIcon = (ImageView) findViewById(R.id.thumb);
		bookTitle = (TextView) findViewById(R.id.title1);
		bookAuthor = (TextView) findViewById(R.id.author1);
		pulisher = (TextView) findViewById(R.id.pulisher1);
		publishedDate = (TextView) findViewById(R.id.pulish_date1);
		bookPrice = (TextView) findViewById(R.id.book_price1);
		description = (TextView) findViewById(R.id.description1);
		btn_read = (Button) findViewById(R.id.btn_read);
		btn_rate = (Button) findViewById(R.id.btn_rate);
		btn_move = (Button) findViewById(R.id.btn_move);
		btn_cancel = (Button) findViewById(R.id.btn_cancel);
		mctx=context;
		loader=new ImageLoader(context);
	}
	
	public void setTitle(String title)
	{
		titleText.setText(title);
	}
	
	public void setBookTitle(String title)
	{
		bookTitle.setText(title);
	}
	
	public void setAuthor(String message)
	{
		bookAuthor.setText(message);
	}
	
	public void setPublisher(String message)
	{
		pulisher.setText(message);
	}
	
	public void setPublishedDate(String message)
	{
		publishedDate.setText(message);
	}
	
	public void setPrice(String message)
	{
		bookPrice.setText(message);
	}
	
	
	public void setDescription(String message)
	{
		description.setText(message);
	}
	
	
	public ImageView getBookIcon()
	{
		return bookIcon;
	}
	
	public void setContent(String title,String bookheading,String booksAuthor,
			String publisher,String booksize,String publishedDates, Spanned spanned,String price)
	{
		titleText.setText(title);
		bookTitle.setText(bookheading);
		titleText.setText(bookheading);
		bookAuthor.setText(booksAuthor);
		pulisher.setText(publisher);
		publishedDate.setText(publishedDates);
		bookPrice.setText(booksize);
		
		/*if(price != null)
		{
		bookPrice.setVisibility(View.VISIBLE);	
		bookPrice.setText("$ "+price);
		}
		else
		{
		bookPrice.setVisibility(View.GONE);	
		}*/
		description.setText(spanned);
	Log.e("BookshelvesDIALOG--", ""+bookIcon);
		loader.DisplayImage(price, bookIcon);
		
	}
	
	
	
	public void setReadButton(String text,View.OnClickListener listener)
	{
//		possitiveButton.setVisibility(View.VISIBLE);
//		setPossitiveButtonText(text);
		setReadButtonClickListener(listener);
	}
	
	public void setRateButton(String text,View.OnClickListener listener)
	{
//		negativeButton.setVisibility(View.VISIBLE);
//		setNegativeButtonText(text);
		setRateButtonClickListener(listener);
	}
	
	public void setMoveButton1(String text,View.OnClickListener listener)
	{
//		rateButton.setVisibility(View.VISIBLE);
//		setNegativeButtonText1(text);
		setMoveButtonClickListener1(listener);
	}
	
	public void setCancelButton(String text,View.OnClickListener listener)
	{
//		neutralButton.setVisibility(View.VISIBLE);
//		setNeutralButtonText(text);
		setCancelButtonClickListener(listener);
	}
	
	
	
	public void setReadButtonClickListener(View.OnClickListener listener)
	{
		btn_read.setOnClickListener(listener);
	}
	
	public void setRateButtonClickListener(View.OnClickListener listener)
	{
		btn_rate.setOnClickListener(listener);
	}
	
	public void setMoveButtonClickListener1(View.OnClickListener listener)
	{
		btn_move.setOnClickListener(listener);
	}
	
	public void setCancelButtonClickListener(View.OnClickListener listener)
	{
		btn_cancel.setOnClickListener(listener);
	}

}
