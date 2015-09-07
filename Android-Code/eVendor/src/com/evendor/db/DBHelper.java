package com.evendor.db;

import android.content.ContentValues;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteStatement;
import android.util.Log;

public class DBHelper implements DB {

	protected final static String TAG = "DBHelper";
	private static SQLiteDatabase mDb;
   
    public DBHelper(SQLiteDatabase db) {
    	mDb = db;
	}

	public DBHelper(SQLiteDatabase db, boolean populate) {
		this(db);
		
        db.execSQL("PRAGMA foreign_keys = ON;");

		if (populate)
			populate(db);

	}
	
	
	private void populate(SQLiteDatabase db) {
		StringBuilder query = new StringBuilder();
		db.execSQL(DROP_TABLE_QUERY + TABLE_DOWNLOADED_BOOK);
		
		db.execSQL("DROP TABLE IF EXISTS '" + TABLE_DOWNLOADED_BOOK + "'");
		db.execSQL("DROP TABLE IF EXISTS '" + TABLE_FEATURED_BOOK + "'");
		db.execSQL("DROP TABLE IF EXISTS '" + TABLE_BOOKSHELF + "'");
		
		//book_shelf--------------------------
				createTable(query, TABLE_BOOKSHELF);

				query.append(COLUMN_SHELF_NAME);
				query.append(TYPE_TEXT);
				query.append(SEPARATE_COLUMN);
				query.append(COLUMN_SHELF_COLOR);
				query.append(TYPE_TEXT);
				
				query.append(END_TABLE);
				db.execSQL(query.toString());	

		
		//downloaded_book--------------------------
		        createTable(query, TABLE_DOWNLOADED_BOOK);

		        query.append(COLUMN_BOOK_NAME);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_DESCRIPTION);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_URL);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_ID);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_ICON);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_ADDED);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_PATH);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_AUTHOR);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_PUBLISHER);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_PUBLISHED_DATE);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_SIZE);
		        
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_BOOK_CATEGORY);
		        query.append(TYPE_TEXT);
		        query.append(SEPARATE_COLUMN);
		        query.append(COLUMN_FK);
		        query.append(TYPE_INT);
		        query.append(SEPARATE_COLUMN);
				
	    query.append("FOREIGN KEY (fk) REFERENCES bookshelf(_id) ON DELETE SET NULL");
				
        query.append(END_TABLE);
		db.execSQL(query.toString());
		
		
		
		//downloaded_book--------------------------
        createTable(query, TABLE_FEATURED_BOOK);

        query.append(COLUMN_BOOK_NAME);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_DESCRIPTION);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_URL);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_ID);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_ICON);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_ADDED);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_PATH);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_AUTHOR);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_PUBLISHER);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_PUBLISHED_DATE);
        query.append(TYPE_TEXT);
       /* query.append(SEPARATE_COLUMN);
        query.append(COLUMN_FK);
        query.append(TYPE_INT);
        query.append(SEPARATE_COLUMN);*/
		
//query.append("FOREIGN KEY (fk) REFERENCES bookshelf(_id) ON DELETE SET NULL");
		
query.append(END_TABLE);
db.execSQL(query.toString());
		
		

		/*//book_in_shelf--------------------------
        createTable(query, TABLE_BOOK_IN_SHELF);

        query.append(COLUMN_BOOK_NAME);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_DESCRIPTION);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_URL);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_ID);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_ICON);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_ADDED);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_PATH);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_BOOK_SHELF_NAME);
        query.append(TYPE_TEXT);
        query.append(SEPARATE_COLUMN);
        query.append(COLUMN_FK);
        query.append(TYPE_INT);
        query.append(SEPARATE_COLUMN);
		
     query.append("FOREIGN KEY (fk) REFERENCES bookshelf(_id) ON DELETE CASCADE");
		
     query.append(END_TABLE);
     db.execSQL(query.toString());*/
		
	}
      // method to create tables with id added--------------------------
	
	private static void createTable(StringBuilder query, String table) {
		query.setLength(0);
		query.append(CREATE_TABLE_QUERY);
		query.append(table);
		query.append(START_TABLE);
		query.append(COLUMN_ID);
		query.append(TYPE_ID);
		query.append(SEPARATE_COLUMN);

		Log.i("-------------table value---------","-------------"+query);
	}
	//--------------------------------------------------------------------------------------------------------

	
	public  Cursor fetchFromTable(String tableName) 
	{
         Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				tableName , null);

		return mCursor;
	}
	
	public void updateCol(String tableName,ContentValues cv,String _id,int id){
		mDb.update(tableName, cv, _id+"='"+id+"'", null);
		}
	
	
	public Cursor fetchDownloadedBookForFKvalue(String tableName , int value) 
	{
	    Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				 tableName +
		  " WHERE fk ="+ value, null);
		 return mCursor;
	}
	
	public Cursor fetchDownloadedBookWhenFKBlank(String tableName) 
	{
	    Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				 tableName +
		  " WHERE fk IS NULL", null);
		 return mCursor;
	}
	
	public void insertToTable(String tableName,ContentValues initialValues)
	{
		mDb.insert(tableName, null,initialValues);
		 
	}
	
	
	public boolean deleteItemFromList(String tableName,String columnName,int rowId) 
	{
		
		return mDb.delete(tableName, columnName +"=" + rowId, null) > 0;
	}
	
	public int ItemExistinList(String tableName,String columnName,int rowId) 
	{
		int value=1;		
		SQLiteStatement a= mDb.compileStatement("SELECT Count(*) from "+tableName+" where "+columnName+" = "+rowId+";");
		String valueString=a.simpleQueryForString();
		try{
			value= Integer.parseInt(valueString);
		}catch(NumberFormatException E){
			E.printStackTrace();
			value=0;
		}
		return value;
	}
	
	public Cursor checkIfIdisPresent(String tableName , String value) 
	{
	    Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				 tableName +
		  " WHERE book_id ="+ value, null);
		 return mCursor;
	}
	
	
	public  Cursor findingDataForCol(String tableName) 
	{
         Cursor   mCursor =mDb.rawQuery("SELECT _id FROM " +
				tableName , null);

		return mCursor;
	}
	
	public Cursor getDataWithColValue(String tableName , String value) 
	{
	    Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				 tableName +
		  " WHERE book_url ="+ value, null);
		 return mCursor;
	}
	
	public Cursor getNeverBillableNumber(String tableName , String value) 
	{
	    Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				 tableName +
		  " WHERE phone_number ="+ value, null);
		 return mCursor;
	}
	
	public Cursor getNeverBillableInMainTable(String tableName , String value) 
	{
	    Cursor   mCursor =mDb.rawQuery("SELECT * FROM " +
				 tableName +
		  " WHERE phone_number ="+ value, null);
		 return mCursor;
	}
	
	
}





