package com.evendor.services;

import java.io.File;
import java.net.MalformedURLException;
import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;
import java.util.Queue;

import android.content.Context;
import android.content.Intent;
import android.util.Log;
import android.widget.Toast;

import com.evendor.Android.ApplicationManager;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.utils.ConfigUtils;
import com.evendor.utils.MyIntents;
import com.evendor.utils.NetworkUtils;
import com.evendor.utils.StorageUtils;
import com.evendor.widgets.DownloadListAdapter;

public class DownloadManager extends Thread {

	private static final int MAX_TASK_COUNT = 100;
	private static final int MAX_DOWNLOAD_THREAD_COUNT = 3;

	private Context mContext;

	private TaskQueue mTaskQueue;
	private List<DownloadTask> mDownloadingTasks;
	private List<DownloadTask> mPausingTasks;

	private Boolean isRunning = false;

	public DownloadManager(Context context) {

		mContext = context;
		mTaskQueue = new TaskQueue();
		mDownloadingTasks = new ArrayList<DownloadTask>();
		mPausingTasks = new ArrayList<DownloadTask>();
	}

	public void startManage() {

		isRunning = true;
		this.start();
		checkUncompleteTasks();
	}

	public void close() {

		isRunning = false;
		pauseAllTask();
		this.stop();
	}

	public boolean isRunning() {

		return isRunning;
	}

	@Override
	public void run() {

		super.run();
		while (isRunning) {
			DownloadTask task = mTaskQueue.poll();
			Log.e("oooooo", "run");
			mDownloadingTasks.add(task);
			task.execute();
		}
	}

	public void addTask(String url , String iconPath , String boolTitle, 
			String bookPrice,String bookDescription,String bookUrl,String bookId,
			String bookPath,String bookAuthor,String bookPublisherName,String bookPublishedDate,
			String bookSize,String bookCategory,String file_name,String priceText,String country_id) {

		if (!StorageUtils.isSDCardPresent()) {
			// Toast.makeText(mContext, "æœªå�‘çŽ°SDå�¡",
			// Toast.LENGTH_LONG).show();
			return;
		}

		if (!StorageUtils.isSdCardWrittenable()) {
			// Toast.makeText(mContext, "SDå�¡ä¸�èƒ½è¯»å†™",
			// Toast.LENGTH_LONG).show();
			return;
		}

		if (getTotalTaskCount() >= MAX_TASK_COUNT) {
			// Toast.makeText(mContext, "ä»»åŠ¡åˆ—è¡¨å·²æ»¡",
			// Toast.LENGTH_LONG).show();
			return;
		}

		try {
			addTask(newDownloadTask(url , iconPath , boolTitle , bookPrice, bookDescription, bookUrl, bookId, bookPath, bookAuthor, bookPublisherName, bookPublishedDate, bookSize, bookCategory,file_name,priceText,country_id));
		} catch (MalformedURLException e) {
			e.printStackTrace();
		}

	}

	private void addTask(DownloadTask task) {

		broadcastAddTask(task.getUrl() , isRunning, task.getIconPath() , 
				task.getBookTitle() ,task.getBookPrice(), task.getBookDescription(), 
				task.getBookUrl(),task.getBookId(), task.getBookPath(),task.getBookAuthor(), 
				task.getBookPublisherName(),task.getBookPublishedDate(), task.getBookSize(), 
				task.getBookCategory(),task.getFilename(),task.getPriceText(),task.getCountry_id() );

		mTaskQueue.offer(task);

		if (!this.isAlive()) {
			this.startManage();
		}
	}

	private void broadcastAddTask(String url, boolean isInterrupt , String iconPath , String bookTitle , String bookPrice,
			String bookDescription,String bookUrl,String bookId,String bookPath,
			String bookAuthor,String bookPublisherName,String bookPublishedDate,
			String bookSize,String bookCategory,String file_name, String priceText, String country_id) {

        
		
		Intent nofityIntent = new Intent(
				"com.yyxu.download.activities.DownloadListActivity");
		nofityIntent.putExtra(MyIntents.TYPE, MyIntents.Types.ADD);
		nofityIntent.putExtra(MyIntents.URL, url);
		nofityIntent.putExtra(MyIntents.IS_PAUSED, isInterrupt);
		nofityIntent.putExtra(MyIntents.BOOK_TITLE, bookTitle);
		nofityIntent.putExtra(MyIntents.BOOK_PRICE, bookPrice);
		nofityIntent.putExtra(MyIntents.BOOK_ICON_PATH, iconPath);
		nofityIntent.putExtra("bookDescription", bookDescription);
		nofityIntent.putExtra("bookUrl", bookUrl);
		nofityIntent.putExtra("bookId", bookId);
		nofityIntent.putExtra("bookPath", bookPath);
		nofityIntent.putExtra("bookAuthor", bookAuthor);
		nofityIntent.putExtra("bookPublisherName", bookPublisherName);
		nofityIntent.putExtra("bookPublishedDate", bookPublishedDate);
		nofityIntent.putExtra("bookSize", bookSize);
		nofityIntent.putExtra("bookCategory", bookCategory);
		nofityIntent.putExtra("filename", file_name);
		nofityIntent.putExtra("priceText", priceText);
		nofityIntent.putExtra("country_id", country_id);
		
		mContext.sendBroadcast(nofityIntent);
	}

	public void reBroadcastAddAllTask() {

		DownloadTask task;
		for (int i = 0; i < mDownloadingTasks.size(); i++) {
			Log.e("---", "mDownloadingTasks");
			task = mDownloadingTasks.get(i);
			broadcastAddTask(task.getUrl(), task.isInterrupt() , task.getIconPath() , task.getBookTitle() , task.getBookPrice(),task.getBookDescription(), task.getBookUrl(),task.getBookId(), task.getBookPath(),task.getBookAuthor(), task.getBookPublisherName(),task.getBookPublishedDate(), task.getBookSize(), task.getBookCategory() ,task.getFilename(), task.getPriceText(),task.getCountry_id());
		}
		for (int i = 0; i < mTaskQueue.size(); i++) {
			Log.e("---", "mTaskQueue");
			task = mTaskQueue.get(i);
			broadcastAddTask(task.getUrl() , isRunning, task.getIconPath() , task.getBookTitle() , task.getBookPrice(),task.getBookDescription(), task.getBookUrl(),task.getBookId(), task.getBookPath(),task.getBookAuthor(), task.getBookPublisherName(),task.getBookPublishedDate(), task.getBookSize(), task.getBookCategory(),task.getFilename(), task.getPriceText() ,task.getCountry_id());
		}
//		for (int i = 0; i < mPausingTasks.size(); i++) {
//			task = mPausingTasks.get(i);
//			broadcastAddTask(task.getUrl() , isRunning, task.getIconPath() , task.getBookTitle() , task.getBookPrice(), task.getBookDescription(), task.getBookUrl(),task.getBookId(), task.getBookPath(),task.getBookAuthor(), task.getBookPublisherName(),task.getBookPublishedDate(), task.getBookSize(), task.getBookCategory(),task.getFilename() );
//		}
	}

	public boolean hasTask(String url) {

		DownloadTask task;
		for (int i = 0; i < mDownloadingTasks.size(); i++) {
			task = mDownloadingTasks.get(i);
			if (task.getUrl().equals(url)) {
				return true;
			}
		}
		for (int i = 0; i < mTaskQueue.size(); i++) {
			task = mTaskQueue.get(i);
		}
		return false;
	}

	public DownloadTask getTask(int position) {

		if (position >= mDownloadingTasks.size()) {
			return mTaskQueue.get(position - mDownloadingTasks.size());
		} else {
			return mDownloadingTasks.get(position);
		}
	}

	public int getQueueTaskCount() {

		return mTaskQueue.size();
	}

	public int getDownloadingTaskCount() {

		return mDownloadingTasks.size();
	}

	public int getPausingTaskCount() {

		return mPausingTasks.size();
	}

	public int getTotalTaskCount() {

		return getQueueTaskCount() + getDownloadingTaskCount()
				+ getPausingTaskCount();
	}

	public void checkUncompleteTasks() {

		List<String> urlList = ConfigUtils.getURLArray(mContext);
		List<String> iconPathList = ConfigUtils.getIconPathArray(mContext);
		List<String> bookTileList = ConfigUtils.getBookTitleArray(mContext);
		List<String> bookPriceList = ConfigUtils.getBookPriceArray(mContext);
		
		
		List<String> bookDescriptionList = ConfigUtils.getbookDescriptionArray(mContext);
		List<String> bookUrlList = ConfigUtils.getbookUrlArray(mContext);
		List<String> bookIdList = ConfigUtils.getbookIdArray(mContext);
		List<String> bookPathList = ConfigUtils.getbookPathArray(mContext);
		List<String> bookAuthorList = ConfigUtils.getbookAuthorArray(mContext);
		List<String> bookPublisherNameList = ConfigUtils.getbookPublisherNameArray(mContext);
		List<String> bookPublishedDateList = ConfigUtils.getbookPublishedDateArray(mContext);
		List<String> bookSizeList = ConfigUtils.getbookSizeArray(mContext);
		List<String> bookCategoryList = ConfigUtils.getbookCategoryArray(mContext);
		List<String> file_nameList = ConfigUtils.getfilenameArray(mContext);
		
		if (urlList.size() >= 0) {
			
			for (int i = 0; i < urlList.size(); i++) {
				ConfigUtils.clearbookUrl(mContext, i);
				ConfigUtils.clearIconPath(mContext, i);
				ConfigUtils.clearBookTitle(mContext, i);
				ConfigUtils.clearBookPrice(mContext, i);
				ConfigUtils.clearbookDescription(mContext, i);
				ConfigUtils.clearURL(mContext, i);
				ConfigUtils.clearbookPath(mContext, i);
				ConfigUtils.clearbookAuthor(mContext, i);
				ConfigUtils.clearbookPublishedDate(mContext, i);
				ConfigUtils.clearbookSize(mContext, i);
				ConfigUtils.clearfilename(mContext, i);
				Log.e("config-task", "urllist--"+i);
				Log.e("DM------------------", "******** uncomplete");
				/*addTask(urlList.get(i) , iconPathList.get(i) , bookTileList.get(i) ,
						bookPriceList.get(i),bookDescriptionList.get(i),bookUrlList.get(i),bookIdList.get(i),
						bookPathList.get(i),bookAuthorList.get(i),bookPublisherNameList.get(i),
						bookPublishedDateList.get(i),bookSizeList.get(i),bookCategoryList.get(i),file_nameList.get(i));*/
			}
		}
/*if (urlList.size() >= 0) {
			
			for (int i = 0; i < urlList.size(); i++) {
			
				Log.e("secnd check-task", "check--"+i);
				
				addTask(urlList.get(i) , iconPathList.get(i) , bookTileList.get(i) ,
						bookPriceList.get(i),bookDescriptionList.get(i),bookUrlList.get(i),bookIdList.get(i),
						bookPathList.get(i),bookAuthorList.get(i),bookPublisherNameList.get(i),
						bookPublishedDateList.get(i),bookSizeList.get(i),bookCategoryList.get(i),file_nameList.get(i));
			}
		}*/
	}

	public synchronized void pauseTask(String url) {

		DownloadTask task;
		for (int i = 0; i < mDownloadingTasks.size(); i++) {
			task = mDownloadingTasks.get(i);
			if (task != null && task.getUrl().equals(url)) {
				pauseTask(task);
			}
		}
	}

	public synchronized void pauseAllTask() {

		DownloadTask task;

		for (int i = 0; i < mTaskQueue.size(); i++) {
			task = mTaskQueue.get(i);
			mTaskQueue.remove(task);
			mPausingTasks.add(task);
		}

		for (int i = 0; i < mDownloadingTasks.size(); i++) {
			task = mDownloadingTasks.get(i);
			if (task != null) {
				pauseTask(task);
			}
		}
	}

	public synchronized void deleteTask(String url) {

		DownloadTask task;
		for (int i = 0; i < mDownloadingTasks.size(); i++) {
			task = mDownloadingTasks.get(i);
			if (task != null && task.getUrl().equals(url)) {
				File file = new File(StorageUtils.FILE_ROOT
						+ NetworkUtils.getFileNameFromUrl(task.getUrl()));
				if (file.exists())
					file.delete();

				task.onCancelled();
//				completeTask(task);
				if (mDownloadingTasks.contains(task)) {
					ConfigUtils.clearURL(mContext, mDownloadingTasks.indexOf(task));
					mDownloadingTasks.remove(task);
				}

				return;
			}
		}
		for (int i = 0; i < mTaskQueue.size(); i++) {
			task = mTaskQueue.get(i);
			if (task != null && task.getUrl().equals(url)) {
				mTaskQueue.remove(task);
			}
		}
		for (int i = 0; i < mPausingTasks.size(); i++) {
			task = mPausingTasks.get(i);
			if (task != null && task.getUrl().equals(url)) {
				mPausingTasks.remove(task);
			}
		}
	}

	public synchronized void continueTask(String url) {

		DownloadTask task;
		for (int i = 0; i < mPausingTasks.size(); i++) {
			task = mPausingTasks.get(i);
			if (task != null && task.getUrl().equals(url)) {
				continueTask(task);
			}

		}
	}

	public synchronized void pauseTask(DownloadTask task) {

		if (task != null) {
			task.onCancelled();

			// move to pausing list
			String url = task.getUrl();
			String iconPath = task.getIconPath();
			String bookTitle = task.getBookTitle();
			String bookPrice = task.getBookPrice();
			try {
				mDownloadingTasks.remove(task);
				task = newDownloadTask(url , iconPath , bookTitle ,bookPrice, 
						task.getBookDescription(), task.getBookUrl(),task.getBookId(), 
						task.getBookPath(),task.getBookAuthor(), task.getBookPublisherName(),
						task.getBookPublishedDate(), task.getBookSize(), task.getBookCategory() ,
						task.getFilename(), task.getPriceText(),task.getCountry_id());
				mPausingTasks.add(task);
			} catch (MalformedURLException e) {
				e.printStackTrace();
			}

		}
	}

	public synchronized void continueTask(DownloadTask task) {

		if (task != null) {
			mPausingTasks.remove(task);
			mTaskQueue.offer(task);
		}
	}

	public synchronized void completeTask(DownloadTask task) {
		Log.e("DM------------------", "******** completeTask");
		if (mDownloadingTasks.contains(task)) {
			
			
			Log.e("DM-----sdsd-------------", "******** completeTask");
			ConfigUtils.clearURL(mContext, mDownloadingTasks.indexOf(task));
			mDownloadingTasks.remove(task);

			// notify list changed
			Intent nofityIntent = new Intent(
					"com.yyxu.download.activities.DownloadListActivity");
			nofityIntent.putExtra(MyIntents.TYPE, MyIntents.Types.COMPLETE);
			
			nofityIntent.putExtra(MyIntents.URL, task.getUrl());
			nofityIntent.putExtra(MyIntents.BOOK_TITLE, task.getBookTitle());
			nofityIntent.putExtra(MyIntents.BOOK_PRICE, task.getBookPrice());
			nofityIntent.putExtra(MyIntents.BOOK_ICON_PATH, task.getIconPath());
			nofityIntent.putExtra("bookDescription", task.getBookDescription());
			nofityIntent.putExtra("bookUrl", task.getBookUrl());
			nofityIntent.putExtra("bookId", task.getBookId());
			nofityIntent.putExtra("bookPath", task.getBookPath());
			nofityIntent.putExtra("bookAuthor", task.getBookAuthor());
			nofityIntent.putExtra("bookPublisherName", task.getBookPublisherName());
			nofityIntent.putExtra("bookPublishedDate", task.getBookPublishedDate());
			nofityIntent.putExtra("bookSize", task.getBookSize());
			nofityIntent.putExtra("bookCategory", task.getBookCategory());
			nofityIntent.putExtra("filename", task.getFilename());
			nofityIntent.putExtra("priceText", task.getPriceText());
			nofityIntent.putExtra("country_id", task.getCountry_id());
			
			AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (mContext);
			appDataSavedMethod.SaveDownloadedBook( task.getBookTitle(),task.getBookDescription(),task.getBookUrl(),  task.getBookId(),  task.getIconPath(),
					 task.getBookPath(), "", task.getBookAuthor(), task.getBookPublisherName(),task.getBookPublishedDate(), task.getBookSize(), task.getBookCategory());
			
			mContext.sendBroadcast(nofityIntent);
		}
	}

	/**
	 * Create a new download task with default config
	 * 
	 * @param url
	 * @return
	 * @throws MalformedURLException
	 */
	private DownloadTask newDownloadTask(String url , String iconPath , 
			String bookTitle , String bookPrice,String bookDescription,String bookUrl,
			String bookId,String bookPath,String bookAuthor,String bookPublisherName,
			String bookPublishedDate,String bookSize,String bookCategory,String file_name,String priceText,String country_id)
			throws MalformedURLException {

		DownloadTaskListener taskListener = new DownloadTaskListener() {

			@Override
			public void updateProcess(DownloadTask task) {

				Intent updateIntent = new Intent(
						"com.yyxu.download.activities.DownloadListActivity");
				updateIntent.putExtra(MyIntents.TYPE, MyIntents.Types.PROCESS);
				/*
				 * updateIntent.putExtra(MyIntents.PROCESS_SPEED,
				 * task.getDownloadSpeed() + "kbps | " + task.getDownloadSize()
				 * + " / " + task.getTotalSize() +
				 * "--"+task.getDownloadPercent() +"%");
				 */

				updateIntent.putExtra(MyIntents.PROCESS_SPEED,
						task.getDownloadPercent() + "%");
				updateIntent.putExtra(MyIntents.PROCESS_PROGRESS,
						task.getDownloadPercent() + "");
				updateIntent.putExtra(MyIntents.URL, task.getUrl());
				mContext.sendBroadcast(updateIntent);
			}

			@Override
			public void preDownload(DownloadTask task) {

				ConfigUtils.storeURL(mContext, mDownloadingTasks.indexOf(task),
						task.getUrl());
			}

			@Override
			public void finishDownload(DownloadTask task) {

				completeTask(task);
			}

			@Override
			public void errorDownload(DownloadTask task, Throwable error) {

				if (error != null) {
					Log.e("errorDownload", ""+error.getCause());
					if(error.toString()==null){
						Toast.makeText(mContext, "File already exist / Network error : Skipping Download.",
								Toast.LENGTH_LONG).show();
					}else
					Toast.makeText(mContext, "Error : " + error.getMessage(),
							Toast.LENGTH_LONG).show();
					Intent error_intent= new Intent("com.yyxu.download.activities.DownloadListActivity");
					error_intent.putExtra(MyIntents.TYPE, MyIntents.Types.COMPLETE);
					error_intent.putExtra("Error", true);
					error_intent.putExtra(MyIntents.URL, task.getUrl());
					error_intent.putExtra("bookId", task.getBookId());
					error_intent.putExtra("filename", task.getFilename());
					mContext.sendBroadcast(error_intent);
					ConfigUtils.clearURL(mContext, mDownloadingTasks.indexOf(task));
					deleteTask(task.getUrl());
					mDownloadingTasks.remove(task);
				/*DownloadListAdapter adapter = new DownloadListAdapter(mContext);
				adapter.removeItem(task.getBookUrl());
				adapter.notifyData();*/
				
				
				
			

				// Intent errorIntent = new
				// Intent("com.yyxu.download.activities.DownloadListActivity");
				// errorIntent.putExtra(MyIntents.TYPE, MyIntents.Types.ERROR);
				// errorIntent.putExtra(MyIntents.ERROR_CODE, error);
				// errorIntent.putExtra(MyIntents.ERROR_INFO,
				// DownloadTask.getErrorInfo(error));
				// errorIntent.putExtra(MyIntents.URL, task.getUrl());
				// mContext.sendBroadcast(errorIntent);
				//
				// if (error != DownloadTask.ERROR_UNKOWN_HOST
				// && error != DownloadTask.ERROR_BLOCK_INTERNET
				// && error != DownloadTask.ERROR_TIME_OUT) {
				// completeTask(task);
				// }
				}
			}
		};
		return new DownloadTask(mContext, url, StorageUtils.FILE_ROOT,
				taskListener , iconPath , bookTitle , bookPrice, 
				bookDescription, bookUrl, bookId, bookPath, bookAuthor, 
				bookPublisherName, bookPublishedDate, bookSize, bookCategory,file_name,priceText,country_id);
	}

	/**
	 * A obstructed task queue
	 * 
	 * @author Yingyi Xu
	 */
	private class TaskQueue {
		private Queue<DownloadTask> taskQueue;

		public TaskQueue() {

			taskQueue = new LinkedList<DownloadTask>();
		}

		public void offer(DownloadTask task) {

			taskQueue.offer(task);
		}

		public DownloadTask poll() {

			DownloadTask task = null;
			while (mDownloadingTasks.size() >= MAX_DOWNLOAD_THREAD_COUNT
					|| (task = taskQueue.poll()) == null) {
				try {
					Thread.sleep(1000); // sleep
				} catch (InterruptedException e) {
					e.printStackTrace();
				}
			}
			return task;
		}

		public DownloadTask get(int position) {

			if (position >= size()) {
				return null;
			}
			return ((LinkedList<DownloadTask>) taskQueue).get(position);
		}

		public int size() {

			return taskQueue.size();
		}

		@SuppressWarnings("unused")
		public boolean remove(int position) {

			return taskQueue.remove(get(position));
		}

		public boolean remove(DownloadTask task) {

			return taskQueue.remove(task);
		}
	}

}
