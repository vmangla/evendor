//
//  PickerViewC.h
//  EVendor
//
//  Created by MIPC-52 on 21/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface PickerViewC : UIViewController
{
    NSMutableArray  *mainArray;
}

@property (assign, nonatomic) BOOL    isSearch;
@property (assign, nonatomic) BOOL    isSort;
@property (nonatomic, assign) id delegate;
@property (retain, nonatomic) IBOutlet UIPickerView *mPicker;
@property (nonatomic, assign) NSInteger selectedRow;
- (IBAction)cancelClicked:(id)sender;
- (IBAction)doneClicked:(id)sender;
- (id)initWithArray:(NSArray*) arr;


@end
