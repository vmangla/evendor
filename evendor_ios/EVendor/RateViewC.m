//
//  RateViewC.m
//  EVendor
//
//  Created by MIPC-52 on 30/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "RateViewC.h"

@interface RateViewC ()

@end

@implementation RateViewC
@synthesize currentBookId, delegate;

- (id)initWithBookId:(NSString*) bookId
{
    self = [super initWithNibName:@"RateViewC" bundle:nil];
    if (self) {
        // Custom initialization
        self.currentBookId = bookId;
    }
    return self;
}

- (void)dealloc
{
    [currentBookId release];
    [_rateImgView1 release];
    [_rateImgView2 release];
    [_rateImgView3 release];
    [_rateImgView4 release];
    [_rateImgView5 release];
    [_commentTextV release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    
    [Utils makeRoundRectInView:self.view];
    [Utils setRoundWithLayer:self.view cornerRadius:1.0 borderWidth:1.0 borderColor:kBgColor maskBounds:YES];
    self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    
    self.rateImgView1.image = [UIImage imageNamed:@"noRate.png"];
    self.rateImgView2.image = [UIImage imageNamed:@"noRate.png"];
    self.rateImgView3.image = [UIImage imageNamed:@"noRate.png"];
    self.rateImgView4.image = [UIImage imageNamed:@"noRate.png"];
    self.rateImgView5.image = [UIImage imageNamed:@"noRate.png"];
    
    totalRate = 0.0f;
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


- (IBAction)starBtnClicked:(id)sender
{
    UIImage *noRateImg = [UIImage imageNamed:@"noRate.png"];
    UIImage *halfRateImg = [UIImage imageNamed:@"halfRate.png"];
    UIImage *fullRateImg = [UIImage imageNamed:@"fullRate.png"];
    
    self.rateImgView1.image = noRateImg;
    self.rateImgView2.image = noRateImg;
    self.rateImgView3.image = noRateImg;
    self.rateImgView4.image = noRateImg;
    self.rateImgView5.image = noRateImg;
    
    UIButton    *aBtn = (UIButton*)sender;
    NSInteger   aTag = [aBtn tag];
    
    int tagMod = aTag%2;
    
    if(tagMod == 0)
    {
        // Even
        if(aTag==2)
            self.rateImgView1.image = fullRateImg;
        else if(aTag==4)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
        }
        else if(aTag==6)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
            self.rateImgView3.image = fullRateImg;
        }
        else if(aTag==8)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
            self.rateImgView3.image = fullRateImg;
            self.rateImgView4.image = fullRateImg;
        }
        else if(aTag==10)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
            self.rateImgView3.image = fullRateImg;
            self.rateImgView4.image = fullRateImg;
            self.rateImgView5.image = fullRateImg;
        }
    }
    else
    {
        // Odd
        if(aTag==1)
            self.rateImgView1.image = halfRateImg;
        else if(aTag==3)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = halfRateImg;
        }
        else if(aTag==5)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
            self.rateImgView3.image = halfRateImg;
        }
        else if(aTag==7)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
            self.rateImgView3.image = fullRateImg;
            self.rateImgView4.image = halfRateImg;
        }
        else if(aTag==9)
        {
            self.rateImgView1.image = fullRateImg;
            self.rateImgView2.image = fullRateImg;
            self.rateImgView3.image = fullRateImg;
            self.rateImgView4.image = fullRateImg;
            self.rateImgView5.image = halfRateImg;
        }
    }
    
    totalRate = aTag/2.0;
    NSLog(@"Total Rate = %.2f", totalRate);
}

#pragma mark - Rate
- (IBAction)rateClicked:(id)sender
{
    NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    NSString    *comment = [self.commentTextV.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSString *rate = [NSString stringWithFormat:@"%0.2f",totalRate];
    if(!comment.length>0)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please write your comment"];
        return;
    }
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/%@/%@/%@/%@",kBaseURL,kRateBookApi,kApiKey,@"userid",userId,kBookId,self.currentBookId,@"rating",rate,@"comments",comment];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
}

- (IBAction)cancelClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}


#pragma mark - Server Response Delegate Methods
-(void) getResult:(NSDictionary*) dataDict
{
    [Utils stopActivityIndicator:self.view];
    
    NSString    *error = [dataDict objectForKey:kError];
    if([@"true" isEqualToString:error])
    {
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showOKAlertWithTitle:kAlertTitle message:msg];
        return;
    }
    
    NSString    *msg = [dataDict objectForKey:kMessage];
    [Utils showOKAlertWithTitle:kAlertTitle message:msg];
    if(self.delegate)
        [self.delegate popoverCancel];
}

-(void) didFailToGetResult
{
    [Utils stopActivityIndicator:self.view];
}



@end




