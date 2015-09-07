package com.evendor.utils;

import java.io.InputStream;
import java.io.OutputStream;
import java.util.List;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.content.pm.ResolveInfo;

import com.evendor.Android.R;

public  class Utils 
{
	public static void ConnectionErrorDialog(Context c) 
	{
		AlertDialog.Builder builder = new AlertDialog.Builder(c);
		builder.setMessage(R.string.NetworkConnection_CommonDialog_Message)
				.setTitle(R.string.NetworkConnection_CommonDialog_Title)
				.setCancelable(false)
				.setPositiveButton(
						R.string.ok,
						new DialogInterface.OnClickListener() {

							public void onClick(DialogInterface dialog,
									int which) {
								
							}
						});
		
		AlertDialog alert = builder.create();
		alert.show();
	}
	
	 public static void CopyStream(InputStream is, OutputStream os)
	    {
	        final int buffer_size=1024;
	        try
	        {
	            byte[] bytes=new byte[buffer_size];
	            for(;;)
	            {
	              int count=is.read(bytes, 0, buffer_size);
	              if(count==-1)
	                  break;
	              os.write(bytes, 0, count);
	            }
	        }
	        catch(Exception ex){}
	    }
	 
	 
	 public static void setBadge(Context context, int count) {
	        String launcherClassName = getLauncherClassName(context);
	        if (launcherClassName == null) {
	            return;
	        }
	        Intent intent = new Intent("android.intent.action.BADGE_COUNT_UPDATE");
	        intent.putExtra("badge_count", count);
	        intent.putExtra("badge_count_package_name", context.getPackageName());
	        intent.putExtra("badge_count_class_name", launcherClassName);
	     
	        context.sendBroadcast(intent);
	    }

	    public static String getLauncherClassName(Context context) {

	        PackageManager pm = context.getPackageManager();

	        Intent intent = new Intent(Intent.ACTION_MAIN);
	        intent.addCategory(Intent.CATEGORY_LAUNCHER);

	        List<ResolveInfo> resolveInfos = pm.queryIntentActivities(intent, 0);
	        for (ResolveInfo resolveInfo : resolveInfos) {
	            String pkgName = resolveInfo.activityInfo.applicationInfo.packageName;
	            if (pkgName.equalsIgnoreCase(context.getPackageName())) {
	                String className = resolveInfo.activityInfo.name;
	                return className;
	            }
	        }
	        return null;
	    }
}
