//
//  YLTiledView.m
//
//  Created by Kemal Taskin on 3/27/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLTiledView.h"
#import "YLDocument.h"
#import "YLDocumentScanner.h"
#import "YLPageInfo.h"
#import "YLPageRenderer.h"
#import "YLSearchResult.h"
#import "YLGlobal.h"

@interface YLTiledView () {
    YLDocument *_document;
    NSUInteger _page;
}

@end

@implementation YLTiledView

+ (Class)layerClass {
    return [YLTiledLayer class];
}

- (id)initWithDocument:(YLDocument *)document page:(NSUInteger)page {
    self = [super initWithFrame:CGRectZero];
    if (self) {
        _document = [document retain];
        _page = page;
        
        YLPageInfo *pageInfo = [_document pageInfoForPage:page];
        if(pageInfo) {
            CGRect pageRect = [pageInfo targetRectForSize:YLPDFImageSizeOriginal];
            pageRect.origin.x = 0;
            pageRect.origin.y = 0;
            self.frame = pageRect;
        }
        
        self.backgroundColor = [UIColor clearColor];
        self.userInteractionEnabled = NO;
        self.autoresizesSubviews = NO;
        self.autoresizingMask = UIViewAutoresizingNone;
        self.clearsContextBeforeDrawing = NO;
        self.contentMode = UIViewContentModeRedraw;
    }
    
    return self;
}

- (void)dealloc {
    [_document release];
    
    [super dealloc];
}

- (void)layoutSubviews {
    [super layoutSubviews];
    
    self.contentScaleFactor = 1.0;
}

- (void)didMoveToWindow {
    [super didMoveToWindow];
    
    self.contentScaleFactor = 1.0;
}

- (void)drawLayer:(CALayer *)layer inContext:(CGContextRef)ctx {
	CGContextSetRGBFillColor(ctx, 1.0f, 1.0f, 1.0f, 1.0f);
	CGContextFillRect(ctx, CGContextGetClipBoundingBox(ctx));
    
    [_document renderPage:_page targetRect:self.bounds inContext:ctx];
}

@end

@implementation YLTiledLayer

+ (CFTimeInterval)fadeDuration {
    return 0.0; // defaults to 0.25, disable fade in animation
}

- (id)init {
    self = [super init];
    if(self) {
        self.levelsOfDetail = ZOOM_LEVELS;
        self.levelsOfDetailBias = ZOOM_LEVELS - 1;
        
        CGRect screenRect = [[UIScreen mainScreen] bounds];
        CGFloat scale = [[UIScreen mainScreen] scale];
        CGFloat w = screenRect.size.width * scale;
        CGFloat h = screenRect.size.height * scale;
        CGFloat max = MAX(w, h);
        CGFloat tileSize;
        
        if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
            tileSize = (max <= 1024.0) ? 1024.0 : 2048.0;
        } else {
            tileSize = (max < 512.0) ? 512.0 : 1024.0;
        }
        
        self.tileSize = CGSizeMake(tileSize, tileSize);
    }
    
    return self;
}

- (void)dealloc {
    [super dealloc];
}

@end
