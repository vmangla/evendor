//
//  UIView+SuperView.m
//  EVendor
//
//  Created by MIPC-52 on 14/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "UIView+SuperView.h"

@implementation UIView (SuperView)

- (UIView *)findSuperViewWithClass:(Class)superViewClass
{
    UIView *superView = self.superview;
    UIView *foundSuperView = nil;
    
    while (nil != superView && nil == foundSuperView)
    {
        if ([superView isKindOfClass:superViewClass])
        {
            foundSuperView = superView;
            break;
        }
        else
            superView = superView.superview;
    }
    return foundSuperView;
}

@end
