//
//  BookShelfViewC.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ReaderViewController.h"
#import <PDFTouch/PDFTouch.h>

@class BookShelfDescriptionViewC;
@class CreateBookShelfViewC;
@class AddBookShelfViewC;
@class RateViewC;
@class EPubViewController;

@interface BookShelfViewC : UIViewController <EVendorProtocol, ReaderViewControllerDelegate,YLPDFViewControllerDataSource,YLPDFViewControllerDelegate>
{
    NSMutableArray  *mainArray, *shelfArr;
    BookShelfDescriptionViewC   *objDescription;
    CreateBookShelfViewC        *objCreateShelf;
    AddBookShelfViewC           *objAddShelf;
    RateViewC                   *objRate;
    EPubViewController          *objEPubViewController;
    
    YLDocument *document;
    YLPDFViewController *v;
}

@property (retain, nonatomic) IBOutlet UITableView *mTable;
@property (nonatomic, retain) UIPopoverController   *popoverC;
@property (nonatomic, retain) UITableViewCell       *selectedCell;
@property (nonatomic, retain) NSDictionary  *currentBookDict;
@property (nonatomic, retain) UIButton  *selectedBtn;


@end
