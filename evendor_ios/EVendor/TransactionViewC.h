//
//  TransactionViewC.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ZeeDownloadsViewController.h"
#import "DescriptionViewC.h"

@interface TransactionViewC : UIViewController <ServerResponseDelegate,ZeeDownloadsViewControllerDelegate>
{
    NSMutableArray  *mainArray;
    ZeeDownloadsViewController *zeeDownloadViewObj;
    DescriptionViewC * objDescriptionViewC;
    UIPopoverController *popoverC;
    UITableViewCell *tableCell;
    NSDictionary *selectedBookDic;
}
@property (nonatomic, retain) NSDictionary *selectedBookDic;
@property (nonatomic, strong) UITableViewCell *tableCell;
@property (nonatomic, strong) UIPopoverController *popoverC;
@property (retain, nonatomic) IBOutlet UITableView *mTableView;
@property (nonatomic, retain) NSString  *totalAmount;
@property (retain, nonatomic) IBOutlet UIView *footerView;
@property (retain, nonatomic) IBOutlet UILabel *totalAmountL;


@end
