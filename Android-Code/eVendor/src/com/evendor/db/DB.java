package com.evendor.db;


public interface DB 
{
	
	
	public static final String DATABASE = "evendor.db";
    public static final String DROP_TABLE_QUERY = "DROP TABLE IF EXISTS ";
    public static final String CREATE_TABLE_QUERY = "CREATE TABLE IF NOT EXISTS ";
    public static final String START_TABLE = " (", SEPARATE_COLUMN = ", ", END_TABLE = ");";
    public static final String TYPE_TEXT = " TEXT";
    public static final String TYPE_ID = " INTEGER PRIMARY KEY AUTOINCREMENT";
    public static final String TYPE_INT = " INTEGER";
    public static final String TYPE_FLOAT = " FLOAT";
    public static final String TYPE_BLOB = " BLOB";
    public static final String TYPE_DATE = " DATE";
    public static final String COLUMN_ID = "_id";
    public static final String START_FOREIGN= "FOREIGN KEY (gradeId) REFERENCES score(_id) ON DELETE CASCADE";
	

	//database tables
	public static final String TABLE_DOWNLOADED_BOOK = "downloaded_book";
	public static final String TABLE_FEATURED_BOOK = "featured_book";
	public static final String TABLE_BOOKSHELF = "bookshelf";
	public static final String TABLE_BOOK_IN_SHELF = "book_in_shelf";
	

	     //downloaded_book
	
	     public static final String COLUMN_BOOK_NAME = "book_name";
		 public static final String COLUMN_BOOK_ID = "book_id";
		 public static final String COLUMN_BOOK_DESCRIPTION = "book_description";
		 public static final String COLUMN_BOOK_URL = "book_url";
		 public static final String COLUMN_BOOK_ICON = "book_icon";
		 public static final String COLUMN_BOOK_PATH = "book_path";
		 public static final String COLUMN_BOOK_ADDED = "book_added";
		 public static final String COLUMN_BOOK_AUTHOR = "book_author";
		 public static final String COLUMN_BOOK_PUBLISHER = "book_publisher";
		 public static final String COLUMN_BOOK_SIZE = "book_size";
		 public static final String COLUMN_BOOK_CATEGORY = "book_category";
		 public static final String COLUMN_BOOK_PUBLISHED_DATE = "book_publisher_date";

		 //bookshelf
			
	     public static final String COLUMN_SHELF_NAME = "shelf_name";
	     public static final String COLUMN_SHELF_COLOR = "shelf_color";

	     //book_in_shelf
	     
	    
		 public static final String COLUMN_BOOK_SHELF_NAME = "book_shelf_name";
		
		 
		 
		 
		 
		 public static final String COLUMN_FK = "fk";
		 
		 
	
		 
}