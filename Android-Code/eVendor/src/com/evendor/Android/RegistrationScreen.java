package com.evendor.Android;

import java.io.File;
import java.util.regex.Pattern;
import org.json.JSONException;
import org.json.JSONObject;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.database.sqlite.SQLiteDatabase;
import android.os.Bundle;
import android.os.Environment;
import android.text.Html;
import android.text.method.LinkMovementMethod;
import android.text.util.Linkify;
import android.util.Log;
import android.view.KeyEvent;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.inputmethod.EditorInfo;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.TextView.OnEditorActionListener;
import com.evendor.Android.R;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.DBHelper;
import com.evendor.utils.CMDialog1;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class RegistrationScreen extends Activity implements ServiceResultListener {
	
	
	private DBHelper dbHelper                 =null;
    private SQLiteDatabase db;
	
	UrlMakingClass urlMakingObject;
	private static  final String TAG = "RegistrationScreen";
	EditText firstName;
	EditText lastName;
	EditText emailId;
	EditText password;
	CheckBox checkBox;
	Button cancel;
	private TextView termsText;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.registration);
		
		 firstName = (EditText) findViewById(R.id.first_name);
		 lastName = (EditText) findViewById(R.id.last_name);
		 emailId = (EditText) findViewById(R.id.userName);
		 password = (EditText) findViewById(R.id.password);
		 checkBox = (CheckBox) findViewById(R.id.checkBox);
		 cancel=(Button) findViewById(R.id.buttoncalcel);
		 termsText = (TextView ) findViewById(R.id.accept);
		 termsText.setMovementMethod(LinkMovementMethod.getInstance());
		 /**@author MIPC27
		  * changed the url from miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/page/index/title/terms
		  * to below:
		  * "miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/page/index/title/terms-and-conditions"
		  */
		 /**@author MIPC27
		  * changed url above to evendornigeria.com below on 30-jun-2014
		  */
		setAsLink(termsText,"www.evendornigeria.com/page/index/title/terms-and-conditions");
	
		urlMakingObject= new UrlMakingClass(this);
		urlMakingObject.setServiceResultListener(this);
		cancel.setOnClickListener(new OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				finish();
			}
		});
		 password.setOnEditorActionListener(new OnEditorActionListener() {
			    @Override
			    public boolean onEditorAction(TextView view, int actionId, KeyEvent event) {
			        int result = actionId & EditorInfo.IME_MASK_ACTION;
			        switch(result) {
			        case EditorInfo.IME_ACTION_DONE:
			        	SignUp(password);
			            break;
			        case EditorInfo.IME_ACTION_NEXT:
			            // next stuff
			            break;
			        }
					return false;
			    }
			});
	}
	private void setAsLink(TextView view, String url){
        Pattern pattern = Pattern.compile(url);
        Linkify.addLinks(view, pattern, "http://");
        view.setText(Html.fromHtml( "I accept the " +"<a href='http://"+url+"'>"+getResources().getString(R.string.terms_link)+"</a>"));
    }
	public void SignUp(View v)
	{
		if(checkBox.isChecked())
		{
		  String userFirstName = firstName.getText().toString();
		  String userLastName = lastName.getText().toString();
		  String userEmailId = emailId.getText().toString();
		  String userPassword = password.getText().toString();
		  
		if(userPassword.length()<=3 && userPassword.length() != 0 )
		{
			showErrorDialog("Password is too short,Minimum characters must be four");
		}
		
		  
		else if(!userFirstName.equals("")&&!userLastName.equals("")&&!userEmailId.equals("")&&!userPassword.equals(""))
		{
			
			if(isEmailValid(userEmailId))
				urlMakingObject.registration(userFirstName, userLastName, userEmailId, userPassword, "226");
			 else
				 showErrorDialog("Not a valid user email");
		
		}
		else
			
		  {
			showErrorDialog("All fields are mandatary");
		  }
		}
		else
		{
	      unCheckDialog("You need to accept the Terms and Conditions before you can be registered"); 
		}
		
	}
	
	
	@SuppressLint("NewApi") boolean isEmailValid(CharSequence email) {
		   return android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches();
		}

	@Override
	public void onSuccess(final String result) {
		// TODO Auto-generated method stub
		
       
		runOnUiThread(new Runnable() {
			
			public void run() 
			{
				try
				{
					JSONObject responseInJSONObject=new JSONObject(result);
					if(responseInJSONObject.getString("Message").equalsIgnoreCase("Success"))
					{
						AppSettings appSetting  = new AppSettings(RegistrationScreen.this);
						appSetting.saveString("UserLoginId", emailId.getText().toString() );
						registrationSuccess("Registered Successfully");
					}
					else
					{
						if(responseInJSONObject.getString("error").equalsIgnoreCase("true")){
						
							if(responseInJSONObject.has("Message")){
							String error = responseInJSONObject.getString("Message");
						Log.i(TAG, "ERROR:"+ error);
						showErrorDialog(error);
							}
						}
			        }
				}
				catch (JSONException e) 
				{
					e.printStackTrace();
				}
			}
		});
		
	}

	@Override
	public void onError(final String errorMessage) {
		// TODO Auto-generated method stub
		
      runOnUiThread(new Runnable() {
			
			public void run() {
				if(errorMessage.equals("-389"))
				{
					showErrorDialog("Network error");
				}
				else if(errorMessage.equals("-390"))
				{
					Utils.ConnectionErrorDialog(RegistrationScreen.this);
				}
			}
		});
		
	}
	
	private void showErrorDialog(String errorMsg) 
	{				
		final CMDialog1 cmDialog = new CMDialog1(RegistrationScreen.this);
		cmDialog.setContent(CMDialog1.REGISTER_TITLE, errorMsg);
		cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	private void unCheckDialog(String errorMsg) 
	{				
		final CMDialog1 cmDialog = new CMDialog1(RegistrationScreen.this);
		cmDialog.setContent(CMDialog1.ACCEPT, errorMsg);
		cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	private void registrationSuccess(String successMessage) 
	{				
		final CMDialog1 cmDialog = new CMDialog1(RegistrationScreen.this);
		cmDialog.setContent(CMDialog1.REGISTER_TITLE, successMessage);
		cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				startActivity(new Intent(RegistrationScreen.this, LoginScreen.class));
				finish();			
			}
		});
		cmDialog.show();
	}
	
	
	
	private void makeAppDirectory()
	{	
		boolean mExternalStorageAvailable = false;
		boolean mExternalStorageWriteable = false;
		String state = Environment.getExternalStorageState();

		if (Environment.MEDIA_MOUNTED.equals(state)) {
		    // We can read and write the media
		    mExternalStorageAvailable = mExternalStorageWriteable = true;
		    
		   
		} else if (Environment.MEDIA_MOUNTED_READ_ONLY.equals(state)) {
		    // We can only read the media
		    mExternalStorageAvailable = true;
		    mExternalStorageWriteable = false;
		} else {
		    // Something else is wrong. It may be one of many other states, but all we need
		    //  to know is we can neither read nor write
		    mExternalStorageAvailable = mExternalStorageWriteable = false;
		}
		
		 
  if(mExternalStorageAvailable || mExternalStorageWriteable)
  {
	  
	  File direct = new File("/data/data/"+"com.evendor"+ "/Books");
		//File direct = new File(Environment.getExternalStorageDirectory().getAbsolutePath() + "/Android/data/com.magnon.evendor/Books");
		
		Log.i("DIRECT", direct.exists()+"");
		
		if(!direct.exists())
		{
		    if(direct.mkdirs()) 
		      {
		    	
		      }

		}
		
		
		
  }
		
	}

	

}
