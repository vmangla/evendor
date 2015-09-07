package com.evendor.Android;

import android.app.Activity;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.support.v4.content.WakefulBroadcastReceiver;

public class GcmBroadcastReceiver extends WakefulBroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
        // Explicitly specify that GcmIntentService will handle the intent.
        ComponentName comp = new ComponentName(context.getPackageName(),
                GcmIntentService.class.getName());
        // Start the service, keeping the device awake while it is launching.
        startWakefulService(context, (intent.setComponent(comp)));
        setResultCode(Activity.RESULT_OK);
        
        changeBadge(context);
    }
    
    
    private void changeBadge(Context ctx){
    	
    	Intent intent = new Intent();

    	intent.setAction("com.sonyericsson.home.action.UPDATE_BADGE");
    	intent.putExtra("com.sonyericsson.home.intent.extra.badge.ACTIVITY_NAME", "com.yourdomain.yourapp.MainActivity");
    	intent.putExtra("com.sonyericsson.home.intent.extra.badge.SHOW_MESSAGE", true);
    	intent.putExtra("com.sonyericsson.home.intent.extra.badge.MESSAGE", "9");
    	intent.putExtra("com.sonyericsson.home.intent.extra.badge.PACKAGE_NAME", "com.yourdomain.yourapp");

    	ctx.sendBroadcast(intent);
    }
}