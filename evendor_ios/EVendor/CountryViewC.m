//
//  CountryViewC.m
//  EVendor
//
//  Created by MIPC-52 on 28/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "CountryViewC.h"
#import "UIImageView+WebCache.h"

@interface CountryViewC ()

@end

@implementation CountryViewC
@synthesize delegate;

- (id)initWithCountryArray:(NSArray*) arr
{
    self = [super initWithNibName:@"CountryViewC" bundle:nil];
    if (self) {
        // Custom initialization
        ReleaseObj(mainArray);
        mainArray = [[NSMutableArray alloc] initWithArray:arr];
    }
    return self;
}

- (void)dealloc
{
    [mainArray release];
    [_mTableView release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    // Set RoundRect
    [Utils makeRoundRectInView:self.view];
    self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    
    selectedCountry = -1;
    [self.mTableView reloadData];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


- (IBAction)cancelClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}

- (IBAction)doneClicked:(id)sender
{
    if(selectedCountry != -1)
    {
        if(self.delegate)
            [self.delegate selectedPickerValue:[mainArray objectAtIndex:selectedCountry]];
    }
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please select your store."];
}

#pragma mark - Table view data source

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    return [mainArray count];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *cellIdentifier=@"CountryCell";
    UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==nil)
    {
        NSString *nibName = @"CountryCell";
        NSArray *nib = [[NSBundle mainBundle]loadNibNamed:nibName owner:self options:nil];
        cell = [nib objectAtIndex:0];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
        cell.accessoryType = UITableViewCellAccessoryNone;
    }
    
    cell.backgroundColor = [UIColor clearColor];
    NSDictionary *aDic = [mainArray objectAtIndex:indexPath.row];
    
    // Title
    UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:101];
    titleL.text = [aDic objectForKey:kStoreName];
    
    UIImageView *aImageView = (UIImageView*)[cell.contentView viewWithTag:102];
    NSString    *flagImage = [aDic objectForKey:@"country_flag_url"];
    //[aImageView setImageWithURL:[NSURL URLWithString:flagImage]];
//    dispatch_async(dispatch_get_global_queue(0,0), ^{
//        
//        NSData * data = [[NSData alloc] initWithContentsOfURL: [NSURL URLWithString:flagImage]];
//        if ( data == nil )
//        {
//            aImageView.image  = [UIImage imageNamed:@"NoImageFound.png"];
//            [aImageView setContentMode:UIViewContentModeScaleAspectFit];
//        }
//        else
//        {
//            dispatch_async(dispatch_get_main_queue(), ^{
//                //img = [UIImage imageWithData: data];
//                aImageView.image = [UIImage imageWithData: data];
//                [aImageView setContentMode:UIViewContentModeScaleAspectFit];
//            });
//        }
//        
//    });
    [aImageView sd_setImageWithURL:[NSURL URLWithString:flagImage]
                       placeholderImage:[UIImage imageNamed:@"placeholder.png"] options:SDWebImageRefreshCached];
    if(indexPath.row == selectedCountry)
        cell.accessoryType = UITableViewCellAccessoryCheckmark;
    else
        cell.accessoryType = UITableViewCellAccessoryNone;

    return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    selectedCountry = indexPath.row;
    [self.mTableView reloadData];
}


@end
