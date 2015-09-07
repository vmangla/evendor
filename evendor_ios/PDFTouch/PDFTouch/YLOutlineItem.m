//
//  YLOutlineItem.m
//
//  Created by Kemal Taskin on 5/1/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLOutlineItem.h"

@interface YLOutlineItem () {
    NSString *_title;
    NSUInteger _indentation;
    NSUInteger _page;
}

@end

@implementation YLOutlineItem

@synthesize title = _title;
@synthesize indentation = _indentation;
@synthesize page = _page;

- (id)initWithTitle:(NSString *)title indentation:(NSUInteger)indent page:(NSUInteger)page {
    self = [super init];
    if(self) {
        _title = [title retain];
        _indentation = indent;
        _page = page;
    }
    
    return self;
}

- (void)dealloc {
    [_title release];
    
    [super dealloc];
}

@end
