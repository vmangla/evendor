package com.evendor.utils;

import android.app.Dialog;
import android.content.Context;
import android.text.Html;
import android.text.Spanned;
import android.view.View;
import android.view.Window;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.RatingBar;
import android.widget.TextView;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;

public class LibraryEvendorDialog extends Dialog 
{

	public static final String READ = "Read";
	public static final String MOVE = "Move to other shelf";
	public static final String READ_BOOK = "Read book";
	public static final String LIBRARY_BUY_BOOK = "Purchase and Download";
	public static final String LOGIN_TITILE = "Log in";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	public static final String YES = "Download";
	public static final String NO = "Cancel";
	
	
	TextView bookTitle;
	TextView bookAuthor;
	TextView pulisher;
	TextView publishedDate;
	TextView description;
	TextView titleText;
	TextView bookPrice;
	TextView bookSize;
	ImageView bookIcon;
	
	Button possitiveButton;
	Button negativeButton;
	Button neutralButton;
	ImageLoader imageLoader;
	RatingBar  rating;
	
	public LibraryEvendorDialog(Context context) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.custom_dialog1);
		imageLoader = new ImageLoader(context);
		
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
		bookIcon = (ImageView) findViewById(R.id.book_icon);
		bookTitle = (TextView) findViewById(R.id.title);
		bookAuthor = (TextView) findViewById(R.id.author);
		pulisher = (TextView) findViewById(R.id.pulisher);
		publishedDate = (TextView) findViewById(R.id.pulish_date);
		bookPrice = (TextView) findViewById(R.id.book_price);
		bookSize = (TextView) findViewById(R.id.book_size);
		rating = (RatingBar)findViewById(R.id.ratingbar_default);
		description = (TextView) findViewById(R.id.description);
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);
		neutralButton = (Button) findViewById(R.id.btn_cmdlg_neutral);
	}
	
	public void setTitle(String title)
	{
		titleText.setText(title);
	}
	
	public void setRateingBar(String rate)
	{
		rating.setRating(Float.parseFloat(rate));
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
	
	public void setBookIcon(String imageUrl)
	{
		imageLoader.DisplayImage(imageUrl, bookIcon);
	}
	
	public ImageView getBookIcon()
	{
		return bookIcon;
	}
	
	public void setContent(String title,String bookheading,String booksAuthor,
			String publisher,String publishedDates, Spanned spanned,String price,String booksize, String priceText)
	{
		titleText.setText(title);
		bookTitle.setText(bookheading);
		bookAuthor.setText(booksAuthor);
		pulisher.setText(publisher);
		publishedDate.setText(publishedDates);
//		publishedDate.setVisibility(View.GONE);
		bookSize.setText(booksize);
		if(price != null)
		{
		bookPrice.setVisibility(View.VISIBLE);	
		try{
			if(price.equalsIgnoreCase("Free")){
				bookPrice.setText(price);
			}else{
				bookPrice.setText(Html.fromHtml(priceText));
			}
		}catch(Exception e ){
			bookPrice.setText(Html.fromHtml(priceText));
		}
	
		}
		else
		{
		bookPrice.setVisibility(View.GONE);	
		}
		description.setText(spanned);
		//imageLoader.DisplayImage(imageurl, bookIcon);
		
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
		neutralButton.setVisibility(View.VISIBLE);
		setNeutralButtonText(text);
		setNeutralButtonClickListener(listener);
	}
	
	public void setPossitiveButtonText(String text)
	{
		possitiveButton.setText(text);
	}
	
	public void setNegativeButtonText(String text)
	{
		negativeButton.setText(text);
	}
	
	
	public void setNeutralButtonText(String text)
	{
		neutralButton.setText(text);
	}
	
	public void setPossitiveButtonClickListener(View.OnClickListener listener)
	{
		possitiveButton.setOnClickListener(listener);
	}
	
	public void setNegativeButtonClickListener(View.OnClickListener listener)
	{
		negativeButton.setOnClickListener(listener);
	}
	
	public void setNeutralButtonClickListener(View.OnClickListener listener)
	{
		neutralButton.setOnClickListener(listener);
	}

}
