//
//  DownloadDescriptionViewC.h
//  EVendor
//
//  Created by MIPC-52 on 24/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface DownloadDescriptionViewC : UIViewController
{
    BOOL isAddedBook;
}
@property (retain, nonatomic) IBOutlet UILabel *sizeL;
@property (retain, nonatomic) IBOutlet UILabel *publishDL;
@property (retain, nonatomic) IBOutlet UILabel *titleL;
@property (retain, nonatomic) IBOutlet UILabel *authorL;
@property (retain, nonatomic) IBOutlet UILabel *priceL;
@property (retain, nonatomic) IBOutlet UILabel *publisherL;
@property (retain, nonatomic) IBOutlet UITextView *descriptionT;
@property (retain, nonatomic) IBOutlet UIImageView *bookImageV;
@property (nonatomic, retain) NSDictionary  *dataDict;
@property (nonatomic, assign) id delegate;
@property (retain, nonatomic) IBOutlet UIButton *addToBookshelf;

- (IBAction)readBookClicked:(id)sender;
- (IBAction)addToBookShelfClicked:(id)sender;
- (IBAction)rateClicked:(id)sender;
- (IBAction)deleteClicked:(id)sender;
- (IBAction)cancelClicked:(id)sender;
- (id)initWithBookData:(NSDictionary*) aDict andAdded:(BOOL)isAdded;

@end
