package com.evendor.Android;

import android.app.Activity;
import android.content.Intent;
import android.content.res.Configuration;
import android.database.sqlite.SQLiteDatabase;
import android.os.Bundle;
import android.os.Handler;
import android.util.Log;

import com.evendor.Android.R;
import com.evendor.appsetting.AppSettings;
import com.evendor.db.DBHelper;

public class SplashScreen extends Activity {
	private DBHelper dbHelper                 =null;
    private SQLiteDatabase                       db;
	
	private final int SPLASH_DISPLAY_DURATION = 1000;
	private boolean SPLASH_INTERRUPTED        = false;
	
	/*private DBHelper dbHelper                 =null;
	private SQLiteDatabase                       db;*/
	private boolean  alwaysKeepLogedIn;

    
	@Override
    public void onCreate(Bundle savedInstanceState) {
		
		
		
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_splash_screen);
        getUserNameAndPassword();
        
        new Handler().postDelayed( new Runnable()
		{
			public void run()
			{
				if (!SplashScreen.this.SPLASH_INTERRUPTED)
				{
						 /* db = openOrCreateDatabase(DB.DATABASE, Context.MODE_PRIVATE, null);
						  dbHelper = new DBHelper(db,false);*/
					
						  SplashScreen.this.startLoginScreen(); 
						
				}
			}
			

		}, SPLASH_DISPLAY_DURATION);
        
    }
    
    
    private void startLoginScreen()
	{
    	
    	//Intent loginIntent ;//= null;
    	if(alwaysKeepLogedIn){
    		try{										//StartFragmentActivity
    			Intent 		loginIntent= new Intent(this,StartFragmentActivity.class);
    			 startActivity(loginIntent);
    		}catch(Exception e){
    			Log.e("startLoginScreen____------------>", e.toString());
    		}
    		
    	}
	        
		else{
			Intent loginIntent1= new Intent(this,LoginScreen.class);	
	    startActivity(loginIntent1);
		}
		finish();
	}

   
    
    
    @Override
	protected void onPause() {
		// TODO Auto-generated method stub
		SPLASH_INTERRUPTED=true;
		super.onPause();
	}
    
    
private void getUserNameAndPassword() {
		
	AppSettings appSetting  = new AppSettings(this);
	alwaysKeepLogedIn = appSetting.getBoolean(AppSettings.LOGIN_STATE);
				
				
		
	}


@Override
public void onConfigurationChanged(Configuration newConfig) {
	// TODO Auto-generated method stub
	super.onConfigurationChanged(newConfig);
	setContentView(R.layout.activity_splash_screen);
	
	
}



}
