//
//  CreateBookShelfViewC.m
//  EVendor
//
//  Created by MIPC-52 on 18/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "CreateBookShelfViewC.h"

@interface CreateBookShelfViewC ()

@end

@implementation CreateBookShelfViewC
@synthesize delegate, shelfDict;

- (id)initWithShelf:(NSDictionary*) aDic
{
    self = [super initWithNibName:@"CreateBookShelfViewC" bundle:nil];
    if (self) {
        // Custom initialization
        self.shelfDict = aDic;
    }
    return self;
}

- (void)dealloc
{
    [shelfDict release];
    [_createL release];
    [_titleT release];
    [_colorBtn release];
    [_createBtn release];
    [_colorScroll release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    // Set RoundRect
    [Utils makeRoundRectInView:self.view];
    [Utils setRoundWithLayer:self.view cornerRadius:1.0 borderWidth:1.0 borderColor:kBgColor maskBounds:YES];
    self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    [Utils setRoundWithLayer:[self.colorScroll viewWithTag:101] cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
    [Utils setRoundWithLayer:[self.colorScroll viewWithTag:102] cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
    [Utils setRoundWithLayer:[self.colorScroll viewWithTag:103] cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
    
    if(self.shelfDict.count>0)
    {
        self.createL.text = [NSString stringWithFormat:@"Update '%@'",[self.shelfDict objectForKey:kShelfTitle]];
        [self.createBtn setTitle:@"Update" forState:UIControlStateNormal];
        self.titleT.text = [self.shelfDict objectForKey:kShelfTitle];
        [self.colorBtn setTitle:[self.shelfDict objectForKey:kShelfColor] forState:UIControlStateNormal];
        NSString    *colorName = [self.shelfDict objectForKey:kShelfColor];
        UIColor *aColor = kBlueColor;
        if([@"Blue" isEqualToString:colorName])
            aColor = kBlueColor;
        else if([@"Green" isEqualToString:colorName])
            aColor = kGreenColor;
        else if([@"Orange" isEqualToString:colorName])
            aColor = kOrangeColor;
        [self.colorBtn setBackgroundColor:aColor];
    }
    else
    {
        self.createL.text = @"Create Bookshelf";
        [self.createBtn setTitle:@"Create" forState:UIControlStateNormal];
    }
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


- (IBAction)createShelfClicked:(id)sender
{
    NSString    *shelfTitle = [self.titleT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    if(shelfTitle.length>0)
    {
        NSString    *colorName = [self.colorBtn titleForState:UIControlStateNormal];
        NSString    *btnTitle = [self.createBtn titleForState:UIControlStateNormal];
        NSInteger   shelfId = [[self.shelfDict objectForKey:kShelfId] integerValue];
        if([@"Update" isEqualToString:btnTitle]) // Update
        {
            BOOL isUpdate = [[LocalDatabase sharedDatabase] updateShelfWithId:shelfId andNewTitle:shelfTitle andNewColor:colorName];
            if(isUpdate == YES)
            {
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Successfully Updated"];
                if(self.delegate)
                    [self.delegate shelfUpdate];
            }
            else
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Updation Failed"];
        }
        else   // Create New
        {
            if(self.delegate)
                [self.delegate createNewShelfWithTitle:shelfTitle andColor:colorName];
        }
    }
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter title."];
}

- (IBAction)cancelClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}

- (IBAction)colorBtnClicked:(id)sender
{
    UIButton    *aBtn = (UIButton*)sender;
    NSString    *colorName = nil;
    if(aBtn.tag == 101)
        colorName = @"Blue";
    else if(aBtn.tag == 102)
        colorName = @"Green";
    if(aBtn.tag == 103)
        colorName = @"Orange";

    [self.colorBtn setTitle:colorName forState:UIControlStateNormal];
    UIColor *aColor = aBtn.backgroundColor;
    [self.colorBtn setBackgroundColor:aColor];
}



@end
