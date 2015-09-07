package com.evendor.utils;

import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;

import android.app.Dialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.AsyncTask;
import android.text.Spanned;
import android.view.View;
import android.view.Window;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import com.evendor.Android.ImageLoader;
import com.evendor.Android.R;



public class EvendorDialog extends Dialog 
{

	public static final String READ = "Read";
	public static final String DELETE = "Delete";
	public static final String MOVE = "Move";
	public static final String READ_BOOK = "Read book";
	public static final String LOGIN_TITILE = "Log in";
	public static final String OK = "OK";
	public static final String CANCEL = "Cancel";
	public static final String RATE = "Rate";
	public static final String YES = "Yes";
	public static final String NO = "No";
	
	Bitmap image;
	TextView bookTitle;
	TextView bookAuthor;
	TextView pulisher;
	TextView publishedDate;
	TextView description;
	TextView titleText;
	TextView bookPrice;
	ImageView bookIcon;
	public void setBookIcon(ImageView bookIcon) {
		this.bookIcon = bookIcon;
	}

	String url_image=null;
	
	Button possitiveButton;
	Button negativeButton;
	Button neutralButton;
	Button rateButton;
	ImageLoader loader;
	Context mctx;
	private Button deleteButton;
	
	
	public EvendorDialog(Context context) 
	{
		super(context);
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.custom_dialog);
		
		
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
		bookIcon = (ImageView) findViewById(R.id.book_icon111);
		bookTitle = (TextView) findViewById(R.id.title);
		bookAuthor = (TextView) findViewById(R.id.author);
		pulisher = (TextView) findViewById(R.id.pulisher);
		publishedDate = (TextView) findViewById(R.id.pulish_date);
		bookPrice = (TextView) findViewById(R.id.book_price);
		description = (TextView) findViewById(R.id.description);
		deleteButton = (Button) findViewById(R.id.btn_cmdlg_read);
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);
		neutralButton = (Button) findViewById(R.id.btn_cmdlg_neutral);
		rateButton = (Button) findViewById(R.id.btn_cmdlg_negative1);
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
			String publisher,String booksize,String publishedDates, Spanned spanned,String price, ImageLoader imageLoader2)
	{
		titleText.setText(title);
		bookTitle.setText(bookheading);
		titleText.setText(bookheading);
		bookAuthor.setText(booksAuthor);
		pulisher.setText(publisher);
		publishedDate.setText(publishedDates);
		bookPrice.setText(booksize);
		loader=imageLoader2;
		url_image=price;
		
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
	
//		loader.DisplayImage(price, bookIcon);
		/*if(image!=null){
			bookIcon.setImageBitmap(image);
		}else{*/
		if(url_image!=null){
			loader.DisplayImage(url_image,getBookIcon());
		//	new GetImageTask(bookIcon).execute(new String[]{url_image});
		//new Thetask().execute();
		}
	//	}
	}
	public void setReadButton(String text,View.OnClickListener listener)
	{
		deleteButton.setVisibility(View.VISIBLE);
		setReadButtonText(text);
		setReadNegativeButtonClickListener(listener);
	}
	public void setReadButtonText(String text)
	{
		deleteButton.setText(text);
	}
	public void setReadNegativeButtonClickListener(View.OnClickListener listener)
	{
		deleteButton.setOnClickListener(listener);
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
	
	public void setNegativeButton1(String text,View.OnClickListener listener)
	{
		rateButton.setVisibility(View.VISIBLE);
		setNegativeButtonText1(text);
		setNegativeButtonClickListener1(listener);
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
	
	public void setNegativeButtonText1(String text)
	{
		rateButton.setText(text);
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
	
	public void setNegativeButtonClickListener1(View.OnClickListener listener)
	{
		rateButton.setOnClickListener(listener);
	}
	
	public void setNeutralButtonClickListener(View.OnClickListener listener)
	{
		neutralButton.setOnClickListener(listener);
	}
	
	class Thetask extends AsyncTask<Void, Void, Void>{

		@Override
		protected Void doInBackground(Void... params) {
			URL url;
			try {
				url = new URL(url_image);
				if(image!=null){
					image.recycle();
					image=null;
				}
				 image = BitmapFactory.decodeStream(url.openConnection().getInputStream());
				
			} catch (MalformedURLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			return null;
		}
		
		@Override
		protected void onPostExecute(Void result) {
			// TODO Auto-generated method stub
			super.onPostExecute(result);
			bookIcon.setImageBitmap(image);
		}
		
		
	}

	private class GetImageTask extends AsyncTask<String, Void, Bitmap> {
		ImageView icon;
		String path;
		String bookTilte;
		String bookAuthor; 
		String bookPublisher; 
		String bookPublisherDate; 
		String bookDescription;
		String bookPkId;
		String bookShelfId;
		String bookId;
		final ProgressDialog dialog = new ProgressDialog(mctx);
		
		/*GetImageTask(String path, String bookTilte,String bookAuthor,String bookPublisher,String bookPublisherDate , String bookDescription , String bookPkId ,String bookShelfId,String bookId)
		{
			
			this.path = path;
			this.bookTilte = bookTilte;
			this.bookAuthor = bookAuthor;
			this.bookPublisher = bookPublisher;
			this.bookPublisherDate = bookPublisherDate;
			this.bookDescription = bookDescription;
			this.bookPkId = bookPkId;
			this.bookShelfId = bookShelfId;
			this.bookId = bookId;
			
		}*/
		
		public GetImageTask(ImageView icon) {
			this.icon=icon;
		}

		@Override
		protected void onPreExecute() {
			// TODO Auto-generated method stub
			super.onPreExecute();
			
			dialog.setMessage(mctx.getResources()
					.getString(R.string.dialog_please_wait));
			//dialog.setCancelable(false);
			dialog.show();
		}
		
        @Override
        protected Bitmap doInBackground(String... urls) {
        	
            Bitmap map = null;
            for (String url : urls) {
            	 
                map = downloadImage(url);
            }
            return map;
        }
 
        @Override
        protected void onPostExecute(Bitmap result) {
        	dialog.dismiss();
        	
        	if(!(result==null)){
        	
        		bookIcon.setImageBitmap(result);
        	}
        	// String descriptionHtmlString = "<b><font face='open sans'>"+bookDescription+"</font></b>" ;
        	 String descriptionHtmlString = "<font face='open sans'>"+bookDescription+"</font>" ;
        //	showBookDetailDialog(bookTilte,bookAuthor,bookPublisher,bookPublisherDate,Html.fromHtml(descriptionHtmlString ),result,path,bookPkId,bookShelfId,null,bookId);
        	
        }
 
    private Bitmap downloadImage(String url) {
    	
            Bitmap bitmap = null;
            InputStream stream = null;
            BitmapFactory.Options bmOptions = new BitmapFactory.Options();
            bmOptions.inSampleSize = 1;
 
            try {
                stream = getHttpConnection(url);
                bitmap = BitmapFactory.
                        decodeStream(stream, null, bmOptions);
                if(stream != null)
                stream.close();
            } catch (IOException e1) {
                e1.printStackTrace();
            }
            return bitmap;
        }
    
        private InputStream getHttpConnection(String urlString)
                throws IOException {
            InputStream stream = null;
            URL url = new URL(urlString);
            URLConnection connection = url.openConnection();
 
            try {
                HttpURLConnection httpConnection = (HttpURLConnection) connection;
                httpConnection.setRequestMethod("GET");
                httpConnection.connect();
                if (httpConnection.getResponseCode() == HttpURLConnection.HTTP_OK) {
                    stream = httpConnection.getInputStream();
                }
            } catch (Exception ex) {
                ex.printStackTrace();
            }
            return stream;
        }
    }
	
}
