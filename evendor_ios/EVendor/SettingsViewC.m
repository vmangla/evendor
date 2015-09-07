//
//  SettingsViewC.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "SettingsViewC.h"
#import "PickerViewC.h"
#import "HostSettings.h"


//UIImageView *aImageView = (UIImageView*)[cell.contentView viewWithTag:102];
//NSString    *flagImage = [aDic objectForKey:@"country_flag_url"];
//[aImageView setImageWithURL:[NSURL URLWithString:flagImage]];

@interface SettingsViewC ()

@end

@implementation SettingsViewC
@synthesize countryId, popoverC;

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
    [countryArray release];
    [objPicker release];
    [countryId release];
    [popoverC release];
    [mainArray release];
    [_mTableView release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    self.navigationItem.title = @"Settings";
    selectedCatagory = 0;
    mainArray = [[NSMutableArray alloc] initWithObjects:@"Profile Settings",@"Change Password",@"About",@"Sign Out", nil];
    [self.mTableView reloadData];
    [Utils addNavigationItm:self];

    
    // Refresh Button On Navigation
    UIBarButtonItem *refreshBtn = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemRefresh target:self action:@selector(refreshClicked)];
    self.navigationItem.rightBarButtonItem = refreshBtn;
    ReleaseObj(refreshBtn);

    isProfileUpdate = YES;
    [self profileSettingsView];
    
    // Get Profile Details
    [self getUserDetails];
}

- (void) viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    self.countryId = [[NSUserDefaults standardUserDefaults] objectForKey:kCountryId];
}

- (void) refreshClicked
{
    selectedCatagory = 0;
    [self.mTableView reloadData];
    [self profileSettingsView];
    // Get Profile Details
    [self getUserDetails];
}


- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark UITableView delegate and datasource method
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    return 44.0;
}
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
    static NSString *cellIdentifier=@"cell";
    UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==nil)
    {
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:cellIdentifier] autorelease];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
    }
    cell.backgroundColor = [UIColor clearColor];
    cell.textLabel.text = [mainArray objectAtIndex:indexPath.row];
    cell.textLabel.textColor = kNavyBlue;
    if(indexPath.row == selectedCatagory)
        cell.textLabel.textColor = kWhiteColor;
    cell.textLabel.textAlignment = NSTextAlignmentLeft;
    cell.accessoryType = UITableViewCellAccessoryDisclosureIndicator;
    return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    selectedCatagory = indexPath.row;
    if(indexPath.row == 0) // Profile Settings
    {
        [self profileSettingsView];
    }
    else if(indexPath.row == 1) // Change Password
    {
        [self changePasswordView];
    }
    else if(indexPath.row == 2) // Sign out
    {
            if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
                [self aboutUsMethod:NO];
            else
                [self aboutUsMethod:YES];
       
    }else if(indexPath.row == 3) // Sign out
    {
        [Utils showAlertViewWithTag:501 title:kAlertTitle message:@"Are you sure you want to sign out?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
    }
    [self.mTableView reloadData];
}

#pragma mark - Profile Settings
- (void) profileSettingsView
{
    [self removeAllChild];
    currentRequestType = ChangeProfileRequest;
    isProfileUpdate = YES;
    UIFont  *aFont = [UIFont systemFontOfSize:17.0];
    CGFloat xx = 300.0, yy = 150.0, ww = 150, hh = 30.0;
    CGRect aRect = CGRectMake(xx, yy, ww, hh);
    // User
    UILabel *user = [Utils createNewLabelWithTag:1011 aRect:aRect text:@"Email:" noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:user];
    xx = xx + user.frame.size.width + 20;
    ww = 250;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // Username
    UILabel *userName = [Utils createNewLabelWithTag:1011 aRect:aRect text:[[NSUserDefaults standardUserDefaults] objectForKey:kUserName] noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:userName];
    userName.textAlignment = NSTextAlignmentLeft;
    xx = 300.0;
    yy = yy + userName.frame.size.height + 20;
    ww = 150;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    //////////
    // First Name
    UILabel *firstName = [Utils createNewLabelWithTag:1011 aRect:aRect text:@"First Name:" noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:firstName];
    xx = xx + firstName.frame.size.width + 20;
    ww = 250;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // First Name Input
    UITextField *firstNameT = [Utils createTextFieldWithTag:101 aFrame:aRect aFont:aFont aPlaceholder:@"First Name" aTextColor:[UIColor blackColor] aKeyboardType:UIKeyboardTypeDefault];
    firstNameT.text = [[NSUserDefaults standardUserDefaults] objectForKey:kFirstName];
    firstNameT.returnKeyType = UIReturnKeyNext;
    [self.view addSubview:firstNameT];
    firstNameT.tag = 101;
    firstNameT.secureTextEntry = NO;
    xx = 300.0;
    yy = yy + firstNameT.frame.size.height + 20;
    ww = 150;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // Last Name
    UILabel *lastName = [Utils createNewLabelWithTag:1011 aRect:aRect text:@"Last Name:" noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:lastName];
    xx = xx + lastName.frame.size.width + 20;
    ww = 250;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // Last Name Input
    UITextField *lastNameT = [Utils createTextFieldWithTag:102 aFrame:aRect aFont:aFont aPlaceholder:@"Last Name" aTextColor:[UIColor blackColor] aKeyboardType:UIKeyboardTypeDefault];
    lastNameT.text = [[NSUserDefaults standardUserDefaults] objectForKey:kLastName];
    lastNameT.returnKeyType = UIReturnKeyDone;
    //lastNameT.tag = 111;//Tag assigned to identify change password text field return
    [self.view addSubview:lastNameT];
    lastNameT.tag = 102;
    lastNameT.secureTextEntry = NO;
    xx = 300.0;
    yy = yy + lastNameT.frame.size.height + 20;
    ww = 150;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    ///////////
    // Store
    UILabel *store = [Utils createNewLabelWithTag:1011 aRect:aRect text:@"Default Store:" noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:store];
    xx = xx + store.frame.size.width + 20;
    ww = 250;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // Store Input
    UIButton    *storeBtn = [Utils newButtonWithTarget:self selector:@selector(storeClicked:) frame:aRect image:nil selectedImage:nil tag:103];
    [storeBtn setBackgroundColor:[UIColor lightGrayColor]];
    NSString *countryName = [[NSUserDefaults standardUserDefaults] objectForKey:kCountry];
    [storeBtn setTitle:countryName forState:UIControlStateNormal];
    [storeBtn setTintColor:[UIColor whiteColor]];
    [self.view addSubview:storeBtn];
    xx = 300.0+store.frame.size.width-75;
    yy = yy + storeBtn.frame.size.height + 50;
    ww = 150;
    hh = 40.0;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    //Getting store button flage images
    UIButton *storeButton = (UIButton*)[self.view viewWithTag:103];
    NSString *urlString   = [[NSUserDefaults standardUserDefaults] objectForKey:kCountryFlagUrl];
    
    if ([self.view viewWithTag:1000]) {
        [[self.view viewWithTag:1000] removeFromSuperview];
    }
   // UIImageView *flagImage = [[UIImageView alloc] initWithFrame:CGRectMake(2, 2, 30, storeButton.frame.size.height - 4)];
    UIImageView *flagImage = [[UIImageView alloc] initWithFrame:CGRectMake(2, 2, 45,26)];
    flagImage.tag = 1000;
    
    NSData *imageData = [NSData dataWithContentsOfURL:[NSURL URLWithString:urlString]];
    flagImage.image = [UIImage imageWithData:imageData];
    
    [storeButton addSubview:flagImage];
    
    ReleaseObj(flagImage);
    isProfileUpdate_DoneBtnClicked=TRUE;
    
    // Done Button
    [self addDoneButtonWithFrme:aRect type:YES];
}

- (void) storeClicked:(id) sender
{
    if(countryArray.count>0)
        [self loadPickerWith:countryArray andSearch:NO];
    else
        [self getStoreLists];
}

- (void) changePasswordView
{
    [self removeAllChild];
    currentRequestType = ChangePasswordRequest;
    isProfileUpdate = NO;
    UIFont  *aFont = [UIFont systemFontOfSize:17.0];
    CGFloat xx = 300.0, yy = 150.0, ww = 150, hh = 30.0;
    CGRect aRect = CGRectMake(xx, yy, ww, hh);
    // Existing Password
    UILabel *password = [Utils createNewLabelWithTag:10 aRect:aRect text:@"New Password:" noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:password];
    xx = xx + password.frame.size.width + 20;
    ww = 250;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // Password Input
    UITextField *passwordT = [Utils createTextFieldWithTag:101 aFrame:aRect aFont:aFont aPlaceholder:@"New password" aTextColor:[UIColor blackColor] aKeyboardType:UIKeyboardTypeDefault];
    [self.view addSubview:passwordT];
    passwordT.tag = 101;
    passwordT.secureTextEntry = YES;
    xx = 300.0;
    yy = yy + passwordT.frame.size.height + 20;
    ww = 150;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // New Password
    UILabel *newPass = [Utils createNewLabelWithTag:10 aRect:aRect text:@"Confirm Password:" noOfLines:1 color:[UIColor blackColor] withFont:aFont];
    [self.view addSubview:newPass];
    xx = xx + newPass.frame.size.width + 20;
    ww = 250;
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // New Password Input
    UITextField *newPasswordT = [Utils createTextFieldWithTag:102 aFrame:aRect aFont:aFont aPlaceholder:@"Confirm password" aTextColor:[UIColor blackColor] aKeyboardType:UIKeyboardTypeDefault];
    [self.view addSubview:newPasswordT];
    newPasswordT.tag = 102;
    newPasswordT.secureTextEntry = YES;
    xx = 300.0+newPasswordT.frame.size.width-75;
    yy = yy + newPasswordT.frame.size.height + 50;
    ww = 150;
    hh = 40.0;
    // NSLog(@"frame is %f",xx);
    aRect = CGRectMake(xx, yy, ww, hh);
    
    // Done Button
    [self addDoneButtonWithFrme:aRect type:NO];
}

#pragma About Us View
-(void)aboutUsMethod:(BOOL)mode{
    
    [self removeAllChild];
    
    if([self.view viewWithTag:11111]){
        [[self.view viewWithTag:11111] removeFromSuperview];
    }
    
    UILabel *aboutLabel = [[UILabel alloc] initWithFrame:CGRectZero];
    aboutLabel.numberOfLines = 0;
    aboutLabel.backgroundColor = [UIColor grayColor];
    aboutLabel.tag = 11111;
    aboutLabel.text = [NSString stringWithFormat:@"eVendor reader app\n%@",[self appNameAndVersionNumberDisplayString]];
    aboutLabel.textAlignment = NSTextAlignmentCenter;
    //CGRect screenSize = [[UIScreen mainScreen] bounds];
    
    CGFloat xx = 300.0, yy = 150.0, ww = 150, hh = 30.0;
    
    aboutLabel.frame = CGRectMake(xx, yy, 400, 300);
    [self.view addSubview:aboutLabel];
}

#pragma Orientation checking 
- (void) viewWillLayoutSubviews
{
    if (selectedCatagory == 2) {
        if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
            [self aboutUsMethod:NO];
        else
            [self aboutUsMethod:YES];
    }
    
    
}

#pragma mark - Done Button
- (void) addDoneButtonWithFrme:(CGRect) aRect type:(BOOL)type
{
    if(isProfileUpdate_DoneBtnClicked){
   aRect.origin.x=aRect.origin.x+95;
    isProfileUpdate_DoneBtnClicked=NO;
     }
    
    // Done
    UIButton    *DoneBtn = [Utils newButtonWithTarget:self selector:@selector(doneClicked:) frame:aRect image:nil selectedImage:nil tag:110];
    [DoneBtn setBackgroundColor:kBlueColor];
    if (type) {
        [DoneBtn setTitle:@"Update" forState:UIControlStateNormal];

    }else{
        [DoneBtn setTitle:@"Change" forState:UIControlStateNormal];

    }
    [DoneBtn setTintColor:[UIColor whiteColor]];
    [self.view addSubview:DoneBtn];
    [Utils setRoundWithLayer:DoneBtn cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
}

- (void) doneClicked:(id) sender
{
    if(isProfileUpdate == YES)
    {
        [self changeProfile];
    }
    else if(isProfileUpdate == NO)
    {
        NSString    *olsPassStr=nil, *newPassStr=nil;
        UITextField *oldPassT = (UITextField*)[self.view viewWithTag:101];
        if([oldPassT isKindOfClass:[UITextField class]])
            olsPassStr = [oldPassT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
        UITextField *newPassT = (UITextField*)[self.view viewWithTag:102];
        if([newPassT isKindOfClass:[UITextField class]])
            newPassStr = [newPassT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
        
        if(newPassStr.length>0 && olsPassStr.length>0 && [newPassStr isEqualToString:olsPassStr]){
            [self changePasswordWithNewPass:newPassStr andOldPass:olsPassStr];
        }
        else{
            
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Password and confirm password should be same."];

            
        }
    }
}


- (void) removeAllChild
{
    NSArray *childArr = [self.view subviews];
    for (int i=0; i<[childArr count]; i++)
    {
        UIView  *tempView = (UIView*)[childArr objectAtIndex:i];
        if(![tempView isKindOfClass:[UITableView class]])
            [tempView removeFromSuperview];
    }
}


#pragma mark - Alert Delegate
- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex
{
    if(alertView.tag == 501 && buttonIndex == 1)
    {
        [[AppDelegate getAppDelegate] signOut];
    }
}

- (void) loadPickerWith:(NSArray*) arr andSearch:(BOOL) isSearch
{
    ReleaseObj(objCountryViewC);
    objCountryViewC = [[CountryViewC alloc] initWithCountryArray:arr];
    objCountryViewC.delegate = self;
    self.popoverC = [[[UIPopoverController alloc] initWithContentViewController:objCountryViewC] autorelease];
    [self.popoverC presentPopoverFromRect:[self.view viewWithTag:103].frame inView:self.view permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    self.popoverC.backgroundColor = kBgColor; //sud
}
#pragma mark - Protocols
- (void) popoverCancel
{
    [self.popoverC dismissPopoverAnimated:YES];
}

- (void) selectedPickerValue:(NSDictionary*) aDic
{
    [self.popoverC dismissPopoverAnimated:YES];
    if(aDic.count>0)  // Store
    {
        self.countryId = [aDic objectForKey:kId];
        UIButton *storeButton = (UIButton*)[self.view viewWithTag:103];
        
        if ([self.view viewWithTag:1000]) {
            [[self.view viewWithTag:1000] removeFromSuperview];
        }
        UIImageView *flagImage = [[UIImageView alloc] initWithFrame:CGRectMake(2, 2, 30, storeButton.frame.size.height - 4)];
        flagImage.tag = 1000;
        
        NSData *imageData = [NSData dataWithContentsOfURL:[NSURL URLWithString:[aDic objectForKey:@"country_flag_url"]]];
        flagImage.image = [UIImage imageWithData:imageData];
        
        [storeButton setTitle:[aDic objectForKey:kStoreName] forState:UIControlStateNormal];
        [storeButton addSubview:flagImage];
        
        ReleaseObj(flagImage);
        
    }
}

#pragma mark - Get user details
- (void) getUserDetails
{
    currentRequestType = GetUserDetailsRequest;
    NSString *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@",kBaseURL,kUserDetailApi,kApiKey,@"id",userId];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [HostSettings host].delegate = self;
    [[HostSettings host] requestWithUrl:requestUrl];
}

#pragma mark - Change Profile/Store
- (void) changeProfile
{
    NSString *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    NSString    *fNameStr=nil, *lNameStr=nil;
    UITextField *fNameT = (UITextField*)[self.view viewWithTag:101];
    if([fNameT isKindOfClass:[UITextField class]])
        fNameStr = [fNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    UITextField *lNameT = (UITextField*)[self.view viewWithTag:102];
    if([lNameT isKindOfClass:[UITextField class]])
        lNameStr = [lNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    
    // Request URL
    currentRequestType = ChangeProfileRequest;
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/%@/%@/%@/%@",kBaseURL,kUpdateProfileApi,kApiKey,kCountryId,self.countryId,@"FirstName",fNameStr,@"LastName",lNameStr,@"id",userId];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [HostSettings host].delegate = self;
    [[HostSettings host] requestWithUrl:requestUrl];
}

#pragma mark - Get Store Lists
- (void) getStoreLists
{
    // Request URL
    currentRequestType = CountryRequest;
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@",kBaseURL,kStoresApi,kApiKey,kStoreId];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [HostSettings host].delegate = self;
    [[HostSettings host] requestWithUrl:requestUrl];
}

#pragma mark - Change Password
- (void) changePasswordWithNewPass:(NSString*) newPass andOldPass:(NSString*) oldPass
{
    currentRequestType = ChangePasswordRequest;
    NSString *userId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/%@/%@",kBaseURL,kChangePasswordApi,kApiKey,@"id",userId,@"NewPassword",oldPass,@"NewConfPassword",newPass];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [HostSettings host].delegate = self;
    [[HostSettings host] requestWithUrl:requestUrl];
}

#pragma mark - Server Response Delegate Methods
-(void) getResult:(NSDictionary*) dataDict
{
    //NSLog(@"Data dict on setting === %@",dataDict);
    [Utils stopActivityIndicator:self.view];
   
    NSString    *error = [dataDict objectForKey:kError];
    if([@"true" isEqualToString:error])
    {
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showOKAlertWithTitle:kAlertTitle message:msg];
        return;
    }
    
    if(currentRequestType == CountryRequest)
    {
        ReleaseObj(countryArray);
        countryArray = [[NSMutableArray alloc] initWithArray:[self removeNullStore:[dataDict objectForKey:kAllStores]]];
        [self loadPickerWith:[self removeNullStore:[dataDict objectForKey:kAllStores]] andSearch:NO];
    }
    else if(currentRequestType == ChangeProfileRequest)
    {
        NSString    *userName = [dataDict objectForKey:kUserName];
        NSString    *userId = [dataDict objectForKey:kUserId];
        NSString    *countryCode = [dataDict objectForKey:kCountryId];
        NSString    *countryName = [dataDict objectForKey:kCountry];
        NSString    *firstName = [dataDict objectForKey:kFirstName];
        NSString    *lastName = [dataDict objectForKey:kLastName];
        NSUserDefaults *prefs = [NSUserDefaults standardUserDefaults];
        [prefs setObject:userName forKey:kUserName];
        [prefs setObject:userId forKey:kUserId];
        [prefs setObject:countryCode forKey:kCountryId];
        [prefs setObject:countryName forKey:kCountry];
        [prefs setObject:firstName forKey:kFirstName];
        [prefs setObject:lastName forKey:kLastName];
        [prefs synchronize];
        
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showOKAlertWithTitle:kAlertTitle message:msg];
//        [[NSNotificationCenter defaultCenter]
//         postNotificationName:@"CHANGESTORE"
//         object:self];
    }
    else if(currentRequestType == ChangePasswordRequest)
    {
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showOKAlertWithTitle:kAlertTitle message:msg];
        UITextField *passTxt=(UITextField *)[self.view viewWithTag:101];
        passTxt.text=@"";
        UITextField *newpassTxt=(UITextField *)[self.view viewWithTag:102];
        newpassTxt.text=@"";
        
    }
    else if(currentRequestType == GetUserDetailsRequest)
    {
        NSDictionary    *aDic = [dataDict objectForKey:@"UserDetail"];
        NSString    *userName = [aDic objectForKey:kUserName];
        NSString    *countryCode = [aDic objectForKey:kCountryId];
        NSString    *countryName = [aDic objectForKey:kCountry];
        NSString    *firstName = [aDic objectForKey:kFirstName];
        NSString    *lastName = [aDic objectForKey:kLastName];
        NSString    *flagUrl = [aDic objectForKey:kCountryFlagUrl];
        NSUserDefaults *prefs = [NSUserDefaults standardUserDefaults];
        [prefs setObject:userName forKey:kUserName];
        [prefs setObject:countryCode forKey:kCountryId];
        [prefs setObject:countryName forKey:kCountry];
        [prefs setObject:firstName forKey:kFirstName];
        [prefs setObject:lastName forKey:kLastName];
        [prefs setObject:flagUrl forKey:kCountryFlagUrl];
        [prefs synchronize];
        [self profileSettingsView];
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


-(void) didFailToGetResult
{
    [Utils stopActivityIndicator:self.view];
}


#pragma - Orientation
- (BOOL)shouldAutorotate
{
    
    return YES;
}


- (NSString *)appNameAndVersionNumberDisplayString {
    NSDictionary *infoDictionary = [[NSBundle mainBundle] infoDictionary];
    
    NSString *majorVersion = [infoDictionary objectForKey:@"CFBundleShortVersionString"];
    NSString *minorVersion = [infoDictionary objectForKey:@"CFBundleVersion"];
    
    return [NSString stringWithFormat:@"Version %@",majorVersion];
}


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


#pragma mark - TextField Delegate
- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    int tagValue = textField.tag;
    if (tagValue == 102) {
        if(isProfileUpdate == YES)
        {
            [self changeProfile];
        }
        else if(isProfileUpdate == NO)
        {
            NSString    *olsPassStr=nil, *newPassStr=nil;
            UITextField *oldPassT = (UITextField*)[self.view viewWithTag:101];
            if([oldPassT isKindOfClass:[UITextField class]])
                olsPassStr = [oldPassT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            UITextField *newPassT = (UITextField*)[self.view viewWithTag:102];
            if([newPassT isKindOfClass:[UITextField class]])
                newPassStr = [newPassT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            
            if(newPassStr.length>0 && olsPassStr.length>0 && ![newPassStr isEqualToString:olsPassStr])
                [self changePasswordWithNewPass:newPassStr andOldPass:olsPassStr];
            else
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter valid password."];
        }
        
    }

    return [textField resignFirstResponder];
}
- (BOOL)textFieldShouldBeginEditing:(UITextField *)textField
{
        return YES;
}


@end
