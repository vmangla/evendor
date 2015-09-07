//
//  LibraryViewC.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "LibraryViewC.h"
#import "Defines.h"
#import "DescriptionViewC.h"
#import "UIButton+WebCache.h"
#import "UIButton+WebCache.h"
#import "UIImageView+WebCache.h"
#import "PickerViewC.h"
#import "NSString+HTML.h"
#import "CountryViewC.h"


int segmentSelectedIndex = 0;
BOOL isCatagorySelected = NO;
int selectedIndex = 101;
int matchedIndex  = 101;
NSString *selectedBookId = @"";

//NSString *selectedCat = @"";

@interface LibraryViewC ()
@property (nonatomic, strong) NSString *selectedCat;
@end

@implementation LibraryViewC
@synthesize countryId;
@synthesize popoverC;
@synthesize selectedToolBtn, selectedType;
@synthesize today_week_monthView;
@synthesize  todayWeekMonthOptionString;
@synthesize tmpArrayForWeek;
@synthesize selectedCat;
@synthesize singlePuchaseBookDic;

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
    if (self.today_week_monthView) {
        [self.today_week_monthView release];
    }
    ReleaseObj(objCountryViewC);
    ReleaseObj(backupBookArray);
    [groupBooksArray release];
    [selectedType release];
    [selectedToolBtn release];
    [[NSNotificationCenter defaultCenter] removeObserver:self];
    ReleaseObj(objDescriptionViewC);
    [featuredArray release];
    [newArrivalArray release];
    [topSellerArray release];
    [popoverC release];
    [booksArray release];
    [categoryArray release];
    [libraryArray release];
    [countryArray release];
    [countryId release];
    [_toolView release];
    [_categoryTable release];
    [_booksTable release];
    [_mSearchbar release];
    [_mSegmentControl release];
    [[NSNotificationCenter defaultCenter] removeObserver:self];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    
    // Refresh Button On Navigation
    //ReleaseObj(refreshBtn);
    //Setting Update Notification
    UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
    self.navigationItem.rightBarButtonItem = refreshBtn;
    
    // Do any additional setup after loading the view from its nib.
    self.today_week_monthView.hidden = YES;
    self.todayWeekMonthOptionString = @"This Month";
    
    [Utils addNavigationItm:self];
    
    if([Utils isiOS7] == NO)
        self.navigationController.navigationBar.translucent = NO;
        
    self.navigationItem.title = @"Store";
    
    //Default featured is selected
    UIButton *tmpButton = (UIButton *)[self.view viewWithTag:101];
    [tmpButton setBackgroundColor:[UIColor blueColor]];

    // Colors
    for (int i=101; i<=109; i++)
    {
        UIButton  *aBtn = (UIButton*)[self.toolView viewWithTag:i];
        if([aBtn isKindOfClass:[UIButton class]])
            [aBtn setTitleColor:kNavyBlue forState:UIControlStateNormal];
    }
    [(UIButton*)[self.toolView viewWithTag:101] setTitleColor:kWhiteColor forState:UIControlStateNormal];
    
    // Add Tool View
    self.toolView.frame = CGRectMake(0, 66, 768, 100);
    // Set RoundRect
    [Utils makeRoundRectInView:self.toolView];
    
    
    selectedCatagory = -1;
    selectedBook = -1;
    self.countryId = [[NSUserDefaults standardUserDefaults] objectForKey:kCountryId];
    
    // Featured
    currentRequestType = FeaturedRequest;
    [self getLibraryDataWithMethod:kFeaturedApi];
    // Init Download Books
    [self initDownload];
    
    NSUserDefaults *tmpDefault = [NSUserDefaults standardUserDefaults];
    self.countryId = [tmpDefault objectForKey:kCountryId];
    UIButton *tmpButton1 = (UIButton *)[self.view viewWithTag:104];
    [tmpButton1 setTitle:[tmpDefault objectForKey:kCountry] forState:UIControlStateNormal];

}


- (void) storeChange
{
    //Setting default store on tool bar
    NSUserDefaults *tmpDefault = [NSUserDefaults standardUserDefaults];
    self.countryId             = [tmpDefault objectForKey:kCountryId];
    UIButton *tmpButton        = (UIButton *)[self.view viewWithTag:104];
    [tmpButton setTitle:[tmpDefault objectForKey:kCountry] forState:UIControlStateNormal];
}

- (void) viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    //Setting default store on tool bar
    [self addDownloadingBtn];
}

//-(void)viewDidDisappear:(BOOL)animated{
//    [super viewDidDisappear:animated];
//    NSLog(@"viewDidDisappear");
//    viewDidDisappear = YES;
//}
- (void) refreshClicked
{
    ReleaseObj(groupBooksArray);
    ReleaseObj(booksArray);
    ReleaseObj(featuredArray);
    ReleaseObj(newArrivalArray);
    ReleaseObj(topSellerArray);
    ReleaseObj(categoryArray);
    ReleaseObj(libraryArray);
    ReleaseObj(self.tmpArrayForWeek);
    ReleaseObj(backupBookArray);
    
    segmentSelectedIndex = 0;
    isCatagorySelected = NO;
    selectedCatagory = -1;
    selectedBook = -1;
    selectedIndex = 0;
    matchedIndex  = 101;
    
    
    [self storeChange];
    
    // Do any additional setup after loading the view from its nib.
    self.today_week_monthView.hidden = YES;
    self.todayWeekMonthOptionString  = @"This Month";
    
    // Colors
    for (int i=101; i<=109; i++)
    {
        UIButton  *aBtn = (UIButton*)[self.toolView viewWithTag:i];
        if([aBtn isKindOfClass:[UIButton class]])
            [aBtn setTitleColor:kNavyBlue forState:UIControlStateNormal];
    }
    for (int i = 101 ; i < 104; i++) {
        [(UIButton *)[self.view viewWithTag:i] setBackgroundColor:[UIColor clearColor]];
        
    }
    //Default featured is selected
    UIButton *tmpButton = (UIButton *)[self.view viewWithTag:101];
    [tmpButton setBackgroundColor:[UIColor blueColor]];
    
    if ([self.mSegmentControl numberOfSegments] < 5) {
        [self.mSegmentControl insertSegmentWithTitle:self.todayWeekMonthOptionString atIndex:4 animated:YES];
    }
    
    
    [(UIButton*)[self.toolView viewWithTag:101] setTitleColor:kWhiteColor forState:UIControlStateNormal];
    
    // Featured
    currentRequestType = FeaturedRequest;
    [self getLibraryDataWithMethod:kFeaturedApi];
    
}

- (void) storeDoneClicked
{
    ReleaseObj(groupBooksArray);
    ReleaseObj(booksArray);
    ReleaseObj(featuredArray);
    ReleaseObj(newArrivalArray);
    ReleaseObj(topSellerArray);
    ReleaseObj(categoryArray);
    ReleaseObj(libraryArray);
    ReleaseObj(self.tmpArrayForWeek);
    ReleaseObj(backupBookArray);
    
    segmentSelectedIndex = 0;
    isCatagorySelected = NO;
    selectedCatagory = -1;
    selectedBook = -1;
    selectedIndex = 0;
    matchedIndex  = 101;

    // Do any additional setup after loading the view from its nib.
    self.today_week_monthView.hidden = YES;
    self.todayWeekMonthOptionString  = @"This Month";
    
    // Colors
    for (int i=101; i<=109; i++)
    {
        UIButton  *aBtn = (UIButton*)[self.toolView viewWithTag:i];
        if([aBtn isKindOfClass:[UIButton class]])
            [aBtn setTitleColor:kNavyBlue forState:UIControlStateNormal];
    }
    for (int i = 101 ; i < 104; i++) {
        [(UIButton *)[self.view viewWithTag:i] setBackgroundColor:[UIColor clearColor]];
        
    }
    //Default featured is selected
    UIButton *tmpButton = (UIButton *)[self.view viewWithTag:101];
    [tmpButton setBackgroundColor:[UIColor blueColor]];
    
    if ([self.mSegmentControl numberOfSegments] < 5) {
        [self.mSegmentControl insertSegmentWithTitle:self.todayWeekMonthOptionString atIndex:4 animated:YES];
    }
    
    
    [(UIButton*)[self.toolView viewWithTag:101] setTitleColor:kWhiteColor forState:UIControlStateNormal];

    // Featured
    currentRequestType = FeaturedRequest;
    [self getLibraryDataWithMethod:kFeaturedApi];
   
}

- (void) initDownload
{
    zeeDownloadViewObj = [[ZeeDownloadsViewController alloc] initWithNibName:@"ZeeDownloadsViewController_ipad" bundle:nil];
    [zeeDownloadViewObj setDelegate:self];
    [zeeDownloadViewObj resumeAllInterruptedDownloads];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void) viewWillLayoutSubviews
{
    if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
        [self toolsWithLandscape:YES];
    else
        [self toolsWithLandscape:NO];
}

#pragma mark - Set frames according to orientations
- (void) toolsWithLandscape:(BOOL) isLandscape
{
    
    
    int maxWidth = 0;
    int xx = 10, yy = 10, padding = 10;
    if(isLandscape == YES)
    {
        //self.toolView.frame = CGRectMake(0, 66, 1024, 50);
        self.toolView.frame = CGRectMake(0, 66, 1024, 100);
        maxWidth = 1024;
        //xx = 0;
        //padding = 2;
        xx = 20;
        padding = 35;
        // Tables
        self.categoryTable.frame = CGRectMake(0, 164, 200, 545);
        //self.booksTable.frame = CGRectMake(200, 164, 824, 545);
        self.booksTable.frame = CGRectMake(200, 164+66, 824, 545-66);
        self.mSegmentControl.frame = CGRectMake(self.booksTable.frame.origin.x+10, 164+15, self.booksTable.frame.size.width-20, 30);
        
        float width = (self.booksTable.frame.size.width-20)/5;
        float xAxis = self.booksTable.frame.origin.x+10 + (width * 4);
        self.today_week_monthView.frame = CGRectMake(xAxis, 208, width, 93);
        
        UIButton *btn1 = (UIButton *)[self.today_week_monthView viewWithTag:1001];
        btn1.frame = CGRectMake(btn1.frame.origin.x, btn1.frame.origin.y, width - 4, btn1.frame.size.height);
        UIButton *btn2 = (UIButton *)[self.today_week_monthView viewWithTag:1002];
        btn2.frame = CGRectMake(btn2.frame.origin.x, btn2.frame.origin.y, width - 4, btn2.frame.size.height);
        UIButton *btn3 = (UIButton *)[self.today_week_monthView viewWithTag:1003];
        btn3.frame = CGRectMake(btn3.frame.origin.x, btn3.frame.origin.y, width - 4, btn3.frame.size.height);

    }
    else
    {
        self.toolView.frame = CGRectMake(0, 66, 768, 100);
        maxWidth = 768;
        xx = 20;
        padding = 35;
        // Tables
        self.categoryTable.frame = CGRectMake(0, 164, 200, 854);
        //self.booksTable.frame = CGRectMake(200, 164, 568, 811);
        self.booksTable.frame = CGRectMake(200, 164+66, 568, 811-66);
        self.mSegmentControl.frame = CGRectMake(self.booksTable.frame.origin.x+10, 164+15, self.booksTable.frame.size.width-20, 30);
        
        self.today_week_monthView.frame = CGRectMake(643, 208, 109, 93);
        UIButton *btn1 = (UIButton *)[self.today_week_monthView viewWithTag:1001];
        btn1.frame = CGRectMake(2,2,105,28);
        UIButton *btn2 = (UIButton *)[self.today_week_monthView viewWithTag:1002];
        btn2.frame = CGRectMake(2,32,105,28);
        UIButton *btn3 = (UIButton *)[self.today_week_monthView viewWithTag:1003];
        btn3.frame = CGRectMake(2,62,105,28);

    }
    
    for (int i=101; i<=109; i++)
    {
        UIView  *aView = (UIView*)[self.toolView viewWithTag:i];
        int viewWidth = aView.frame.size.width;
        if(viewWidth<maxWidth)
        {}
        else
        {
            if(isLandscape == YES)
            {
                maxWidth = 1024;
                yy = 10 + aView.frame.size.height;
            }
            else
            {
                maxWidth = 768;
                yy = 10 + aView.frame.size.height + 10;
            }
            xx = padding;
            //yy = 10 + aView.frame.size.height + 10;
        }
        if(i==108) //107
            yy = yy - 7;
        else if(i==109) //108
            yy = yy + 7;
        aView.frame = CGRectMake(xx, yy, aView.frame.size.width, aView.frame.size.height);
        xx = xx + aView.frame.size.width + padding;
        maxWidth = maxWidth - aView.frame.size.width - padding;
    }
    [self.view bringSubviewToFront:self.categoryTable];
    [self popoverCancel];
    [self.booksTable reloadData];
}

#pragma mark UITableView delegate and datasource method
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    CGFloat hh = 44.0;
    if(tableView.tag == 202)
        hh = 280;
    return hh;
}
- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    return 1;
}
- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    NSInteger rowCount = 0;
    if(tableView.tag == 201)
        rowCount = [categoryArray count];
    else if(tableView.tag == 202)
    {
        rowCount = 0;
        NSInteger   arrCount = [booksArray count];
        rowCount = arrCount / 3;
        if([Utils isPortrait] == NO) // For Landscape
            rowCount = arrCount / 4;
        NSInteger reminder = arrCount % 3;
        if([Utils isPortrait] == NO) // For Landscape
            reminder = arrCount % 4;
        if(reminder>0)
            rowCount = rowCount + 1;
    }
    //NSLog(@"rowCount = %d", rowCount);
    return rowCount;
}
- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    //NSLog(@"===========%@\n================%@",booksArray,backupBookArray);
    UITableViewCell *cell = nil;
    //// For Category Table
    if(tableView.tag == 201)
    {
        static NSString *cellIdentifier=@"cell";
        UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
        if(cell==nil)
        {
            cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:cellIdentifier] autorelease];
            cell.selectionStyle = UITableViewCellSelectionStyleNone;
        }
        cell.backgroundColor = [UIColor clearColor];
        NSDictionary    *aDic = [categoryArray objectAtIndex:indexPath.row];
        NSString    *genere = [aDic objectForKey:kGenre];
        cell.textLabel.text = genere;
        cell.textLabel.textColor = kNavyBlue;
        if(indexPath.row == selectedCatagory)
                cell.textLabel.textColor = kWhiteColor;
        cell.textLabel.textAlignment = NSTextAlignmentLeft;
        cell.accessoryType = UITableViewCellAccessoryDisclosureIndicator;
        return cell;
    }
    
    //// For Books Table   // ******************
    else if(tableView.tag == 202)
    {
        static NSString *cellIdentifier=@"LibCell";
        UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
        if(cell==nil)
        {
            NSString *nibName = @"LibraryCell";
            NSArray *nib = [[NSBundle mainBundle]loadNibNamed:nibName owner:self options:nil];
            cell = [nib objectAtIndex:0];
            cell.selectionStyle = UITableViewCellSelectionStyleNone;
            cell.accessoryType = UITableViewCellAccessoryNone;
        }
        cell.backgroundColor = [UIColor clearColor];
        NSInteger   index = indexPath.row * 3;
        if([Utils isPortrait] == NO) // For Landscape
            index = indexPath.row * 4;
        NSDictionary *aDic = nil;
        // BOOK 1
        if ([booksArray count] > index)
        {
            aDic = [booksArray objectAtIndex:index];
            // Book Btn
            UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:102];
            bookImage.backgroundColor = [UIColor lightGrayColor];
            bookImage.tag = index;
            [bookImage setContentMode:UIViewContentModeScaleAspectFit];
            [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
           /// [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
            [self downloadImageWithURL:[NSURL URLWithString:[NSString stringWithFormat:@"%@", thumbImage]]completionBlock:^(BOOL succeeded, UIImage *image) {
                if (succeeded) {
                    // change the image in the cell
                    //bookImage.image = image;
                    [bookImage setImage:image forState:UIControlStateNormal];
                    
                    // cache the image for use later (when scrolling up)
                    //venue.image = image;
                }}];
            [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
            // Get Btn
            UIButton *getBtn = (UIButton*)[cell.contentView viewWithTag:103];
            getBtn.tag = index;
            [getBtn addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            // Group
            NSString    *isGroup = [aDic objectForKey:kIsFree];
            UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:104];
            if([kTrue isEqualToString:isGroup])
            {
                groupL.hidden = NO;
                [Utils setRoundWithLayer:groupL cornerRadius:10.0 borderWidth:0.1 borderColor:[UIColor clearColor] maskBounds:YES];
            }
            else
                groupL.hidden = YES;
            
            // Title
            UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:105];
            titleL.text = [aDic objectForKey:kTitle];
            // File Size
            UILabel *sizeL = (UILabel*) [cell.contentView viewWithTag:106];
            sizeL.text = [aDic objectForKey:kFileSize];
            // File Type
            UILabel *typeL = (UILabel*) [cell.contentView viewWithTag:107];
            //NSString    *price = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];//price
            NSString    *price = [aDic objectForKey:@"price"];//price
            NSLog(@"price == %@==",price);
            if ([@"0" isEqualToString:price] || [@"null" isEqualToString:price]) {
                typeL.text = @"free";
            }else{
                NSString    *price1 = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
                typeL.text = price1;
            }
            
            // Classification
            UILabel *classL = (UILabel*) [cell.contentView viewWithTag:108];
            classL.text = [aDic objectForKey:kCategoryName];
            // Category
            UILabel *CatNameL = (UILabel*) [cell.contentView viewWithTag:109];
            CatNameL.text = [aDic objectForKey:kGenre];
            // Country Name
            UILabel *countryL = (UILabel*) [cell.contentView viewWithTag:110];
            countryL.text = [aDic objectForKey:kCountryName];

            // View
            UIView  *aView = (UIView*)[cell.contentView viewWithTag:101];
            aView.hidden = NO;
            [self putStartsOnView:aView andRating:[[aDic objectForKey:kRating] floatValue]];
        }
        // BOOK 2
        index = index + 1;
        if ([booksArray count] > index)
        {
            aDic = [booksArray objectAtIndex:index];
            // Book Btn
            UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:202];
            bookImage.backgroundColor = [UIColor lightGrayColor];
            bookImage.tag = index;
            [bookImage setContentMode:UIViewContentModeScaleAspectFit];
            [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
           // [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
            [self downloadImageWithURL:[NSURL URLWithString:[NSString stringWithFormat:@"%@", thumbImage]]completionBlock:^(BOOL succeeded, UIImage *image) {
                if (succeeded) {
                    // change the image in the cell
                    //bookImage.image = image;
                    [bookImage setImage:image forState:UIControlStateNormal];
                    
                    // cache the image for use later (when scrolling up)
                    //venue.image = image;
                }}];
            [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
            // Get Btn
            UIButton *getBtn = (UIButton*)[cell.contentView viewWithTag:203];
            getBtn.tag = index;
            [getBtn addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            // Group
            NSString    *isGroup = [aDic objectForKey:kIsFree];
            UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:204];
            if([kTrue isEqualToString:isGroup])
            {
                groupL.hidden = NO;
                [Utils setRoundWithLayer:groupL cornerRadius:10.0 borderWidth:0.1 borderColor:[UIColor clearColor] maskBounds:YES];
            }
            else
                groupL.hidden = YES;
            // Title
            UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:205];
            titleL.text = [aDic objectForKey:kTitle];
            // File Size
            UILabel *sizeL = (UILabel*) [cell.contentView viewWithTag:206];
            sizeL.text = [aDic objectForKey:kFileSize];
            // File Type
            UILabel *typeL = (UILabel*) [cell.contentView viewWithTag:207];
            //NSString    *price = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
            NSString    *price = [aDic objectForKey:@"price"];//price
            NSLog(@"price == %@==",price);
            if ([@"0" isEqualToString:price] || [@"null" isEqualToString:price]) {
                typeL.text = @"free";
            }else{
                NSString    *price1 = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
                typeL.text = price1;
            }
            //typeL.text = price;
            // Classification
            UILabel *classL = (UILabel*) [cell.contentView viewWithTag:208];
            classL.text = [aDic objectForKey:kCategoryName];
            // Category
            UILabel *CatNameL = (UILabel*) [cell.contentView viewWithTag:209];
            CatNameL.text = [aDic objectForKey:kGenre];
            // Country Name
            UILabel *countryL = (UILabel*) [cell.contentView viewWithTag:210];
            countryL.text = [aDic objectForKey:kCountryName];
            // View
            UIView  *aView = (UIView*)[cell.contentView viewWithTag:201];
            aView.hidden = NO;
            [self putStartsOnView:aView andRating:[[aDic objectForKey:kRating] floatValue]];
        }
        // BOOK 3
        index = index + 1;
        if ([booksArray count] > index)
        {
            aDic = [booksArray objectAtIndex:index];
            // Book Btn
            UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:302];
            bookImage.backgroundColor = [UIColor lightGrayColor];
            bookImage.tag = index;
            [bookImage setContentMode:UIViewContentModeScaleAspectFit];
            [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
           // [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
            [self downloadImageWithURL:[NSURL URLWithString:[NSString stringWithFormat:@"%@", thumbImage]]completionBlock:^(BOOL succeeded, UIImage *image) {
                if (succeeded) {
                    // change the image in the cell
                    //bookImage.image = image;
                    [bookImage setImage:image forState:UIControlStateNormal];
                    
                    // cache the image for use later (when scrolling up)
                    //venue.image = image;
                }}];
            [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
            // Get Btn
            UIButton *getBtn = (UIButton*)[cell.contentView viewWithTag:303];
            getBtn.tag = index;
            [getBtn addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            // Group
            NSString    *isGroup = [aDic objectForKey:kIsFree];
            UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:304];
            if([kTrue isEqualToString:isGroup])
            {
                groupL.hidden = NO;
                [Utils setRoundWithLayer:groupL cornerRadius:10.0 borderWidth:0.1 borderColor:[UIColor clearColor] maskBounds:YES];
            }
            else
                groupL.hidden = YES;
            
            // Title
            UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:305];
            titleL.text = [aDic objectForKey:kTitle];
            // File Size
            UILabel *sizeL = (UILabel*) [cell.contentView viewWithTag:306];
            sizeL.text = [aDic objectForKey:kFileSize];
            // File Type
            UILabel *typeL = (UILabel*) [cell.contentView viewWithTag:307];
           // NSString    *price = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
            NSString    *price = [aDic objectForKey:@"price"];//price
            NSLog(@"price == %@==",price);
            if ([@"0" isEqualToString:price] || [@"null" isEqualToString:price]) {
                typeL.text = @"free";
            }else{
                NSString    *price1 = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
                typeL.text = price1;
            }
            //typeL.text = price;
            // Classification
            UILabel *classL = (UILabel*) [cell.contentView viewWithTag:308];
            classL.text = [aDic objectForKey:kCategoryName];
            // Category
            UILabel *CatNameL = (UILabel*) [cell.contentView viewWithTag:309];
            CatNameL.text = [aDic objectForKey:kGenre];
            // Country Name
            UILabel *countryL = (UILabel*) [cell.contentView viewWithTag:310];
            countryL.text = [aDic objectForKey:kCountryName];
            // View
            UIView  *aView = (UIView*)[cell.contentView viewWithTag:301];
            aView.hidden = NO;
            [self putStartsOnView:aView andRating:[[aDic objectForKey:kRating] floatValue]];
        }
        // BOOK 4 // Only For Landscape
        index = index + 1;
        if ([booksArray count] > index && [Utils isPortrait] == NO)
        {
            aDic = [booksArray objectAtIndex:index];
            // Book Btn
            UIButton *bookImage = (UIButton*)[cell.contentView viewWithTag:402];
            bookImage.backgroundColor = [UIColor lightGrayColor];
            bookImage.tag = index;
            [bookImage setContentMode:UIViewContentModeScaleAspectFit];
            [bookImage addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            NSString    *thumbImage = [aDic objectForKey:kProductThumbnail];
           // [bookImage setImageWithURL:[NSURL URLWithString:thumbImage] forState:UIControlStateNormal];
            [self downloadImageWithURL:[NSURL URLWithString:[NSString stringWithFormat:@"%@", thumbImage]]completionBlock:^(BOOL succeeded, UIImage *image) {
                if (succeeded) {
                    // change the image in the cell
                    //bookImage.image = image;
                    [bookImage setImage:image forState:UIControlStateNormal];
                    
                    // cache the image for use later (when scrolling up)
                    //venue.image = image;
                }}];
            [Utils setRoundWithLayer:bookImage cornerRadius:2.0 borderWidth:3.0 borderColor:kNavyBlue maskBounds:YES];
            // Get Btn
            UIButton *getBtn = (UIButton*)[cell.contentView viewWithTag:403];
            getBtn.tag = index;
            [getBtn addTarget:self action:@selector(bookClicked:) forControlEvents:UIControlEventTouchUpInside];
            // Group
            NSString    *isGroup = [aDic objectForKey:kIsFree];
            UILabel *groupL = (UILabel*) [cell.contentView viewWithTag:404];
            if([kTrue isEqualToString:isGroup])
            {
                groupL.hidden = NO;
                [Utils setRoundWithLayer:groupL cornerRadius:10.0 borderWidth:0.1 borderColor:[UIColor clearColor] maskBounds:YES];
            }
            else
                groupL.hidden = YES;
            
            // Title
            UILabel *titleL = (UILabel*) [cell.contentView viewWithTag:405];
            titleL.text = [aDic objectForKey:kTitle];
            // File Size
            UILabel *sizeL = (UILabel*) [cell.contentView viewWithTag:406];
            sizeL.text = [aDic objectForKey:kFileSize];
            // File Type
            UILabel *typeL = (UILabel*) [cell.contentView viewWithTag:407];
           // NSString    *price = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
            NSString    *price = [aDic objectForKey:@"price"];//price
            NSLog(@"price == %@==",price);
            if ([@"0" isEqualToString:price] || [@"null" isEqualToString:price]) {
                typeL.text = @"free";
            }else{
                NSString    *price1 = [[aDic objectForKey:kPriceText] stringByDecodingHTMLEntities];
                typeL.text = price1;
            }            //typeL.text = price;
            // Classification
            UILabel *classL = (UILabel*) [cell.contentView viewWithTag:408];
            classL.text = [aDic objectForKey:kCategoryName];
            // Category
            UILabel *CatNameL = (UILabel*) [cell.contentView viewWithTag:409];
            CatNameL.text = [aDic objectForKey:kGenre];
            // Country Name
            UILabel *countryL = (UILabel*) [cell.contentView viewWithTag:410];
            countryL.text = [aDic objectForKey:kCountryName];
            // View
            UIView  *aView = (UIView*)[cell.contentView viewWithTag:401];
            aView.hidden = NO;
            [self putStartsOnView:aView andRating:[[aDic objectForKey:kRating] floatValue]];
        }

        return cell;
    }
    return cell;
}

#pragma Downloading image from URL
- (void)downloadImageWithURL:(NSURL *)url completionBlock:(void (^)(BOOL succeeded, UIImage *image))completionBlock
{
    NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:url];
    [NSURLConnection sendAsynchronousRequest:request
                                       queue:[NSOperationQueue mainQueue]
                           completionHandler:^(NSURLResponse *response, NSData *data, NSError *error) {
                               if ( !error )
                               {
                                   UIImage *image = [[UIImage alloc] initWithData:data];
                                   completionBlock(YES,image);
                               } else{
                                   completionBlock(NO,nil);
                               }
                           }];
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    
    
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    if(tableView.tag == 201)  //Category Table
    {
        isCatagorySelected = YES;
        [self.mSegmentControl removeSegmentAtIndex:4 animated:YES];
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];
        for (int i = 101 ; i < 104; i++) {
            [(UIButton *)[self.view viewWithTag:i] setBackgroundColor:[UIColor clearColor]];
            
        }
        selectedCatagory = indexPath.row;
        // Call Library Api if not called
        if(libraryArray.count==0)
            [self sendLibraryRequest];
        else
        {
            NSDictionary    *aDic = [categoryArray objectAtIndex:indexPath.row];
            NSString    *genere = [aDic objectForKey:kGenre];
            [self libraryWithGenre:genere];
        }
        [self.categoryTable reloadData];
        
        if(selectedBook!=-1)
        {
            selectedBook = -1;

        }
        // Colors
        for (int i=101; i<=108; i++)
        {
            UIButton  *aBtn = (UIButton*)[self.toolView viewWithTag:i];
            if([aBtn isKindOfClass:[UIButton class]])
                [aBtn setTitleColor:kNavyBlue forState:UIControlStateNormal];
        }
        self.mSearchbar.text = nil;
    }else{
    isCatagorySelected = NO;
    }
}

- (void) putStartsOnView:(UIView*)aView andRating:(CGFloat) rating
{
    int rateInt = rating;
    int i;
    for (i=1; i<=rateInt; i++)
    {
        UIImageView *imgView = (UIImageView*)[aView viewWithTag:i+500];
        imgView.image = [Utils getImageFromResource:@"fullRate"];
    }
    
    float   rateFloat = rating - rateInt;
    if(rateFloat>0.0)
    {
        UIImageView *imgView = (UIImageView*)[aView viewWithTag:i+500];
        imgView.image = [Utils getImageFromResource:@"halfRate"];
    }
}

#pragma mark - SearchBar Delegates
- (BOOL)searchBarShouldBeginEditing:(UISearchBar *)searchBar
{
    return YES;
}

- (void)searchBar:(UISearchBar *)searchBar textDidChange:(NSString *)searchText
{
    if(tempArray.count==0)
    {
        ReleaseObj(tempArray);
        tempArray = [[NSMutableArray alloc] initWithArray:booksArray];
    }
    ReleaseObj(booksArray);
    booksArray = [[NSMutableArray alloc] init];
    NSString    *str = [searchBar.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    if(str.length>0)
    {
        for (int i=0; i<[tempArray count]; i++)
        {
            NSDictionary    *aDic = [tempArray objectAtIndex:i];
            NSString    *tempStr = nil;
            if([@"SEARCH BY NAME" isEqualToString:self.selectedType])
                tempStr = [aDic objectForKey:kTitle];
            else if([@"SEARCH BY AUTHOR" isEqualToString:self.selectedType])
                tempStr = [aDic objectForKey:kAuthorName];
            else if([@"SEARCH BY PUBLISHER" isEqualToString:self.selectedType])
                tempStr = [aDic objectForKey:kPublisher];
            else
                tempStr = [aDic objectForKey:kTitle];
            NSRange aRange = [tempStr rangeOfString:str options:NSCaseInsensitiveSearch];
            if (aRange.location != NSNotFound)
                [booksArray addObject:aDic];
        }
        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
        //self.mSegmentControl.selectedSegmentIndex = 0;
    }
    else if(str.length == 0)
    {
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:tempArray];
        ReleaseObj(tempArray);
        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
        //self.mSegmentControl.selectedSegmentIndex = 0;
    }
}

- (void)searchBarSearchButtonClicked:(UISearchBar *)searchBar
{
    [self.mSearchbar resignFirstResponder];
}

#pragma mark - Book Clicked
- (void) bookClicked:(id) sender
{
    [self.mSearchbar resignFirstResponder];
    UIButton *aBtn = (UIButton*)sender;
    NSInteger   aTag = [aBtn tag];
    selectedBook = aTag;
    NSDictionary    *aDict = [booksArray objectAtIndex:aTag];
    ReleaseObj(objDescriptionViewC);
    objDescriptionViewC = [[DescriptionViewC alloc] initWithBookData:aDict andImage:nil];
    objDescriptionViewC.delegate = self;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objDescriptionViewC] autorelease];
    NSInteger   row = aTag/3;
    if([Utils isPortrait] == NO)
        row = aTag/4;
    UITableViewCell *cell = [self.booksTable cellForRowAtIndexPath:[NSIndexPath indexPathForRow:row inSection:0]];
    [self.popoverC presentPopoverFromRect:aBtn.frame inView:[cell.contentView viewWithTag:aTag] permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(400, 400);
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

- (void) purchaseBook:(id)delegateMethod
{
    [self.popoverC dismissPopoverAnimated:YES];
    NSDictionary    *aDict = [booksArray objectAtIndex:selectedBook];
    NSString    *bookId = [aDict objectForKey:kId];
    selectedBookId = bookId;
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
        [Utils showOKAlertWithTitle:kAlertTitle message:@"You have already purchased this book."];
        return;
    }
    
    NSString    *urlString = [aDict objectForKey:kProductURL];
    if(urlString!=nil && ![@"(null)" isEqualToString:urlString])
    {
        //NSLog(@"Product URL: %@", urlString);
        
        //=============== checking purchased or unpurcheased books================

        NSString *pStatus = [aDict objectForKey:@"is_free"];
        if([@"false" isEqualToString:pStatus]){
            /*
            UIAlertView *tmpAlert = [[UIAlertView alloc] initWithTitle:@"Buy"
                                                               message:@"You have to purchase from website."
                                                              delegate:self
                                                     cancelButtonTitle:@"Cancel"
                                                     otherButtonTitles:@"Buy Now",nil];
             
             */
            
            UIAlertView *tmpAlert = [[UIAlertView alloc] initWithTitle:@"Download Info"
                                                               message:@"Please note that publication can be downloaded only when they are purchased. Publication can not be purchased from this app. However, once a publication is in your dashboard you can read the publications here, with the great reading experience you'll come to love."
                                                              delegate:self
                                                     cancelButtonTitle:@"Thank You"
                                                     otherButtonTitles:nil];
            

            
            [tmpAlert show];
            
            //DescriptionViewC *popView = (DescriptionViewC *)delegateMethod;
            // [popView.downloadBtn setTitle:@"Buy Now" forState:UIControlStateNormal];
            //            if ([popView.view viewWithTag:50]) {
            //                UIButton *downloadBtn = (UIButton *)[delegateMethod viewWithTag:50];
            //                downloadBtn.titleLabel.text = @"Buy Now";
            //            }
            
            //            self.singlePuchaseBookDic = [NSDictionary dictionaryWithDictionary:aDict];
            //            currentRequestType = GetBookPurchaseStatus;
            //            NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
            //            NSString *bookId = [self.singlePuchaseBookDic objectForKey:kId];
            //            NSString *urlString = [NSString stringWithFormat:@"http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/purchasedBooks/apikey/998905797b8646fd44134910d1f88c33/userid/%@/bookid/%@",userId,bookId];
            //            [Host host].delegate = self;
            //            [[Host host] requestWithUrl:urlString];
            //            NSLog(@"Purchased ==========  %@",urlString);
        }else{
            zeeDownloadViewObj.currentInfoDict = aDict;
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
            
        }
        //=============== checking purchased or unpurcheased books================
        
    }
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Book not found."];
}

- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex {
    if (buttonIndex==0){
    
    }else{
        /*
        NSUserDefaults *uDefault = [NSUserDefaults standardUserDefaults];
        NSString *countryId1 = [uDefault objectForKey:kCountryId];
        
        NSString *urlString = [NSString stringWithFormat:@"http://evendornigeria.com/catalogue/detail/id/%@/store/%@/lang/1",selectedBookId,countryId1];
        NSLog(@"Url string == %@",urlString);
        //942261/
        NSURL *url = [NSURL URLWithString:urlString];
        
        [[UIApplication sharedApplication] openURL:url];
         */

    }
            
}

- (void) selectedPickerValue:(NSDictionary*) aDic
{
    [self.popoverC dismissPopoverAnimated:YES];
    //NSLog(@"Id: %@", [aDic objectForKey:kStoreName]);
    if(aDic.count>0 && self.selectedToolBtn.tag == 107)  // Local Search //106
    {
        self.selectedType = [aDic objectForKey:kStoreName];
        [self.selectedToolBtn setTitle:[aDic objectForKey:kStoreName] forState:UIControlStateNormal];
    }
    else if(aDic.count>0 && self.selectedToolBtn.tag == 104)  // Store
    {
        self.countryId = [aDic objectForKey:kId];
        [self.selectedToolBtn setTitle:[aDic objectForKey:kStoreName] forState:UIControlStateNormal];
        //[self refreshClicked];
        [self storeDoneClicked];
    }
    else if(aDic.count>0 && self.selectedToolBtn.tag == 105)  // Sort
    {
        NSString    *aStr = [aDic objectForKey:kStoreName];
        NSString    *sortKey = nil;
        BOOL        accending = NO;
        if([@"A TO Z" isEqualToString:aStr])
        {
            sortKey = kTitle;
            accending = YES;
        }
        else if([@"Z TO A" isEqualToString:aStr])
        {
            sortKey = kTitle;
            accending = NO;
        }
        else if([@"PRICE HIGHEST" isEqualToString:aStr])
        {
            sortKey = kPrice;
            accending = NO;
        }
        else if([@"PRICE LOWEST" isEqualToString:aStr])
        {
            sortKey = kPrice;
            accending = YES;
        }
        else
        {
            sortKey = kTitle;
            accending = YES;
        }
        [self sortBooksWithKey:sortKey andAccending:accending];
    }
}

#pragma mark - Sort Books
- (void) sortBooksWithKey:(NSString*) sortKey andAccending:(BOOL) accending
{
    if([kPrice isEqualToString:sortKey])
    {
        NSSortDescriptor *aSortDescriptor = [NSSortDescriptor sortDescriptorWithKey:sortKey ascending:accending comparator:^(id obj1, id obj2) {
            
            if ([obj1 integerValue] > [obj2 integerValue]) {
                return (NSComparisonResult)NSOrderedDescending;
            }
            if ([obj1 integerValue] < [obj2 integerValue]) {
                return (NSComparisonResult)NSOrderedAscending;
            }
            return (NSComparisonResult)NSOrderedSame;
        }];
        NSArray *descriptors = [NSArray arrayWithArray:[booksArray sortedArrayUsingDescriptors:[NSArray arrayWithObjects:aSortDescriptor,nil]]];
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:descriptors];
        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//        self.mSegmentControl.selectedSegmentIndex = 0;
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];

    }
    else
    {
        NSSortDescriptor *descriptor = [[NSSortDescriptor alloc] initWithKey:sortKey ascending:accending];
        NSArray *descriptors = [NSArray arrayWithArray:[booksArray sortedArrayUsingDescriptors:[NSArray arrayWithObjects:descriptor,nil]]];
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:descriptors];
        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
       //self.mSegmentControl.selectedSegmentIndex = 0;
        ReleaseObj(descriptor);
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];

    }
}

#pragma mark - Tool Clicked
- (IBAction)toolButtonClicked:(id)sender
{
    self.selectedCat = @"";
    UIButton    *aBtn = (UIButton*)sender;
    self.selectedToolBtn = aBtn;
    // Colors
    for (int i=101; i<=109; i++)
    {
        UIButton  *aBtn = (UIButton*)[self.toolView viewWithTag:i];
        if([aBtn isKindOfClass:[UIButton class]])
            [aBtn setTitleColor:kNavyBlue forState:UIControlStateNormal];
    }
    [aBtn setTitleColor:kWhiteColor forState:UIControlStateNormal];
    
    for (int i = 101;i < 104; i++) {
        UIButton *tmpButton = (UIButton *)[self.view viewWithTag:i];
        [tmpButton setBackgroundColor:[UIColor clearColor]];
    }
    
    
    NSInteger   aTag = aBtn.tag;
    
    switch (aTag)
    {
        case 101:
        {
            
            selectedIndex = aTag;
            if ([self.mSegmentControl numberOfSegments] < 5) {
                [self.mSegmentControl insertSegmentWithTitle:self.todayWeekMonthOptionString atIndex:4 animated:YES];
            }
            
            [aBtn setBackgroundColor:[UIColor blueColor]];
            // Featured
            if(featuredArray.count>0)
            {
                ReleaseObj(booksArray);
                booksArray = [[NSMutableArray alloc] initWithArray:featuredArray];
//                [self.booksTable reloadData];
                ReleaseObj(backupBookArray);
                backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//                self.mSegmentControl.selectedSegmentIndex = 0;
               // UIButton *tmpButton = (UIButton *)[self.view viewWithTag:1003]; // Selecting default button "This Month"
               // [self todayWeekMonthOptionMethod:tmpButton];
                [self.booksTable reloadData];
               // [self rel];
            }
            else
            {
                ReleaseObj(booksArray);
                currentRequestType = FeaturedRequest;
                [self getLibraryDataWithMethod:kFeaturedApi];
            }
            self.mSearchbar.text = nil;
            
           
        }
            break;
        case 102:
        {
            selectedIndex =  aTag;
            if ([self.mSegmentControl numberOfSegments] < 5) {
                [self.mSegmentControl insertSegmentWithTitle:self.todayWeekMonthOptionString atIndex:4 animated:YES];
            }
            [aBtn setBackgroundColor:[UIColor blueColor]];
            // New Arrivals
            if(newArrivalArray.count>0)
            {
                ReleaseObj(booksArray);
                booksArray = [[NSMutableArray alloc] initWithArray:newArrivalArray];
//                [self.booksTable reloadData];
                ReleaseObj(backupBookArray);
                backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//                self.mSegmentControl.selectedSegmentIndex = 0;
               // UIButton *tmpButton = (UIButton *)[self.view viewWithTag:1003]; // Selecting default button "This Month"
               // [self todayWeekMonthOptionMethod:tmpButton];
                [self.booksTable reloadData];
            }
            else
            {
                ReleaseObj(booksArray);
                currentRequestType = NewArrivalsRequest;
                [self getLibraryDataWithMethod:kNewArrivalsApi];
            }
            self.mSearchbar.text = nil;
            
        }
            break;
        case 103:
        {
            selectedIndex =  aTag;
            if ([self.mSegmentControl numberOfSegments] < 5) {
                [self.mSegmentControl insertSegmentWithTitle:self.todayWeekMonthOptionString atIndex:4 animated:YES];
            }
            [aBtn setBackgroundColor:[UIColor blueColor]];
            // Top Sellers
            if(topSellerArray.count>0)
            {
                ReleaseObj(booksArray);
                booksArray = [[NSMutableArray alloc] initWithArray:topSellerArray];
//                [self.booksTable reloadData];
                ReleaseObj(backupBookArray);
                backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//                self.mSegmentControl.selectedSegmentIndex = 0;
              //  UIButton *tmpButton = (UIButton *)[self.view viewWithTag:1003]; // Selecting default button "This Month"
               // [self todayWeekMonthOptionMethod:tmpButton];
                [self.booksTable reloadData];

            }
            else
            {
                ReleaseObj(booksArray);
                currentRequestType = BestSellersRequest;
                [self getLibraryDataWithMethod:kBestSellersApi];
            }
            self.mSearchbar.text = nil;
                    }
            
            break;
        case 104:
        {
            selectedIndex =  0;
            // Stores
            if(countryArray.count>0)
            {
                NSArray *arr = [NSArray arrayWithArray:countryArray];
                //[self loadPickerWith:arr andSearch:NO];
                [self loadCountryListWithArr:arr];
            }
            else
                [self getStoreLists];
            self.mSearchbar.text = nil;
        }
            break;
        case 105:
        {
            selectedIndex =  0;
            // Sort by
            [self loadSortingPicker:[NSArray array]];
            self.mSearchbar.text = nil;
        }
            break;
        case 106:
        {
           selectedIndex =  0;
            // Group Book
            if(groupBooksArray.count>0)
            {
                ReleaseObj(booksArray);
                booksArray = [[NSMutableArray alloc] initWithArray:groupBooksArray];
                [self.booksTable reloadData];
                ReleaseObj(backupBookArray);
                backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
                self.mSegmentControl.selectedSegmentIndex = 0;
            }
            else
                [self getAllGroupBooks];
            self.mSearchbar.text = nil;        }
            break;
        case 109:
        {
            selectedIndex =  0;
            // All Store Search
            NSString    *str = [self.mSearchbar.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            if(str.length>0)
                [self allStoreSearch:str];
            else
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter a search string"];
        }
            break;
        case 107: //108
        {
            selectedIndex =  0;
            // Local Search
            [self loadPickerWith:[NSArray array] andSearch:YES];
            self.mSearchbar.text = nil;

        }
            break;
        default:
            break;
    }
    
    if(selectedCatagory!=-1)
    {
        selectedCatagory = -1;
        [self.categoryTable reloadData];
    }
}



- (void) loadPickerWith:(NSArray*) arr andSearch:(BOOL) isSearch
{
    ReleaseObj(objPicker);
    objPicker = [[PickerViewC alloc] initWithArray:arr];
    objPicker.delegate = self;
    objPicker.isSort = NO;
    if(isSearch == YES)
        objPicker.isSearch = YES;
    else
        objPicker.isSearch = NO;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objPicker] autorelease];
    [self.popoverC presentPopoverFromRect:self.selectedToolBtn.frame inView:self.toolView permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(350, 300);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
}

- (void) loadSortingPicker:(NSArray*) arr
{
    ReleaseObj(objPicker);
    objPicker = [[PickerViewC alloc] initWithArray:arr];
    objPicker.delegate = self;
    objPicker.isSort = YES;
    objPicker.isSearch = NO;
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objPicker] autorelease];
    [self.popoverC presentPopoverFromRect:self.selectedToolBtn.frame inView:self.toolView permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    if ([Utils isiOS8]) {
        self.popoverC.popoverContentSize = CGSizeMake(350, 300);
    }
    else{
        //self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    self.popoverC.backgroundColor = kBgColor;
}

- (void) loadCountryListWithArr:(NSArray*) arr
{
    ReleaseObj(objCountryViewC);
    objCountryViewC = [[CountryViewC alloc] initWithCountryArray:arr];
    objCountryViewC.delegate = self;
   
    ReleaseObj(popoverC);
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objCountryViewC] autorelease];
    //UIButton *tmpButton = (UIButton *)
    self.popoverC.backgroundColor = kBgColor;
   [self.popoverC presentPopoverFromRect:self.selectedToolBtn.frame inView:self.toolView permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];//
    
    
}

#pragma - Orientation
- (BOOL)shouldAutorotate
{
    return YES;
}

#pragma mark - Genre Filter
- (void) libraryWithGenre:(NSString *) genre
{
    self.selectedCat = [NSString stringWithString:genre];
    NSLog(@"==========genre == %@",genre);
    ReleaseObj(booksArray);
    booksArray = [[NSMutableArray alloc] init];
    
    for (int i=0; i<[libraryArray count]; i++)
    {
        NSDictionary    *aDic = [libraryArray objectAtIndex:i];
        NSString    *aStr = [aDic objectForKey:kGenre];
        if([genre isEqualToString:aStr])
        {
            [booksArray addObject:aDic];
        }
    }
    [self.booksTable reloadData];
    ReleaseObj(backupBookArray);
    backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
    //self.mSegmentControl.selectedSegmentIndex = 0;
    [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];
}

- (void) sendCategoryRequest
{
    // Category
    currentRequestType = CategoryRequest;
    [self getLibraryDataWithMethod:kCategoryApi];
}

- (void) sendLibraryRequest
{
    // Category
    currentRequestType = LibraryRequest;
    [self getLibraryDataWithMethod:kLibraryApi];
}

#pragma mark -  Get Books
- (void) getLibraryDataWithMethod:(NSString*)method
{
    NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@",kBaseURL,method,kApiKey,kStoreId,self.countryId,@"UserId",userId];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
    NSLog(@"getLibraryDataWithMethod == %@",requestUrl);
}

#pragma mark - Get Store Lists
- (void) getStoreLists
{
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@",kBaseURL,kStoresApi,kApiKey,kStoreId];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    currentRequestType = CountryRequest;
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
}

#pragma mark - All Store Search
- (void) allStoreSearch:(NSString*) searchText
{
    [self.mSearchbar resignFirstResponder];
    NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/%@/%@",kBaseURL,kAllStoreSearchApi,kApiKey,kStoreId,self.countryId,kSearchKey,searchText, @"UserId",userId];
    currentRequestType = AllStoreSearchRequest;
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
}

#pragma mark - Group Books
- (void) getAllGroupBooks
{
    NSString    *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@",kBaseURL,kGroupBooksApi,kApiKey,@"UserId",userId];
    currentRequestType = GetAllGroupBooksRequest;
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
}

#pragma mark - Server Response Delegate Methods
-(void) getResult:(NSDictionary*) dataDict
{
    NSLog(@"Data dict in library ==== %@",dataDict);
    [Utils stopActivityIndicator:self.view];
    if(!dataDict.count>0)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No items found"];
        return;
    }
    
    NSString    *error = [dataDict objectForKey:kError];
    if([@"true" isEqualToString:error])
    {
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showOKAlertWithTitle:kAlertTitle message:msg];
        //return;
    }
    
    //Sudesh
    if (currentRequestType == GetBookPurchaseStatus) {
        NSString *pResult = [dataDict objectForKey:@"error"];
        NSString *productUrl = [self.singlePuchaseBookDic objectForKey:kProductURL];
        if ([@"false" isEqualToString:pResult]) {
            //NSString    *urlString = [aDict objectForKey:kProductURL];
            zeeDownloadViewObj.currentInfoDict = self.singlePuchaseBookDic;
            [zeeDownloadViewObj addDownloadRequest:productUrl];
            [self.navigationController pushViewController:zeeDownloadViewObj animated:YES];

        }else{
//            UIAlertView *purchaseAlert = [[UIAlertView alloc] initWithTitle:@"Purchase error!"
//                                                                message:@"Sorry! You have to purchase this item from the webside"
//                                                               delegate:nil
//                                                      cancelButtonTitle:@"OK"
//                                                      otherButtonTitles:nil];
//            [purchaseAlert show];
        }
    }else if(currentRequestType == CountryRequest)
    {
        ReleaseObj(countryArray);
        countryArray = [[NSMutableArray alloc] initWithArray:[self removeNullStore:[dataDict objectForKey:kAllStores]]];
        //[self loadPickerWith:[dataDict objectForKey:kAllStores] andSearch:NO];
        //NSLog(@"%@",[self removeNullStore:[dataDict objectForKey:kAllStores]]);
        
        [self loadCountryListWithArr:countryArray];
        //[self loadCountryListWithArr:[dataDict objectForKey:kAllStores]];


    }
    else if(currentRequestType == FeaturedRequest)
    {
        ReleaseObj(featuredArray);
        featuredArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kFeatured]];
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:featuredArray]; //sud
        ReleaseObj(self.tmpArrayForWeek);
        self.tmpArrayForWeek = [[NSMutableArray alloc] initWithArray:featuredArray];
        // Call Category Api if not called
        if(categoryArray.count==0)
            [self sendCategoryRequest];
//
//        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//        self.mSegmentControl.selectedSegmentIndex = 0;
        //UIButton *tmpButton = (UIButton *)[self.view viewWithTag:1003];
       // [self todayWeekMonthOptionMethod:tmpButton];
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];

        
    }
    else if(currentRequestType == NewArrivalsRequest)
    {
        ReleaseObj(newArrivalArray);
        newArrivalArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kNewArrivals]];
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:newArrivalArray];
//        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//        self.mSegmentControl.selectedSegmentIndex = 0;
        //UIButton *tmpButton = (UIButton *)[self.view viewWithTag:1003];
        //[self todayWeekMonthOptionMethod:tmpButton];
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];


    }
    else if(currentRequestType == BestSellersRequest)
    {
        ReleaseObj(topSellerArray);
        topSellerArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kBestSellers]];
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:topSellerArray];
//        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
//        self.mSegmentControl.selectedSegmentIndex = 0;
       // UIButton *tmpButton = (UIButton *)[self.view viewWithTag:1003]; //sud
        //[self todayWeekMonthOptionMethod:tmpButton];
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];


    }
    else if(currentRequestType == LibraryRequest)
    {
        ReleaseObj(libraryArray);
        libraryArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kLibrary]];
        
        NSDictionary    *aDic = [categoryArray objectAtIndex:selectedCatagory];
        NSString    *genere = [aDic objectForKey:kGenre];
        [self libraryWithGenre:genere];
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];

    }
    else if(currentRequestType == CategoryRequest)
    {
        ReleaseObj(categoryArray);
        categoryArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kAllCategories]];
        [self.categoryTable reloadData];
    }
    else if(currentRequestType == AllStoreSearchRequest)
    {
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kLibrary]];
        
        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
        //self.mSegmentControl.selectedSegmentIndex = 0;
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];
        [self.mSegmentControl removeSegmentAtIndex:4 animated:YES];


    }
    else if(currentRequestType == GetAllGroupBooksRequest)
    {
        ReleaseObj(groupBooksArray);
        groupBooksArray = [[NSMutableArray alloc] initWithArray:[dataDict objectForKey:kGroupBooks]];
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:groupBooksArray];
        [self.booksTable reloadData];
        ReleaseObj(backupBookArray);
        backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];
        //self.mSegmentControl.selectedSegmentIndex = 0;
        [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];
        [self.mSegmentControl removeSegmentAtIndex:4 animated:YES];


    }
}

//Removing null or empty dictionary from store array

//-(NSArray *)removeNullStore:(NSArray *)storeArray{
//    NSMutableArray *tmpArray = [[NSMutableArray alloc] init];
//    for (NSDictionary *tmpDict in storeArray) {
//        NSString *cName = [tmpDict objectForKey:@"store"];
//        NSLog(@"--%@ %d\n",cName,cName.length);
//        if ([@"" isEqualToString:cName ]) {
//        }else{
//            [tmpArray addObject:tmpDict];
//
//        }
//    }
//    return (NSArray *)tmpArray;
//}
-(NSArray *)removeNullStore:(NSArray *)storeArray{
    NSMutableArray *tmpArray = [[NSMutableArray alloc] init];
    for (NSDictionary *tmpDict in storeArray) {
        NSString *cName = [tmpDict objectForKey:@"store"];
        NSLog(@"--%@ %d\n",cName,cName.length);
        if ([@"" isEqualToString:cName ]) {
        }else{
            [tmpArray addObject:tmpDict];
            
        }
    }
    
    NSMutableArray *storeList = [[NSMutableArray alloc] initWithArray:tmpArray];
    
    id object = [[[storeList objectAtIndex:[storeList count] - 1] retain] autorelease];
    [storeList removeObjectAtIndex:[storeList count] - 1];
    [storeList insertObject:object atIndex:0];
    
    return (NSArray *)storeList;
}

-(void) didFailToGetResult
{
    [Utils stopActivityIndicator:self.view];
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
   // [zeeDownloadViewObj ];
    [self.navigationController pushViewController:zeeDownloadViewObj animated:YES];
}


#pragma mark - Category Filters
- (IBAction)segmentChanged:(id)sender
{
    NSInteger   segmentIndex = [(UISegmentedControl*)sender selectedSegmentIndex];
    segmentSelectedIndex = [(UISegmentedControl*)sender selectedSegmentIndex];
    switch (segmentIndex)
    {
        case 0:
        {
            // News Paper
            [self filterBooksWithCatId:@"1"];
           
        }
            break;
        case 1:
        {
            // Magazine
            [self filterBooksWithCatId:@"2"];
        }
            break;
        case 2:
        {
            // Book
            
            [self filterBooksWithCatId:@"3"];
            
        }
            break;
        case 3:
        {
            // Brochures
            [self filterBooksWithCatId:@"5"];
        }
            break;
        case 4:
        {
            NSLog(@"Today week selected");
            self.today_week_monthView.hidden = NO;

        }
            break;
        
        default:
            break;
    }
}

- (void) filterBooksWithCatId:(NSString*) catId
{
    
    NSMutableArray  *tempArr = [[NSMutableArray alloc] init];
    if (isCatagorySelected) {
        for (int i=0; i<[backupBookArray count]; i++)
        {
            NSDictionary    *aDic = [backupBookArray objectAtIndex:i];
            NSString    *cId = [aDic objectForKey:kCategory];
            if([catId isEqualToString:cId])
                [tempArr addObject:aDic];
        }
    }else{
        
        
        for (int i=0; i<[backupBookArray count]; i++)
            {
                NSDictionary    *aDic = [backupBookArray objectAtIndex:i];
                NSString    *cId = [aDic objectForKey:kCategory];
                if([catId isEqualToString:cId])
                    [tempArr addObject:aDic];
            }

        
    }
    
    ReleaseObj(booksArray);
    booksArray = [[NSMutableArray alloc] initWithArray:tempArr];
    [self.booksTable reloadData];
    ReleaseObj(tempArr);
    if([catId isEqualToString:@"1"]){
        if(booksArray.count==0){
            NSLog(@"detail is %@",backupBookArray);
            if ([self.selectedCat length]) {
                NSString *msgStr = [NSString stringWithFormat:@"No newspaper found in the %@ category",selectedCat];
                 [Utils showOKAlertWithTitle:kAlertTitle message:msgStr];
                self.selectedCat = @"";
            }else{
                
                if (selectedIndex == 101) {
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No Featured newsPaper found. "];
                }else if(selectedIndex == 102){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No newspaper found in the New Arrivals."];
                }else if(selectedIndex == 103){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No newspaper found in the Top Sellers."];
                }else{
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No newspaper found."];
                }

            }
        }
    }
    if([catId isEqualToString:@"2"]){
        if(booksArray.count==0){
            
            if ([self.selectedCat length]) {
                NSString *msgStr = [NSString stringWithFormat:@"No magazine found in the %@ category",selectedCat];
                [Utils showOKAlertWithTitle:kAlertTitle message:msgStr];
                self.selectedCat = @"";
            }else{

                if (selectedIndex == 101) {
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No Featured magazine found. "];
                }else if(selectedIndex == 102){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No magazine found in the New Arrivals."];
                }else if(selectedIndex == 103){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No magazine found in the Top Sellers."];
                }else{
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No magazine found. "];
                }
            }
        }
    }
    if([catId isEqualToString:@"3"]){
        if(booksArray.count==0){
            if ([self.selectedCat length]) {
                NSString *msgStr = [NSString stringWithFormat:@"No book found in the %@ category",selectedCat];
                [Utils showOKAlertWithTitle:kAlertTitle message:msgStr];
                self.selectedCat = @"";
            }else{

                if (selectedIndex == 101) {
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No Featured book found."];
                }else if(selectedIndex == 102){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No book found in the New Arrivals."];
                }else if(selectedIndex == 103){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No book found in the Top Sellers."];
                }else{
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No book found. "];
                }
            }
        }
    }
    if([catId isEqualToString:@"4"]){
        if(booksArray.count==0)
            
            [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No Today week found "];
    }
    if([catId isEqualToString:@"5"]){
        if(booksArray.count==0){
            if ([self.selectedCat length]) {
                NSString *msgStr = [NSString stringWithFormat:@"No brochures found in the %@ category",selectedCat];
                [Utils showOKAlertWithTitle:kAlertTitle message:msgStr];
                self.selectedCat = @"";
            }else{

                if (selectedIndex == 101) {
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No Featured brochures found. "];
                }else if(selectedIndex == 102){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No brochures found in the New Arrivals."];
                }else if(selectedIndex == 103){
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No brochures found in the Top Sellers."];
                }else{
                    [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No brochures found."];
                }
            }
        }
    }


}


#pragma Today/Week/Month oprtion method
- (IBAction)todayWeekMonthOptionMethod:(id)sender{
    
    for (UIButton *btn in [self.today_week_monthView subviews]) {
        btn.backgroundColor = [UIColor blackColor];//kNavyBlue;
        [btn setTitleColor:kNavyBlue forState:UIControlStateNormal];
    }
    
    UIButton  *tmpButton = (UIButton *)sender;
    switch (tmpButton.tag) {
        case 1001:
            self.todayWeekMonthOptionString = @"Today";
            tmpButton.backgroundColor = kNavyBlue;
            [tmpButton setTitleColor:[UIColor blackColor] forState:UIControlStateNormal];
            break;
        case 1002:
            self.todayWeekMonthOptionString = @"This Week";
            tmpButton.backgroundColor = kNavyBlue;
            [tmpButton setTitleColor:[UIColor blackColor] forState:UIControlStateNormal];
            break;
        case 1003:
            self.todayWeekMonthOptionString = @"This Month";
            tmpButton.backgroundColor = kNavyBlue;
            [tmpButton setTitleColor:[UIColor blackColor] forState:UIControlStateNormal];
            break;
        default:
            break;
    }
    self.today_week_monthView.hidden = YES;
    if ([self.mSegmentControl numberOfSegments] == 5) {
        [self.mSegmentControl setTitle:self.todayWeekMonthOptionString forSegmentAtIndex:4];
    }
    [self filterBooksWithToday_Week_Month:self.todayWeekMonthOptionString];
    [self.mSegmentControl setSelectedSegmentIndex:UISegmentedControlNoSegment];

    //NSLog(@"Backup book %@",featuredArray);
}

- (void) filterBooksWithToday_Week_Month:(NSString*) type
{
    NSLog(@"Books == %@",booksArray);
    NSDateComponents *components = [[NSCalendar currentCalendar] components:NSCalendarUnitDay | NSCalendarUnitMonth | NSCalendarUnitYear fromDate:[NSDate date]];
    
    NSInteger day = [components day];
    NSInteger month = [components month];
    NSInteger year = [components year];
    
    NSCalendar *gregorian = [[[NSCalendar alloc] initWithCalendarIdentifier:NSGregorianCalendar] autorelease];
    NSDateComponents *comps = [gregorian components:NSWeekdayCalendarUnit fromDate:[NSDate date]];
    int weekday = [comps weekday];
    
    NSMutableArray  *tempArr = [[NSMutableArray alloc] init];
    
    if ([self.todayWeekMonthOptionString isEqualToString:@"This Month"] && selectedIndex == matchedIndex) {
        ReleaseObj(booksArray);
        booksArray = [[NSMutableArray alloc] initWithArray:self.tmpArrayForWeek];
        //NSLog(@"TmpArrayForWeek === %@",self.tmpArrayForWeek);
    }
    
    for (int i=0; i<[booksArray count]; i++)
    {
        NSDictionary    *aDic = [booksArray objectAtIndex:i];
        NSString    *pTime = [aDic objectForKey:@"add_time"]; //sud
        NSArray *dateComponent = [[[pTime componentsSeparatedByString:@" "] objectAtIndex:0] componentsSeparatedByString:@"-"];
        
        if ([[dateComponent objectAtIndex:0] intValue] == year) {
            if ([[dateComponent objectAtIndex:1] intValue] == month) {
                if ([@"Today" isEqualToString:self.todayWeekMonthOptionString]) {
                    if (day == [[dateComponent objectAtIndex:2] intValue]) {
                        [tempArr addObject:aDic];
                    }
                }else if ([@"This Week" isEqualToString:self.todayWeekMonthOptionString]) {
                    if (month == [[dateComponent objectAtIndex:1] intValue]) {
                        int calWeekDay = day - (weekday - 1);
                        if ( [[dateComponent objectAtIndex:2] intValue] >= calWeekDay) {
                            [tempArr addObject:aDic];
                        }
                    }
                }else if ([@"This Month" isEqualToString:self.todayWeekMonthOptionString]) {
                    //if (month == [[dateComponent objectAtIndex:1] intValue]) {
                        [tempArr addObject:aDic];
                    //}
                }
                
            }
        }
    }
    ReleaseObj(booksArray);
    booksArray = [[NSMutableArray alloc] initWithArray:tempArr];
    backupBookArray = [[NSMutableArray alloc] initWithArray:booksArray];//sud
    
    if ([self.todayWeekMonthOptionString isEqualToString:@"This Month"]) {
        matchedIndex = selectedIndex;
        ReleaseObj(self.tmpArrayForWeek);
        self.tmpArrayForWeek = [[NSMutableArray alloc] initWithArray:booksArray];
    }
    
    [self.booksTable reloadData];
    ReleaseObj(tempArr);
    if(booksArray.count==0)
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Sorry! No books found corresponding this category"];
}

@end
