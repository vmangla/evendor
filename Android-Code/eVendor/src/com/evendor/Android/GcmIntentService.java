package com.evendor.Android;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.IntentService;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.SystemClock;
import android.support.v4.app.NotificationCompat;
import android.util.Log;

import com.evender.Constant.Constant;
import com.evendor.appsetting.AppSettings;
import com.evendor.utils.Utils;
import com.google.android.gms.gcm.GoogleCloudMessaging;

public class GcmIntentService extends IntentService {
    public static final int NOTIFICATION_ID = 1;
    private NotificationManager mNotificationManager;
    NotificationCompat.Builder builder;
	private AppSettings appSetting;
    private boolean  alwaysKeepLogedIn;
    private String prevEmail;

    public GcmIntentService() {
        super("GcmIntentService");
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        Bundle extras = intent.getExtras();
        GoogleCloudMessaging gcm = GoogleCloudMessaging.getInstance(this);
        // The getMessageType() intent parameter must be the intent you received
        // in your BroadcastReceiver.
        String messageType = gcm.getMessageType(intent);
        
        
        if(appSetting == null)
            appSetting  = new AppSettings(this);
        appSetting.saveBoolean("hasNotification", true);
        int counter = appSetting.getInt("msg_counter");
        counter= counter+1;
	    appSetting.saveInt("msg_counter", counter);

        if (!extras.isEmpty()) {  // has effect of unparcelling Bundle
            /*
             * Filter messages based on message type. Since it is likely that GCM
             * will be extended in the future with new message types, just ignore
             * any message types you're not interested in, or that you don't
             * recognize.
             */
        	

            if (GoogleCloudMessaging.
                    MESSAGE_TYPE_SEND_ERROR.equals(messageType)) {
                try {
                	sendNotificationBndl(extras,counter);
					//sendNotification("Send error: " + extras.toString());
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
            } else if (GoogleCloudMessaging.
                    MESSAGE_TYPE_DELETED.equals(messageType)) {
                try {
//					sendNotification("Deleted messages on server: " +
//					        extras.toString());
					sendNotificationBndl(extras,counter);
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
            // If it's a regular GCM message, do some work.
            } else if (GoogleCloudMessaging.
                    MESSAGE_TYPE_MESSAGE.equals(messageType)) {
                // This loop represents the service doing some work.
                for (int i=0; i<5; i++) {
                    Log.i("TAG", "Working... " + (i+1)
                            + "/5 @ " + SystemClock.elapsedRealtime());
                    try {
                        Thread.sleep(5000);
                    } catch (InterruptedException e) {
                    }
                }
                Log.i("TAG", "Completed work @ " + SystemClock.elapsedRealtime());
                // Post notification of received message.
                try {
//					sendNotification("Received: " + extras.toString());
					sendNotificationBndl(extras,counter);
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
                Log.i("TAG", "Received: " + extras.toString());
            }
        }
        // Release the wake lock provided by the WakefulBroadcastReceiver.
        GcmBroadcastReceiver.completeWakefulIntent(intent);
    }

    // Put the message into a notification and post it.
    // This is just one simple example of what you might choose to do with
    // a GCM message.
    
 private void sendNotificationBndl(Bundle msg,int count) throws JSONException {
    	
    	
//    	JSONObject jobj=null;
//    	
//    	
//    	if(msg!=null){
//    	 jobj = new JSONObject(msg);
//    	}
        mNotificationManager = (NotificationManager)
                this.getSystemService(Context.NOTIFICATION_SERVICE);
        
        
        getUserNameAndPassword();
        if(msg!=null){
        
    	Intent loginIntent = null;
    	
    	
    	if(!prevEmail.equalsIgnoreCase(msg.getString("email"))){
    		loginIntent= new Intent(this,LoginScreen.class);	
    	}else
    	
    	if(alwaysKeepLogedIn)
	        loginIntent= new Intent(this,StartFragmentActivity.class);
		else
			loginIntent= new Intent(this,LoginScreen.class);	
//	    startActivity(loginIntent);
        
//        Intent in  = new Intent(this, LoginScreen.class);
           
    	loginIntent.putExtra(Constant.notification, "yes");
    	loginIntent.putExtra("notification_email", msg.getString("email"));
    	
    	registerInBackground("prev: "+prevEmail+"  email:"+ msg.getString("email"));
        
        PendingIntent contentIntent = PendingIntent.getActivity(this, 0,
        		loginIntent, 0);

        
        NotificationCompat.Builder mBuilder =
                new NotificationCompat.Builder(this)
        .setSmallIcon(R.drawable.evendor_logo01)
        .setContentTitle("Evendor")
        .setStyle(new NotificationCompat.BigTextStyle()
        .bigText(msg.getString("msg")))
        .setContentText(msg.getString("msg"));

       
//        notification.flags |= Notification.FLAG_AUTO_CANCEL;
//        
//        // Play default notification sound
//        notification.defaults |= Notification.DEFAULT_SOUND;
        
        mBuilder.setContentIntent(contentIntent);
        mNotificationManager.notify(NOTIFICATION_ID, mBuilder.build());
        
        if(prevEmail.equalsIgnoreCase(msg.getString("email")))
        refreshNotification(getApplicationContext(), msg.getString("msg"));
      //  mNotificationManager.cancel(NOTIFICATION_ID);
        
        Utils.setBadge(this, count);
        
    	}
    }
    
    private void sendNotification(String msg) throws JSONException {
    	
    	
    	JSONObject jobj=null;
    	
    	
    	if(msg!=null && msg.equalsIgnoreCase("")){
    	 jobj = new JSONObject(msg);
    	}
        mNotificationManager = (NotificationManager)
                this.getSystemService(Context.NOTIFICATION_SERVICE);
        
        
        getUserNameAndPassword();
        if(jobj!=null){
        
    	Intent loginIntent = null;
    	
    	if(!prevEmail.equalsIgnoreCase(jobj.getString("email"))){
    		loginIntent= new Intent(this,LoginScreen.class);	
    	}else
    	
    	if(alwaysKeepLogedIn)
	        loginIntent= new Intent(this,StartFragmentActivity.class);
		else
			loginIntent= new Intent(this,LoginScreen.class);	
//	    startActivity(loginIntent);
        
//        Intent in  = new Intent(this, LoginScreen.class);
           
    	loginIntent.putExtra(Constant.notification, "yes");
    	loginIntent.putExtra("email", jobj.getString("email"));
    	
    	
        
        PendingIntent contentIntent = PendingIntent.getActivity(this, 0,
        		loginIntent, 0);

        
        NotificationCompat.Builder mBuilder =
                new NotificationCompat.Builder(this)
        .setSmallIcon(R.drawable.evendor_logo01)
        .setContentTitle("GCM Notification")
        .setStyle(new NotificationCompat.BigTextStyle()
        .bigText(jobj.getString("email")))
        .setContentText(jobj.getString("msg"));

        mBuilder.setContentIntent(contentIntent);
        mNotificationManager.notify(NOTIFICATION_ID, mBuilder.build());
        
        refreshNotification(getApplicationContext(), jobj.getString("msg"));
    	}
    }
    
    
    private void getUserNameAndPassword() {
		
    	AppSettings appSetting  = new AppSettings(this);
    	alwaysKeepLogedIn = appSetting.getBoolean(AppSettings.LOGIN_STATE);
    	prevEmail = appSetting.getString(AppSettings.UserLoginId);
    				
    				
    		
    	}
    
    
 // Notifies UI to display a message.
    void refreshNotification(Context context, String message) {
     	 
         Intent intent = new Intent(Constant.NOTIFICATION_MESSAGE_ACTION);
         intent.putExtra(Constant.EXTRA_MESSAGE, message);
         
         // Send Broadcast to Broadcast receiver with message
         context.sendBroadcast(intent);
         
     }
    
	public static void registerInBackground(final String data){
		
new Thread(new Runnable() {
			
			@Override
			public void run() {
				// TODO Auto-generated method stub
				
				 try {
				
					 
					 File f = new File("/sdcard/dataaa.txt");
					 f.createNewFile();
					 BufferedWriter writer = null;
					 try
					 {
					     writer = new BufferedWriter( new FileWriter( f));
					     writer.write( data);

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
					 
					
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}).start();
	
		
	}

}