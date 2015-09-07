//
//  NSBundle+PDFTouch.m
//
//  Created by Kemal Taskin on 2/16/13.
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "NSBundle+PDFTouch.h"

@implementation NSBundle (PDFTouch)

+ (NSBundle *)myLibraryResourcesBundle {
    static dispatch_once_t onceToken;
    static NSBundle *myLibraryResourcesBundle = nil;
    dispatch_once(&onceToken, ^{
        myLibraryResourcesBundle = [NSBundle bundleWithURL:
                                    [[NSBundle mainBundle] URLForResource:@"PDFTouch" withExtension:@"bundle"]];
    });
    
    return myLibraryResourcesBundle;
}

+ (NSString *)myLocalizedStringForKey:(NSString *)key {
    NSBundle *bundle = [NSBundle myLibraryResourcesBundle];
    return [bundle localizedStringForKey:key value:@"" table:@"PDFTouch"];
}

@end
