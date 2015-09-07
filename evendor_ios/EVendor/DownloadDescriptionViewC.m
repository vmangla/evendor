//
//  DownloadDescriptionViewC.m
//  EVendor
//
//  Created by MIPC-52 on 24/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "DownloadDescriptionViewC.h"
#import "UIImageView+WebCache.h"
#import "NSString+HTML.h"
#import "DownloadedViewC.h"


@interface DownloadDescriptionViewC ()

@end

@implementation DownloadDescriptionViewC
@synthesize dataDict, delegate;

- (id)initWithBookData:(NSDictionary*) aDict andAdded:(BOOL)isAdded
{
    self = [super initWithNibName:@"DownloadDescriptionViewC" bundle:nil];
    if (self) {
        // Custom initialization
        self.dataDict = aDict;
        isAddedBook = isAdded;
    }
    return self;
}

- (void)dealloc
{
    [dataDict release];
    [_titleL release];
    [_authorL release];
    [_priceL release];
    [_publisherL release];
    [_descriptionT release];
    [_bookImageV release];
    [_addToBookshelf release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    
    [Utils addNavigationItm:self];

    if(isAddedBook == YES)
    {
        [self.addToBookshelf setTitle:@"Move" forState:UIControlStateNormal];
    }
    
    // Set RoundRect
    [Utils makeRoundRectInView:self.view];
    [Utils setRoundWithLayer:self.bookImageV cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
    [Utils setRoundWithLayer:self.view cornerRadius:1.0 borderWidth:1.0 borderColor:kBgColor maskBounds:YES];
    self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    
    // Show Data Description
    [self displayDiscription];
}

#pragma mark - Description
- (void) displayDiscription
{
    self.titleL.text = [self.dataDict objectForKey:kTitle];
    self.authorL.text = [self.dataDict objectForKey:kAuthorName];
    NSString    *price = [[self.dataDict objectForKey:kPriceText] stringByDecodingHTMLEntities];
    self.priceL.text = price;
    self.publisherL.text = [self.dataDict objectForKey:kPublisher];
    self.descriptionT.text = [self.dataDict objectForKey:kDescription];
    self.sizeL.text=[self.dataDict objectForKey:kFileSize];
    self.publishDL.text=[self.dataDict objectForKey:kPublishDate];
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


- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}




- (IBAction)readBookClicked:(id)sender
{
    if(self.delegate)
        [self.delegate readBook];
}

- (IBAction)addToBookShelfClicked:(id)sender
{
    UIButton    *aBtn = (UIButton*) sender;
    NSString    *btnTitle = [aBtn titleForState:UIControlStateNormal];
    if([@"Move" isEqualToString:btnTitle])
    {
        if(self.delegate)
            [self.delegate moveBook];
    }
    else
    {
        if(self.delegate)
            [self.delegate addToBookShelf];
    }
}

- (IBAction)rateClicked:(id)sender
{
    if(self.delegate)
        [self.delegate rateBook];
}

- (IBAction)deleteClicked:(id)sender
{
     NSString    *booktitle = [self.dataDict objectForKey:kTitle];
    [Utils showAlertViewWithTag:502 title:kAlertTitle message:[NSString stringWithFormat:@"Are you sure want to delete '%@'?",booktitle] delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
}

- (IBAction)cancelClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}


- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex
{
    if(alertView.tag == 502)   // Delete Book
    {
        if(buttonIndex == 1)
        {
            if(self.delegate)
                [self.delegate deleteBook];
        }
    }
}


@end
