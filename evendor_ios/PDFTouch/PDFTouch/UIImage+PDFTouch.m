//
//  UIImage+PDFTouch.m
//
//  Created by Kemal Taskin on 2/16/13.
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "UIImage+PDFTouch.h"
#import "NSBundle+PDFTouch.h"

@implementation UIImage (PDFTouch)

+ (UIImage *)myLibraryImageNamed:(NSString *)name {
    UIImage *imageFromMainBundle = [UIImage imageNamed:name];
    if(imageFromMainBundle) {
        return imageFromMainBundle;
    }
    
    UIImage *imageFromMyLibraryBundle = [UIImage imageWithContentsOfFile:
                                         [[[NSBundle myLibraryResourcesBundle] resourcePath] stringByAppendingPathComponent:name]];
    return imageFromMyLibraryBundle;
}

@end
