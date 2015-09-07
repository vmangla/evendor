//
//  LocalDatabase.m
//  EVendor
//
//  Created by MIPC-52 on 26/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "LocalDatabase.h"

#define kLocalDB            @"EVendorDB.sqlite"
#define kBookTable          @"BookTable"
#define kBookShelfTable     @"BookShelf"

@implementation LocalDatabase
@synthesize dataBaseConnection;

#pragma mark - Shared Database

+ (LocalDatabase*) sharedDatabase
{
	static LocalDatabase *_sharedDatabase;
    
	if(!_sharedDatabase)
    {
		static dispatch_once_t oncePredicate;
		dispatch_once(&oncePredicate, ^{
			_sharedDatabase = [[super allocWithZone:nil] init];
            
        });
    }
    return _sharedDatabase;
}

+ (id)allocWithZone:(NSZone *)zone
{
	return [self sharedDatabase];
}

- (id)copyWithZone:(NSZone *)zone
{
	return self;
}

- (void) dealloc
{
    [super dealloc];
}

-(id)init
{
    self = [super init];
    if (self)
    {
    }
    return self;
}



#pragma mark -  DB
+ (void) createDatabaseIfNeeded
{
	// First, test for existence.
	BOOL success;
	NSFileManager *fileManager = [NSFileManager defaultManager];
	NSError *error;
	NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
	NSString *documentsDirectory = [paths objectAtIndex:0];
	NSString *writableDBPath = [documentsDirectory stringByAppendingPathComponent:kLocalDB];
	success = [fileManager fileExistsAtPath:writableDBPath];
	if (success) return;
	NSString *defaultDBPath = [[[NSBundle mainBundle] resourcePath] stringByAppendingPathComponent:kLocalDB];
	success = [fileManager copyItemAtPath:defaultDBPath toPath:writableDBPath error:&error];
	if (!success)
		NSAssert1(0, @"Failed to create writable database file with message '%@'.", [error localizedDescription]);
}

- (void) openDatabaseConnection
{
	NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
	NSString *documentsDirectory = [paths objectAtIndex:0];
	NSString *path = [documentsDirectory stringByAppendingPathComponent:kLocalDB];
	// Open the database. The database was prepared outside the application.
	if (sqlite3_open([path UTF8String], &dataBaseConnection) == SQLITE_OK)
	{
		//NSLog(@"Database Successfully Opened :)");
	}
	else
	{
		//NSLog(@"Error in opening Database :(");
	}
}

- (void) closeDatabaseConnection
{
	sqlite3_close(dataBaseConnection);
	//NSLog(@"Database Successfully Closed :)");
}


#pragma mark- Fetch All Books
-(NSArray*) fetchAllBooks
{
    [self openDatabaseConnection];
    NSMutableArray  *dataArr = [[NSMutableArray alloc] init];
	NSString *qStr = @"SELECT * FROM BookTable";
	const char *sql = [qStr cStringUsingEncoding:NSUTF8StringEncoding];
	sqlite3_stmt *statement = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        while (sqlite3_step(statement) == SQLITE_ROW)
        {
            NSString *Id = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 0)];
            NSString *bookId = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,1)];
            NSString *title = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,2)];
            NSString *publisher = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,3)];
            NSString *price = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,4)];
            NSString *author_name = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,5)];
            NSString *description = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,6)];
            NSString *ProductThumbnail = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,7)];
            NSString *Producturl = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,8)];
            NSString *shelfId = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 9)];
            NSString *isPurchase = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,10)];
            NSString *catId = [NSString stringWithFormat:@"%s",(char*)sqlite3_column_text(statement,11)];
            NSString *fileSize = [NSString stringWithFormat:@"%s",(char*)sqlite3_column_text(statement,12)];

            NSString *publishDate = [NSString stringWithFormat:@"%s",(char*)sqlite3_column_text(statement,13)];
           

            NSLog(@"Catid == %@\n",catId);
            NSDictionary    *aDic = [NSDictionary dictionaryWithObjectsAndKeys:Id,kId,bookId,kBookId,title,kTitle,publisher,kPublisher,price,kPriceText,author_name,kAuthorName,description,kDescription,ProductThumbnail,kProductThumbnail,Producturl,kProductURL,shelfId,kShelfId,isPurchase,kIsPurchase,catId,kCategory,fileSize,kFileSize,publishDate,kPublishDate, nil];
            [dataArr addObject:aDic];
        }
    }
	sqlite3_finalize(statement);
	[self closeDatabaseConnection];
    return [dataArr autorelease];
}

#pragma mark - Insert A Book
- (void) insertBookWithDict:(NSDictionary*) aDict
{
    NSString *bookId = [NSString stringWithFormat:@"%@", [aDict objectForKey:kId]];
    NSString *title = [NSString stringWithFormat:@"%@", [aDict objectForKey:kTitle]];
    NSString *publisher = [NSString stringWithFormat:@"%@", [aDict objectForKey:kPublisher]];
    NSString *price = [NSString stringWithFormat:@"%@", [aDict objectForKey:kPriceText]];
    NSString *author_name = [NSString stringWithFormat:@"%@", [aDict objectForKey:kAuthorName]];
    NSString *description = [NSString stringWithFormat:@"%@", [aDict objectForKey:kDescription]];
    NSString *ProductThumbnail = [NSString stringWithFormat:@"%@", [aDict objectForKey:kProductThumbnail]];
    NSString *Producturl = [NSString stringWithFormat:@"%@", [aDict objectForKey:kProductURL]];
    NSString *shelfId = [NSString stringWithFormat:@"%@", [aDict objectForKey:kShelfId]];
    NSString *catId = [NSString stringWithFormat:@"%@", [aDict objectForKey:kCategory]];
    NSString *fileSize = [NSString stringWithFormat:@"%@", [aDict objectForKey:kFileSize]];
    NSString *publish_date = [NSString stringWithFormat:@"%@", [aDict objectForKey:kPublishDate]];//sud
    
    
    publish_date = [publish_date substringToIndex:[publish_date rangeOfString:@" "].location];
    //NSLog(@"date is %@",publish_date);
    if([@"(null)" isEqualToString:shelfId])
        shelfId = @"-1";
    NSString *isPurchase = [NSString stringWithFormat:@"%@", [aDict objectForKey:kIsPurchase]];
    if([@"(null)" isEqualToString:isPurchase])
        isPurchase = @"NO";
    
    [self openDatabaseConnection];
    const char *sql = "INSERT into BookTable (bookId,title,publisher,price,author_name,description,ProductThumbnail,Producturl,shelfId,isPurchase,catId,file_Size,publish_date) Values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
   // NSLog(@"Sql statement == %s",sql);
    sqlite3_stmt *addStmt = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &addStmt, NULL)!= SQLITE_OK)
    {
       // NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
        NSAssert1(0,@"error binding 1: %s",sqlite3_errmsg(dataBaseConnection));
        sqlite3_finalize(addStmt);
    }
    else
    {
	    sqlite3_bind_text(addStmt,   1, [bookId UTF8String],-1,SQLITE_TRANSIENT);
		sqlite3_bind_text(addStmt,   2, [title  UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   3, [publisher  UTF8String],-1,SQLITE_TRANSIENT);
	    sqlite3_bind_text(addStmt,   4, [price UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   5, [author_name UTF8String],-1,SQLITE_TRANSIENT);
		sqlite3_bind_text(addStmt,   6, [description  UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   7, [ProductThumbnail  UTF8String],-1,SQLITE_TRANSIENT);
	    sqlite3_bind_text(addStmt,   8, [Producturl UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   9, [shelfId UTF8String],-1,SQLITE_TRANSIENT);
		sqlite3_bind_text(addStmt,   10,[isPurchase  UTF8String],-1,SQLITE_TRANSIENT);
		sqlite3_bind_text(addStmt,   11,[catId  UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   12,[fileSize  UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   13,[publish_date  UTF8String],-1,SQLITE_TRANSIENT);

		if(SQLITE_DONE != sqlite3_step(addStmt)){
			NSAssert1(0, @"Error while saving. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(addStmt);
        }
    }
	[self closeDatabaseConnection];
}

#pragma mark - Update Purchase Info Of A Book
- (BOOL) updatePurchaseInfoWithBookId:(NSString*) aId
{
    BOOL isUpdate = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"Update BookTable set isPurchase = \'YES\' Where bookId = \'%@\'", aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if (sqlite3_prepare_v2(dataBaseConnection, sql, -1, &statement, NULL) == SQLITE_OK)
        isUpdate = YES;
    else
        isUpdate = NO;
    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isUpdate;
}

#pragma mark - Update Purchase Info Of A Book
- (BOOL) updateShelfInfoWithBookId:(NSString*) aId andShelfId:(NSInteger)shelfId
{
    BOOL isUpdate = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"Update BookTable set shelfId=%d Where bookId='%@'", shelfId,aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while saving. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isUpdate = YES;
        }
    }
    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isUpdate;
}


#pragma mark - Delete Book
- (BOOL) deleteBookWithBookId:(NSString*) aId
{
    BOOL isDelete = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"DELETE FROM BookTable WHERE bookId = \'%@\'",aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while deleting. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isDelete = YES;
        }
    }    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isDelete;
}

#pragma mark - Fetch All Shelf
- (NSArray*) fetchAllBookShelf
{
    [self openDatabaseConnection];
    NSMutableArray  *dataArr = [[NSMutableArray alloc] init];
	NSString *qStr = @"SELECT * FROM BookShelf";
	const char *sql = [qStr cStringUsingEncoding:NSUTF8StringEncoding];
	sqlite3_stmt *statement = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        while (sqlite3_step(statement) == SQLITE_ROW)
        {
            NSString *Id = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 0)];
            NSString *title = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,1)];
            NSString *color = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,2)];
            
            NSDictionary    *aDic = [NSDictionary dictionaryWithObjectsAndKeys:Id,kShelfId,title,kShelfTitle,color,kShelfColor, nil];
            [dataArr addObject:aDic];
        }
    }
	sqlite3_finalize(statement);
	[self closeDatabaseConnection];
    return [dataArr autorelease];
}


#pragma mark - Create a new shelf
- (void) createNewShelfTitle:(NSString*) title andColor:(NSString*) color
{
    [self openDatabaseConnection];
    NSString    *qStr = [NSString stringWithFormat:@"INSERT into BookShelf (shelfTitle,shelfColorName) VALUES (\"%@\",\"%@\")",title,color];
    const char *sql = [qStr cStringUsingEncoding:NSUTF8StringEncoding];
    sqlite3_stmt *addStmt = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &addStmt, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
		if(SQLITE_DONE != sqlite3_step(addStmt)){
			NSAssert1(0, @"Error while saving. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(addStmt);
        }
    }
	[self closeDatabaseConnection];
}

- (NSDictionary*) getLastAddedShelf
{
    [self openDatabaseConnection];
    NSMutableDictionary *aDic = [[NSMutableDictionary alloc] init];
    NSString *qStr = @"SELECT * FROM BookShelf WHERE 1 order by id desc limit 0,1";
	const char *sql = [qStr cStringUsingEncoding:NSUTF8StringEncoding];
	sqlite3_stmt *statement = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        while (sqlite3_step(statement) == SQLITE_ROW)
        {
            ReleaseObj(aDic);
            aDic = [[NSMutableDictionary alloc] init];
            NSString *Id = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 0)];
            NSString *title = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,1)];
            NSString *color = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,2)];
            [aDic setObject:Id forKey:kShelfId];
            [aDic setObject:title forKey:kShelfTitle];
            [aDic setObject:color forKey:kShelfColor];
        }
    }
	sqlite3_finalize(statement);
	[self closeDatabaseConnection];
    return [aDic autorelease];
}


#pragma mark- Fetch All Books
- (NSArray*) fetchAllBooksWithShelf
{
    [self openDatabaseConnection];
    NSMutableArray  *dataArr = [[NSMutableArray alloc] init];
	NSString *qStr = @"SELECT BookTable.*,shelfTitle,shelfColorName  from BookTable Inner Join BookShelf on BookShelf.id=BookTable.shelfId group by bookId";
	const char *sql = [qStr cStringUsingEncoding:NSUTF8StringEncoding];
	sqlite3_stmt *statement = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        while (sqlite3_step(statement) == SQLITE_ROW)
        {
            NSString *Id = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 0)];
            NSString *bookId = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,1)];
            NSString *title = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,2)];
            NSString *publisher = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,3)];
            NSString *price = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,4)];
            NSString *author_name = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,5)];
            NSString *description = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,6)];
            NSString *ProductThumbnail = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,7)];
            NSString *Producturl = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,8)];
            NSString *shelfId = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 9)];
            NSString *isPurchase = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,10)];
            NSString *shelfTitle = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,14)];
            NSString *shelfColor = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,15)];
            
            NSString *fileSize = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,12)];
            NSString *publish_date = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement,13)];
            
            
            //publish_date = [publish_date substringToIndex:[publish_date rangeOfString:@" "].location];
                                  
            NSDictionary    *aDic = [NSDictionary dictionaryWithObjectsAndKeys:Id,kId,bookId,kBookId,title,kTitle,publisher,kPublisher,price,kPriceText,author_name,kAuthorName,description,kDescription,ProductThumbnail,kProductThumbnail,Producturl,kProductURL,shelfId,kShelfId,isPurchase,kIsPurchase,shelfTitle,kShelfTitle,shelfColor,kShelfColor,fileSize,kFileSize,publish_date,kPublishDate, nil];
            [dataArr addObject:aDic];
        }
    }
	sqlite3_finalize(statement);
	[self closeDatabaseConnection];
    return [dataArr autorelease];
}

#pragma mark - Update Shelf
- (BOOL) updateShelfWithId:(NSInteger) shelfId andNewTitle:(NSString*) title andNewColor:(NSString*) color
{
    BOOL isUpdate = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"Update BookShelf set shelfTitle='%@',shelfColorName='%@' Where id=%d",title, color, shelfId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while saving. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isUpdate = YES;
        }
    }
    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isUpdate;
}


#pragma mark - Delete Shelf
- (BOOL) deleteShelfWithId:(NSString*) aId
{
    BOOL isDelete = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"DELETE FROM BookShelf WHERE id = \'%@\'",aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while deleting. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isDelete = YES;
        }
    }    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isDelete;
}


#pragma mark - Check Existance of Book in DB
- (BOOL) isExistBookWithId:(NSString*) aId
{
    BOOL isExist = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"SELECT id FROM BookTable WHERE bookId = \'%@\'",aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        while (sqlite3_step(statement) == SQLITE_ROW)
        {
            NSString *Id = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 0)];
            if(Id.length>0 && ![@"(null)" isEqualToString:Id] && ![@"<null>" isEqualToString:Id])
                isExist = YES;
        }
    }
    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isExist;
}


#pragma mark - Delete Table recorde
- (BOOL) deleteRecordsFromBookTable
{
    BOOL isDelete = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"DELETE FROM  BookTable"];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while deleting. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isDelete = YES;
        }
    }    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isDelete;
}

- (BOOL) deleteRecordsFromShelfTable
{
    BOOL isDelete = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"DELETE FROM  BookShelf"];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while deleting. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isDelete = YES;
        }
    }    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isDelete;

}

#pragma mark - BookMarks
#pragma mark -  Get All BookMarks
-(NSArray*) allBookMarksWithBookId:(NSInteger) bookId
{
    [self openDatabaseConnection];
    NSMutableArray  *dataArr = [[NSMutableArray alloc] init];
	NSString *qStr = [NSString stringWithFormat:@"SELECT * FROM BookMark where bookId=%d", bookId];
	const char *sql = [qStr cStringUsingEncoding:NSUTF8StringEncoding];
	sqlite3_stmt *statement = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        while (sqlite3_step(statement) == SQLITE_ROW)
        {
            NSString *Id = [NSString stringWithFormat:@"%d",sqlite3_column_int(statement, 0)];
            NSString *bookId = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement, 1)];
            NSString *currentSpineIndex = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement, 2)];
            NSString *currentPageInSpineIndex = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement, 3)];
            NSString *currentTextSize = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement, 4)];
            NSString *currentPageText = [NSString stringWithFormat:@"%s", (char*)sqlite3_column_text(statement, 5)];
            
            NSDictionary    *aDic = [NSDictionary dictionaryWithObjectsAndKeys:Id,kId,bookId,kBookId,currentSpineIndex,kCurrentSpineIndex,currentPageInSpineIndex,kCurrentPageInSpineIndex,currentTextSize,kCurrentTextSize,currentPageText,kCurrentPageText, nil];
            [dataArr addObject:aDic];
        }
    }
	sqlite3_finalize(statement);
	[self closeDatabaseConnection];
    return [dataArr autorelease];
}

- (void) insertBookMarkWithBookId:(NSInteger) bookId andCurrentSpineIndex:(NSInteger) currentSpineIndex andCurrentPageInSpineIndex:(NSInteger) currentPageInSpineIndex andCurrentTextSize:(NSInteger) currentTextSize andcurrentPageText:(NSString*) currentPageText
{
    [self openDatabaseConnection];
    const char *sql = "INSERT into BookMark (bookId,currentSpineIndex,currentPageInSpineIndex,currentTextSize,currentPageText) VALUES (?,?,?,?,?)";
    sqlite3_stmt *addStmt = nil;
    if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &addStmt, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        sqlite3_bind_text(addStmt,   1, [[NSString stringWithFormat:@"%d", bookId] UTF8String],-1,SQLITE_TRANSIENT);
		sqlite3_bind_text(addStmt,   2, [[NSString stringWithFormat:@"%d", currentSpineIndex]  UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   3, [[NSString stringWithFormat:@"%d", currentPageInSpineIndex]  UTF8String],-1,SQLITE_TRANSIENT);
	    sqlite3_bind_text(addStmt,   4, [[NSString stringWithFormat:@"%d", currentTextSize] UTF8String],-1,SQLITE_TRANSIENT);
        sqlite3_bind_text(addStmt,   5, [[NSString stringWithFormat:@"%@", currentPageText] UTF8String],-1,SQLITE_TRANSIENT);

		if(SQLITE_DONE != sqlite3_step(addStmt)){
			NSAssert1(0, @"Error while saving. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(addStmt);
        }
    }
	[self closeDatabaseConnection];
}


- (BOOL) updateBookMarkWithId:(NSInteger) aId andBookId:(NSInteger) bookId andCurrentSpineIndex:(NSInteger) currentSpineIndex andCurrentPageInSpineIndex:(NSInteger) currentPageInSpineIndex andCurrentTextSize:(NSInteger) currentTextSize
{
    BOOL isUpdate = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"Update BookMark set currentSpineIndex=%@, currentPageInSpineIndex=%@, currentTextSize=%@ Where id=%d", [NSString stringWithFormat:@"%d", currentSpineIndex], [NSString stringWithFormat:@"%d", currentPageInSpineIndex], [NSString stringWithFormat:@"%d", currentTextSize], aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while saving. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isUpdate = YES;
        }
    }
    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isUpdate;
}

- (BOOL) deleteBookMarkWithId:(NSInteger) aId
{
    BOOL isDelete = NO;
	sqlite3_stmt *statement = nil;
	[self openDatabaseConnection];
    NSString *query = [NSString stringWithFormat:@"DELETE FROM BookMark WHERE id=%d",aId];
    const char *sql =[query cStringUsingEncoding:NSUTF8StringEncoding];
    //NSLog(@"%@", query);
	if(sqlite3_prepare_v2(dataBaseConnection,sql, -1, &statement, NULL)!= SQLITE_OK)
    {
        NSAssert1(0,@"error preparing statement",sqlite3_errmsg(dataBaseConnection));
    }
    else
    {
        if(SQLITE_DONE != sqlite3_step(statement)){
			NSAssert1(0, @"Error while deleting. '%s'", sqlite3_errmsg(dataBaseConnection));
        }
		else{
			sqlite3_reset(statement);
            isDelete = YES;
        }
    }    sqlite3_finalize(statement);
    [self closeDatabaseConnection];
    return isDelete;
}





@end
