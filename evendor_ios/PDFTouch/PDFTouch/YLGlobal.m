//
//  YLGlobal.h
//
//  Created by Kemal Taskin on 4/5/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#include "YLGlobal.h"

BOOL YLIsIOS7OrGreater(void) {
    static BOOL isIOS7 = NO;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        isIOS7 = ([[[UIDevice currentDevice] systemVersion] floatValue] >= 7.0);
    });
    
    return isIOS7;
}