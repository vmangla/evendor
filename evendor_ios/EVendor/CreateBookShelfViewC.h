//
//  CreateBookShelfViewC.h
//  EVendor
//
//  Created by MIPC-52 on 18/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface CreateBookShelfViewC : UIViewController
{
    
}

@property (retain, nonatomic) IBOutlet UILabel *createL;
@property (retain, nonatomic) IBOutlet UITextField *titleT;
@property (retain, nonatomic) IBOutlet UIButton *colorBtn;
@property (retain, nonatomic) IBOutlet UIButton *createBtn;
@property (nonatomic, assign) id delegate;
@property (retain, nonatomic) IBOutlet UIScrollView *colorScroll;
@property (nonatomic, retain) NSDictionary  *shelfDict;

- (IBAction)createShelfClicked:(id)sender;
- (IBAction)cancelClicked:(id)sender;
- (IBAction)colorBtnClicked:(id)sender;
- (id)initWithShelf:(NSDictionary*) aDic;


@end
