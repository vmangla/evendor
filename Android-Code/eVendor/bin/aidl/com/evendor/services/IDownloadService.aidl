package com.evendor.services;

interface IDownloadService {
	
	void startManage();
	
	void addTask(String url , String iconPath , String bookTitle , String bookPrice,String bookDescription,String bookUrl,String bookId,String bookPath,String bookAuthor,String bookPublisherName,String bookPublishedDate,String bookSize,String bookCategory,String filename, String priceText, String country_id);
	
	void pauseTask(String url);
	
	void deleteTask(String url);
	
	void continueTask(String url);
}
