//
//  YLSelection.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLSelection.h"
#import "YLRenderingState.h"

@interface YLSelectionSegment () {
    YLRenderingState *_startState;
    
    CGAffineTransform _transform;
    CGRect _frame;
}

@end

@implementation YLSelectionSegment

@synthesize frame = _frame;
@synthesize transform = _transform;
@synthesize startState = _startState;

+ (YLSelectionSegment *)segmentWithStartState:(YLRenderingState *)state {
    return [[[YLSelectionSegment alloc] initWithStartState:state] autorelease];
}

- (id)initWithStartState:(YLRenderingState *)state {
    self = [super init];
    if(self) {
        self.startState = state;
    }
    
    return self;
}

- (void)dealloc {
    [_startState release];
    
	[super dealloc];
}

- (void)finalizeWithState:(YLRenderingState *)state {
	// Concatenate CTM onto text matrix
	_transform = CGAffineTransformConcat([_startState textMatrix], [_startState ctm]);
    
    /*CGAffineTransform temp = CGAffineTransformIdentity;
    temp.a = (_startState.fontSize * _startState.horizontalScaling);
    temp.d = _startState.fontSize;
    temp.ty = _startState.textRise;
    _transform = CGAffineTransformConcat(temp, _transform);*/

	YLFont *openingFont = [_startState font];
	YLFont *closingFont = [state font];
	
	// Width (difference between caps) with text transformation removed
	CGFloat width = [state textMatrix].tx - [_startState textMatrix].tx;
	width /= [state textMatrix].a;

	// Use tallest cap for entire selection
	CGFloat startHeight = [openingFont maxY] - [openingFont minY];
	CGFloat finishHeight = [closingFont maxY] - [closingFont minY];
	YLRenderingState *s = (startHeight > finishHeight) ? _startState : state;

	YLFont *font = [s font];
	
	// Height is ascent plus (negative) descent
	CGFloat height = [s convertToUserSpace:(font.maxY - font.minY)];

	// Descent
    CGFloat descent = [s convertToUserSpace:font.minY];

	// Selection frame in text space
	_frame = CGRectMake(0, descent, width, height);
}

@end


@interface YLSelection () {
    NSMutableArray *_segments;
    YLSelectionSegment *_currentSegment;
    BOOL _selectionStarted;
}

@end

@implementation YLSelection

@synthesize segments;

- (id)init {
    self = [super init];
    if(self) {
        _segments = [[NSMutableArray alloc] init];
        _selectionStarted = NO;
    }
    
    return self;
}

- (void)dealloc {
    [_segments release];
    [_currentSegment release];
    
    [super dealloc];
}

- (NSArray *)segments {
    return [NSArray arrayWithArray:_segments];
}

- (void)startWithState:(YLRenderingState *)state {
    if(_currentSegment == nil) {
        _currentSegment = [[YLSelectionSegment alloc] initWithStartState:state];
    } else {
        [_currentSegment setStartState:state];
    }
    
    _selectionStarted = YES;
}

- (void)finalizeWithState:(YLRenderingState *)state {
    if(_currentSegment == nil || !_selectionStarted) {
        return;
    }
    
    [_currentSegment finalizeWithState:state];
    [_segments addObject:_currentSegment];
    
    [_currentSegment release];
    _currentSegment = nil;
    _selectionStarted = NO;
}

- (void)reset {
    [_currentSegment release];
    _currentSegment = nil;
    _selectionStarted = NO;
    
    [_segments removeAllObjects];
}

@end
