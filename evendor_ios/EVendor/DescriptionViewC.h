//
//  DescriptionViewC.h
//  EVendor
//
//  Created by MIPC-52 on 11/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface DescriptionViewC : UIViewController
{
    
}
@property (retain, nonatomic) IBOutlet UILabel *publishDL;
@property (retain, nonatomic) IBOutlet UILabel *sizeL;
@property (retain, nonatomic) IBOutlet UILabel *titleL;
@property (retain, nonatomic) IBOutlet UILabel *autherL;
@property (retain, nonatomic) IBOutlet UILabel *priceL;
@property (retain, nonatomic) IBOutlet UILabel *publisherL;
@property (retain, nonatomic) IBOutlet UITextView *descriptionT;
@property (retain, nonatomic) IBOutlet UIImageView *bookImageV;
@property (retain, nonatomic) IBOutlet UIButton *downloadBtn;

@property (nonatomic, retain) NSDictionary  *dataDict;
@property (nonatomic, assign) id delegate;
@property (nonatomic, retain) UIImage   *aImage;

- (id)initWithBookData:(NSDictionary*) aDict andImage:(UIImage*) image;

- (IBAction)cancelClicked:(id)sender;
- (IBAction)purchaseClicked:(id)sender;

@end
