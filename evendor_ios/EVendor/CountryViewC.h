//
//  CountryViewC.h
//  EVendor
//
//  Created by MIPC-52 on 28/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface CountryViewC : UIViewController
{
    NSMutableArray  *mainArray;
    NSInteger   selectedCountry;
}

@property (retain, nonatomic) IBOutlet UITableView *mTableView;
@property (nonatomic, assign) id delegate;
- (IBAction)cancelClicked:(id)sender;
- (IBAction)doneClicked:(id)sender;
- (id)initWithCountryArray:(NSArray*) arr;


@end
