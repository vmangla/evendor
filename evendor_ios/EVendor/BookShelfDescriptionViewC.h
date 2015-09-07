//
//  BookShelfDescriptionViewC.h
//  EVendor
//
//  Created by MIPC-52 on 15/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface BookShelfDescriptionViewC : UIViewController
{
    
}
@property (retain, nonatomic) IBOutlet UILabel *publishDL;
@property (retain, nonatomic) IBOutlet UILabel *sizeL;
@property (retain, nonatomic) IBOutlet UILabel *titleL;
@property (retain, nonatomic) IBOutlet UILabel *authorL;
@property (retain, nonatomic) IBOutlet UILabel *dateL;
@property (retain, nonatomic) IBOutlet UILabel *publisherL;
@property (retain, nonatomic) IBOutlet UITextView *descriptionT;
@property (nonatomic, assign) id delegate;
@property (retain, nonatomic) IBOutlet UIImageView *bookImageV;
@property (nonatomic, retain) NSDictionary  *dataDict;

- (IBAction)readBookClicked:(id)sender;
- (IBAction)moveClicked:(id)sender;
- (IBAction)deleteClicked:(id)sender;
- (IBAction)rateClicked:(id)sender;
- (IBAction)closeClicked:(id)sender;
- (id)initWithBookData:(NSDictionary*) aDict;

@end
