
package com.evendor.services;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;
import android.os.RemoteException;
import android.text.TextUtils;
import android.util.Log;

import com.evendor.services.IDownloadService;
import com.evendor.utils.MyIntents;

public class DownloadService extends Service {

    private DownloadManager mDownloadManager;

    @Override
    public IBinder onBind(Intent intent) {

        return new DownloadServiceImpl();
    }

    @Override
    public void onCreate() {

        super.onCreate();
        mDownloadManager = new DownloadManager(this);
    }

    @Override
    public void onStart(Intent intent, int startId) {

        super.onStart(intent, startId);

        // if (mDownloadManager == null) {
        // mDownloadManager = new DownloadManager(this);
        // }

        if(intent!=null)
        if (intent.getAction().equals("com.yyxu.download.services.IDownloadService")) {
            int type = intent.getIntExtra(MyIntents.TYPE, -1);
            String url;
            String iconPath;
            String bookPrice;
            String boolTitle;
            String  bookDescription;
            String  bookUrl;
            String  bookId;
            String country_id;

            String  bookPath;
            String  bookAuthor;
            String  bookPublisherName;

            String  bookPublishedDate;
            String  bookSize;
            String  bookCategory;
            String filename;
            String priceText;

            switch (type) {
                case MyIntents.Types.START:
                
                    if (!mDownloadManager.isRunning()) {
                    	Log.e("eeeeeeeeeeeee", "start.isRunning()");
                        mDownloadManager.startManage();
                    } else {
                    	Log.e("eeeeeeeeeeeee", "startreBroadcastAddAllTask");
                        mDownloadManager.reBroadcastAddAllTask();
                    }
                    break;
                case MyIntents.Types.ADD:
                    url = intent.getStringExtra(MyIntents.URL);
                    iconPath = intent.getStringExtra(MyIntents.BOOK_ICON_PATH);
                    bookPrice = intent.getStringExtra(MyIntents.BOOK_PRICE);
                    boolTitle = intent.getStringExtra(MyIntents.BOOK_TITLE);
                    
                	bookDescription=intent.getStringExtra("bookDescription");
					bookUrl=intent.getStringExtra("bookUrl");
					bookId=intent.getStringExtra("bookId");
					bookPath=intent.getStringExtra("bookPath");
					bookAuthor=intent.getStringExtra("bookAuthor");
					bookPublisherName=intent.getStringExtra("bookPublisherName");
					bookPublishedDate=intent.getStringExtra("bookPublishedDate");
					bookSize=intent.getStringExtra("bookSize");
					bookCategory=intent.getStringExtra("bookCategory");
					filename=intent.getStringExtra("filename");
					priceText=intent.getStringExtra("priceText");
					country_id=intent.getStringExtra("country_id");
				
					
                    if (!TextUtils.isEmpty(url) && !mDownloadManager.hasTask(url)) {
                    	//if(!bookPrice.isEmpty() && !boolTitle.isEmpty() && !bookUrl.isEmpty() ){
                        mDownloadManager.addTask(url , iconPath , boolTitle , 
                        		bookPrice,bookDescription,bookUrl,bookId,bookPath
                        		,bookAuthor,bookPublisherName,bookPublishedDate,bookSize,bookCategory,filename,priceText,country_id);
                    	//}
                    }
                    break;
                case MyIntents.Types.CONTINUE:
                    url = intent.getStringExtra(MyIntents.URL);
                    if (!TextUtils.isEmpty(url)) {
                        mDownloadManager.continueTask(url);
                    }
                    break;
                case MyIntents.Types.DELETE:
                    url = intent.getStringExtra(MyIntents.URL);
                    if (!TextUtils.isEmpty(url)) {
                        mDownloadManager.deleteTask(url);
                    }
                    break;
                case MyIntents.Types.PAUSE:
                	
                	 url = intent.getStringExtra(MyIntents.URL);
                     if (!TextUtils.isEmpty(url)) {
                         mDownloadManager.deleteTask(url);
                     }
//                    url = intent.getStringExtra(MyIntents.URL);
//                    if (!TextUtils.isEmpty(url)) {
//                        mDownloadManager.pauseTask(url);
//                    }
                    break;
                case MyIntents.Types.STOP:
                    mDownloadManager.close();
                    // mDownloadManager = null;
                    break;

                default:
                    break;
            }
        }

    }

    private class DownloadServiceImpl extends IDownloadService.Stub {

        @Override
        public void startManage() throws RemoteException {

            mDownloadManager.startManage();
        }

      

        @Override
        public void pauseTask(String url) throws RemoteException {

        }

        @Override
        public void deleteTask(String url) throws RemoteException {

        }

        @Override
        public void continueTask(String url) throws RemoteException {

        }

		@Override
		public void addTask(String url, String iconPath, String bookTitle,
				String bookPrice, String bookDescription, String bookUrl,
				String bookId, String bookPath, String bookAuthor,
				String bookPublisherName, String bookPublishedDate,
				String bookSize, String bookCategory,String filename, String priceText, String country_id) throws RemoteException {
			
			  mDownloadManager.addTask(url , iconPath , bookTitle , bookPrice ,bookDescription,bookUrl,bookId,bookPath,
					  bookAuthor,bookPublisherName,bookPublishedDate,bookSize,bookCategory,filename,priceText,country_id);
		}

    }

}
