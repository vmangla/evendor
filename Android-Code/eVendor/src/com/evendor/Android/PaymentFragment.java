package com.evendor.Android;

import java.util.Calendar;

import android.app.Activity;
import android.app.DatePickerDialog;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.TextView;
import com.evendor.Android.R;

public class PaymentFragment extends Fragment {
	
	View rootView;
	TextView tvPathView;
	TextView userName;
	EditText userNameEditText;
	EditText cardExpiryDate;
	Button bottomButton;
	@Override
	public void onCreate(Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onCreate(savedInstanceState);
		}
	
	public View onCreateView(LayoutInflater inflater, ViewGroup container,Bundle savedInstanceState) 
	{
		
		 super.onActivityCreated(savedInstanceState);
         rootView= inflater.inflate(R.layout.account_setting, container, false);
        
         
		 return rootView;
	}
	

	
	public void onActivityCreated(Bundle savedInstanceState) 
     {
		 super.onActivityCreated(savedInstanceState);
		 Activity activity =getActivity();
		 userName = (TextView) activity.findViewById(R.id.usernameText);
		 userNameEditText = (EditText) activity.findViewById(R.id.userName);
		 
		 bottomButton = (Button) activity.findViewById(R.id.register);
		 cardExpiryDate = (EditText) activity.findViewById(R.id.date);
		 userName.setText("Card Number");
		 userNameEditText.setHint("Card Number");
		 bottomButton.setText("Store");
		 
		 
		 cardExpiryDate.setOnClickListener(new View.OnClickListener() {
				// @Override
				public void onClick(View v) {
					final Calendar c = Calendar.getInstance();
					
					int y = c.get(Calendar.YEAR)+4;
					int m = c.get(Calendar.MONTH)-2;
				//	int d = c.get(Calendar.DAY_OF_MONTH);
					final String[] MONTH = {"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"};
	 
					DatePickerDialog dp = new DatePickerDialog(getActivity(),
							new DatePickerDialog.OnDateSetListener() {
	 
								@Override
								
								
								public void onDateSet(DatePicker view, int year,
										int monthOfYear, int dayOfMonth) {
									
									
									String erg = "";
									erg = String.valueOf(dayOfMonth);
									erg += "." + String.valueOf(monthOfYear + 1);
									erg += "." + year;
	 
									(cardExpiryDate).setText(erg);
	 
								}
	 
							}, y, m,0);
					dp.setTitle("Calender");
					dp.setMessage("Select expiry date");
					
					dp.show();
					
					
					
				}
			});
		 
		 	
	}
	
	@Override
	public void onDetach() {
		// TODO Auto-generated method stub
		 
		super.onDetach();
	}
	

      
}

	

	


