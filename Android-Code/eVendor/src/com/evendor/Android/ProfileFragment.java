package com.evendor.Android;

import java.util.ArrayList;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.inputmethod.EditorInfo;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ListPopupWindow;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;
import android.widget.TextView.OnEditorActionListener;

import com.evendor.Android.R;
import com.evendor.Modal.Store;
import com.evendor.appsetting.AppSettings;
import com.evendor.utils.CMDialog;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class ProfileFragment extends Fragment implements ServiceResultListener {
	
	UrlMakingClass urlMakingObject;
	View rootView;
	TextView tvPathView;
	TextView userName;
	TextView firstNmae;
	TextView lastNmae;
	TextView username;
	TextView cvv;
	TextView date;
	EditText userNameEditText;
	EditText firstNameEditText;
	EditText lastNameEditText;
	EditText countryEditText;
	Button register;
	JSONArray Allstores;
	
	ImageLoader imageloader;
//	public static JSONArray Allstores;
	String contryId;
	boolean webserviceCallingRestrictions;
	boolean webServiceDecidedCheck;
	CheckBox checkBox;
	String accountType;
	ListPopupWindow popup;
	ImageView imgflag;
	Activity activity ;
	
	@Override
	public void onCreate(Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onCreate(savedInstanceState);
		}
	
	public View onCreateView(LayoutInflater inflater, ViewGroup container,Bundle savedInstanceState) 
	{
		 super.onActivityCreated(savedInstanceState);
         rootView= inflater.inflate(R.layout.profile_setting, container, false);
		 return rootView;
	}
	

	
	public void onActivityCreated(Bundle savedInstanceState) 
     {
		 super.onActivityCreated(savedInstanceState);
		 
		  activity =getActivity();
		 
		 firstNmae = (TextView) activity.findViewById(R.id.first_name_text);
		 lastNmae = (TextView) activity.findViewById(R.id.last_name_text);
		 userName = (TextView) activity.findViewById(R.id.usernameText);
		 cvv = (TextView) activity.findViewById(R.id.cvvText);
		 date = (TextView) activity.findViewById(R.id.expiry_date);
		 checkBox = (CheckBox) activity.findViewById(R.id.checkBox);
		 imgflag = (ImageView) activity.findViewById(R.id.imageView1);
		 username = (TextView) activity.findViewById(R.id.username);
		 
		 userNameEditText = (EditText) activity.findViewById(R.id.userName);
		 
		 firstNameEditText  = (EditText) activity.findViewById(R.id.first_name);
		 
		 lastNameEditText  = (EditText) activity.findViewById(R.id.last_name);
		 
	
		 
		 register = (Button) activity.findViewById(R.id.register);
		 
		 userName.setText("Default Store");
		 userNameEditText.setHint("Country");
		 
		 imageloader=new ImageLoader(activity);
		 
		    AppSettings appSetting  = new AppSettings(activity);
			
			final String userId = appSetting.getString("UserId");
			contryId= appSetting.getString("countryid");
			 username.setText(appSetting.getString("Username"));
		
		 
		    urlMakingObject= new UrlMakingClass(activity);
			urlMakingObject.setServiceResultListener(this);
			urlMakingObject.profileSetting(userId);
			 lastNameEditText.setOnEditorActionListener(new OnEditorActionListener() {
				    @Override
				    public boolean onEditorAction(TextView view, int actionId, KeyEvent event) {
				        int result = actionId & EditorInfo.IME_MASK_ACTION;
				        switch(result) {
				        case EditorInfo.IME_ACTION_DONE:
				        	/* AppSettings appSetting  = new AppSettings(activity);
				 			
				 			final String userId = appSetting.getString("UserId");*/
				        	updateMethod(userId);
				            break;
				        case EditorInfo.IME_ACTION_NEXT:
				            // next stuff
				            break;
				        }
						return false;
				    }
				});
		    register.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				updateMethod(userId);
			}
		});
		 
		
		 userNameEditText.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				Log.e("countrytStrores...", "****");
				if(StartFragmentActivity.store_flag){
//					getfulllib_flag=false;
					showListPopup(userNameEditText);
				}else{

					webserviceCallingRestrictions = true;
//					webserviceCallingRestrictions = true;
					urlMakingObject.countryName();
//					urlMakingObject= new UrlMakingClass(getActivity());
//					//urlMakingObject.setServiceResultListener(getActivity());
//					urlMakingObject.stroreName();
				}
				
//				webserviceCallingRestrictions = true;
//				urlMakingObject.countryName();
			}
		});
		 	
	}
	public void updateMethod(final String userId){
		if(!firstNameEditText.getText().toString().equals("") && !lastNameEditText.getText().toString().equals("")
				&& !userNameEditText.getText().toString().equals(""))
		{
		    /*if(checkBox.isChecked())
			accountType = "1";
		    else
		    accountType = "2";*/
			webServiceDecidedCheck = true;
			webserviceCallingRestrictions=false;
		    urlMakingObject.updateProfile(userId,firstNameEditText.getText().toString(), 
				lastNameEditText.getText().toString(), contryId);
		    
		   
		 
		ApplicationManager.libraryChacingArray = null ; // used to reload the library .
		
		}
		else
		{
			showErrorDialog("All fields are mandatary");
			
		}
	}
	@Override
	public void onDetach() {
		// TODO Auto-generated method stub
		super.onDetach();
	}

	@Override
	public void onSuccess(final String result) {
		// TODO Auto-generated method stub
		
		activity.runOnUiThread(new Runnable() {
			
			public void run() 
			{
				
				
				Log.e("hhhhhhhhhhh", "  "+result);
			if(webServiceDecidedCheck)
				{
				if(webserviceCallingRestrictions)
				{
					
					try {
						JSONObject responseInJSONObject=new JSONObject(result);

						Allstores =responseInJSONObject.getJSONArray("Allstores");
						StartFragmentActivity.storeList.clear();
						for(int i=0;i<Allstores.length();i++){
							if(Allstores.getJSONObject(i).getString(Store.TAG_STORE)!=null && (Allstores.getJSONObject(i).getString(Store.TAG_STORE).trim().length()>0)){
							Store store=new Store();
							store.setId(Allstores.getJSONObject(i).getString(Store.TAG_ID));
							store.setCountry_flag(Allstores.getJSONObject(i).getString(Store.TAG_COUNTRY_FLAG));
							store.setCountry_flag_url(Allstores.getJSONObject(i).getString(Store.TAG_COUNTRY_FLAG_URL));
							store.setStore(Allstores.getJSONObject(i).getString(Store.TAG_STORE));
							StartFragmentActivity.storeList.add(store);
							/**@author MI Date : 03/April/14
							 * added if condition for 
							 * moving International store to 0 position.
							 */
							
							if (Allstores.getJSONObject(i).getString(Store.TAG_STORE).contains("International Store")) {
								StartFragmentActivity.storeList.add(0,store);
							  }else
								  StartFragmentActivity.storeList.add(store);
							
							
							}
						}
						
//						Log.i("STORES", Allstores +"");

						//						 showListPopup(filerLayoutMenu);	//filerLayoutMenu
						showListPopup(userNameEditText);
						//						 initiatePopupWindow();

					} catch (JSONException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}

					
					
//					 try {
//						JSONObject responseInJSONObject=new JSONObject(result);
//						
//						Allcountries =responseInJSONObject.getJSONArray("Allcountries");
//						
//					} catch (JSONException e) {
//						// TODO Auto-generated catch block
//						e.printStackTrace();
//					}
					 
//					 showListPopup(userNameEditText);
					
					webserviceCallingRestrictions = false;
					
				}
				else
				{
				try
				{
                    JSONObject responseInJSONObject=new JSONObject(result);
					
                    if(responseInJSONObject.has("Message"))
					{
						showSuccessDialog(responseInJSONObject.getString("Message"));
					}
                    /**@author MIPC27
                     * commented the below code as the condition was not correct.
                     */
					/*if(responseInJSONObject.getString("Message").equalsIgnoreCase("Success"))
					{
						showSuccessDialog("Profile Successfully updated");
					}
					else if(responseInJSONObject.getString("Message").equalsIgnoreCase("Unsuccess"))
					{
						showErrorDialog(responseInJSONObject.getString("Error"));
						
			        }*/

				}
				catch (JSONException e) 
				{
					e.printStackTrace();
				}
				
			} 
	}
				
	 if(!webServiceDecidedCheck)
		{
		String error = null;
		String userFirstName = null;
		String userName = null;
		String userLastName = null;
		String Country = null;
		String userType = null;
		String country_flag = null;
		JSONObject response = null;
    	JSONObject responseInJSONObject = null;
    	
    	
    	try {
			 response = new JSONObject(result);
			 if(response.getString("Message").equalsIgnoreCase("Success")){
			 responseInJSONObject=response.getJSONObject("UserDetail");
			 userFirstName = responseInJSONObject.getString("FirstName");
			 userLastName = responseInJSONObject.getString("LastName");
			 Country = responseInJSONObject.getString("Country");
			 userType = responseInJSONObject.getString("UserType");//country_flag_url   Username
			 country_flag = responseInJSONObject.getString("country_flag_url");//
			 userName = responseInJSONObject.getString("Username");//
			 
			 
			  AppSettings appSetting  = new AppSettings(activity);
				 appSetting.saveString("Country", Country);
				 appSetting.saveString("Username", userName);
				 if(responseInJSONObject.getString("Country")!=null)
				 appSetting.saveString("countryid",responseInJSONObject.getString("countryid"));
//					appSetting.saveString("apikey", responseInJSONObject.getString("apikey") );
				
				 
			 userNameEditText.setText(Country);
			 
			 firstNameEditText.setText(userFirstName);
			 
			 lastNameEditText.setText(userLastName);
			 username.setText(userName);
			 
			 imageloader.DisplayImage(country_flag, imgflag);
			 }else{
				 
				 Toast.makeText(activity,response.getString("Message"), Toast.LENGTH_SHORT).show();
			 }
			 
			
			/* 
			 if(userType.equals("Company"))
			 {
				checkBox.setChecked(true);
				accountType = "1" ;
			 }
			 */
			 
			
			 
			
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
    	
    	webServiceDecidedCheck = true;
					
		}
				
			}
		});

		
	}

	@Override
	public void onError(final String errorMessage) {
		// TODO Auto-generated method stub
		if(activity!=null && errorMessage!=null)
			activity.runOnUiThread(new Runnable() {
				
				public void run() {
					if(errorMessage.equals("-389"))
					{
						showErrorDialog("Network error");
					}
					else if(errorMessage.equals("-390"))
					{
						Utils.ConnectionErrorDialog(activity);
					}
				}
			 });
		
	}
	private void showErrorDialog(String errorMsg) 
	{	
		if(getActivity()!=null){
		final CMDialog cmDialog = new CMDialog(activity);
		cmDialog.setContent(CMDialog.PROFILE_UPDATE, errorMsg);
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
	
	private void showSuccessDialog(String success) 
	{				
		final CMDialog cmDialog = new CMDialog(activity);
		cmDialog.setContent(CMDialog.PROFILE_UPDATE, success);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
			//	firstNameEditText.setText(""); 
			//	lastNameEditText.setText(""); 
			//	userNameEditText.setText(""); 
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	public void showListPopup(View anchor) {
		popup = new ListPopupWindow(activity);
		popup.setAnchorView(anchor);
		popup.setHeight(370);
		popup.setWidth(300);
		
		
		StoreAdapter adp=new StoreAdapter(activity, StartFragmentActivity.storeList);
		//   ListAdapter adapter = new MyAdapter(this);
		popup.setAdapter(adp);
		StartFragmentActivity.store_flag=true;
		popup.show();

	}
	
//	public void showListPopup(View anchor) {
//        popup = new ListPopupWindow(getActivity());
//        popup.setAnchorView(anchor);
//
//        ListAdapter adapter = new MyAdapter(getActivity());
//        popup.setAdapter(adapter);
//
//        popup.show();
//    }

//    public  class MyAdapter extends BaseAdapter implements ListAdapter {
//        private Activity activity;
//        
//        JSONObject jsonObject = null;
//        String country = null;
//
//        public MyAdapter(Activity activity) {
//            this.activity = activity;
//        }
//
//        @Override
//        public int getCount() {
//            return Allcountries.length();
//        }
//
//        @Override
//        public Object getItem(int position) {
//        	
//        	 JSONObject jsonObject = null;
//        	
//        	try {
//        		jsonObject = (JSONObject) Allcountries.get(position);
//			} catch (JSONException e) {
//				// TODO Auto-generated catch block
//				e.printStackTrace();
//			}
//        	
//            return jsonObject;
//        }
//
//        @Override
//        public long getItemId(int position) {
//            return position;
//        }
//
//        private static final int textid = 1234;
//        @Override
//        public View getView(final int position, View convertView, ViewGroup parent) {
//            TextView text = null;
//            if (convertView == null) {
//                LinearLayout layout = new LinearLayout(activity);
//          
//                layout.setOrientation(LinearLayout.HORIZONTAL);
//
//                text = new TextView(activity);
//                LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, 30);
//                llp.setMargins(5, 0, 0, 0);
//                text.setGravity(Gravity.CENTER_VERTICAL);
//                text.setLayoutParams(llp);
//                text.setId(textid);
//
//                layout.addView(text);
//
//                convertView = layout;
//            }
//            else {
//                text = (TextView)convertView.findViewById(textid);
//            }
//            
//            try {
//				jsonObject = (JSONObject) Allcountries.get(position);
//				country = jsonObject.getString("country");
//			} catch (JSONException e) {
//				// TODO Auto-generated catch block
//				e.printStackTrace();
//			}
//            
//            text.setText(country);
//            convertView.setOnClickListener(new View.OnClickListener() {
//				
//				@Override
//				public void onClick(View v) {
//					// TODO Auto-generated method stub
//					
//				//	contryIdToAddToUrl
//					
//					try {
//						jsonObject = (JSONObject) Allcountries.get(position);
//						country = jsonObject.getString("country");
//						contryIdToAddToUrl = jsonObject.getString("id");
//						
//					} catch (JSONException e) {
//						// TODO Auto-generated catch block
//						e.printStackTrace();
//					}
//					
//					userNameEditText.setText(country);
//					
//					popup.dismiss();
//					
//				}
//			});
//            return convertView;
//        }
//    }

    class StoreAdapter extends BaseAdapter{

		private Context mContext;
		ImageLoader imageLoader;
		 String country = null;

		private LayoutInflater  mInflater;
		private ArrayList<Store> mlist;
		public StoreAdapter(Context ctxt,ArrayList<Store> list){
			mInflater = (LayoutInflater) ctxt.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
			mContext=ctxt;
			mlist=list;
			imageLoader=new ImageLoader(ctxt);
		}

		@Override
		public int getCount() {
			// TODO Auto-generated method stub
			if(mlist==null){
				return 0;
			}
			return mlist.size();
		}

		@Override
		public Object getItem(int arg0) {
			// TODO Auto-generated method stub
			return mlist.get(arg0);
		}

		@Override
		public long getItemId(int position) {
			// TODO Auto-generated method stub
			return position;
		}

		@Override
		public View getView(final int position, View convertView, ViewGroup parent) {

			// View       view = convertView;
			ViewHolder viewHolder;

			if (convertView == null) {
				convertView = mInflater.inflate(R.layout.store_item, parent, false);

				viewHolder = new ViewHolder();

				viewHolder.storeIcon  = (ImageView) convertView.findViewById(R.id.imageView1);
				viewHolder.title  = (TextView) convertView.findViewById(R.id.textView1);
				viewHolder.rl=(RelativeLayout) convertView.findViewById(R.id.RelativeLayout1);
				convertView.setTag(viewHolder);
			}
			else {
				viewHolder = (ViewHolder) convertView.getTag();
			}

			viewHolder.title.setText(mlist.get(position).getStore());
			if(mlist.get(position).getCountry_flag_url()!=null){
//				if(mlist.get(position).getCountry_flag_url().endsWith(".jpg")||mlist.get(position).getCountry_flag_url().endsWith(".png")||mlist.get(position).getCountry_flag_url().endsWith(".JPEG"))
				{
					imageLoader.DisplayImage(mlist.get(position).getCountry_flag_url(), viewHolder.storeIcon);
				}
			}

			viewHolder.rl.setOnClickListener(new OnClickListener() {

				@Override
				public void onClick(View v) {
//					popup.dismiss();
					
					country = mlist.get(position).getStore();
					contryId = mlist.get(position).getId();
					userNameEditText.setText(country);
//					
					imageloader.DisplayImage(mlist.get(position).getCountry_flag_url(), imgflag);
					popup.dismiss();
					  AppSettings appSetting  = new AppSettings(activity);
//					  appSetting.saveString("UserId", responseInJSONObject.getString("userid"));
						
						appSetting.saveString("countryid",contryId);
//						appSetting.saveString("apikey", responseInJSONObject.getString("apikey") );
						appSetting.saveString("Country",country);
						
//					appSetting.saveString("countryid", storeList.get(position).getId());
//					new TheTask().execute(Constant.GalarryURL+"GetAllCategories/apikey/"+appSetting.getString("apikey")+"/StoreId/"+storeList.get(position).getId()+"/UserId/"+appSetting.getString("UserId"),
//							Constant.GalarryURL+"GetOnlyFeatured/apikey/"+appSetting.getString("apikey")+"/StoreId/"+storeList.get(position).getId()+"/UserId/"+appSetting.getString("UserId"),"Featured");

				}
			});


			return convertView;
		}

		private class ViewHolder {
			public ImageView storeIcon;
			public TextView title;

			public RelativeLayout rl;

		}

	}
    
    public void refresh(){
    	webServiceDecidedCheck=false;
    	 AppSettings appSetting  = new AppSettings(activity);
			
			final String userId = appSetting.getString("UserId");
		 
		    urlMakingObject= new UrlMakingClass(activity);
			urlMakingObject.setServiceResultListener(this);
			urlMakingObject.profileSetting(userId);
		
    	
    }
    
}

	

	


