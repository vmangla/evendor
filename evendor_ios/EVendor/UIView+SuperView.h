//
//  UIView+SuperView.h
//  EVendor
//
//  Created by MIPC-52 on 14/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface UIView (SuperView)

- (UIView *)findSuperViewWithClass:(Class)superViewClass;

@end
