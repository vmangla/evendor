package com.evendor.Android;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.inputmethod.EditorInfo;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.TextView.OnEditorActionListener;

import com.evendor.Android.R;
import com.evendor.appsetting.AppSettings;
import com.evendor.utils.CMDialog;
import com.evendor.utils.Md5EncryptionUtility;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class ChangePassswordFragment extends Fragment implements ServiceResultListener {

	View rootView;
	TextView tvPathView;
	RelativeLayout lastRelativeLayout;
	UrlMakingClass urlMakingObject;
	Button register;
	EditText currentPassword;
	EditText newPassword;
	EditText user_name;
	@Override
	public void onCreate(Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onCreate(savedInstanceState);
	}

	public View onCreateView(LayoutInflater inflater, ViewGroup container,Bundle savedInstanceState) 
	{

		super.onActivityCreated(savedInstanceState);
		rootView= inflater.inflate(R.layout.change_password_setting, container, false);


		return rootView;
	}



	public void onActivityCreated(Bundle savedInstanceState) 
	{
		super.onActivityCreated(savedInstanceState);

		Activity activity =getActivity();
		user_name = (EditText) activity.findViewById(R.id.user_name);
		currentPassword  = (EditText) activity.findViewById(R.id.currentPassword);
		newPassword  = (EditText) activity.findViewById(R.id.newPassword);
		register = (Button) activity.findViewById(R.id.register);


		urlMakingObject= new UrlMakingClass(activity);
		urlMakingObject.setServiceResultListener(this);

		user_name.setText(new AppSettings(getActivity()).getString("UserLoginId"));

		register.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				
				updare_Pass();
				
			}
		});

		newPassword.setOnEditorActionListener(new OnEditorActionListener() {
		    @Override
		    public boolean onEditorAction(TextView view, int actionId, KeyEvent event) {
		        int result = actionId & EditorInfo.IME_MASK_ACTION;
		        switch(result) {
		        case EditorInfo.IME_ACTION_DONE:
		        	updare_Pass();
		            break;
		        case EditorInfo.IME_ACTION_NEXT:
		            // next stuff
		            break;
		        }
				return false;
		    }
		});
	

	}
public void updare_Pass(){
	StartFragmentActivity.hideSoftKeyboard(getActivity());
	AppSettings appSetting  = new AppSettings(getActivity());

	String userId = appSetting.getString("UserId");

	if(currentPassword.getText().toString().length()<=3 && currentPassword.getText().toString().length() != 0 )
	{
		showErrorDialog("Password is too short,Minimum characters must be four");

	}
	else
	{

		if(!currentPassword.getText().toString().equals("") && !newPassword.getText().toString().equals("")
				&& !user_name.getText().toString().equals(""))
		{

			if(isEmailValid(user_name.getText().toString()))
			{

				if(currentPassword.getText().toString().equals(newPassword.getText().toString()))
				{

					urlMakingObject.changePassword(userId,currentPassword.getText().toString(), 
							newPassword.getText().toString());
				}
				else
				{
					showErrorDialog("Password doesn't match");
				}
			}

			else
			{
				showErrorDialog("Not a valid user email");
			}

		}
		else
		{
			showErrorDialog("All fields are mandatary");
		}
	}

}
	boolean isEmailValid(CharSequence email) {
		return android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches();
	}

	@Override
	public void onDetach() {
		// TODO Auto-generated method stub

		super.onDetach();
	}

	@Override
	public void onSuccess(final String result) {
		// TODO Auto-generated method stub

		getActivity().runOnUiThread(new Runnable() {

			private AppSettings appSetting;

			public void run() 
			{
				try
				{
					JSONObject responseInJSONObject=new JSONObject(result);

					if(responseInJSONObject.getString("Message").equalsIgnoreCase("Success"))
					{
						 appSetting  = new AppSettings(getActivity());
							String encryptedUserLoginPasswordString = Md5EncryptionUtility.encrypt(currentPassword.getText().toString());
							appSetting.saveString("UserLoginPassword", encryptedUserLoginPasswordString);
						
						currentPassword.setText("") ;
						newPassword.setText("") ;
						showErrorDialog("Password changed successfully");

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

		//	showErrorDialog("Password has been changed successfully");

	}

	@Override
	public void onError(final String errorMessage) {
		// TODO Auto-generated method stub
		getActivity().runOnUiThread(new Runnable() {

			public void run() {
				if(errorMessage.equals("-389"))
				{
					showErrorDialog("Network error");
				}
				else if(errorMessage.equals("-390"))
				{
					Utils.ConnectionErrorDialog(getActivity());
				}
			}
		});
	}


	private void showErrorDialog(String errorMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
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






