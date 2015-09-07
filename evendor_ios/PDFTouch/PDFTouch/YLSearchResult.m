//
//  YLSearchResult.m
//
//  Created by Kemal Taskin on 5/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLSearchResult.h"
#import "YLSelection.h"

@interface YLSearchResult () {
    NSUInteger _page;
    NSString *_shortText;
    NSRange _boldRange;
    
    YLSelection *_selection;
}

@end

@implementation YLSearchResult

@synthesize page = _page;
@synthesize shortText = _shortText;
@synthesize boldRange = _boldRange;
@synthesize selection = _selection;

- (id)initWithPage:(NSUInteger)page shortText:(NSString *)text {
    self = [super init];
    if(self) {
        _page = page;
        _shortText = [text copy];
        _boldRange = NSMakeRange(0, 0);
        _selection = nil;
    }
    
    return self;
}

- (void)dealloc {
    [_selection release];
    
    [super dealloc];
}

@end
