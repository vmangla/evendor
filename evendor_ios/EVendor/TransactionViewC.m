//
//  TransactionViewC.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "TransactionViewC.h"
#import "UIImageView+WebCache.h"
#import "NSString+HTML.h"
#import "HostTransaction.h"

@interface TransactionViewC ()

@end

@implementation TransactionViewC
@synthesize totalAmount;
@synthesize popoverC;
@synthesize tableCell;
@synthesize selectedBookDic;

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        // Custom initialization
    }
    return self;
}

- (void)dealloc
{
    self.selectedBookDic = nil;
    [totalAmount release];
    [mainArray release];
    [_mTableView release];
    [_footerView release];
    [_totalAmountL release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    //self.navigationItem.title = @"Transaction History";
    
    self.navigationItem.title = @"Dashboard";

    // Add notification for device orientation
   // [[NSNotificationCenter defaultCenter]addObserver:self selector:@selector(orientationDidChanged:) name:UIDeviceOrientationDidChangeNotification object:nil];
    [Utils addNavigationItm:self];

    // Refresh Button On Navigation
    UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
    self.navigationItem.rightBarButtonItem = refreshBtn;
    ReleaseObj(refreshBtn);
    
    ////self.mTableView.tableFooterView = self.footerView;
    [Utils setRoundWithLayer:self.footerView cornerRadius:2.0 borderWidth:1.0 borderColor:kNavyBlue maskBounds:YES];
    [self initDownload];
    [self getTransactionHistory];
}

- (void) viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    
    // Set Orientation
    if([Utils isPortrait] == YES)
        self.mTableView.frame = CGRectMake(0, 0, 768, 965);
    else
        self.mTableView.frame = CGRectMake((1024-768)/2, 0, 768, 710);
}

- (void) refreshClicked
{
    [self getTransactionHistory];
}

#pragma mark - Get Orientation Notification
//- (void)orientationDidChanged:(NSNotification*)note
//{
//    UIDeviceOrientation orientation = [[UIDevice currentDevice] orientation];
//    if(orientation == UIDeviceOrientationLandscapeLeft || orientation == UIDeviceOrientationLandscapeRight)
//    {
//        self.mTableView.frame = CGRectMake((1024-768)/2, 0, 768, 710);
//    }
//    else if(orientation == UIDeviceOrientationPortrait || orientation == UIDeviceOrientationPortraitUpsideDown)
//    {
//        self.mTableView.frame = CGRectMake(0, 0, 768, 965);
//    }
//}

- (void) viewWillLayoutSubviews
{
    if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
        self.mTableView.frame = CGRectMake((1024-768)/2, 0, 768, 710);
    else
        self.mTableView.frame = CGRectMake(0, 0, 768, 965);
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark - TableView Delegate/DataSource
//- (UIView *)tableView:(UITableView *)tableView viewForFooterInSection:(NSInteger)section
//{
//    return self.footerView;
//}
//- (CGFloat)tableView:(UITableView *)tableView heightForFooterInSection:(NSInteger)section
//{
//    return 60.0;
//}
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    return 140.0;
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
	if (![mainArray count]) {
        return 1;
    }
    return [mainArray count];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *cellIdentifier=@"TransactionCell";
    UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==nil)
    {
        NSString *nibName = @"TransactionCell";
        NSArray *nib = [[NSBundle mainBundle]loadNibNamed:nibName owner:self options:nil];
        cell = [nib objectAtIndex:0];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
        cell.accessoryType = UITableViewCellAccessoryNone;
    }
    cell.backgroundColor = [UIColor clearColor];

    //sudesh
    if (![mainArray count]) {
        cell.textLabel.text = @"No publication found on your cloud.\n Please visit our website to add publications to your cloud.";
        cell.textLabel.font = [UIFont systemFontOfSize:17];
        cell.textLabel.textColor = [UIColor whiteColor];
        cell.textLabel.textAlignment = NSTextAlignmentCenter;
        cell.textLabel.numberOfLines = 0;
        cell.contentView.backgroundColor = [UIColor clearColor];//[UIColor colorWithRed:100.0/255.0 green:104.0/255.0 blue:106.0/255.0 alpha:1];
        //cell.textLabel.backgroundColor = [UIColor yellowColor];//[UIColor colorWithRed:100.0/255.0 green:104.0/255.0 blue:106.0/255.0 alpha:1];

        UIImageView *bookImage1 = (UIImageView*)[cell.contentView viewWithTag:101];
        bookImage1.hidden = YES;

        UILabel *genreL1 = (UILabel*)[cell.contentView viewWithTag:102];
        genreL1.hidden = YES;

        UILabel *titleL1 = (UILabel*)[cell.contentView viewWithTag:103];
        titleL1.hidden = YES;

        UILabel *publisherL1 = (UILabel*)[cell.contentView viewWithTag:104];
        publisherL1.hidden = YES;
        
        UILabel *dateL1 = (UILabel*)[cell.contentView viewWithTag:105];
        dateL1.hidden = YES;

        return cell;
    }

    if(indexPath.row%2!=0)
        cell.contentView.backgroundColor = [UIColor darkGrayColor];
    NSDictionary    *aDict = [mainArray objectAtIndex:indexPath.row];
    
    // Button
    UIImageView *bookImage = (UIImageView*)[cell.contentView viewWithTag:101];
    bookImage.backgroundColor = [UIColor lightGrayColor];
    NSString    *thumbImage = [aDict objectForKey:kProductThumbnail];
    
    [bookImage sd_setImageWithURL:[NSURL URLWithString:thumbImage]
                      placeholderImage:[UIImage imageNamed:@"placeholder.png"] options:indexPath.row == 0 ? SDWebImageRefreshCached : 0];
    
    
    //[bookImage setImageWithURL:[NSURL URLWithString:thumbImage]];
    // download the image asynchronously
//    [self downloadImageWithURL:[NSURL URLWithString:[NSString stringWithFormat:@"%@", thumbImage]]completionBlock:^(BOOL succeeded, UIImage *image) {
//        if (succeeded) {
//            // change the image in the cell
//            bookImage.image = image;
//            
//            // cache the image for use later (when scrolling up)
//            bookImage.image = image;
//        }}];
    [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:1.0 borderColor:kNavyBlue maskBounds:YES];
    
    // Genre
    UILabel *genreL = (UILabel*)[cell.contentView viewWithTag:102];
    genreL.text = [aDict objectForKey:kGenre];
    // Title
    UILabel *titleL = (UILabel*)[cell.contentView viewWithTag:103];
    titleL.text = [aDict objectForKey:kTitle];
    // Publication
    UILabel *publisherL = (UILabel*)[cell.contentView viewWithTag:104];
    publisherL.text = [aDict objectForKey:@"publisher_name"];
    // Date
    NSString *publishDateTime=[aDict objectForKey:kAddDate];
   
    publishDateTime = [publishDateTime substringToIndex:[publishDateTime rangeOfString:@" "].location];
    UILabel *dateL = (UILabel*)[cell.contentView viewWithTag:105];
    dateL.text = publishDateTime;//[aDict objectForKey:kAddDate];
    // Price
    /*
    UILabel *priceL = (UILabel*)[cell.contentView viewWithTag:106];
    priceL.hidden=YES;
    //priceL.text = [aDict objectForKey:kPrice];
    NSString    *price = [[aDict objectForKey:kPriceText] stringByDecodingHTMLEntities];
    priceL.text = price;
     */

	return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    if (![mainArray count]) {
        return;
    }
    self.selectedBookDic = [mainArray objectAtIndex:indexPath.row];
    self.tableCell = [self.mTableView cellForRowAtIndexPath:[NSIndexPath indexPathForRow:indexPath.row inSection:0]];
    [self bookClicked:self.selectedBookDic];
}

#pragma Download image from url
//- (void)downloadImageWithURL:(NSURL *)url completionBlock:(void (^)(BOOL succeeded, UIImage *image))completionBlock
//{
//    NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:url];
//    [NSURLConnection sendAsynchronousRequest:request
//                                       queue:[NSOperationQueue mainQueue]
//                           completionHandler:^(NSURLResponse *response, NSData *data, NSError *error) {
//                               if ( !error )
//                               {
//                                   UIImage *image = [[UIImage alloc] initWithData:data];
//                                   completionBlock(YES,image);
//                               } else{
//                                   completionBlock(NO,nil);
//                               }
//                           }];
//}

#pragma mark - Get Store Lists
- (void) getTransactionHistory
{
    NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/type/user",kBaseURL,kPurchaseHistoryApi,kApiKey, @"UserId",userId];
    NSLog(@"Url %@",requestUrl);
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [HostTransaction host].delegate = self;
    [[HostTransaction host] requestWithUrl:requestUrl];
}

#pragma mark - Server Response Delegate Methods
-(void) getResult:(NSDictionary*) dataDict
{
    [Utils stopActivityIndicator:self.view];
    if(!dataDict.count>0)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"You have not purchased any book."];
        return;
    }
    //self.totalAmount = [NSString stringWithFormat:@"%@", [dataDict objectForKey:kTotalAmount]];
    //self.totalAmountL.text = self.totalAmount;
    ReleaseObj(mainArray);
    mainArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kPurchase]];
    NSLog(@"Main Array == %@",mainArray);
    [self.mTableView reloadData];
}

-(void) didFailToGetResult
{
    [Utils stopActivityIndicator:self.view];
}


#pragma - Orientation
- (BOOL)shouldAutorotate
{
    return YES;
}

#pragma Downloading books from server
- (void) initDownload
{
    zeeDownloadViewObj = [[ZeeDownloadsViewController alloc] initWithNibName:@"ZeeDownloadsViewController_ipad" bundle:nil];
    [zeeDownloadViewObj setDelegate:self];
    [zeeDownloadViewObj resumeAllInterruptedDownloads];
}

#pragma mark - Download Protocals

-(void)downloadRequestStarted:(ASIHTTPRequest *)request
{
    [self addDownloadingBtn];
}

-(void)downloadRequestReceivedResponseHeaders:(ASIHTTPRequest *)request responseHeaders:(NSDictionary *)responseHeaders
{
    
}

-(void)downloadRequestFinished:(ASIHTTPRequest *)request
{
    [self addDownloadingBtn];
    NSDictionary    *userData = request.userInfo;
    NSLog(@"User data == %@",request.userInfo);
    // Insert into DB
    if(userData.count>0)
        [[LocalDatabase sharedDatabase] insertBookWithDict:userData];
    [self performSelector:@selector(refreshDownloadedItem) withObject:nil afterDelay:1.0];
    
    // Update to Server DB
    NSString *bookId = [NSString stringWithFormat:@"%@", [userData objectForKey:kId]];
    NSString *storeId = [NSString stringWithFormat:@"%@", [userData objectForKey:@"country_id"]];
    [self updateServerWhilePurchasedWithBookId:bookId andStoreId:storeId];
}

- (void) updateServerWhilePurchasedWithBookId:(NSString*) bookId andStoreId:(NSString*) storeId
{
    NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/%@/%@",kBaseURL,kStoreDownloadsApi,kApiKey, @"userid",userId,@"StoreId",storeId,@"bookid",bookId];
    [Host host].delegate = self;
    [[Host host] purchaseUpdateWithUrl:requestUrl];
}


- (void) refreshDownloadedItem
{
    [[NSNotificationCenter defaultCenter] postNotificationName:kDownloadedRefreshNoti object:nil];
}

-(void)downloadRequestFailed:(ASIHTTPRequest *)request
{
    [self addDownloadingBtn];
}

-(void)downloadRequestPaused:(ASIHTTPRequest *)request
{
    [self addDownloadingBtn];
}

-(void)downloadRequestCanceled:(ASIHTTPRequest *)request
{
    [self addDownloadingBtn];
}

#pragma mark - Downloading Buuton on Navigation Bar
- (void) addDownloadingBtn
{
    self.navigationItem.rightBarButtonItem = nil;
    
    // Downloading Buuton on Navigation Bar
    if([zeeDownloadViewObj downloadingCount]>0)
    {
        UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
        
        UIBarButtonItem *downloadBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemOrganize target:self action:@selector(downloadBtnClicked)];
        self.navigationItem.rightBarButtonItems = @[refreshBtn,downloadBtn];
        
        ReleaseObj(downloadBtn);
        ReleaseObj(refreshBtn);
        
    }
    else{
        UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
        self.navigationItem.rightBarButtonItem = refreshBtn;
    }
}

- (void) downloadBtnClicked
{
    [self.navigationController pushViewController:zeeDownloadViewObj animated:YES];
}

//#pragma Purchase method
- (void) purchaseBook:(id)delegateClass
{
    [self.popoverC dismissPopoverAnimated:YES];
    //NSDictionary    *aDict = [mainArray objectAtIndex:selectedBook];
    NSString    *bookId = [self.selectedBookDic objectForKey:kId];
    BOOL isExist = NO;
    if(bookId.length>0 && ![@"(null)" isEqualToString:bookId] && ![@"<null>" isEqualToString:bookId])
        isExist = [[LocalDatabase sharedDatabase] isExistBookWithId:bookId];
    else
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry, This book has been corrupted."];
        return;
    }
    
    if(isExist == YES)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"You have already downloaded this book."];
        return;
    }
    
    NSString    *urlString = [self.selectedBookDic objectForKey:kProductURL];
    if(urlString!=nil && ![@"(null)" isEqualToString:urlString])
    {
        //NSLog(@"Product URL: %@", urlString);
        zeeDownloadViewObj.currentInfoDict = self.selectedBookDic;
        if ([zeeDownloadViewObj isRequestExistInQueue:urlString]) {
            UIAlertView *fileExistAlert = [[UIAlertView alloc] initWithTitle:@"eVendor!"
                                                                     message:@"Download is in progress."
                                                                    delegate:nil
                                                           cancelButtonTitle:@"OK"
                                                           otherButtonTitles:nil];
            [fileExistAlert show];
            return;
        }else{
            [zeeDownloadViewObj addDownloadRequest:urlString];
            [self.navigationController pushViewController:zeeDownloadViewObj animated:YES];
        }

        //[zeeDownloadViewObj addDownloadRequest:urlString];
        //[self.navigationController pushViewController:zeeDownloadViewObj animated:YES];
    }
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Book not found."];
}

#pragma mark - Book Clicked
- (void) bookClicked:(NSDictionary *)bookDic
{
    
    ReleaseObj(objDescriptionViewC);
    objDescriptionViewC = [[DescriptionViewC alloc] initWithBookData:bookDic andImage:nil];
    objDescriptionViewC.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objDescriptionViewC] autorelease];
    UIImageView *bookImageFromCell = (UIImageView *)[self.tableCell viewWithTag:101];
    if (bookImageFromCell) {
        self.popoverC.backgroundColor = kBgColor;
        [self.popoverC presentPopoverFromRect:bookImageFromCell.frame inView:bookImageFromCell permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
        if ([Utils isiOS8]) {
            self.popoverC.popoverContentSize = CGSizeMake(400, 450);
        }
        else{
            //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
        }
    }
}

#pragma mark - Protocols
- (void) popoverCancel
{
    [self.popoverC dismissPopoverAnimated:YES];
}


@end
