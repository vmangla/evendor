package com.evendor.db;

import android.content.ContentValues;
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;

public class AppDataSavedMethhods implements DB {

	private DBHelper dbHelper                 =null;
    private SQLiteDatabase                       db;
    Context mContext;
   
    public AppDataSavedMethhods(Context ctx) {
    	
    	this.mContext = ctx;
	}

	public  void SaveDownloadedBook(String bookName,String bookDescription,String bookUrl,
			String bookId,String bookIcon,String bookPath,String bookAdded,String bookAuthor, 
			String bookPublisher , String bookPublishedDate , String bookSize,String cetagory)
	{
		db = mContext.openOrCreateDatabase(DB.DATABASE, mContext.MODE_PRIVATE, null);
		dbHelper = new DBHelper(db, false);
		
		    ContentValues cv = new ContentValues();
		    cv.put(DB.COLUMN_BOOK_NAME,bookName);
		    cv.put(DB.COLUMN_BOOK_DESCRIPTION,bookDescription);
		    cv.put(DB.COLUMN_BOOK_URL,bookUrl);
			cv.put(DB.COLUMN_BOOK_ID,bookId);
			cv.put(DB.COLUMN_BOOK_ICON,bookIcon);
			cv.put(DB.COLUMN_BOOK_PATH,bookPath);
			cv.put(DB.COLUMN_BOOK_ADDED,bookAdded);
			cv.put(DB.COLUMN_BOOK_AUTHOR,bookAuthor);
			cv.put(DB.COLUMN_BOOK_PUBLISHER,bookPublisher);
			cv.put(DB.COLUMN_BOOK_PUBLISHED_DATE,bookPublishedDate);
			cv.put(DB.COLUMN_BOOK_CATEGORY, cetagory);
			cv.put(DB.COLUMN_BOOK_SIZE,bookSize);
			
			//cv.put(DB.COLUMN_FK,fk);
			
			dbHelper.insertToTable("downloaded_book",cv);
			
		
		db.close();
	}
	
	
	public  void updateDownloadedBook(int DownloadBookTablerowIds , int bookSelfId)
	{
		db = mContext.openOrCreateDatabase(DB.DATABASE, mContext.MODE_PRIVATE, null);
		dbHelper = new DBHelper(db, false);
		
		    ContentValues cv = new ContentValues();
		    
			cv.put(DB.COLUMN_FK, bookSelfId);
			cv.put(DB.COLUMN_BOOK_ADDED,"yes");
			
			//cv.put(DB.COLUMN_FK,fk);
			dbHelper.updateCol("downloaded_book",cv,DB.COLUMN_ID,DownloadBookTablerowIds);
		//	dbHelper.insertToTable("downloaded_book",cv);
			
		
		db.close();
	}
	
	
	public  void updateBookSelf(int bookSelfId , String bookSelfRenameText , String bookSelfColorText)
	{
		db = mContext.openOrCreateDatabase(DB.DATABASE, mContext.MODE_PRIVATE, null);
		dbHelper = new DBHelper(db, false);
		
		    ContentValues cv = new ContentValues();
		    
			cv.put(DB.COLUMN_SHELF_NAME, bookSelfRenameText);
			cv.put(DB.COLUMN_SHELF_COLOR, bookSelfColorText);
			dbHelper.updateCol("bookshelf",cv,DB.COLUMN_ID,bookSelfId);
		
		
		db.close();
	}
	
	
	public  void removeBookFromShelse(int DownloadBookTablerowIds)
	{
		db = mContext.openOrCreateDatabase(DB.DATABASE, mContext.MODE_PRIVATE, null);
		dbHelper = new DBHelper(db, false);
		
		Integer nullInteger  =  null;
		
		    ContentValues cv = new ContentValues();
		    
			cv.put(DB.COLUMN_FK, nullInteger);
			cv.put(DB.COLUMN_BOOK_ADDED,"");
			
			//cv.put(DB.COLUMN_FK,fk);
			dbHelper.updateCol("downloaded_book",cv,DB.COLUMN_ID,DownloadBookTablerowIds);
		//	dbHelper.insertToTable("downloaded_book",cv);
			
		
		db.close();
	}
	
	

	public  void SaveBookShelve(String bookSheleveName,String bookShelevColor)
	{
		db = mContext.openOrCreateDatabase("evendor.db", mContext.MODE_PRIVATE, null);
		dbHelper = new DBHelper(db, false);
		
		    ContentValues cv = new ContentValues();
		    cv.put(DB.COLUMN_SHELF_NAME,bookSheleveName);
			cv.put(DB.COLUMN_SHELF_COLOR,bookShelevColor);
			
			//cv.put(DB.COLUMN_FK,fk);
			
			dbHelper.insertToTable("bookshelf",cv);
			
		
		db.close();
	}
	
	

}





