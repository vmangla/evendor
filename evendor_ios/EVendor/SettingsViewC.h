//
//  SettingsViewC.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "CountryViewC.h"

@class PickerViewC;
@interface SettingsViewC : UIViewController <EVendorProtocol>
{
    NSMutableArray  *mainArray, *countryArray;
    NSInteger   selectedCatagory;
    PickerViewC         *objPicker;
    ServerRequestType   currentRequestType;
    BOOL    isProfileUpdate;
    CountryViewC    *objCountryViewC;
    BOOL isProfileUpdate_DoneBtnClicked;

}

@property (retain, nonatomic) IBOutlet UITableView *mTableView;
@property (nonatomic, retain) NSString  *countryId;
@property (nonatomic, retain) UIPopoverController   *popoverC;

@end
