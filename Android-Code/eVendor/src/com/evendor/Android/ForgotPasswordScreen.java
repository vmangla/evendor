package com.evendor.Android;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.inputmethod.EditorInfo;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.TextView.OnEditorActionListener;

import com.evendor.Android.R;
import com.evendor.utils.CMDialog;
import com.evendor.utils.CMDialog1;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class ForgotPasswordScreen extends Activity implements ServiceResultListener {
	
	UrlMakingClass urlMakingObject;
	private static  final String TAG = "ForgotPasswordScreen";
	EditText emailId;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.forgot_password_screen);
		emailId = (EditText) findViewById(R.id.email_address);
		
		urlMakingObject= new UrlMakingClass(this);
		urlMakingObject.setServiceResultListener(this);
		emailId.setOnEditorActionListener(new OnEditorActionListener() {
			    @Override
			    public boolean onEditorAction(TextView view, int actionId, KeyEvent event) {
			        int result = actionId & EditorInfo.IME_MASK_ACTION;
			        switch(result) {
			        case EditorInfo.IME_ACTION_DONE:
			        	ForgotButtonClick(emailId);
			            break;
			        case EditorInfo.IME_ACTION_NEXT:
			            // next stuff
			            break;
			        }
					return false;
			    }
			});
	}
	
	public void ForgotButtonClick(View v)
	{
		/**@author MIPC27
		 * added switch
		 */
		switch (v.getId()) {
		case R.id.reset_button:
			String emailID = emailId.getText().toString();
			if(!emailID.equals(""))
			{
			 if(isEmailValid(emailID))
				
				urlMakingObject.forgotPasswordApiCall(emailID);
			 else
				 showErrorDialog("Not a valid user email");
			}
			else
			{
				showErrorDialog("Field is mandatary");
			}
			
			break;

		case R.id.button1:
			ForgotPasswordScreen.this.finish();
			break;
		default:
			break;
		}
		
	}
	
	boolean isEmailValid(CharSequence email) {
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
						    emailId.setText("");
							showErrorDialog("Credentials are sent to your email address");
						
					}
					
					else if(responseInJSONObject.getString("Message").equalsIgnoreCase("Unsuccess"))
					{
						showErrorDialog(responseInJSONObject.getString("Error"));
						
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
	public void onError(final String errorMessage) 
	{
		runOnUiThread(new Runnable() {
			
			public void run() {
				
				if(errorMessage.equals("-389"))
				{	
					showErrorDialog1("Network error");
				}
				else if(errorMessage.equals("-390"))
				{
					Utils.ConnectionErrorDialog(ForgotPasswordScreen.this);
					
				}
				
			}
		});
	}
	
	private void showErrorDialog(final String errorMsg) 
	{				
		final CMDialog1 cmDialog = new CMDialog1(ForgotPasswordScreen.this);
		cmDialog.setContent(CMDialog1.FORGOTPASSWORD_TITILE, errorMsg);
		cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				if(errorMsg.equals("Your credentials has been sent to your email address"))
				{
					finish();
				}
				
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}

	
	private void showErrorDialog1(String errorMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(ForgotPasswordScreen.this);
		cmDialog.setContent(CMDialog.CHANGE_PASSWORD, errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
}
