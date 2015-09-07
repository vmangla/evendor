//
//  BookShelfViewC.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "BookShelfViewC.h"
#import "BookShelfDescriptionViewC.h"
#import "CreateBookShelfViewC.h"
#import "AddBookShelfViewC.h"
#import "UIButton+WebCache.h"
#import "RateViewC.h"
#import "EPubViewController.h"
#import "ReaderViewController.h"

#define fileDestination [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/Downloaded Files"]

@interface BookShelfViewC ()
{
    NSString *titleString;
}
@end

@implementation BookShelfViewC
@synthesize popoverC, selectedCell, currentBookDict;
@synthesize selectedBtn;

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
    ReleaseObj(objEPubViewController);
    [selectedBtn release];
    [objRate release];
    [shelfArr release];
    [currentBookDict release];
    [objAddShelf release];
    [selectedCell release];
    [mainArray release];
    [objCreateShelf release];
    [objDescription release];
    [popoverC release];
    [_mTable release];
    [[NSNotificationCenter defaultCenter] removeObserver:self];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    self.navigationItem.title = @"Bookshelf";
    
    // Notification for Refresh Book Shelf
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(refreshClicked) name:kBookShelfRefreshNoti object:nil];
    [Utils addNavigationItm:self];

    // Refresh Button On Navigation
    //UIBarButtonItem *addShelfOnNav = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemAdd target:self action:@selector(addShelfOnNavClicked)];
    
    // Refresh Button On Navigation
    UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
    //self.navigationItem.rightBarButtonItem = refreshBtn;
    
    
    UIBarButtonItem *addShelfOnNav = [[UIBarButtonItem alloc] initWithTitle:@"Add Shelf" style:UIBarButtonItemStylePlain target:self action:@selector(addShelfOnNavClicked)];
    
    self.navigationItem.rightBarButtonItems = @[refreshBtn,addShelfOnNav];
    
    ReleaseObj(refreshBtn);
    ReleaseObj(addShelfOnNav);

    [self refreshClicked];
}

- (void) viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    //if(mainArray.count==0)
        //[Utils showOKAlertWithTitle:kAlertTitle message:@"No any book shelf found."];
}

- (void) refreshClicked
{
    ReleaseObj(mainArray);
    mainArray = [[NSMutableArray alloc] init];
    NSMutableArray *booksArr = [[NSMutableArray alloc] initWithArray:[[LocalDatabase sharedDatabase] fetchAllBooksWithShelf]];
    ReleaseObj(shelfArr);
    shelfArr = [[NSMutableArray alloc] initWithArray:[[LocalDatabase sharedDatabase] fetchAllBookShelf]];
    NSMutableArray  *tempArr = nil;
    NSMutableDictionary *tempDict = nil;
    
    for (int i=0; i<[shelfArr count]; i++)
    {
        ReleaseObj(tempDict);
        tempDict = [[NSMutableDictionary alloc] init];
        ReleaseObj(tempArr);
        tempArr = [[NSMutableArray alloc] init];
        
        NSDictionary *shelfDict = [shelfArr objectAtIndex:i];
        NSString    *shelfId = [shelfDict objectForKey:kShelfId];
        
        for (int j=0; j<[booksArr count]; j++)
        {
            NSDictionary *bookDict = [booksArr objectAtIndex:j];
            NSString    *bookShelfId = [bookDict objectForKey:kShelfId];
            if([shelfId isEqualToString:bookShelfId])
                [tempArr addObject:bookDict];
        }
        
        if(shelfDict.count>0)
            [tempDict setObject:shelfDict forKey:kShelfDict];
        if(tempArr.count>0)
            [tempDict setObject:tempArr forKey:kBookArr];
        [mainArray addObject:tempDict];
    }
    
    ReleaseObj(booksArr);
    ReleaseObj(tempArr);
    ReleaseObj(tempDict);
    
    [self.mTable reloadData];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


#pragma mark - TableView Delegate/DataSource
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    return 140.0;
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    //sudesh
    if (![mainArray count]) {
        return 1;
    }
    
    return [mainArray count];
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
	return 1;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *cellIdentifier=@"BookShelfCell";
    UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==nil)
    {
        NSString *nibName = @"BookShelfCell";
        NSArray *nib = [[NSBundle mainBundle]loadNibNamed:nibName owner:self options:nil];
        cell = [nib objectAtIndex:0];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
        cell.accessoryType = UITableViewCellAccessoryNone;
        cell.backgroundColor = [UIColor grayColor];
    }
    cell.backgroundColor = [UIColor clearColor];

    //sudeh
    if (![mainArray count]) {
        cell.textLabel.text = @"No shelves found.\n Tap on the \"Add Shelf\" at top right to create a shelf.";
        cell.textLabel.font = [UIFont systemFontOfSize:17];
        cell.textLabel.textColor = [UIColor whiteColor];
        cell.textLabel.textAlignment = NSTextAlignmentCenter;
        cell.textLabel.numberOfLines = 0;
        //cell.textLabel.backgroundColor = [UIColor colorWithRed:100.0/255.0 green:104.0/255.0 blue:106.0/255.0 alpha:1];
        cell.contentView.backgroundColor = [UIColor clearColor];//[UIColor colorWithRed:100.0/255.0 green:104.0/255.0 blue:106.0/255.0 alpha:1];

        UIButton    *shelfBtn1 = (UIButton*)[cell.contentView viewWithTag:101];
        shelfBtn1.hidden = YES;
        
        UILabel *shelfTitle1 = (UILabel*) [cell.contentView viewWithTag:102];
        shelfTitle1.hidden = YES;
        
        UIScrollView    *aScroll1 = (UIScrollView*)[cell.contentView viewWithTag:103];
        aScroll1.hidden = YES;
        
        return cell;
    }
    
    
    if(indexPath.section%2!=0)
        cell.contentView.backgroundColor = [UIColor lightGrayColor];
    
    // Shelf Button
    UIButton    *shelfBtn = (UIButton*)[cell.contentView viewWithTag:101];
    shelfBtn.tag = indexPath.section;
    [shelfBtn addTarget:self action:@selector(shelfClicked:) forControlEvents:UIControlEventTouchUpInside];
    NSString    *colorName = [[[mainArray objectAtIndex:indexPath.section] objectForKey:kShelfDict] objectForKey:kShelfColor];
    UIImage *shelfImage = [Utils getImageFromResource:@"icon_shelf_blue"];
    if([@"Blue" isEqualToString:colorName])
        shelfImage = [Utils getImageFromResource:@"icon_shelf_blue"];
    else if([@"Green" isEqualToString:colorName])
        shelfImage = [Utils getImageFromResource:@"icon_shelf_green"];
    else if([@"Orange" isEqualToString:colorName])
        shelfImage = [Utils getImageFromResource:@"icon_shelf_orange"];
    [shelfBtn setImage:shelfImage forState:UIControlStateNormal];

    // Shelf Title
    UILabel *shelfTitle = (UILabel*) [cell.contentView viewWithTag:102];
    shelfTitle.text = [[[mainArray objectAtIndex:indexPath.section] objectForKey:kShelfDict] objectForKey:kShelfTitle];
    // Shelf Books
    UIScrollView    *aScroll = (UIScrollView*)[cell.contentView viewWithTag:103];
    [self addShelfBooksInView:aScroll andBooks:[[mainArray objectAtIndex:indexPath.section] objectForKey:kBookArr]];
    
	return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
}

#pragma mark - Shelf Clicked
- (void) shelfClicked:(id) sender
{
    UIButton *aBtn = (UIButton*)sender;
    UITableViewCell *cell = (UITableViewCell*)[aBtn findSuperViewWithClass:[UITableViewCell class]];
    self.selectedCell = cell;
    //NSIndexPath *indexPath = [self.mTable indexPathForCell:cell];
    //NSLog(@"shelf: Section = %d Tag = %d",indexPath.section, aBtn.tag);
    UILabel *titleL = (UILabel*)[cell.contentView viewWithTag:102];
    NSString *title = titleL.text;
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:title message:nil delegate:self
                                          cancelButtonTitle:@"Cancel" otherButtonTitles:@"Edit",@"Delete", nil];
    alert.tag = 501;
    [alert show];
    [alert release];
}

#pragma mark - Shelf Books
- (void) addShelfBooksInView:(UIScrollView*) scrollView andBooks:(NSArray*) arr
{
    CGFloat xx = 0.0;
    for (int i=0; i<[arr count]; i++)
    {
        CGRect rect = CGRectMake(xx, 5, 90, 110);
        UIButton    *aBtn = [Utils newButtonWithTarget:self selector:@selector(shelfBookClicked:) frame:rect image:[UIImage imageNamed:@"placeholder.png"] selectedImage:nil tag:i];
        //[aBtn setTitle:[NSString stringWithFormat:@"Book: %d",i+1] forState:UIControlStateNormal];
        aBtn.backgroundColor = [UIColor clearColor];
        [aBtn setContentMode:UIViewContentModeScaleAspectFit];
        NSDictionary *aDic = [arr objectAtIndex:i];
        NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
        dispatch_async(dispatch_get_global_queue(0,0), ^{
            
            NSData * data = [[NSData alloc] initWithContentsOfURL: [NSURL URLWithString:thumbImage]];
            if ( data == nil )
            {
                [aBtn setImage:[UIImage imageNamed:@"NoImageFound.png"] forState:UIControlStateNormal];
               // self.bookImageV.image  = ;
                //[self.bookImageV setContentMode:UIViewContentModeScaleAspectFit];
            }
            else
            {
                dispatch_async(dispatch_get_main_queue(), ^{
                    //img = [UIImage imageWithData: data];
                    [aBtn setImage:[UIImage imageWithData: data] forState:UIControlStateNormal];
                    
                });
            }
            
        });
        
        [scrollView addSubview:aBtn];
        [Utils setRoundWithLayer:aBtn cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
        xx = xx + 90 +10;
    }
    scrollView.contentSize = CGSizeMake(xx+10, 120);
}

#pragma mark - Shelf Book Clicked
- (void) shelfBookClicked:(id) sender
{
    self.currentBookDict = nil;
    UIButton *aBtn = (UIButton*)sender;
    self.selectedBtn = aBtn;
    UITableViewCell *cell = (UITableViewCell*)[aBtn findSuperViewWithClass:[UITableViewCell class]];
    self.selectedCell = cell;
    NSIndexPath *indexPath = [self.mTable indexPathForCell:cell];
    NSDictionary    *aDict = [[[mainArray objectAtIndex:indexPath.section] objectForKey:kBookArr] objectAtIndex:aBtn.tag];
    self.currentBookDict = aDict;
    ReleaseObj(objDescription);
    objDescription = [[BookShelfDescriptionViewC alloc] initWithBookData:aDict];
    objDescription.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objDescription] autorelease];
    [self.popoverC presentPopoverFromRect:aBtn.frame inView:[cell.contentView viewWithTag:103] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 500);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
}

#pragma mark - Protocols
- (void) popoverCancel
{
    [self.popoverC dismissPopoverAnimated:YES];
}

- (void) createNewShelfWithTitle:(NSString*) title andColor:(NSString*) color
{
    [self.popoverC dismissPopoverAnimated:YES];
    [[LocalDatabase sharedDatabase] createNewShelfTitle:title andColor:color];
    [self refreshClicked];
    [[NSNotificationCenter defaultCenter] postNotificationName:kDownloadedRefreshNoti object:nil];
}

- (void) shelfUpdate
{
    [self.popoverC dismissPopoverAnimated:YES];
    [self refreshClicked];
}

- (void) readBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    [Utils startActivityIndicatorWithMessage:@"Reading..." onView:self.view];
    BOOL isEpub = YES;
    NSURL *url = [NSURL URLWithString:[self.currentBookDict objectForKey:kProductURL]];
    NSString    *fileName = [url.absoluteString lastPathComponent];
    NSString    *filePath = [NSString stringWithFormat:@"%@/%@.epub",fileDestination,fileName];
    BOOL isExist = [[NSFileManager defaultManager] fileExistsAtPath:filePath];
    titleString = [self.currentBookDict objectForKey:@"title"];
    if(isExist == NO)
    {
        isEpub = NO;
        filePath = nil;
        filePath = [NSString stringWithFormat:@"%@/%@.pdf",fileDestination,fileName];
        isExist = [[NSFileManager defaultManager] fileExistsAtPath:filePath];
        if(isExist == NO)
        {
            [Utils stopActivityIndicator:self.view];
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry, Book not found."];
            return;
        }
    }
    
    // Open Reader
    if(filePath && isExist == YES)
    {
        if(isEpub == YES)
        {
            // Open Epub Reader
            NSString    *bookId = [self.currentBookDict objectForKey:kBookId];
            ReleaseObj(objEPubViewController);
            objEPubViewController = [[EPubViewController alloc] initWithNibName:@"EPubView" bundle:nil];
            objEPubViewController.currentBookId = [bookId integerValue];
            [self.navigationController presentViewController:objEPubViewController animated:YES completion:nil];
            [objEPubViewController loadEpub:[NSURL fileURLWithPath:filePath]];
        }
        else
        {
            // Open Pdf Reader
//            ReaderDocument * document = [[ReaderDocument alloc] initWithFilePath:filePath password:@"1234"];
//            ReaderViewController *readerViewController = [[ReaderViewController alloc] initWithReaderDocument:document];
//            readerViewController.delegate = self;
//            readerViewController.modalTransitionStyle = UIModalTransitionStyleCrossDissolve;
//            readerViewController.modalPresentationStyle = UIModalPresentationFullScreen;
//            [self.navigationController presentViewController:readerViewController animated:YES completion:nil];
//            ReleaseObj(readerViewController);
//            ReleaseObj(document);
            [self getDocumentMethod:filePath];
        }
    }
    [Utils stopActivityIndicator:self.view];
}

- (void) rateBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    if(self.currentBookDict.count>0)
    {
        NSString    *bookId = [self.currentBookDict objectForKey:kBookId];
        ReleaseObj(objRate);
        objRate = [[RateViewC alloc] initWithBookId:bookId];
        objRate.delegate = self;
        ReleaseObj(popoverC);
        self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objRate] autorelease];
        [self.popoverC presentPopoverFromRect:self.selectedBtn.frame inView:[self.selectedCell.contentView viewWithTag:103] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
        if ([Utils isiOS8]) {
            self.popoverC.popoverContentSize = CGSizeMake(300, 300);
        }
        else{
            //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
        }
        self.popoverC.backgroundColor = kBgColor;
    }
}

- (void) moveBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSIndexPath *indexPath = [self.mTable indexPathForCell:self.selectedCell];
    NSDictionary    *aDict = [[mainArray objectAtIndex:indexPath.section] objectForKey:kShelfDict];
    NSMutableArray *tempArr = [[NSMutableArray alloc] initWithArray:shelfArr];
    [tempArr removeObject:aDict];
    if([tempArr count]>0)
        [self addShelf];
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"No more shelfs are available to move, please create a shelf."];
    ReleaseObj(tempArr);
}

- (void) moveBookToShelf:(NSString*) aId
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSInteger   shelfId = [aId integerValue];
    NSString    *bookId = [self.currentBookDict objectForKey:kBookId];
    BOOL isMove = [[LocalDatabase sharedDatabase] updateShelfInfoWithBookId:bookId andShelfId:shelfId];
    if(isMove){}
    [self refreshClicked];
    [[NSNotificationCenter defaultCenter] postNotificationName:kDownloadedRefreshNoti object:nil];
}

- (void) deleteBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    if(self.currentBookDict.count>0)
    {
        NSString    *bookId = [self.currentBookDict objectForKey:kBookId];
        BOOL isDeleted = [[LocalDatabase sharedDatabase] updateShelfInfoWithBookId:bookId andShelfId:-1];
        if(isDeleted == YES)
            [self refreshClicked];
        [[NSNotificationCenter defaultCenter] postNotificationName:kDownloadedRefreshNoti object:nil];
    }
}

- (void) plusBtnClicked
{
    [self.popoverC dismissPopoverAnimated:YES];
}


#pragma mark- Alert View Delegate
- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex
{
    if(alertView.tag == 501)
    {
        if(buttonIndex == 1) // Edit
        {
            [self editShelf];
        }
        else if(buttonIndex == 2) // Delete
        {
            [Utils showAlertViewWithTag:502 title:kAlertTitle message:@"Are you sure want to delete this shelf? " delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
        }
    }
    else if(alertView.tag == 502)
    {
        if(buttonIndex == 1) // Delete
        {
            [self deleteShelf];
        }
    }
}

#pragma mark - Shelf Operation
- (void) editShelf
{
    NSIndexPath *indexPath = [self.mTable indexPathForCell:self.selectedCell];
    NSDictionary    *aDict = [[mainArray objectAtIndex:indexPath.section] objectForKey:kShelfDict];
    ReleaseObj(objCreateShelf);
    objCreateShelf = [[CreateBookShelfViewC alloc] initWithShelf:aDict];
    objCreateShelf.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objCreateShelf] autorelease];
    [self.popoverC presentPopoverFromRect:([self.selectedCell.contentView viewWithTag:102]).frame inView:[self.selectedCell.contentView viewWithTag:102] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 250);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
}

- (void) addShelf
{
    NSIndexPath *indexPath = [self.mTable indexPathForCell:self.selectedCell];
    NSDictionary    *aDict = [[mainArray objectAtIndex:indexPath.section] objectForKey:kShelfDict];
    NSMutableArray *tempArr = [[NSMutableArray alloc] initWithArray:shelfArr];
    [tempArr removeObject:aDict];
    ReleaseObj(objAddShelf);
    objAddShelf = [[AddBookShelfViewC alloc] initWithArray:tempArr];
    objAddShelf.isMove = YES;
    objAddShelf.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objAddShelf] autorelease];
    [self.popoverC presentPopoverFromRect:([self.selectedCell.contentView viewWithTag:102]).frame inView:[self.selectedCell.contentView viewWithTag:102] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 380);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
    ReleaseObj(tempArr);
}


- (void) deleteShelf
{
    [Utils startActivityIndicatorWithMessage:@"Deleting..."  onView:self.view];
    NSIndexPath *indexPath = [self.mTable indexPathForCell:self.selectedCell];
    NSArray    *arr = [[mainArray objectAtIndex:indexPath.section] objectForKey:kBookArr];
    NSDictionary    *aDic = [[mainArray objectAtIndex:indexPath.section] objectForKey:kShelfDict];
    if(arr.count==0 && aDic.count==0)
    {
        [self refreshClicked];
        [Utils stopActivityIndicator:self.view];
        return;
    }
    BOOL isDeleted = NO;
    for (int i=0; i<[arr count]; i++)
    {
        NSString    *bookId = [[arr objectAtIndex:i] objectForKey:kBookId];
        isDeleted = [[LocalDatabase sharedDatabase] updateShelfInfoWithBookId:bookId andShelfId:-1];
    }
    
    isDeleted = [[LocalDatabase sharedDatabase] deleteShelfWithId:[aDic objectForKey:kShelfId]];
    if(isDeleted == YES)
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Successfully Deleted."];
    [self refreshClicked];
    [[NSNotificationCenter defaultCenter] postNotificationName:kDownloadedRefreshNoti object:nil];
    [Utils stopActivityIndicator:self.view];
}


- (void) addShelfOnNavClicked
{
    ReleaseObj(objCreateShelf);
    objCreateShelf = [[CreateBookShelfViewC alloc] initWithShelf:[NSDictionary dictionary]];
    objCreateShelf.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objCreateShelf] autorelease];
    [self.popoverC presentPopoverFromBarButtonItem:self.navigationItem.rightBarButtonItem permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 320);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }

    self.popoverC.backgroundColor = kBgColor;
}



#pragma - Orientation
- (BOOL)shouldAutorotate
{
    return YES;
}

#pragma PDFTouch reader

- (void)getDocumentMethod:(NSString *)filePath{
    document = nil;
    document = [[YLDocument alloc] initWithFilePath:filePath];
    
    if(document.isLocked) {
        // unlock pdf document
        [document unlockWithPassword:@"1234"];
    }
    [self modalPDFView];
}

- (void)modalPDFView {
    [document setTitle:titleString];
    v = [[[YLPDFViewController alloc] initWithDocument:document] autorelease];
    [v setDelegate:self];
    [v setDocumentMode:YLDocumentModeDouble];
    [v setAutoLayoutEnabled:YES];
    [v setPageCurlEnabled:YES];
    [v setDocumentLead:YLDocumentLeadRight];
    [v setModalPresentationStyle:UIModalPresentationFullScreen];
    [v setModalTransitionStyle:UIModalTransitionStyleCoverVertical];
    [self.navigationController presentViewController:v animated:YES completion:nil];
    //[self.navigationController pushViewController:v animated:YES];
}
//
#pragma mark YLPDFViewControllerDelegate Methods
- (void)pdfViewController:(YLPDFViewController *)controller didDisplayDocument:(YLDocument *)document {
    NSLog(@"Did display document.");
}

- (void)pdfViewController:(YLPDFViewController *)controller willDismissDocument:(YLDocument *)document {
    NSLog(@"Will dismiss document.");
}

@end





