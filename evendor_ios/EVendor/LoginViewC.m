//
//  LoginViewC.m
//  EVendor
//
//  Created by MIPC-52 on 31/10/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//



#import "LoginViewC.h"

@interface LoginViewC ()

@end
//BOOL isForgotClicked = NO;

@implementation LoginViewC
@synthesize dataDictionary;

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
    [[NSNotificationCenter defaultCenter] removeObserver:self];
    [_controlsView release];
    [_userNameT release];
    [_passwordT release];
    [super dealloc];
}
    
- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    
    // Set RoundRect
    [Utils makeRoundRectInView:self.controlsView];
    
    //self.userNameT.text = @"chandu@gmail.com";
    //self.passwordT.text = @"1234";
    
    //self.userNameT.text = @"rahul.chauhan@magnoninternational.com";
    //self.passwordT.text = @"12345678888";
}


- (void) viewWillLayoutSubviews
{
    if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
        self.controlsView.frame = CGRectMake((1024-self.controlsView.frame.size.width)/2, 50+(768-self.controlsView.frame.size.height)/2, self.controlsView.frame.size.width, self.controlsView.frame.size.height);
    else
        self.controlsView.frame = CGRectMake((768-self.controlsView.frame.size.width)/2, 90, self.controlsView.frame.size.width, self.controlsView.frame.size.height);
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark - 

- (IBAction)forgotPasswordClicked:(id)sender
{
    //isForgotClicked = YES;
    
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:@"" message:@"Please enter your registered email. A mail will be sent to you."  delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Send", nil];
    alert.tag = 501;
    [alert setAlertViewStyle:UIAlertViewStylePlainTextInput];
    [[alert textFieldAtIndex:0] setDelegate:self];
    [alert show];
    [alert release];
}

- (IBAction)registrationClicked:(id)sender
{
    [[AppDelegate getAppDelegate] addRegistrationView];
}

//- (IBAction)loginClicked:(id)sender
//{
//    NSString    *userName = [self.userNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
//    userName = [userName lowercaseString];
//    NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
//
//    // Validations
//    if(!userName.length>0 || !password.length>0)
//    {
//        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter username and password"];
//        return;
//    }
//    
//    if([Utils emailValidate:userName] == YES)
//    {
//        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter correct username/email"];
//        return;
//    }
//    
//    // Single user validation
//    NSString    *logedinUserName = [[NSUserDefaults standardUserDefaults] objectForKey:kUserName];
//    NSString    *logedinUserId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
//    
//    if((logedinUserName.length>0 && ![@"(null)" isEqualToString:logedinUserName]) && (logedinUserId.length>0 && ![@"(null)" isEqualToString:logedinUserId]))
//    {
//        if(![logedinUserName isEqualToString:userName])
//        {
//            [Utils showAlertViewWithTag:601 title:@"Alert" message:@"This user is different from the previous user on this device. Do you want to continue with this user?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
//            return;
//        }
//        else if([logedinUserName isEqualToString:userName] && [Utils isInternetAvailable] == NO)
//        {
//            [Utils showAlertViewWithTag:701 title:@"Alert" message:@"Internet connection is not available. Do you want to login in offline mode?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
//            return;
//        }
//    }
//    
//    [self loginRequest];
//    
//}
- (IBAction)loginClicked:(id)sender
{
    NSString    *userName = [self.userNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    userName = [userName lowercaseString];
    NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    
    // Validations
    if(!userName.length>0 || !password.length>0)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter username and password"];
        return;
    }
    
    if([Utils emailValidate:userName] == YES)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter correct username/email"];
        return;
    }
    
    if([Utils isInternetAvailable] == NO)
    {
        [Utils showAlertViewWithTag:701 title:@"Alert" message:@"Internet connection is not available. Do you want to login in offline mode?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
        return;
    }
    //=================
    // Single user validation
//    NSString    *logedinUserName = [[NSUserDefaults standardUserDefaults] objectForKey:kUserName];
//    NSString    *logedinUserId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
//    
//    if((logedinUserName.length>0 && ![@"(null)" isEqualToString:logedinUserName]) && (logedinUserId.length>0 && ![@"(null)" isEqualToString:logedinUserId]))
//    {
//        if(![logedinUserName isEqualToString:userName])
//        {
//            [Utils showAlertViewWithTag:601 title:@"Alert" message:@"This user is different from the previous user on this device. Do you want to continue with this user?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
//            return;
//        }
//        
//    }
    
    [self loginRequest];
    
}


- (void) loginRequest
{
    NSString    *userName = [self.userNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    userName = [userName lowercaseString];

    NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSString    *deviceId = nil;
    //DEVICEID
    NSUserDefaults *uDefault = [NSUserDefaults standardUserDefaults];
    if ([uDefault objectForKey:@"DEVICEID"]) {
        deviceId = [uDefault objectForKey:@"DEVICEID"];
    }
    
    
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/DeviceId/%@",kBaseURL,kLoginApi,kApiKey,kEmailId,userName,kPassword,password,deviceId];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    currentRequestType = LoginRequest;
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
}


#pragma mark - TextField Delegate
- (BOOL)textFieldShouldBeginEditing:(UITextField *)textField
{
    return YES;
}
- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    
    if (textField == self.passwordT) {
        
        [self loginClicked:nil];
    }
    return [textField resignFirstResponder];
    
}

#pragma mark - AlertView Delegate
- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex
{
    if(alertView.tag == 501)
    {
        if(buttonIndex == 1)
        {
            UITextField *textfield =  [alertView textFieldAtIndex:0];
            NSLog(@"%@",textfield.text);
            NSString    *email = [textfield.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            if([Utils emailValidate:email] == YES)
            {
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter correct email"];
                return;
            }
            
            // Request URL
            NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@",kBaseURL,kForgetPasswordApi,kApiKey,kEmailId,email];
            [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
            currentRequestType = ForgetPasswordRequest;
            [Host host].delegate = self;
            [[Host host] requestWithUrl:requestUrl];
        }
    }
    else if(alertView.tag == 601)
    {
        if(buttonIndex == 1)
        {
            [Utils showAlertViewWithTag:602 title:@"Alert" message:@"This application will lose all the previously downloaded/created items. Do you still want to continue?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
        }
    }
    else if(alertView.tag == 602)
    {
        if(buttonIndex == 1)
        {
            BOOL isDeleted = [[LocalDatabase sharedDatabase] deleteRecordsFromBookTable];
            isDeleted = [[LocalDatabase sharedDatabase] deleteRecordsFromShelfTable];
            NSUserDefaults *prefs = [NSUserDefaults standardUserDefaults];
            [prefs setObject:@"" forKey:kUserName];
            [prefs setObject:@"" forKey:kUserId];
            [prefs setObject:@"" forKey:kCountryId];
            [prefs setObject:@"" forKey:kPassword];
            [prefs setObject:@"" forKey:kCountry];
            [prefs setObject:@"" forKey:kFirstName];
            [prefs setObject:@"" forKey:kLastName];
            [prefs synchronize];
            
            // Remove Interrept items
            NSError *error = nil;
            NSString *downloaded = [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/Downloaded Files/"];
            NSString *temporary = [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/Temporary Files/"];
            NSString *unzipped = [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/UnzippedEpub/"];
            NSArray *arr = [[NSFileManager defaultManager] contentsOfDirectoryAtPath:downloaded error:&error];
            if (error == nil)
            {
                for (NSString *path in arr) {
                    NSString *fullPath = [downloaded stringByAppendingPathComponent:path];
                    BOOL removeSuccess = [[NSFileManager defaultManager] removeItemAtPath:fullPath error:&error];
                    if (!removeSuccess) {
                        // Error handling
                    }
                }
            }
            arr = [[NSFileManager defaultManager] contentsOfDirectoryAtPath:temporary error:&error];
            if (error == nil)
            {
                for (NSString *path in arr) {
                    NSString *fullPath = [temporary stringByAppendingPathComponent:path];
                    BOOL removeSuccess = [[NSFileManager defaultManager] removeItemAtPath:fullPath error:&error];
                    if (!removeSuccess) {
                        // Error handling
                    }
                }
            }
            arr = [[NSFileManager defaultManager] contentsOfDirectoryAtPath:unzipped error:&error];
            if (error == nil)
            {
                for (NSString *path in arr) {
                    NSString *fullPath = [unzipped stringByAppendingPathComponent:path];
                    BOOL removeSuccess = [[NSFileManager defaultManager] removeItemAtPath:fullPath error:&error];
                    if (!removeSuccess) {
                        // Error handling
                    }
                }
            }
            
            //[self loginRequest];
            [self saveUserData];
        }
    }
    else if(alertView.tag == 701)
    {
        if(buttonIndex == 1)
        {
            NSString    *logedinUserName = [[NSUserDefaults standardUserDefaults] objectForKey:kUserName];
            NSString    *userName = [self.userNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            userName = [userName lowercaseString];
            
            NSString    *logedinUserPassword = [[NSUserDefaults standardUserDefaults] objectForKey:kPassword];
            
            NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            
            if([logedinUserPassword isEqualToString:password] && [userName isEqualToString:logedinUserName])
                [[AppDelegate getAppDelegate] addTabBar];
            else
                [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter correct username/password"];
        }
    }
}

- (void)saveUserData{
    
    NSString    *userName = [self.dataDictionary objectForKey:kUserName];
    NSString    *userId = [self.dataDictionary objectForKey:kUserId];
    NSString    *countryId = [self.dataDictionary objectForKey:kCountryId];
    NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSString    *countryName = [self.dataDictionary objectForKey:kCountry];
    NSString    *firstName = [self.dataDictionary objectForKey:kFirstName];
    NSString    *lastName = [self.dataDictionary objectForKey:kLastName];
    NSUserDefaults *prefs = [NSUserDefaults standardUserDefaults];
    [prefs setObject:userName forKey:kUserName];
    [prefs setObject:userId forKey:kUserId];
    [prefs setObject:countryId forKey:kCountryId];
    [prefs setObject:password forKey:kPassword];
    [prefs setObject:countryName forKey:kCountry];
    [prefs setObject:firstName forKey:kFirstName];
    [prefs setObject:lastName forKey:kLastName];
    [prefs synchronize];
    
    // Add TabBar
    [[AppDelegate getAppDelegate] addTabBar];

}

#pragma mark - Server Response Delegate Methods
-(void) getResult:(NSDictionary*) dataDict
{
    [Utils stopActivityIndicator:self.view];
    NSString    *error = [dataDict objectForKey:kError];
    if([kFalse isEqualToString:error])
    {
        if(currentRequestType == ForgetPasswordRequest)
        {
            NSString    *msg = [dataDict objectForKey:kMessage];
            [Utils showOKAlertWithTitle:kAlertTitle message:msg];
        }
        else if(currentRequestType == LoginRequest)
        {
           //==============
            
                NSString    *logedinUserName = [[NSUserDefaults standardUserDefaults] objectForKey:kUserName];
                NSString    *logedinUserId = [[NSUserDefaults standardUserDefaults] objectForKey:kUserId];
            
               NSString *dataUserName = [dataDict objectForKey:kUserName];
               NSString *dataUserId   = [dataDict objectForKey:kUserId];
            
            if (self.dataDictionary) {
                self.dataDictionary = nil;
            }
            
            self.dataDictionary = [NSDictionary dictionaryWithDictionary:dataDict];
            
            
                if(![logedinUserId isEqualToString:dataUserId] && logedinUserId )
                {
                    [Utils showAlertViewWithTag:601 title:@"Alert" message:@"This user is different from the previous user on this device. Do you want to continue with this user?" delegate:self cancelButtonTitle:@"Cancel" otherButtonTitles:@"Yes"];
                        return;
                }
            
            //=================
            
            if (self.dataDictionary) {
                self.dataDictionary = nil;
            }
            
            self.dataDictionary = [NSDictionary dictionaryWithDictionary:dataDict];
            NSString    *userName = [dataDict objectForKey:kUserName];
            NSString    *userId = [dataDict objectForKey:kUserId];
            NSString    *countryId = [dataDict objectForKey:kCountryId];
            NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
            NSString    *countryName = [dataDict objectForKey:kCountry];
            NSString    *firstName = [dataDict objectForKey:kFirstName];
            NSString    *lastName = [dataDict objectForKey:kLastName];
            NSUserDefaults *prefs = [NSUserDefaults standardUserDefaults];
            [prefs setObject:userName forKey:kUserName];
            [prefs setObject:userId forKey:kUserId];
            [prefs setObject:countryId forKey:kCountryId];
            [prefs setObject:password forKey:kPassword];
            [prefs setObject:countryName forKey:kCountry];
            [prefs setObject:firstName forKey:kFirstName];
            [prefs setObject:lastName forKey:kLastName];
            [prefs synchronize];
            
            // Add TabBar
            [[AppDelegate getAppDelegate] addTabBar];
        }
    }
    else
    {
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showOKAlertWithTitle:kAlertTitle message:msg];
    }
}
-(void) didFailToGetResult
{
    [Utils stopActivityIndicator:self.view];
}


@end
