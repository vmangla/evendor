//
//  YLRenderingStateStack.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLRenderingStateStack.h"
#import "YLRenderingState.h"

@interface YLRenderingStateStack () {
    NSMutableArray *_stack;
}

@end

@implementation YLRenderingStateStack

+ (YLRenderingStateStack *)stack {
	return [[[YLRenderingStateStack alloc] init] autorelease];
}

- (id)init {
    self = [super init];
    if(self) {
		_stack = [[NSMutableArray alloc] init];
		YLRenderingState *rootRenderingState = [[[YLRenderingState alloc] init] autorelease];
		[self pushRenderingState:rootRenderingState];
	}
    
	return self;
}

- (void)dealloc {
    [_stack release];
    
    [super dealloc];
}

- (YLRenderingState *)topRenderingState {
	return [_stack lastObject];
}

- (void)pushRenderingState:(YLRenderingState *)state {
	[_stack addObject:state];
}

- (YLRenderingState *)popRenderingState {
    if([_stack count] == 0) {
        return nil;
    }
    
	YLRenderingState *state = [_stack lastObject];
	[[_stack retain] autorelease];
	[_stack removeLastObject];
    
	return state;
}

@end
