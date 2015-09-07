//
//  AppDelegate.m
//  EVendor
//
//  Created by MIPC-52 on 30/10/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "AppDelegate.h"
#import "LoginViewC.h"
#import "RegistrationViewC.h"
#import "TabBarViewC.h"

@implementation AppDelegate

- (void) dealloc
{
    [objLoginViewC release];
    [objRegistrationViewC release];
    [objTabBar release];
    [super dealloc];
}

- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions
{
    self.window = [[UIWindow alloc] initWithFrame:[[UIScreen mainScreen] bounds]];
    // Override point for customization after application launch.
    
    [self GetDeviceID];

    // Remove Interrept items
    [[NSFileManager defaultManager] removeItemAtPath:[NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/InterruptedDownloadsFile/interruptedDownloads.txt"] error:nil];
    
    // Copy Database
    [LocalDatabase createDatabaseIfNeeded];
    
    self.window.backgroundColor = [UIColor whiteColor];
    [self.window makeKeyAndVisible];
    
    [self addLoginView];
    //[self addTabBar];
    
//    if ([[[UIDevice currentDevice] systemVersion] floatValue] >= 7) {
//        
//        [application setStatusBarStyle:UIStatusBarStyleLightContent];
//        
//        self.window.clipsToBounds =YES;
//        
//        self.window.frame =  CGRectMake(0,20,self.window.frame.size.width,self.window.frame.size.height-20);
//    }
    
    return YES;
}

#pragma mark - 

+ (AppDelegate*) getAppDelegate
{
    return (AppDelegate*)[UIApplication sharedApplication].delegate;
}

// Add Login
- (void) addLoginView
{
    self.window.rootViewController = nil;
    ReleaseObj(objTabBar);
    ReleaseObj(objLoginViewC);
    ReleaseObj(objRegistrationViewC);
    objLoginViewC    = [[LoginViewC alloc] initWithNibName:@"LoginViewC" bundle:nil];
    self.window.rootViewController = objLoginViewC;
}

// Add Registration
- (void) addRegistrationView
{
    self.window.rootViewController = nil;
    ReleaseObj(objLoginViewC);
    ReleaseObj(objRegistrationViewC);
    objRegistrationViewC    = [[RegistrationViewC alloc] initWithNibName:@"RegistrationViewC" bundle:nil];
    self.window.rootViewController = objRegistrationViewC;
}

#pragma mark - Add Tab Bar
- (void) addTabBar
{
    self.window.rootViewController = nil;
    ReleaseObj(objTabBar);
    objTabBar   =   [[TabBarViewC alloc] initWithNibName:@"TabBarViewC" bundle:nil];
    if([objTabBar view])
    {
        self.window.rootViewController = objTabBar;
        //objTabBar.mTabBar.selectedIndex = 1;
    }
}

- (void) signOut
{
    [self addLoginView];
}

- (NSUInteger)application:(UIApplication *)application supportedInterfaceOrientationsForWindow:(UIWindow *)window
{
    return UIInterfaceOrientationMaskAll;
}

- (void)applicationWillResignActive:(UIApplication *)application
{
    // Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
    // Use this method to pause ongoing tasks, disable timers, and throttle down OpenGL ES frame rates. Games should use this method to pause the game.
}

- (void)applicationDidEnterBackground:(UIApplication *)application
{
    
    NSArray *myPathList = NSSearchPathForDirectoriesInDomains(NSCachesDirectory, NSUserDomainMask, YES);
    NSString *mainPath    = [myPathList  objectAtIndex:0];
    
    mainPath = [mainPath stringByAppendingPathComponent:@"/YLPDFKit"];
    NSFileManager *fileManager = [NSFileManager defaultManager];
    NSError *error;
    BOOL fileExists = [fileManager fileExistsAtPath:mainPath];
    
    if (fileExists)
    {
        BOOL success = [fileManager removeItemAtPath:mainPath error:&error];
        if (!success) NSLog(@"Error: %@", [error localizedDescription]);
        
    }
    
    
    // Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later. 
    // If your application supports background execution, this method is called instead of applicationWillTerminate: when the user quits.
}

- (void)applicationWillEnterForeground:(UIApplication *)application
{
    // Called as part of the transition from the background to the inactive state; here you can undo many of the changes made on entering the background.
}

- (void)applicationDidBecomeActive:(UIApplication *)application
{
    // Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
}

- (void)applicationWillTerminate:(UIApplication *)application
{
    // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
}

#pragma Getting unique devide id

- (void)GetDeviceID {
    NSString *udidString;
    NSUserDefaults *userDefault = [NSUserDefaults standardUserDefaults];
    udidString = [userDefault objectForKey:@"DEVICEID"];
    if(!udidString)
    {
        CFUUIDRef cfuuid = CFUUIDCreate(kCFAllocatorDefault);
        udidString = (NSString*)CFBridgingRelease(CFUUIDCreateString(kCFAllocatorDefault, cfuuid));
        [userDefault setObject:udidString forKey:@"DEVICEID"];
    }
    // return udidString;
}
@end
