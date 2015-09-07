package com.evendor.Android;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.util.concurrent.atomic.AtomicInteger;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager.NameNotFoundException;
import android.content.res.Configuration;
import android.database.sqlite.SQLiteDatabase;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.telephony.TelephonyManager;
import android.util.Log;
import android.view.KeyEvent;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.inputmethod.EditorInfo;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.TextView.OnEditorActionListener;

import com.evender.Constant.Constant;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.DB;
import com.evendor.db.DBHelper;
import com.evendor.utils.CMDialog1;
import com.evendor.utils.Md5EncryptionUtility;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;
import com.google.android.gms.gcm.GoogleCloudMessaging;

public class LoginScreen extends Activity implements ServiceResultListener {
	 AppSettings appSetting ;
	UrlMakingClass urlMakingObject;
	private static  final String TAG = "LoginScreen";
	EditText emailId;
	EditText password;
	private DBHelper dbHelper =null;
    private SQLiteDatabase                       db;
    private boolean webserviceCallCheck;
    private final static int PLAY_SERVICES_RESOLUTION_REQUEST = 9000;
    public static final String EXTRA_MESSAGE = "message";
    public static final String PROPERTY_REG_ID = "registration_id";
    private static final String PROPERTY_APP_VERSION = "appVersion";
    GoogleCloudMessaging gcm;
    public static final   String SENDER_ID = "766337954520";//"47308199086";
    String regid;
    AtomicInteger msgId = new AtomicInteger();
    
    private Bundle bndl;
   

    /**
     * Tag used on log messages.
     */
    


	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.login);
		emailId = (EditText) findViewById(R.id.userName);
		password = (EditText) findViewById(R.id.password);
		
		urlMakingObject= new UrlMakingClass(this);
		urlMakingObject.setServiceResultListener(this);
		
		regid = getRegistrationId(getApplicationContext());

        if (regid.isEmpty()) {
            registerInBackground();
        }
		
        bndl = getIntent().getExtras();
        
        if(bndl!=null){
        	
        	String key = bndl.getString(Constant.notification);
        	if(key!=null && key.equalsIgnoreCase("yes")){
        		
        		emailId.setText(bndl.getString("notification_email"));
        		GcmIntentService.registerInBackground(bndl.getString("notification_email"));
        	}
        	
        	
        }
        
        
        
		
//		String reg_id = GCMRegistrar.getRegistrationId(LoginScreen.this);
//		Log.e("++++", reg_id);
		 // Check device for Play Services APK. If check succeeds, proceed with
        //  GCM registration.
      
		
		password.setOnEditorActionListener(new OnEditorActionListener() {
		    @Override
		    public boolean onEditorAction(TextView view, int actionId, KeyEvent event) {
		        int result = actionId & EditorInfo.IME_MASK_ACTION;
		        switch(result) {
		        case EditorInfo.IME_ACTION_DONE:
		           Login(password);
		            break;
		        case EditorInfo.IME_ACTION_NEXT:
		            // next stuff
		            break;
		        }
				return false;
		    }
		});
	
		
	}
	
	
	@Override
	public void onConfigurationChanged(Configuration newConfig) {
		// TODO Auto-generated method stub
		super.onConfigurationChanged(newConfig);
		
		
		
	}
	
	public void CreateAccount(View v)
	{
		startActivity(new Intent(this, RegistrationScreen.class));
		
		//finish();
	}
	
	public void Login(View v)
	{
		webserviceCallCheck = false;
		registerBackground();
		
		String userEmailId = emailId.getText().toString();
		String userPassword = password.getText().toString();
		if(!userEmailId.equals("")&&!userPassword.equals(""))
		{
		 if(isEmailValid(userEmailId))
		     urlMakingObject.login(userEmailId, userPassword,getEMEINO(),regid);
		 else
			 showErrorDialog("Not a valid user email",2, null);
		}
		else
		{
			 showErrorDialog("All fields are mandatory",2, null); // mandatary is replaced by mandatory 
		}
		
   }
	
	public void ForgotButtonClick(View v)
	{
		startActivity(new Intent(LoginScreen.this, ForgotPasswordScreen.class));
	}
	
	boolean isEmailValid(CharSequence email) {
		   return android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches();
		}

	@Override
	public void onSuccess(final String result) {
		
		
		runOnUiThread(new Runnable() {
			
			public void run() 
			{
				try
				{
					
					Log.e("---------------", "*********"+result);
					JSONObject responseInJSONObject=new JSONObject(result);
					
					if(responseInJSONObject.getString("Message").equalsIgnoreCase("Success"))
					{
					    appSetting  = new AppSettings(LoginScreen.this);
						 if(appSetting.getString("UserId").equals(""))
						 {
							 makeAppDirectory();
							 db = openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
								dbHelper = new DBHelper(db,true); 
						 } if(!appSetting.getString("UserId").equals(responseInJSONObject.getString("userid")) && !appSetting.getString("UserId").equals("")){
							 showErrorDialog("This user is different from the previous user on this device. Do you want to continue with this user?",0, responseInJSONObject);
						 }
						 /**@author MI
						  * else condition removed dated 10 April, 2014.-
						  * edited again on 17 April, 2014
						  */
						 else{
						appSetting.saveString("UserId", responseInJSONObject.getString("userid"));
						appSetting.saveString("UserLoginId", emailId.getText().toString() );
						appSetting.saveString("countryid", responseInJSONObject.getString("countryid"));
						appSetting.saveString("apikey", responseInJSONObject.getString("apikey") );
						appSetting.saveString("Country", responseInJSONObject.getString("Country") );
						
						//Start----Lines added/modified by Manish
						String encryptedUserLoginPasswordString = Md5EncryptionUtility.encrypt(password.getText().toString());
						appSetting.saveString("UserLoginPassword", encryptedUserLoginPasswordString);
						//End----Lines added/modified by Manish
						
						//appSetting.saveString("UserLoginPassword", password.getText().toString());
						
						appSetting.saveBoolean(AppSettings.LOGIN_STATE,true);//
						try{
							
						
						Intent in =new Intent(LoginScreen.this, StartFragmentActivity.class);
						if(bndl!=null){
							if(bndl.getString(Constant.notification)!=null && bndl.getString(Constant.notification).equalsIgnoreCase("yes"))
							in.putExtra(Constant.notification, "yes");
						}
						startActivity(in);
						finish();
						}catch(Exception e){
							e.printStackTrace();
						}
						}
					}
					else if(responseInJSONObject.getString("Message").equalsIgnoreCase("Unsuccess"))
					{
						//String error = responseInJSONObject.getJSONObject("LoginResponse").getString("Error");
					//	Log.i(TAG, "ERROR:"+ error);
						showErrorDialog("Invalid User Id or Password",2, null);
			        }
					else if(responseInJSONObject.getString("error").equalsIgnoreCase("Invalid User Id or Password"))
					   {
						//String error = responseInJSONObject.getJSONObject("LoginResponse").getString("Error");
					//	Log.i(TAG, "ERROR:"+ error);
						showErrorDialog("Invalid User Id or Password",2, null);
						}else{
							
							showErrorDialog(responseInJSONObject.getString("Message"),2, null);
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
				Log.e("Web service errorMessage: ", errorMessage);
				if(errorMessage.equals("-389") || errorMessage.equals("-389"))
				{	
					if(new AppSettings(LoginScreen.this).getString("UserLoginId").equalsIgnoreCase(emailId.getText().toString()) &&
							new AppSettings(LoginScreen.this).getString("UserLoginPassword").equals(Md5EncryptionUtility.encrypt(password.getText().toString())))
					{
                         startActivity(new Intent(LoginScreen.this, StartFragmentActivity.class));
						 finish();
					}
					else
					{
						 Log.e("errorMessage"+errorMessage,"err"+errorMessage);
						showErrorDialog("Network Error",2, null);
					}
					
				}
				else if(errorMessage.equals("-390"))
				{
					if(!new AppSettings(LoginScreen.this).getString("UserLoginId").equals("") &&
							!new AppSettings(LoginScreen.this).getString("UserLoginPassword").equals(""))
					{
				
					      if(new AppSettings(LoginScreen.this).getString("UserLoginId").equalsIgnoreCase(emailId.getText().toString()) &&
							new AppSettings(LoginScreen.this).getString("UserLoginPassword").equals(Md5EncryptionUtility.encrypt(password.getText().toString())))
					        {
                               startActivity(new Intent(LoginScreen.this, StartFragmentActivity.class));
						       finish();
					        }
					     else
					        {
					    	 Log.e("errorMessage"+errorMessage,"err"+errorMessage);
						   showErrorDialog("Invalid User Id or Password",2, null);
					        }
					
					}
					else
					{
						Utils.ConnectionErrorDialog(LoginScreen.this);
					}
				
					
				}
					
				
			}
		});
	}
	
	private void showErrorDialog(String errorMsg,int flag,final JSONObject responseInJSONObject) 
	{				
		final CMDialog1 cmDialog = new CMDialog1(LoginScreen.this);
		cmDialog.setContent(CMDialog1.LOGIN_TITILE, errorMsg);
		switch (flag) {
	
		case 0:
			cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					showErrorDialog("This application will lose all the previously downloaded/created items. Do you still want to continue?",1,responseInJSONObject);
				cmDialog.dismiss();
				}
			});
			cmDialog.setNegativeButton(CMDialog1.CANCEL, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					cmDialog.cancel();					
				}
			});
			break;
		case 1:
			cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					try {
						 makeAppDirectory();
						 appSetting.ClearAll();
						 db = openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
							dbHelper = new DBHelper(db,true); 
						appSetting.saveString("UserId", responseInJSONObject.getString("userid"));
						appSetting.saveString("UserLoginId", emailId.getText().toString() );
						appSetting.saveString("countryid", responseInJSONObject.getString("countryid"));
						appSetting.saveString("apikey", responseInJSONObject.getString("apikey") );
						appSetting.saveString("Country", responseInJSONObject.getString("Country") );
						
						//Start----Lines added/modified by Manish
						String encryptedUserLoginPasswordString = Md5EncryptionUtility.encrypt(password.getText().toString());
						appSetting.saveString("UserLoginPassword", encryptedUserLoginPasswordString);
						//End----Lines added/modified by Manish
						
						//appSetting.saveString("UserLoginPassword", password.getText().toString());
						
						appSetting.saveBoolean(AppSettings.LOGIN_STATE,true);
						startActivity(new Intent(LoginScreen.this, StartFragmentActivity.class));
						cmDialog.cancel();	
						finish();
				
					} catch (JSONException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
				}
			});
			cmDialog.setNegativeButton(CMDialog1.CANCEL, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					cmDialog.cancel();					
				}
			});
			break;

		default:
			cmDialog.setPossitiveButton(CMDialog1.OK, new OnClickListener() 
			{
				public void onClick(View v) 
				{
					cmDialog.cancel();					
				}
			});
			break;
		}
		
		cmDialog.show();
	}
	
	
	private void makeAppDirectory()
	{
		Log.i("INSIDE","makeAppDirectory");
		
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
	  /**@author MIPC27
	   * Changed the external Storage to internal storage.
	   */
		//File direct = new File(Environment.getExternalStorageDirectory().getAbsolutePath() + "/Android/data/com.evendor.Android/Books");
	  File direct = new File("/data/data/com.evendor.Android/Books");
		Log.i("DIRECT", direct.getPath()+"");
		
		if(!direct.exists())
		{
		    if(direct.mkdirs()) 
		      {
		    	Log.i("DIRECT not exist", direct.exists()+"");
		    	
		      }

		}else{
			try{
				DeleteRecursive(direct);
			 if(direct.mkdirs()) 
		      {
		    	Log.i("DIRECT deleted previous books", direct.exists()+"");
		    	
		      }
			}catch(Exception e){
				e.printStackTrace();
			}
		}
		
    }
		
  }
	
	void DeleteRecursive(File fileOrDirectory) {
		Log.i("DeleteRecursive", fileOrDirectory.exists()+"");
		 if (fileOrDirectory.isDirectory()){
		    for (File child : fileOrDirectory.listFiles()){
		    	Log.e("delete child-", child.getName());
		        DeleteRecursive(child);
		    }
		 }
		    fileOrDirectory.delete();

		    }
	
	private static boolean deleteDir(File dir) {
		
		
		
		Log.i("deleteDir", dir.exists()+"");
	    if (dir != null ) {
	        File[] children = dir.listFiles();
	        Log.i("children", dir.exists()+""+children.length);
	        for (int i = 0; i < children.length; i++) {
	            boolean success = deleteDir(children[i]);
	            Log.i("success", dir.exists()+"");
	            if (!success) {
	                return false;
	            }
	        }
	    }
	    return dir.delete();
	}
	private String getEMEINO(){
		
		TelephonyManager mngr = (TelephonyManager)getSystemService(Context.TELEPHONY_SERVICE); 
		 return mngr.getDeviceId();
	}
	
//	private boolean checkPlayServices() {
//	    int resultCode = GooglePlayServicesUtil.isGooglePlayServicesAvailable(LoginScreen.this);
//	    if (resultCode != ConnectionResult.SUCCESS) {
//	        if (GooglePlayServicesUtil.isUserRecoverableError(resultCode)) {
//	            GooglePlayServicesUtil.getErrorDialog(resultCode, this,
//	                    PLAY_SERVICES_RESOLUTION_REQUEST).show();
//	        } else {
//	            Log.i(TAG, "This device is not supported.");
//	            finish();
//	        }
//	        return false;
//	    }
//	    return true;
//	}

	private String getRegistrationId(Context context) {
	    final SharedPreferences prefs = getGCMPreferences(context);
	    String registrationId = prefs.getString(PROPERTY_REG_ID, "");
	    if (registrationId.isEmpty()) {
	        Log.i(TAG, "Registration not found.");
	        return "";
	    }
	    // Check if app was updated; if so, it must clear the registration ID
	    // since the existing regID is not guaranteed to work with the new
	    // app version.
	    int registeredVersion = prefs.getInt(PROPERTY_APP_VERSION, Integer.MIN_VALUE);
	    int currentVersion = getAppVersion(context);
	    if (registeredVersion != currentVersion) {
	        Log.i(TAG, "App version changed.");
	        return "";
	    }
	    return registrationId;
	}
	
	/**
	 * @return Application's {@code SharedPreferences}.
	 */
	private SharedPreferences getGCMPreferences(Context context) {
	    // This sample app persists the registration ID in shared preferences, but
	    // how you store the regID in your app is up to you.
	    return getSharedPreferences(LoginScreen.class.getSimpleName(),
	            Context.MODE_PRIVATE);
	}
	
	private static int getAppVersion(Context context) {
	    try {
	        PackageInfo packageInfo = context.getPackageManager()
	                .getPackageInfo(context.getPackageName(), 0);
	        return packageInfo.versionCode;
	    } catch (NameNotFoundException e) {
	        // should never happen
	        throw new RuntimeException("Could not get package name: " + e);
	    }
	}
	
	private void registerInBackground(){
		
new Thread(new Runnable() {
			
			@Override
			public void run() {
				// TODO Auto-generated method stub
				 gcm =  GoogleCloudMessaging.getInstance(getApplicationContext());
				 // gcm =  GoogleCloudMessaging.getInstance(LoginScreen.this);
				 try {
					String reg_id = gcm.register(SENDER_ID);
					
					regid=reg_id;
					//gcm.send(to, msgId, data);
					
					 storeRegistrationId(getApplicationContext(), reg_id);
					 
					 File f = new File("/sdcard/regid.txt");
					 f.createNewFile();
					 BufferedWriter writer = null;
					 try
					 {
					     writer = new BufferedWriter( new FileWriter( f));
					     writer.write( reg_id);

					 }
					 catch ( IOException e)
					 {
					 }
					 finally
					 {
					     try
					     {
					         if ( writer != null)
					         writer.close( );
					     }
					     catch ( IOException e)
					     {
					     }
					 }
					 
					Log.e("++++", reg_id);
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}).start();
	
		
	}
	
	private void storeRegistrationId(Context context, String regId) {
	    final SharedPreferences prefs = getGCMPreferences(context);
	    int appVersion = getAppVersion(context);
	    Log.i(TAG, "Saving regId on app version " + appVersion);
	    SharedPreferences.Editor editor = prefs.edit();
	    editor.putString(PROPERTY_REG_ID, regId);
	    editor.putInt(PROPERTY_APP_VERSION, appVersion);
	    editor.commit();
	}

	@SuppressWarnings("unchecked")
	private void registerBackground() {

	    new AsyncTask(){

	        @Override
	        protected Object doInBackground(Object[] objects) {
	        	 String msg = "";
	                try {
	                    Bundle data = new Bundle();
	                        data.putString("my_message", "Hello World");
	                        data.putString("my_action",
	                                "com.google.android.gcm.demo.app.ECHO_NOW");
	                        String id = Integer.toString(msgId.incrementAndGet());
	                        GoogleCloudMessaging gcmz=gcm;
	                        if(null==gcm){
	                        	gcm =  GoogleCloudMessaging.getInstance(getApplicationContext());
	                        }
	                        gcm.send(SENDER_ID + "@gcm.googleapis.com", id, data);
	                        msg = "Sent message";
	                        Log.e("+++++++++++++++++++", msg);
	                } catch (Exception ex) {
	                    msg = "Error :" + ex.getMessage();
	                }
				
	
	            return null;
	        }
	    }.execute(null,null,null);
	}
	
	
}
