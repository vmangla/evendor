//
//  LocalDatabase.h
//  EVendor
//
//  Created by MIPC-52 on 26/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>
#import <sqlite3.h>

@interface LocalDatabase : NSObject
{
    sqlite3     *dataBaseConnection;
}

@property (nonatomic, assign)sqlite3     *dataBaseConnection;

+ (LocalDatabase*) sharedDatabase;

+ (void) createDatabaseIfNeeded;
- (void) openDatabaseConnection ;
- (void) closeDatabaseConnection;

-(NSArray*) fetchAllBooks;
- (void) insertBookWithDict:(NSDictionary*) aDict;
- (BOOL) updatePurchaseInfoWithBookId:(NSString*) aId;
- (BOOL) updateShelfInfoWithBookId:(NSString*) aId andShelfId:(NSInteger)shelfId;
- (BOOL) deleteBookWithBookId:(NSString*) aId;
- (NSArray*) fetchAllBookShelf;
- (void) createNewShelfTitle:(NSString*) title andColor:(NSString*) color;
- (NSDictionary*) getLastAddedShelf;
- (NSArray*) fetchAllBooksWithShelf;
- (BOOL) updateShelfWithId:(NSInteger) shelfId andNewTitle:(NSString*) title andNewColor:(NSString*) color;
- (BOOL) deleteShelfWithId:(NSString*) aId;
- (BOOL) isExistBookWithId:(NSString*) aId;
- (BOOL) deleteRecordsFromBookTable;
- (BOOL) deleteRecordsFromShelfTable;

-(NSArray*) allBookMarksWithBookId:(NSInteger) bookId;
- (void) insertBookMarkWithBookId:(NSInteger) bookId andCurrentSpineIndex:(NSInteger) currentSpineIndex andCurrentPageInSpineIndex:(NSInteger) currentPageInSpineIndex andCurrentTextSize:(NSInteger) currentTextSize andcurrentPageText:(NSString*) currentPageText;
- (BOOL) updateBookMarkWithId:(NSInteger) aId andBookId:(NSInteger) bookId andCurrentSpineIndex:(NSInteger) currentSpineIndex andCurrentPageInSpineIndex:(NSInteger) currentPageInSpineIndex andCurrentTextSize:(NSInteger) currentTextSize;
- (BOOL) deleteBookMarkWithId:(NSInteger) aId;


@end
