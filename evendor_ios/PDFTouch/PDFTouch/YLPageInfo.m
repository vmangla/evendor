//
//  YLPageInfo.m
//
//  Created by Kemal Taskin on 3/28/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPageInfo.h"
#import "YLGlobal.h"

@interface YLPageInfo () {
    CGRect _origRect;
    CGRect _rotatedRect;
    int _rotation;
    NSUInteger _page;
    
    NSMutableDictionary *_targetRectDict;
}

- (void)calculateRotatedRect;

@end

@implementation YLPageInfo

@synthesize origRect = _origRect;
@synthesize rotatedRect = _rotatedRect;
@synthesize rotation = _rotation;
@synthesize page = _page;

+ (YLPageInfo *)YLPageInfoWithPage:(NSUInteger)page rect:(CGRect)rect rotation:(int)rotation {
    return [[[YLPageInfo alloc] initWithPage:page rect:rect rotation:rotation] autorelease];
}

- (id)initWithPage:(NSUInteger)page rect:(CGRect)rect rotation:(int)rotation {
    self = [super init];
    if(self) {
        _origRect = rect;
        _rotatedRect = CGRectZero;
        _rotation = rotation;
        _page = page;
        _targetRectDict = [[NSMutableDictionary alloc] initWithCapacity:3];
        
        [self calculateRotatedRect];
    }
    
    return self;
}

- (void)dealloc {
    [_targetRectDict release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (CGRect)targetRectForSize:(YLPDFImageSize)size {
    NSValue *target = [_targetRectDict objectForKey:[NSNumber numberWithInt:size]];
    if(target) {
        return [target CGRectValue];
    }
    
    CGRect targetRect = CGRectZero;
    if(size == YLPDFImageSizeSmall) {
        targetRect = CGRectMake(0, 0, 50, 100);
    } else if(size == YLPDFImageSizeThumbnail) {
        targetRect = CGRectMake(0, 0, 200, 400);
    } else if(size == YLPDFImageSizeOriginal) {
        targetRect = _rotatedRect;
    }
    
    if(CGRectIsEmpty(targetRect)) {
        return CGRectZero;
    }
    
    CGSize pageVisibleSize = CGSizeMake(_rotatedRect.size.width, _rotatedRect.size.height);
    float scaleX = targetRect.size.width / pageVisibleSize.width;
    float scaleY = targetRect.size.height / pageVisibleSize.height;
    float scale = scaleX < scaleY ? scaleX : scaleY;
    
    // Offset relative to top left corner of rectangle where the page will be displayed
    float offsetX = 0;
    float offsetY = 0;
    
    float targetAspectRatio = targetRect.size.width / targetRect.size.height;
    float pageAspectRatio = pageVisibleSize.width / pageVisibleSize.height;
    
    if (pageAspectRatio < targetAspectRatio) {
        // The page is narrower than the rectangle, we place it at center on the horizontal
        offsetX = (targetRect.size.width - pageVisibleSize.width * scale) / 2;
    } else {
        // The page is wider than the rectangle, we place it at center on the vertical
        offsetY = (targetRect.size.height - pageVisibleSize.height * scale) / 2;
    }
    
    targetRect = CGRectInset(targetRect, offsetX, offsetY);
    [_targetRectDict setObject:[NSValue valueWithCGRect:targetRect] forKey:[NSNumber numberWithInt:size]];
    
    return targetRect;
}


#pragma mark -
#pragma mark Private Methods
- (void)calculateRotatedRect {
    switch(_rotation) {
        case 90:
        case 270:
        case -90:
            _rotatedRect.size.width = _origRect.size.height;
            _rotatedRect.size.height = _origRect.size.width;
            break;
        case 0:
        case 180:
        case -180:
        default:
            _rotatedRect.size.width = _origRect.size.width;
            _rotatedRect.size.height = _origRect.size.height;
            break;
    }
    
    // Use even values for width and height
    NSInteger width = _rotatedRect.size.width;
    NSInteger height = _rotatedRect.size.height;
    if((width % 2) != 0) {
        width -= 1;
    }
    if((height % 2) != 0) {
        height -= 1;
    }
    _rotatedRect.size.width = width;
    _rotatedRect.size.height = height;
}

@end
