//
//  LibraryViewC.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ZeeDownloadsViewController.h"

@class DescriptionViewC;
@class PickerViewC;
@class CountryViewC;

@interface LibraryViewC : UIViewController <ZeeDownloadsViewControllerDelegate, EVendorProtocol,UIAlertViewDelegate>
{
    ZeeDownloadsViewController *zeeDownloadViewObj;
    NSMutableArray  *categoryArray, *countryArray;
    NSMutableArray  *booksArray, *libraryArray;
    NSMutableArray  *featuredArray, *newArrivalArray, *topSellerArray;
    NSMutableArray  *tempArray, *groupBooksArray;
    
    ServerRequestType   currentRequestType;
    NSInteger   selectedCatagory, selectedBook;
    DescriptionViewC    *objDescriptionViewC;
    PickerViewC         *objPicker;
    NSMutableArray  *backupBookArray;
    CountryViewC    *objCountryViewC;
    
    UIView *today_week_monthView;
    NSString *todayWeekMonthOptionString;
    
    NSMutableArray *tmpArrayForWeek;
    NSDictionary *singlePuchaseBookDic;
    
}
@property (nonatomic, retain) NSDictionary *singlePuchaseBookDic;
@property (nonatomic, strong) NSMutableArray *tmpArrayForWeek;
@property (nonatomic, strong) IBOutlet UIView *today_week_monthView;
@property (nonatomic, retain)    NSString *todayWeekMonthOptionString;

@property (retain, nonatomic) IBOutlet UIView *toolView;
@property (retain, nonatomic) IBOutlet UITableView *categoryTable;
@property (retain, nonatomic) IBOutlet UITableView *booksTable;
@property (retain, nonatomic) IBOutlet UISearchBar *mSearchbar;

@property (nonatomic, retain) NSString  *countryId;
@property (nonatomic, retain) UIPopoverController   *popoverC;
@property (nonatomic, retain) UIButton  *selectedToolBtn;
@property (nonatomic, retain) NSString  *selectedType;
@property (retain, nonatomic) IBOutlet UISegmentedControl *mSegmentControl;

- (IBAction)toolButtonClicked:(id)sender;
- (IBAction)segmentChanged:(id)sender;
- (IBAction)todayWeekMonthOptionMethod:(id)sender;

@end
