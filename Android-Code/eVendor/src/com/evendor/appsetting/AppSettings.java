package com.evendor.appsetting;

import android.app.Activity;
import android.content.Context;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;

public class AppSettings
{
	Context appContext;
	SharedPreferences preferences;
	
	public static final boolean _DEBUG = true;
	private static String PREF_NAME = "CM_EVENTS";
	private static AppSettings instance;
	
	public static final String REGISTER_STATE = "REG_STATE";
	public static final String LOGIN_STATE = "LOGIN_STATE";
	public static final String APP_USER_ID = "APP_USER_ID";
	public static final String TOKEN_ID = "TOKEN_ID";	
	public static final String DEVICE_ID = "DEVICE_ID";	
	public static final String USER_NAME = "USER_NAME";
	public static final String UserLoginId = "UserLoginId";
	public static final String PASSWORD = "PASSWORD";
	
	public AppSettings(Context ctx)
	{
		appContext = ctx;
		preferences = ctx.getSharedPreferences(PREF_NAME,Activity.MODE_PRIVATE);
	}
	
	/*public static AppSettings getInstance(Context ctx)
	{
		if(instance == null)
		{
			instance = new AppSettings(ctx);
		}
		
		return instance;
	}*/
	
	public void saveString(String key,String value)
	{
		Editor editor = preferences.edit();
		editor.putString(key, value);
		editor.commit();
	}
	
	public String getString(String key)
	{
		String result="";		
		result = preferences.getString(key, "");		
		return result;
	}
	
	public void saveBoolean(String key, boolean value)
	{
		Editor editor = preferences.edit();
		editor.putBoolean(key, value);
		editor.commit();
	}
	
	public boolean getBoolean(String key)
	{
		boolean result = false;		
		result = preferences.getBoolean(key, false);		
		return result;
	}
	
	public int getInt(String key)
	{
		int result = 0;		
		result = preferences.getInt(key, 0);		
		return result;
	}
	
	public void saveInt(String key, int value)
	{
		Editor editor = preferences.edit();
		editor.putInt(key, value);
		editor.commit();
	}
	/**@author MIPC27
	 * added clear all method
	 * @param key
	 * @param value
	 */
	
	public boolean clearKey(String key) {
		Editor editor = preferences.edit();
		editor.remove(key);
		editor.commit();
		return true;
		
	}
	public void ClearAll()
	{
		  preferences.edit().clear().commit();
	}
	
}
