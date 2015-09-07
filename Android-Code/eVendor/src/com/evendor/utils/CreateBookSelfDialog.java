package com.evendor.utils;

import org.json.JSONObject;

import android.app.Dialog;
import android.content.Context;
import android.graphics.Color;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.inputmethod.InputMethodManager;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.ListAdapter;
import android.widget.ListPopupWindow;
import android.widget.RatingBar;
import android.widget.TextView;

import com.evendor.Android.R;

public class CreateBookSelfDialog extends Dialog 
{

	public static final String CREATE_SELF = "Create Bookshelf";
	public static final String RENAME_SELF = "Edit";
	public static final String UPDATE_SELF = "Update ";
	public static final String CREATE = "Create";
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
	public void setComment(String comment) {
		this.comment.setText(comment);
	}

	EditText color;
	public void setColor(String color) {
		this.color.setText(color);
	}

	String rate;
	Button possitiveButton;
	Button negativeButton;
	Context context;
	
	
	ListPopupWindow popup;
	
	public CreateBookSelfDialog(final Context context) 
	{
		super(context);
		this.context = context;
		this.requestWindowFeature(Window.FEATURE_NO_TITLE);
		this.setContentView(R.layout.create_bookself_dialog);
		titleText = (TextView) findViewById(R.id.tv_dialog_title);
	
		
		comment = (EditText) findViewById(R.id.comment);
		color = (EditText) findViewById(R.id.bookself_color);
		possitiveButton = (Button) findViewById(R.id.btn_cmdlg_possitive);
		negativeButton = (Button) findViewById(R.id.btn_cmdlg_negative);

		color.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				
				/*InputMethodManager imm = (InputMethodManager)context.getSystemService(
					      Context.INPUT_METHOD_SERVICE);
					imm.hideSoftInputFromWindow(color.getWindowToken(), 0);*/
				showListPopup(color);
			}
		});
		
		
	}
	
	public void setTitle(String title)
	{
		titleText.setText(title);
	}
	
	public void setContent(String title, String message)
	{
		titleText.setText(title);
		
	}
	
	public EditText getColorEditText()
	{
		return color;
	}
	
	public EditText geNametEditText()
	{
		return comment;
	}
	
	
	
	public String getComment()
	{
		return comment.getText().toString();
	}
	
	public String getColor()
	{
		return color.getText().toString();
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
	
	
	
	 public void showListPopup(View anchor) {
	        popup = new ListPopupWindow(context);
	        popup.setAnchorView(anchor);

	        ListAdapter adapter = new MyAdapter(context);
	        popup.setAdapter(adapter);

	        popup.show();
	    }
	 
	 public  class MyAdapter extends BaseAdapter implements ListAdapter {
	       
	    	private final String[] list = new String[] {"Blue","Green","Orange"};
	        private Context activity;
	        
	        JSONObject jsonObject = null;
	        String country = null;

	        public MyAdapter(Context activity) {
	            this.activity = activity;
	        }

	        @Override
	        public int getCount() {
	            return list.length;
	        }

	        @Override
	        public Object getItem(int position) {
	            return list[position];
	        }

	        @Override
	        public long getItemId(int position) {
	            return position;
	        }

	        private static final int textid = 1234;
	        @Override
	        public View getView(final int position, View convertView, ViewGroup parent) {
	            TextView text = null;
	            if (convertView == null) {
	                LinearLayout layout = new LinearLayout(activity);
	                layout.setOrientation(LinearLayout.HORIZONTAL);

	                text = new TextView(activity);
	                LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, 40);
	                llp.setMargins(5, 0, 0, 0);
	                text.setGravity(Gravity.CENTER_VERTICAL);
	                text.setLayoutParams(llp);
	                text.setId(textid);
	                text.setTextColor(Color.parseColor("#ffffff"));
	                layout.addView(text);

	                convertView = layout;
	            }
	            else {
	                text = (TextView)convertView.findViewById(textid);
	            }
	            text.setText(list[position]);
	            if(position == 0)
	            {
	            	convertView.setBackgroundColor(Color.BLUE);
	            }
	            else if(position == 1)
	            {
	            	convertView.setBackgroundColor(Color.parseColor("#04B404"));
	            }
	            
	            else if(position == 2)
	            {
	            	convertView.setBackgroundColor(Color.parseColor("#FF6600"));
	            }
	            
	            convertView.setOnClickListener(new View.OnClickListener() {
					
					@Override
					public void onClick(View v) {
						// TODO Auto-generated method stub
						
						color.setText(list[position]);
						popup.dismiss();
					}
				});
	            return convertView;
	        }
	    }
	 
	 
	 

}
