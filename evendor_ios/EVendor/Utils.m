//
//  Utils.m
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "Utils.h"
#import <QuartzCore/QuartzCore.h>
#import "MBProgressHUD.h"
#import "Reachability.h"

@implementation Utils
+ (BOOL) isiOS8
{
    BOOL iOS8 = NO;
    if ([[[UIDevice currentDevice] systemVersion] floatValue] >= 8.0)
    {
        iOS8 = YES;
    }
    return iOS8;
}

#pragma mark - Activity Indicator
+ (void) startActivityIndicatorWithMessage:(NSString*)aMessage onView:(UIView*) aView
{
	MBProgressHUD *_hud = [MBProgressHUD showHUDAddedTo:aView animated:YES];
	_hud.dimBackground = YES;
	_hud.labelText = aMessage;
}

+ (void) stopActivityIndicator:(UIView*) aView
{
	[MBProgressHUD hideHUDForView:[[AppDelegate getAppDelegate] window] animated:YES];
    [MBProgressHUD hideHUDForView:aView animated:YES];
}

#pragma mark - Alerts
+ (void) showAlertView :(NSString*)title message:(NSString*)msg delegate:(id)delegate
      cancelButtonTitle:(NSString*)CbtnTitle otherButtonTitles:(NSString*)otherBtnTitles
{
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:title message:msg delegate:delegate
                                          cancelButtonTitle:CbtnTitle otherButtonTitles:otherBtnTitles, nil];
    [alert show];
    [alert release];
}

+ (void) showAlertViewWithTag:(NSInteger)tag title:(NSString*)title message:(NSString*)msg delegate:(id)delegate
            cancelButtonTitle:(NSString*)CbtnTitle otherButtonTitles:(NSString*)otherBtnTitles
{
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:title message:msg delegate:delegate
                                          cancelButtonTitle:CbtnTitle otherButtonTitles:otherBtnTitles, nil];
    alert.tag = tag;
    [alert show];
    [alert release];
}

+ (void) showOKAlertWithTitle:(NSString*)aTitle message:(NSString*)aMsg
{
    UIAlertView	*aAlert = [[UIAlertView alloc] initWithTitle:aTitle message:aMsg delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil];
    [aAlert show];
    [aAlert release];
}


#pragma mark - Round Corners And Border
+ (void) setRoundWithLayer:(UIView*) aLayer cornerRadius:(CGFloat) aRadius borderWidth:(CGFloat) aWidth borderColor:(UIColor*) aColor maskBounds:(BOOL) aBool
{
    // Round rect and border color
    [aLayer.layer setBorderColor: [aColor CGColor]];
    [aLayer.layer setBorderWidth: aWidth];
    [aLayer.layer setMasksToBounds:aBool];
    [aLayer.layer setCornerRadius:aRadius];
}

+ (void) makeRoundRectInView:(UIView*) aView
{
    NSArray *arr = [aView subviews];
    for (int i=0; i<[arr count]; i++)
    {
        UIView  *tempView = (UIView*)[arr objectAtIndex:i];
        if(tempView.tag == 50)
            [Utils setRoundWithLayer:tempView cornerRadius:4.0 borderWidth:0.5 borderColor:[UIColor whiteColor] maskBounds:YES];
    }
}

#pragma mark - Check Device Orientation
+ (BOOL) isPortrait
{
    return [[UIApplication sharedApplication] statusBarOrientation] == UIInterfaceOrientationPortrait || [[UIApplication sharedApplication] statusBarOrientation] == UIInterfaceOrientationPortraitUpsideDown;
}

+ (BOOL) isInternetAvailable
{
    Reachability *r = [Reachability reachabilityWithHostName:@"www.google.com"];
	NetworkStatus internetStatus = [r currentReachabilityStatus];
	if ((internetStatus != ReachableViaWiFi) && (internetStatus != ReachableViaWWAN))
	{
		//[Utils showAlertView:kAlertTitle message:@"Internet connection is not available." delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil];
        
        return NO;
	}
    return YES;
}

+ (UIImage*) getImageFromResource:(NSString*) fileName
{
    UIImage *aImage = nil;
    NSString *filePath = [[NSBundle mainBundle] pathForResource:fileName ofType:@"png"];
    //NSString *filePath = [[NSBundle mainBundle] pathForResource:fileName ofType:@"png" inDirectory:@"AppImages/Images_iPhone"];
    aImage = [UIImage imageWithContentsOfFile:filePath];
    return aImage;
}

#pragma mark-Check Email Formate
+(BOOL)emailValidate:(NSString *)email
{
	//Based on the string below
	//NSString *strEmailMatchstring=@"\\b([a-zA-Z0-9%_.+\\-]+)@([a-zA-Z0-9.\\-]+?\\.[a-zA-Z]{2,6})\\b";
	
	//Quick return if @ Or . not in the string
	if([email rangeOfString:@"@"].location==NSNotFound || [email rangeOfString:@"."].location==NSNotFound)
		//////NSLog(@"%@",[email rangeOfString:@"@"]);
		//			////NSLog(@"%@",[email rangeOfString:@"."]);
		return YES;
	
	//Break email address into its components
	NSString *accountName=[email substringToIndex: [email rangeOfString:@"@"].location];
	////NSLog(@"%@",accountName);
	email=[email substringFromIndex:[email rangeOfString:@"@"].location+1];
	////NSLog(@"%@",email);
	//'.' not present in substring
	if([email rangeOfString:@"."].location==NSNotFound)
		return YES;
	NSString *domainName=[email substringToIndex:[email rangeOfString:@"."].location];
	////NSLog(@"%@",domainName);
	
	NSString *subDomain=[email substringFromIndex:[email rangeOfString:@"."].location+1];
	////NSLog(@"%@",subDomain);
	
	//username, domainname and subdomain name should not contain the following charters below
	//filter for user name
	NSString *unWantedInUName = @" ~!@#$^&*()={}[]|;':\"<>,?/`";
	//filter for domain
	NSString *unWantedInDomain = @" ~!@#$%^&*()={}[]|;':\"<>,+?/`";
	//filter for subdomain
	NSString *unWantedInSub = @" `~!@#$%^&*()={}[]:\";'<>,?/1234567890";
	
	//subdomain should not be less that 2 and not greater 6
	if(!(subDomain.length>=2 && subDomain.length<=6))
		return YES;
	
	if([accountName isEqualToString:@""] || [accountName rangeOfCharacterFromSet:[NSCharacterSet characterSetWithCharactersInString:unWantedInUName]].location!=NSNotFound || [domainName isEqualToString:@""] || [domainName rangeOfCharacterFromSet:[NSCharacterSet characterSetWithCharactersInString:unWantedInDomain]].location!=NSNotFound || [subDomain isEqualToString:@""] || [subDomain rangeOfCharacterFromSet:[NSCharacterSet characterSetWithCharactersInString:unWantedInSub]].location!=NSNotFound)
		return YES;
	
	return NO;
}

#pragma mark - Button
+ (UIButton *)newButtonWithTarget:(id)target  selector:(SEL)selector frame:(CGRect)frame
							image:(UIImage *)image
					selectedImage:(UIImage *)selectedImage
							  tag:(NSInteger)aTag
{
	UIButton *button = [[UIButton buttonWithType:UIButtonTypeCustom] retain];
	button.frame = frame;
	[button setImage:image forState:UIControlStateNormal];
	[button setImage:selectedImage forState:UIControlStateSelected];
	
	[button addTarget:target action:selector forControlEvents:UIControlEventTouchUpInside];
	button.backgroundColor = [UIColor clearColor];
	//button.showsTouchWhenHighlighted = YES;
	button.tag = aTag;
	return button;
}

#pragma mark label
+ (UILabel*) createNewLabelWithTag:(NSInteger)aTag aRect:(CGRect)aRect text:(NSString*)aText noOfLines:(NSInteger)noOfLine color:(UIColor*)color withFont:(UIFont*)font
{
	UILabel *aLabel = [[UILabel alloc] init];
	aLabel.frame = aRect;
	aLabel.text = aText;
	aLabel.tag = aTag;
    aLabel.font = font;
	aLabel.textColor = color;
	aLabel.backgroundColor = [UIColor clearColor];
	aLabel.adjustsFontSizeToFitWidth = YES;
	aLabel.numberOfLines = noOfLine;
	aLabel.textAlignment = NSTextAlignmentLeft;
	return [aLabel autorelease];
}

#pragma mark - TextField
+ (UITextField*) createTextFieldWithTag:(NSInteger) aTag aFrame:(CGRect) aFrame aFont:(UIFont*) aFont aPlaceholder:(NSString*) aPlaceholder aTextColor:(UIColor*) aTextColor aKeyboardType:(UIKeyboardType) aKeyboardType
{
    UITextField *aTextField = [[[UITextField alloc] init] autorelease];
    aTextField.frame = aFrame;
    aTextField.font = aFont;
    aTextField.textColor = aTextColor;
    aTextField.keyboardType = aKeyboardType;
    aTextField.returnKeyType = UIReturnKeyDone;
    aTextField.placeholder = aPlaceholder;
    aTextField.borderStyle = UITextBorderStyleRoundedRect;
    aTextField.autocorrectionType = UITextAutocorrectionTypeNo;
    aTextField.clearButtonMode = UITextFieldViewModeWhileEditing;
    aTextField.contentVerticalAlignment = UIControlContentVerticalAlignmentCenter;
    return aTextField;
}

+ (BOOL) isiOS7
{
    BOOL iOS7 = NO;
    if ([[[UIDevice currentDevice] systemVersion] floatValue] >= 7.0)
    {
        iOS7 = YES;
    }
    return iOS7;
}

//+(void)addNavigationItm:(id)controller{
//    UIViewController *tmpController = (UIViewController *)controller;
//    
//    
//    UILabel *mainLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, 250, 44)];
//    mainLabel.backgroundColor = [UIColor clearColor];
//    mainLabel.font = [UIFont systemFontOfSize:13];
//    mainLabel.text = @"eVendor Reader";
//    mainLabel.textColor = [UIColor blackColor];
//    
//    
//    
//    UIBarButtonItem *rightBarItem = [[UIBarButtonItem alloc] initWithCustomView:mainLabel];
//    
//    UIImageView *logoView = [[UIImageView alloc] initWithFrame:CGRectMake(1, 9, 25, 25)];
//    [logoView setImage:[UIImage imageNamed:@"logo_29.png"]];
//    [tmpController.navigationController.navigationBar addSubview:logoView];
//    [logoView release];
//    
//    [mainLabel release];
//    
//    tmpController.navigationItem.leftBarButtonItem = rightBarItem;
//    [rightBarItem release];
//    
//}

+(void)addNavigationItm:(id)controller{
    
    UIViewController *tmpController = (UIViewController *)controller;

        UILabel *mainLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, 250, 44)];
        mainLabel.backgroundColor = [UIColor clearColor];
        mainLabel.font = [UIFont systemFontOfSize:13];
        mainLabel.text = [self appNameAndVersionNumberDisplayString];
        mainLabel.textColor = [UIColor blackColor];
    
    UIBarButtonItem *rightBarItem1 = [[UIBarButtonItem alloc] initWithCustomView:mainLabel];
    
       UIImageView *logoView = [[UIImageView alloc] initWithFrame:CGRectMake(0, 0, 92, 25)];
       logoView.image = [UIImage imageNamed:@"evendorlogo92x25.png"];
    
    UIBarButtonItem *rightBarItem2 = [[UIBarButtonItem alloc] initWithCustomView:logoView];
    
       tmpController.navigationItem.leftBarButtonItems = @[rightBarItem2,rightBarItem1];

 
}

+ (NSString *)appNameAndVersionNumberDisplayString {
    NSDictionary *infoDictionary = [[NSBundle mainBundle] infoDictionary];
    
    NSString *majorVersion = [infoDictionary objectForKey:@"CFBundleShortVersionString"];
  //  NSString *minorVersion = [infoDictionary objectForKey:@"CFBundleVersion"];
    
    return [NSString stringWithFormat:@"Version %@",majorVersion];
}

@end
