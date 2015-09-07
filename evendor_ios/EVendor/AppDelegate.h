//
//  AppDelegate.h
//  EVendor
//
//  Created by MIPC-52 on 30/10/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@class LoginViewC;
@class RegistrationViewC;
@class TabBarViewC;

@interface AppDelegate : UIResponder <UIApplicationDelegate>
{
    LoginViewC  *objLoginViewC;
    RegistrationViewC   *objRegistrationViewC;
    TabBarViewC *objTabBar;
}

@property (strong, nonatomic) UIWindow *window;

+ (AppDelegate*) getAppDelegate;
- (void) addLoginView;
- (void) addRegistrationView;
- (void) addTabBar;
- (void) signOut;

@end
