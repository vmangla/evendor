//
//  AppDelegate.h
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import <UIKit/UIKit.h>

@class MainViewController;

@interface AppDelegate : UIResponder <UIApplicationDelegate> {
    MainViewController *_mainViewController;
    UINavigationController *_navController;
}


@property (strong, nonatomic) UIWindow *window;

@end
