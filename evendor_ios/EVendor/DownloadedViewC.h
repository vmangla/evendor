//
//  DownloadedViewC.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ReaderViewController.h"
#import <PDFTouch/PDFTouch.h>
//#import "ChildViewController.h"


@class CreateBookShelfViewC;
@class AddBookShelfViewC;
@class RateViewC;
@class EPubViewController;
@class DownloadDescriptionViewC;


@interface DownloadedViewC : UIViewController <ReaderViewControllerDelegate, UIAlertViewDelegate,UISearchBarDelegate,YLPDFViewControllerDataSource,YLPDFViewControllerDelegate>
{
    NSMutableArray  *booksArray,*shelfArray;
    CreateBookShelfViewC        *objCreateShelf;
    AddBookShelfViewC           *objAddShelf;
    RateViewC                   *objRate;
    EPubViewController          *objEPubViewController;
    UIAlertView *alert;
    DownloadDescriptionViewC    *objDownloadDescription;
    
    NSMutableArray     *mainBookArray;
    UISegmentedControl *segmentController;
    UISearchBar        *searchBar;
    UIView             *toolBarImage;
    NSString           *lastSelectedBookTitle;
    
    YLDocument *document;
    YLPDFViewController *v;
}
@property (nonatomic, strong) NSMutableArray *mainBookArray;
@property (nonatomic, strong) IBOutlet UISegmentedControl *segmentController;
@property (nonatomic, retain) IBOutlet UISearchBar        *searchBar;
@property (nonatomic, retain) IBOutlet UIView             *toolBarImage;

@property (retain, nonatomic) IBOutlet UITableView *mTableView;
@property (nonatomic, retain) UIPopoverController   *popoverC;
@property (nonatomic, retain) UITableViewCell       *selectedCell;
@property (nonatomic, retain) UIButton  *selectedBtn;

- (void) addToBookShelf;
- (void) rateBook;
- (void) popoverCancel;
- (void) deleteBook;
- (void) readBook;
- (void) moveBook;
- (IBAction)segmentMethodClicked:(id)sender;

@end
