//
//  DownloadedViewC.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "DownloadedViewC.h"
#import "UIImageView+WebCache.h"
#import "UIButton+WebCache.h"
#import "CreateBookShelfViewC.h"
#import "AddBookShelfViewC.h"
#import "RateViewC.h"
#import "EPubViewController.h"
#import "ReaderViewController.h"
#import "DownloadDescriptionViewC.h"


NSString *titleString;

#define fileDestination [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/Downloaded Files"]

@interface DownloadedViewC (){
//YLPDFViewController *v;
}
@end

@implementation DownloadedViewC
@synthesize popoverC, selectedCell, selectedBtn;
@synthesize segmentController, searchBar, toolBarImage;
@synthesize mainBookArray;


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
    ReleaseObj(objDownloadDescription);
    [alert release];
    [objEPubViewController release];
    [objRate release];
    [selectedBtn release];
    [selectedCell release];
    [objAddShelf release];
    [objCreateShelf release];
    [popoverC release];
    [shelfArray release];
    [booksArray release];
    [_mTableView release];
    [[NSNotificationCenter defaultCenter] removeObserver:self];
     //[document release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    
    // Do any additional setup after loading the view from its nib.
    self.navigationItem.title = @"Downloaded Books";
    [Utils addNavigationItm:self];

    // Refresh Button On Navigation
    UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
    self.navigationItem.rightBarButtonItem = refreshBtn;
    ReleaseObj(refreshBtn);

        // Notification for Refresh Book Shelf
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(refreshClickedOnNotification) name:kDownloadedRefreshNoti object:nil];
    ReleaseObj(self.mainBookArray);
    self.mainBookArray = [[NSMutableArray alloc] initWithArray:[[LocalDatabase sharedDatabase] fetchAllBooks]];
   // NSLog(@"self.mainBoojk  %@\n",self.mainBookArray);
    //Default selected index of segment controller is ALL option
    self.searchBar.delegate = self;
    self.searchBar.text = @"";
    self.segmentController.selectedSegmentIndex = 4;
    [self refreshClicked];
    
}

- (void) viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    [self refreshClicked];
}

- (void) refreshClickedOnNotification
{
    self.searchBar.text = @"";
    self.segmentController.selectedSegmentIndex = 4;
    ReleaseObj(shelfArray);
    shelfArray = [[NSMutableArray alloc] initWithArray:[[LocalDatabase sharedDatabase] fetchAllBookShelf]];
    NSArray *arr = [[LocalDatabase sharedDatabase] fetchAllBooks];
    ReleaseObj(booksArray);
    booksArray = [[NSMutableArray alloc] initWithArray:arr];
    [self.mTableView reloadData];
//    if(booksArray.count==0)
//        [Utils showOKAlertWithTitle:kAlertTitle message:@"No any book is downloaded."];
}

- (void) refreshClicked
{
   
    ReleaseObj(shelfArray);
    shelfArray = [[NSMutableArray alloc] initWithArray:[[LocalDatabase sharedDatabase] fetchAllBookShelf]];
    NSArray *arr = [[LocalDatabase sharedDatabase] fetchAllBooks];
    
    ReleaseObj(self.mainBookArray);
    self.mainBookArray = [[NSMutableArray alloc] initWithArray:arr];
    ReleaseObj(booksArray);
    booksArray = [[NSMutableArray alloc] initWithArray:arr];
    [self.mTableView reloadData];
    //if(booksArray.count==0)
        //[Utils showOKAlertWithTitle:kAlertTitle message:@"No Publications found"];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark UITableView delegate and datasource method
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    return 180.0;
}
- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    return 1;
}
- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    // sudesh
    if (![booksArray count]) {
        return 1;
    }
    
    int rowCount = 0;
    rowCount = 0;
    NSInteger   arrCount = [booksArray count];
    rowCount = arrCount / 4;
    if([Utils isPortrait] == NO) // For Landscape
        rowCount = arrCount / 5;
    NSInteger reminder = arrCount % 4;
    if([Utils isPortrait] == NO) // For Landscape
        reminder = arrCount % 5;
    if(reminder>0)
        rowCount = rowCount + 1;
    return rowCount;
}
- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *cellIdentifier=@"DownloadedBooksCell";
    UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==nil)
    {
        NSString *nibName = @"DownloadedBooksCell";
        NSArray *nib = [[NSBundle mainBundle]loadNibNamed:nibName owner:self options:nil];
        cell = [nib objectAtIndex:0];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
        cell.accessoryType = UITableViewCellAccessoryNone;
    }
    cell.backgroundColor = [UIColor clearColor];
    
    //sudeh
    if (![booksArray count]) {
        cell.textLabel.text = @"No publication found.\n Please visit your dashboard to download publications from your cloud.";
        cell.textLabel.font = [UIFont systemFontOfSize:17];
        cell.textLabel.textColor = [UIColor whiteColor];
        cell.textLabel.textAlignment = NSTextAlignmentCenter;
        cell.textLabel.numberOfLines = 0;
        cell.contentView.backgroundColor = [UIColor clearColor];//[UIColor colorWithRed:100.0/255.0 green:104.0/255.0 blue:106.0/255.0 alpha:1];


        return cell;
    }
    
    
    NSInteger   index = indexPath.row * 4;
    if([Utils isPortrait] == NO) // For Landscape
        index = indexPath.row * 5;
    NSDictionary *aDic = nil;
    // BOOK 1
    if ([booksArray count] > index)
    {
        aDic = [booksArray objectAtIndex:index];
        // TITLE
        UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:103];
        titleL.text = [NSString stringWithFormat:@"%@",[aDic objectForKey:kTitle]];
        // Book Btn
        UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:102];
        bookImage.backgroundColor = [UIColor lightGrayColor];
        bookImage.tag = index;
        [bookImage setContentMode:UIViewContentModeScaleAspectFit];
        [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
        NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
        [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
        [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
        // ADDED
        NSString    *isGroup = [aDic objectForKey:kShelfId];
        UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:104];
        if([@"-1" isEqualToString:isGroup])
            groupL.hidden = YES;
        else
        {
            groupL.hidden = NO;
            [Utils setRoundWithLayer:groupL cornerRadius:5.0 borderWidth:1.0 borderColor:[UIColor whiteColor] maskBounds:YES];
        }
        // View
        UIView  *aView = (UIView*)[cell.contentView viewWithTag:101];
        aView.hidden = NO;
    }
    // BOOK 2
    index = index + 1;
    if ([booksArray count] > index)
    {
        aDic = [booksArray objectAtIndex:index];
        // TITLE
        UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:203];
        titleL.text = [NSString stringWithFormat:@"%@",[aDic objectForKey:kTitle]];
        // Book Btn
        UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:202];
        bookImage.backgroundColor = [UIColor lightGrayColor];
        bookImage.tag = index;
        [bookImage setContentMode:UIViewContentModeScaleAspectFit];
        [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
        NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
        [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
        [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
        // ADDED
        NSString    *isGroup = [aDic objectForKey:kShelfId];
        UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:204];
        if([@"-1" isEqualToString:isGroup])
            groupL.hidden = YES;
        else
        {
            groupL.hidden = NO;
            [Utils setRoundWithLayer:groupL cornerRadius:5.0 borderWidth:1.0 borderColor:[UIColor whiteColor] maskBounds:YES];
        }
        // View
        UIView  *aView = (UIView*)[cell.contentView viewWithTag:201];
        aView.hidden = NO;
    }
    // BOOK 3
    index = index + 1;
    if ([booksArray count] > index)
    {
        aDic = [booksArray objectAtIndex:index];
        // TITLE
        UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:303];
        titleL.text = [NSString stringWithFormat:@"%@",[aDic objectForKey:kTitle]];
        // Book Btn
        UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:302];
        bookImage.backgroundColor = [UIColor lightGrayColor];
        bookImage.tag = index;
        [bookImage setContentMode:UIViewContentModeScaleAspectFit];
        [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
        NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
        [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
        [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
        // ADDED
        NSString    *isGroup = [aDic objectForKey:kShelfId];
        UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:304];
        if([@"-1" isEqualToString:isGroup])
            groupL.hidden = YES;
        else
        {
            groupL.hidden = NO;
            [Utils setRoundWithLayer:groupL cornerRadius:5.0 borderWidth:1.0 borderColor:[UIColor whiteColor] maskBounds:YES];
        }
        // View
        UIView  *aView = (UIView*)[cell.contentView viewWithTag:301];
        aView.hidden = NO;
    }
    // BOOK 4
    index = index + 1;
    if ([booksArray count] > index)
    {
        aDic = [booksArray objectAtIndex:index];
        // TITLE
        UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:403];
        titleL.text = [NSString stringWithFormat:@"%@",[aDic objectForKey:kTitle]];
        // Book Btn
        UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:402];
        bookImage.backgroundColor = [UIColor lightGrayColor];
        bookImage.tag = index;
        [bookImage setContentMode:UIViewContentModeScaleAspectFit];
        [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
        NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
        [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
        [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
        // ADDED
        NSString    *isGroup = [aDic objectForKey:kShelfId];
        UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:404];
        if([@"-1" isEqualToString:isGroup])
            groupL.hidden = YES;
        else
        {
            groupL.hidden = NO;
            [Utils setRoundWithLayer:groupL cornerRadius:5.0 borderWidth:1.0 borderColor:[UIColor whiteColor] maskBounds:YES];
        }
        // View
        UIView  *aView = (UIView*)[cell.contentView viewWithTag:401];
        aView.hidden = NO;
    }
    
    // BOOK 5 // Only For Landscape
    index = index + 1;
    if ([booksArray count] > index && [Utils isPortrait] == NO)
    {
        aDic = [booksArray objectAtIndex:index];
        // TITLE
        UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:503];
        titleL.text = [NSString stringWithFormat:@"%@",[aDic objectForKey:kTitle]];
        // Book Btn
        UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:502];
        bookImage.backgroundColor = [UIColor lightGrayColor];
        bookImage.tag = index;
        [bookImage setContentMode:UIViewContentModeScaleAspectFit];
        [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
        NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
        [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
        [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
        // ADDED
        NSString    *isGroup = [aDic objectForKey:kShelfId];
        UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:504];
        if([@"-1" isEqualToString:isGroup])
            groupL.hidden = YES;
        else
        {
            groupL.hidden = NO;
            [Utils setRoundWithLayer:groupL cornerRadius:5.0 borderWidth:1.0 borderColor:[UIColor whiteColor] maskBounds:YES];
        }
        // View
        UIView  *aView = (UIView*)[cell.contentView viewWithTag:501];
        aView.hidden = NO;
    }


    return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
}


#pragma mark - Book Clicked
- (void) bookClicked:(id) sender
{
    UIButton *aBtn = (UIButton*)sender;
    self.selectedBtn = aBtn;
    NSInteger   aTag = [aBtn tag];
    UITableViewCell *cell = (UITableViewCell*)[aBtn findSuperViewWithClass:[UITableViewCell class]];
    self.selectedCell = cell;
    //NSString *title = [[booksArray objectAtIndex:aTag] objectForKey:kTitle];
    NSString    *isAdded = [[booksArray objectAtIndex:aTag] objectForKey:kShelfId];
    lastSelectedBookTitle = [[booksArray objectAtIndex:aTag] objectForKey:kTitle];
//    ReleaseObj(alert);
//    if([@"-1" isEqualToString:isAdded])
//    {
//        alert = [[UIAlertView alloc] initWithTitle:title message:nil delegate:self
//                                 cancelButtonTitle:@"Cancel" otherButtonTitles:@"Rate Book",@"Read Book",@"Delete Book",@"Add To Book Shelf", nil];
//    }
//    else
//    {
//        alert = [[UIAlertView alloc] initWithTitle:title message:nil delegate:self
//                                 cancelButtonTitle:@"Cancel" otherButtonTitles:@"Rate Book",@"Read Book",@"Delete Book", nil];
//    }
//    alert.tag = 501;
//    [alert show];
    
    BOOL add = NO;
    if([@"-1" isEqualToString:isAdded])
        add = NO;
    else
        add = YES;
    
    ReleaseObj(objDownloadDescription);
    objDownloadDescription = [[DownloadDescriptionViewC alloc] initWithBookData:[booksArray objectAtIndex:aTag] andAdded:add];
    objDownloadDescription.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objDownloadDescription] autorelease];
    [self.popoverC presentPopoverFromRect:self.selectedBtn.frame inView:[self.selectedCell.contentView viewWithTag:self.selectedBtn.tag] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 480);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
}


#pragma mark- Alert View Delegate
- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex
{
    if(alertView.tag == 501)
    {
        if(buttonIndex == 1) // Rate
        {
            [self rateBook];
        }
        else if(buttonIndex == 2) // Read
        {
            [self readBook];
        }
        else if(buttonIndex == 3) // Delete
        {
            NSDictionary    *bookDict = [booksArray objectAtIndex:[self.selectedBtn tag]];
            NSString    *booktitle = [bookDict objectForKey:kTitle];
            [Utils showAlertViewWithTag:502 title:kAlertTitle message:[NSString stringWithFormat:@"Are you sure want to delete '%@'?",booktitle] delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
        }
        else if(buttonIndex == 4) // Add to book shelf
        {
            [self addToBookShelf];
        }
    }
    else if(alertView.tag == 502)   // Delete Book
    {
        if(buttonIndex == 1)
            [self deleteBook];
    }
}


#pragma mark - Add to book shelf
- (void) addToBookShelf
{
    if([shelfArray count]>0)  // Add with existing shelf
    {
        [self addShelf];
    }
    else  // Create a new shelf first and add it
    {
        [self addNewShelf];
    }
}

- (void) addNewShelf
{
    [self.popoverC dismissPopoverAnimated:YES];
    ReleaseObj(objCreateShelf);
    objCreateShelf = [[CreateBookShelfViewC alloc] initWithShelf:[NSDictionary dictionary]];
    objCreateShelf.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objCreateShelf] autorelease];
    [self.popoverC presentPopoverFromRect:self.selectedBtn.frame inView:[self.selectedCell.contentView viewWithTag:self.selectedBtn.tag] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 280);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
}

- (void) addShelf
{
    [self.popoverC dismissPopoverAnimated:YES];
    ReleaseObj(objAddShelf);
    objAddShelf = [[AddBookShelfViewC alloc] initWithArray:shelfArray];
    objAddShelf.isMove = NO;
    objAddShelf.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objAddShelf] autorelease];
    [self.popoverC presentPopoverFromRect:self.selectedBtn.frame inView:[self.selectedCell.contentView viewWithTag:self.selectedBtn.tag] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 350);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }

    self.popoverC.backgroundColor = kBgColor;
}

- (void) moveBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSDictionary    *bookDict = [booksArray objectAtIndex:[self.selectedBtn tag]];
    NSString        *currentShelfId = [bookDict objectForKey:kShelfId];
    NSMutableArray *tempArr = [[NSMutableArray alloc] initWithArray:shelfArray];
    for (int i=0; i<[tempArr count]; i++)
    {
        NSString        *shelfId = [[tempArr objectAtIndex:i] objectForKey:kShelfId];
        if([currentShelfId isEqualToString:shelfId])
        {
            [tempArr removeObjectAtIndex:i];
            break;
        }
    }

    if([tempArr count]>0)
    {
        [self.popoverC dismissPopoverAnimated:YES];
        ReleaseObj(objAddShelf);
        objAddShelf = [[AddBookShelfViewC alloc] initWithArray:tempArr];
        objAddShelf.isMove = YES;
        objAddShelf.delegate = self;
        ReleaseObj(popoverC);
        self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objAddShelf] autorelease];
        [self.popoverC presentPopoverFromRect:self.selectedBtn.frame inView:[self.selectedCell.contentView viewWithTag:self.selectedBtn.tag] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
        if ([Utils isiOS8]) {
            self.popoverC.popoverContentSize = CGSizeMake(400, 350);
        }
        else{
            //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
        }
        self.popoverC.backgroundColor = kBgColor;
    }
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"No more shelfs are available to move, please create a shelf."];
    ReleaseObj(tempArr);
}

- (void) moveBookToShelf:(NSString*) aId
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSInteger   shelfId = [aId integerValue];
    NSString    *bookId = [[booksArray objectAtIndex:[self.selectedBtn tag]] objectForKey:kBookId];
    BOOL isMove = [[LocalDatabase sharedDatabase] updateShelfInfoWithBookId:bookId andShelfId:shelfId];
    if(isMove){}
    [self refreshClicked];
    [[NSNotificationCenter defaultCenter] postNotificationName:kBookShelfRefreshNoti object:nil];
}


- (void) rateBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    if(self.selectedBtn)
    {
        NSString    *bookId = [[booksArray objectAtIndex:[self.selectedBtn tag]] objectForKey:kBookId];
        ReleaseObj(objRate);
        objRate = [[RateViewC alloc] initWithBookId:bookId];
        objRate.delegate = self;
        ReleaseObj(popoverC);
        self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objRate] autorelease];
        [self.popoverC presentPopoverFromRect:self.selectedBtn.frame inView:[self.selectedCell.contentView viewWithTag:self.selectedBtn.tag] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
        if ([Utils isiOS8]) {
            self.popoverC.popoverContentSize = CGSizeMake(300, 300);
        }
        else{
            //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
        }
        self.popoverC.backgroundColor = kBgColor;
    }
}

#pragma mark - Protocols
- (void) popoverCancel
{
    [self.popoverC dismissPopoverAnimated:YES];
}

- (void) plusBtnClicked
{
    [self.popoverC dismissPopoverAnimated:YES];
    [self addNewShelf];
}
- (void) createNewShelfWithTitle:(NSString*) title andColor:(NSString*) color
{
    [self.popoverC dismissPopoverAnimated:YES];
    [[LocalDatabase sharedDatabase] createNewShelfTitle:title andColor:color];
    NSDictionary    *aDic = [[LocalDatabase sharedDatabase] getLastAddedShelf];
    //NSLog(@"aDic = %@", aDic);
    NSInteger   shelfId = [[aDic objectForKey:kShelfId] integerValue];
    NSString    *bookId = [[booksArray objectAtIndex:[self.selectedBtn tag]] objectForKey:kBookId];
    BOOL isUpdate = NO;
    if(bookId && shelfId)
       isUpdate = [[LocalDatabase sharedDatabase] updateShelfInfoWithBookId:bookId andShelfId:shelfId];
    if(isUpdate == YES)
    {
        [self refreshClicked];
        [[NSNotificationCenter defaultCenter] postNotificationName:kBookShelfRefreshNoti object:nil];
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Successfully Created."];
    }
}

- (void) addBookWithShelfId:(NSString*) aId
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSInteger   shelfId = [aId integerValue];
    NSString    *bookId = [[booksArray objectAtIndex:[self.selectedBtn tag]] objectForKey:kBookId];
    BOOL isUpdate = NO;
    if(bookId && shelfId)
        isUpdate = [[LocalDatabase sharedDatabase] updateShelfInfoWithBookId:bookId andShelfId:shelfId];
    if(isUpdate == YES)
    {
        [self refreshClicked];
        [[NSNotificationCenter defaultCenter] postNotificationName:kBookShelfRefreshNoti object:nil];
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Successfully Added."];
    }
}

- (void) deleteBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSDictionary    *bookDict = [booksArray objectAtIndex:[self.selectedBtn tag]];
    if(bookDict.count>0)
    {
        NSString    *bookId = [bookDict objectForKey:kBookId];
        BOOL isDeleted = [[LocalDatabase sharedDatabase] deleteBookWithBookId:bookId];
        if(isDeleted == YES)
        {
            // Delete from Document directory
            BOOL isEpub = YES;
            NSURL *url = [NSURL URLWithString:[bookDict objectForKey:kProductURL]];
            NSString    *fileName = [url.absoluteString lastPathComponent];
            NSString    *filePath = [NSString stringWithFormat:@"%@/%@.epub",fileDestination,fileName];
            BOOL isExist = [[NSFileManager defaultManager] fileExistsAtPath:filePath];
            if(isExist == NO)
            {
                isEpub = NO;
                filePath = nil;
                filePath = [NSString stringWithFormat:@"%@/%@.pdf",fileDestination,fileName];
                isExist = [[NSFileManager defaultManager] fileExistsAtPath:filePath];
            }
            if(isExist == YES)
            {
                [[NSFileManager defaultManager] removeItemAtPath:filePath error:nil];
            }
            
            // Refresh
            [self refreshClicked];
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Successfully Deleted."];
        }
        [[NSNotificationCenter defaultCenter] postNotificationName:kBookShelfRefreshNoti object:nil];
    }
}

- (void) readBook
{
    [self.popoverC dismissPopoverAnimated:YES];
    [Utils startActivityIndicatorWithMessage:@"Reading..." onView:self.view];
    BOOL isEpub = YES;
    NSDictionary    *bookDict = [booksArray objectAtIndex:[self.selectedBtn tag]];
    NSURL *url = [NSURL URLWithString:[bookDict objectForKey:kProductURL]];
    NSString    *fileName = [url.absoluteString lastPathComponent];
    NSString    *filePath = [NSString stringWithFormat:@"%@/%@.epub",fileDestination,fileName];
    BOOL isExist = [[NSFileManager defaultManager] fileExistsAtPath:filePath];
    NSLog(@"Book dict ========================= %@",bookDict);
    titleString = [bookDict objectForKey:@"title"];
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
            NSString    *bookId = [bookDict objectForKey:kBookId];
            // Open Epub Reader
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
//            [readerViewController setBookTitle:lastSelectedBookTitle];
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


#pragma - Orientation
- (BOOL)shouldAutorotate
{
    NSLog(@"shouldAutorotate");

    return YES;
}

- (void) viewWillLayoutSubviews
{
    
    if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation)){
        [self toolsWithLandscape:YES];
    }else{
        [self toolsWithLandscape:NO];
        
    }

}

#pragma mark - Set frames according to orientations
- (void) toolsWithLandscape:(BOOL) isLandscape
{
    

    if(isLandscape == YES)
    {
        self.toolBarImage.frame = CGRectMake(0,66,1024,100);
        self.searchBar.frame = CGRectMake(13, 2, 996, 44);
        self.segmentController.frame = CGRectMake(20, 43, 982, 28);
        
    }
    else
    {
        self.toolBarImage.frame = CGRectMake(0,66,768,100);
        self.searchBar.frame = CGRectMake(13, 2, 740, 44);
        self.segmentController.frame = CGRectMake(20, 43, 728, 28);

    }
    [alert dismissWithClickedButtonIndex:-1 animated:YES];
    [self.popoverC dismissPopoverAnimated:YES];
    [self.mTableView reloadData];

}

- (IBAction) segmentMethodClicked:(id)sender{
    UISegmentedControl *tmpSegment = (UISegmentedControl *)sender;
    int selectedIndex = tmpSegment.selectedSegmentIndex;
    
    switch (selectedIndex) {
        case 0:{
            //Newspapers
            [self filterBooksWithCatId:@"1"];
        }
            break;
            
        case 1:{
            //Magazines
            [self filterBooksWithCatId:@"2"];
        }
            break;
            
        case 2:{
            //Books
            [self filterBooksWithCatId:@"3"];

        }
            break;
            
        case 3:{
            //Brochures
            [self filterBooksWithCatId:@"5"];
        }
            break;
            
        case 4:{
            //All
            [self refreshClicked];
        }
            break;
            
        default:
            break;
    }
}


- (void) filterBooksWithCatId:(NSString*) catId
{
    NSArray *arr = [[NSMutableArray alloc] initWithArray:self.mainBookArray];
    //NSLog(@"Book details === %@",arr);

    NSMutableArray  *tempArr = [[NSMutableArray alloc] init];

    for (int i=0; i<[arr count]; i++)
    {
        NSDictionary    *aDic = [arr objectAtIndex:i];
        NSString    *cId = [aDic objectForKey:kCategory];
        if([catId isEqualToString:cId])
            [tempArr addObject:aDic];
    }
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:tempArr];
        [self.mTableView reloadData];
        ReleaseObj(tempArr);
    
    if([catId isEqualToString:@"1"]){
        if(booksArray.count==0)
           // NSLog(@"detail is %@",backupBookArray);
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No newspaper found. "];
    }
    if([catId isEqualToString:@"2"]){
        if(booksArray.count==0)
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No magazine found. "];
    }
    if([catId isEqualToString:@"3"]){
        if(booksArray.count==0)
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No book found."];
    }
    if([catId isEqualToString:@"4"]){
        if(booksArray.count==0)
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No Today week found. "];
    }
    if([catId isEqualToString:@"5"]){
        if(booksArray.count==0)
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No brochures found "];
    }
    
    //}
}


#pragma Searchbar delegate methods

- (void)searchBar:(UISearchBar *)searchBar textDidChange:(NSString *)searchText{
    }

- (BOOL)searchBarShouldBeginEditing:(UISearchBar *)searchBar{
    self.searchBar.text = @"";
    return YES;
}
- (void)searchBarSearchButtonClicked:(UISearchBar *)searchBar{
    NSString *searchedString  = searchBar.text;
    if ([searchedString length]) {
        NSMutableArray *tmpArray   = [[NSMutableArray alloc] initWithArray:self.mainBookArray];
        NSPredicate *predicate     = [NSPredicate predicateWithFormat:@"SELF.title contains[cd] %@",searchedString];
        NSArray *searchedDataArray = [tmpArray filteredArrayUsingPredicate:predicate];
        
        if ([searchedDataArray count]) {
            ReleaseObj(booksArray);
            booksArray = [[NSMutableArray alloc] initWithArray:searchedDataArray];
            [self.mTableView reloadData];
            self.segmentController.selectedSegmentIndex = 4;
            
            if ([self.mainBookArray count]) {
                ReleaseObj(self.mainBookArray);
                self.mainBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
            }
            
        }else{
            ReleaseObj(booksArray);
            [booksArray removeAllObjects];
            [self.mTableView reloadData];
            [Utils showOKAlertWithTitle:@"Books not found!" message:@"Sorry! No books found."];
            
        }

    }else{
        [Utils showOKAlertWithTitle:@"String not found!" message:@"Please enter searched string."];

    }
   
}

- (void)searchBarCancelButtonClicked:(UISearchBar *) searchBar{
    self.searchBar.text = @"";
    [self.searchBar resignFirstResponder];
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
    NSLog(@"Document title %@",document.title);
    
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
