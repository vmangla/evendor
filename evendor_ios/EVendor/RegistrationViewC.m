//
//  RegistrationViewC.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "RegistrationViewC.h"

@interface RegistrationViewC ()

@end

@implementation RegistrationViewC
@synthesize termsConditionLbl;

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
    [_controlsView release];
    [_firstNameT release];
    [_lastNameT release];
    [_userNameT release];
    [_passwordT release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    
    isAccept = NO;
    // Set RoundRect
    [Utils makeRoundRectInView:self.controlsView];
    
    
    NSMutableAttributedString *temString=[[NSMutableAttributedString alloc]initWithString:@"I accept terms and conditions."];
    [temString addAttribute:NSUnderlineStyleAttributeName
                      value:[NSNumber numberWithInt:1]
                      range:(NSRange){0,[temString length]}];
    
    self.termsConditionLbl.attributedText = temString;
    
    [self.termsConditionLbl addGestureRecognizer:[[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(openTermsAndConditionPage)]];
    

}

- (void)openTermsAndConditionPage{
    NSURL *url = [NSURL URLWithString:@"http://evendornigeria.com/page/index/title/terms-and-conditions"];
    
    [[UIApplication sharedApplication] openURL:url];

}

- (void) viewWillLayoutSubviews
{
    if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
        self.controlsView.frame = CGRectMake((1024-self.controlsView.frame.size.width)/2, 90+(768-self.controlsView.frame.size.height)/2, self.controlsView.frame.size.width, self.controlsView.frame.size.height);
    else
        self.controlsView.frame = CGRectMake((768-self.controlsView.frame.size.width)/2, 90, self.controlsView.frame.size.width, self.controlsView.frame.size.height);
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


#pragma mark -
- (IBAction)cancelClicked:(id)sender
{
    [[AppDelegate getAppDelegate] addLoginView];
}

- (IBAction)signupClicked:(id)sender
{
    NSString    *firstName = [self.firstNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSString    *lastName = [self.lastNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSString    *userName = [self.userNameT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    NSString    *password = [self.passwordT.text stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    
    
    // Validations
    if(!firstName.length>0 || !lastName.length>0)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter first name and last name"];
        return;
    }
    if(!userName.length>0 || !password.length>0)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter username/email and password"];
        return;
    }
    
    if([Utils emailValidate:userName] == YES)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please enter correct username/email"];
        return;
    }
    
    if(isAccept == NO)
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please accept terms and conditions."];
        return;
    }
    
    // Request URL
    NSString    *requestUrl = [NSString stringWithFormat:@"%@/%@/%@/%@/%@/%@/%@/%@/%@/%@/%@",kBaseURL,kRegistrationApi,kApiKey,kEmailId,userName,kPassword,password,kFirstName,firstName,kLastName,lastName];
    [Utils startActivityIndicatorWithMessage:kPleaseWait onView:self.view];
    [Host host].delegate = self;
    [[Host host] requestWithUrl:requestUrl];
}

- (IBAction)acceptClicked:(id)sender
{
    UIButton    *aBtn = (UIButton*)sender;
    aBtn.selected = !aBtn.selected;
    if(aBtn.selected == YES)
        isAccept = YES;
    else
        isAccept = NO;
}


#pragma mark - TextField Delegate
- (BOOL)textFieldShouldReturn:(UITextField *)textField {
    if (textField == self.passwordT) {
        [self signupClicked:nil];
    }
    return [textField resignFirstResponder];
}
- (BOOL)textFieldShouldBeginEditing:(UITextField *)textField
{
    return YES;
}

#pragma mark - Server Response Delegate Methods
-(void) getResult:(NSDictionary*) dataDict
{
    [Utils stopActivityIndicator:self.view];
    NSString    *error = [dataDict objectForKey:kError];
    if([kFalse isEqualToString:error])
    {
        NSString    *msg = [dataDict objectForKey:kMessage];
        [Utils showAlertViewWithTag:501 title:kAlertTitle message:msg delegate:self cancelButtonTitle:@"Ok" otherButtonTitles:nil];
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


#pragma mark - AlertView Delegate
- (void)alertView:(UIAlertView *)alertView clickedButtonAtIndex:(NSInteger)buttonIndex
{
    if(alertView.tag == 501)
    {
        if(buttonIndex == 0)
        {
            [[AppDelegate getAppDelegate] addLoginView];
        }
    }
}


@end
