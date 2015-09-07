//
//  YLRenderOperation.m
//
//  Created by Kemal Taskin on 4/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLRenderOperation.h"
#import "YLPageRenderer.h"
#import "YLDocument.h"
#import "YLPageInfo.h"
#import "YLGlobal.h"

@interface YLRenderOperation () {
    BOOL _shouldCache;
}

@end

@implementation YLRenderOperation

@synthesize shouldCache = _shouldCache;

- (id)initWithDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size path:(NSString *)path {
    self = [super initWithDocument:document page:page size:size path:path];
    if(self) {
        _shouldCache = NO;
    }
    
    return self;
}

- (void)dealloc {
    [super dealloc];
}

- (void)main {
    @autoreleasepool {
        if(self.isCancelled) {
            return;
        }
        
        YLPageInfo *pageInfo = [_document pageInfoForPage:_page];
        if(pageInfo == nil) {
            return;
        }
        
        CGRect targetRect = [pageInfo targetRectForSize:_size];
        if(CGRectIsEmpty(targetRect)) {
            return;
        }
        
        CGRect rotatedRect = pageInfo.rotatedRect;
        CGSize pageVisibleSize = CGSizeMake(rotatedRect.size.width, rotatedRect.size.height);
        float scaleX = targetRect.size.width / pageVisibleSize.width;
        float scaleY = targetRect.size.height / pageVisibleSize.height;
        float scale = scaleX < scaleY ? scaleX : scaleY;
        
        if(self.isCancelled) {
            return;
        }
                
        UIGraphicsBeginImageContextWithOptions(targetRect.size, NO, 0);
        CGContextRef imageContext = UIGraphicsGetCurrentContext();
        [_document renderPage:_page targetRect:targetRect scale:scale inContext:imageContext];
        _image = UIGraphicsGetImageFromCurrentImageContext();
        UIGraphicsEndImageContext();
        
        if(_image == nil) {
            return;
        }
        
        [_image retain];
    }
}

@end
