//
//  DescriptionViewC.m
//  EVendor
//
//  Created by MIPC-52 on 11/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "DescriptionViewC.h"
//#import "LibraryViewC.h"
#import <QuartzCore/QuartzCore.h>
#import "UIImageView+WebCache.h"
#import "NSString+HTML.h"

@interface DescriptionViewC ()

@end

@implementation DescriptionViewC
@synthesize dataDict, delegate, aImage;
@synthesize downloadBtn;

- (id)initWithBookData:(NSDictionary*) aDict andImage:(UIImage*) image
{
    self = [super initWithNibName:@"DescriptionViewC" bundle:nil];
    if (self) {
        // Custom initialization
        self.dataDict = aDict;
       // NSLog(@"dict is %@",aDict);
        self.aImage = image;
    }
    return self;
}

- (void)dealloc
{
    [aImage release];
    [dataDict release];
    [_titleL release];
    [_autherL release];
    [_priceL release];
    [_publisherL release];
    [_descriptionT release];
    [_bookImageV release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    // Set RoundRect
    [Utils makeRoundRectInView:self.view];
    [Utils addNavigationItm:self];

    [Utils setRoundWithLayer:self.bookImageV cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
    [Utils setRoundWithLayer:self.view cornerRadius:1.0 borderWidth:1.0 borderColor:kBgColor maskBounds:YES];
    self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    
    // Show Data Description
    [self displayDiscription];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark - Description
- (void) displayDiscription
{
    NSString *publishDateTime=[self.dataDict objectForKey:kPublishDate];
    NSLog(@"date is %@",publishDateTime);
     publishDateTime = [publishDateTime substringToIndex:[publishDateTime rangeOfString:@" "].location];
    self.titleL.text = [self.dataDict objectForKey:kTitle];
    self.autherL.text = [self.dataDict objectForKey:kAuthorName];
    
    NSString    *price = [self.dataDict objectForKey:@"price"];//price
    NSLog(@"price == %@==",price);
    if ([@"0" isEqualToString:price] || [@"null" isEqualToString:price]) {
        self.priceL.text = @"free";
    }else{
        NSString    *price1 = [[self.dataDict objectForKey:kPriceText] stringByDecodingHTMLEntities];
        self.priceL.text = price1;
    }            //typeL.text = price;
    //self.priceL.text = price;
    //self.priceL.text = [self.dataDict objectForKey:kPrice];
    self.publisherL.text = [self.dataDict objectForKey:kPublisher];
    self.descriptionT.text = [self.dataDict objectForKey:kDescription];
    self.sizeL.text=[self.dataDict objectForKey:kFileSize];
    self.publishDL.text=publishDateTime;//[self.dataDict objectForKey:publishDateTime];
    NSString    *thumbImage = [self.dataDict objectForKey:kProductThumbnail];
//    dispatch_async(dispatch_get_global_queue(0,0), ^{
//        
//        NSData * data = [[NSData alloc] initWithContentsOfURL: [NSURL URLWithString:thumbImage]];
//        if ( data == nil )
//        {
//            [self.bookImageV setImage:[UIImage imageNamed:@"NoImageFound.png"]];
//            // self.bookImageV.image  = ;
//            //[self.bookImageV setContentMode:UIViewContentModeScaleAspectFit];
//        }
//        else
//        {
//            dispatch_async(dispatch_get_main_queue(), ^{
//                //img = [UIImage imageWithData: data];
//                [self.bookImageV setImage:[UIImage imageWithData: data]];
//                
//            });
//        }
//        
//    });
    [self.bookImageV sd_setImageWithURL:[NSURL URLWithString:thumbImage]
                       placeholderImage:[UIImage imageNamed:@"placeholder.png"] options:SDWebImageRefreshCached];
    //[self.bookImageV setImageWithURL:[NSURL URLWithString:thumbImage]];
}


- (IBAction)cancelClicked:(id)sender
{
   if(delegate)
       [delegate popoverCancel];
}

#pragma mark - Purchase
- (IBAction)purchaseClicked:(id)sender
{
    if(delegate)
        [delegate purchaseBook:self];
}





@end
