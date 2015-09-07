//
//  BookShelfDescriptionViewC.m
//  EVendor
//
//  Created by MIPC-52 on 15/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "BookShelfDescriptionViewC.h"
#import "UIImageView+WebCache.h"
#import "NSString+HTML.h"

@interface BookShelfDescriptionViewC ()

@end

@implementation BookShelfDescriptionViewC
@synthesize delegate, dataDict;

- (id)initWithBookData:(NSDictionary*) aDict
{
    self = [super initWithNibName:@"BookShelfDescriptionViewC" bundle:nil];
    if (self) {
        // Custom initialization
        self.dataDict = aDict;
    }
    return self;
}

- (void)dealloc
{
    [dataDict release];
    [_titleL release];
    [_authorL release];
    [_dateL release];
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
    //NSLog(@"date is %@",publishDateTime);
    //publishDateTime = [publishDateTime substringToIndex:[publishDateTime rangeOfString:@" "].location];
    self.titleL.text = [self.dataDict objectForKey:kTitle];
    self.authorL.text = [self.dataDict objectForKey:kAuthorName];
    NSString    *price = [[self.dataDict objectForKey:kPriceText] stringByDecodingHTMLEntities];
    self.dateL.text = price;
    //self.dateL.text = [self.dataDict objectForKey:kPrice];
    self.publisherL.text = [self.dataDict objectForKey:kPublisher];
    self.descriptionT.text = [self.dataDict objectForKey:kDescription];
    self.sizeL.text=[self.dataDict objectForKey:kFileSize];
    self.publishDL.text=publishDateTime;
    NSString    *thumbImage = [self.dataDict objectForKey:kProductThumbnail];
//    dispatch_async(dispatch_get_global_queue(0,0), ^{
//        
//        NSData * data = [[NSData alloc] initWithContentsOfURL: [NSURL URLWithString:thumbImage]];
//        if ( data == nil )
//        {
//            self.bookImageV.image  = [UIImage imageNamed:@"NoImageFound.png"];
//            [self.bookImageV setContentMode:UIViewContentModeScaleAspectFit];
//        }
//        else
//        {
//            dispatch_async(dispatch_get_main_queue(), ^{
//                //img = [UIImage imageWithData: data];
//                self.bookImageV.image = [UIImage imageWithData: data];
//                [self.bookImageV setContentMode:UIViewContentModeScaleAspectFit];
//            });
//        }
//        
//    });
    [self.bookImageV sd_setImageWithURL:[NSURL URLWithString:thumbImage]
               placeholderImage:[UIImage imageNamed:@"placeholder.png"] options:SDWebImageRefreshCached];
   // [self.bookImageV setImageWithURL:[NSURL URLWithString:thumbImage]];
}


- (IBAction)readBookClicked:(id)sender
{
    if(self.delegate)
        [self.delegate readBook];
}

- (IBAction)moveClicked:(id)sender
{
    if(self.delegate)
        [self.delegate moveBook];
}

- (IBAction)deleteClicked:(id)sender
{
    if(self.delegate)
        [self.delegate deleteBook];
}

- (IBAction)rateClicked:(id)sender
{
    if(self.delegate)
        [self.delegate rateBook];
}

- (IBAction)closeClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}



@end
