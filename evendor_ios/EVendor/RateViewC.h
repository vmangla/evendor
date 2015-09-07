//
//  RateViewC.h
//  EVendor
//
//  Created by MIPC-52 on 30/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface RateViewC : UIViewController <ServerResponseDelegate>
{
    float   totalRate;
}

@property (retain, nonatomic) IBOutlet UIImageView *rateImgView1;
@property (retain, nonatomic) IBOutlet UIImageView *rateImgView2;
@property (retain, nonatomic) IBOutlet UIImageView *rateImgView3;
@property (retain, nonatomic) IBOutlet UIImageView *rateImgView4;
@property (retain, nonatomic) IBOutlet UIImageView *rateImgView5;
@property (retain, nonatomic) IBOutlet UITextView *commentTextV;
@property (nonatomic, retain) NSString  *currentBookId;
@property (nonatomic, assign) id delegate;


- (IBAction)starBtnClicked:(id)sender;
- (IBAction)rateClicked:(id)sender;
- (IBAction)cancelClicked:(id)sender;
- (id)initWithBookId:(NSString*) bookId;


@end
