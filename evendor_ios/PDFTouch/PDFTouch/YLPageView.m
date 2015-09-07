//
//  YLPageView.m
//
//  Created by Kemal Taskin on 3/27/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPageView.h"
#import "YLTiledView.h"
#import "YLDocument.h"
#import "YLDocumentScanner.h"
#import "YLAnnotationParser.h"
#import "YLAnnotation.h"
#import "YLAnnotationView.h"
#import "YLLinkAnnotationView.h"
#import "YLPageInfo.h"
#import "YLOperation.h"
#import "YLAnnotationView.h"
#import "YLPDFViewController.h"
#import "YLPDFViewControllerDataSource.h"
#import "YLPDFViewControllerDelegate.h"

@interface YLPageView () {
    YLDocument *_document;
    NSUInteger _page;
    
    UIImageView *_imageView;
    YLTiledView *_tiledView;
    
    BOOL _loadingAnnotations;
    BOOL _tilingEnabled;
    BOOL _forceTiling;
    
    NSMutableArray *_annotationViews;
    NSMutableArray *_customAnnotationViews;
    YLOperation *_operation;
    NSTimer *_retryTimer;
}

- (void)retryTimerFired:(NSTimer *)timer;

@end

@implementation YLPageView

@synthesize pdfViewController = _pdfViewController;

- (id)initWithDocument:(YLDocument *)document page:(NSUInteger)page {
    self = [super initWithFrame:CGRectZero];
    if (self) {
        _document = [document retain];
        _page = page;
        _pdfViewController = nil;
        _tilingEnabled = NO;
        _forceTiling = YES;
        _loadingAnnotations = NO;
        _annotationViews = nil;
        _customAnnotationViews = nil;
        _operation = nil;
        
        CGRect tileRect = CGRectZero;
        YLPageInfo *pageInfo = [_document pageInfoForPage:_page];
        if(pageInfo) {
            tileRect = [pageInfo targetRectForSize:YLPDFImageSizeOriginal];
            tileRect.origin.x = 0;
            tileRect.origin.y = 0;
        }
        
        _imageView = [[UIImageView alloc] initWithFrame:tileRect];
        [_imageView setContentMode:UIViewContentModeScaleAspectFill];
        [_imageView setClipsToBounds:YES];
        
        self.frame = tileRect;
        [self addSubview:_imageView];
        
        self.autoresizesSubviews = NO;
        self.autoresizingMask = UIViewAutoresizingNone;
        self.backgroundColor = [UIColor whiteColor];
        self.userInteractionEnabled = YES;
        self.clearsContextBeforeDrawing = NO;
        self.contentMode = UIViewContentModeRedraw;
        
        YLCache *cache = [YLCache sharedCache];
        [cache addDelegate:self delegateQueue:dispatch_get_main_queue()];
        id pageImage = [cache cachedImageForDocument:_document page:_page size:YLPDFImageSizeOriginal];
        if(pageImage != nil) {
            if([pageImage isKindOfClass:[UIImage class]]) {
                [_imageView setImage:pageImage];
                if(_forceTiling) {
                    [self enableTiling];
                }
            } else if([pageImage isKindOfClass:[YLOperation class]]) {
                _operation = [pageImage retain];
            }
        } else {
            // The image could be in a write operation while we requested it, so we wait and try again later.
            _retryTimer = [NSTimer scheduledTimerWithTimeInterval:0.1 target:self selector:@selector(retryTimerFired:) userInfo:nil repeats:NO];
        }
        
        NSArray *searchResults = [_document.scanner searchResultsForPage:_page];
        if(searchResults && [searchResults count] > 0) {
            [self enableTiling];
        }
    }
    
    return self;
}

- (void)dealloc {
    [[YLCache sharedCache] removeDelegate:self];
    if(_retryTimer) {
        [_retryTimer invalidate];
    }
    [_document release];
    
    [_imageView release];
    [_tiledView release];
    [_annotationViews release];
    [_customAnnotationViews release];
    if(_operation) {
        [[YLCache sharedCache] cancelOperation:_operation];
        [_operation release];
        _operation = nil;
    }
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)updateForSearchResults {
    NSArray *searchResults = [_document.scanner searchResultsForPage:_page];
    if(searchResults && [searchResults count] > 0) {
        if(_tilingEnabled) {
            // recreate tiled view to correctly display search results at all zoom levels
            _tilingEnabled = NO;
            if(_tiledView) {
                [_tiledView removeFromSuperview];
                [_tiledView release];
                _tiledView = nil;
            }
        }
        
        [self enableTiling];
    } else {
        // no search results - clear previous one if tiling is enabled
        if(_tilingEnabled) {
            _tilingEnabled = NO;
            if(_tiledView) {
                [_tiledView removeFromSuperview];
                [_tiledView release];
                _tiledView = nil;
            }
            
            [self enableTiling];
        }
    }
}

- (void)enableTiling {
    if(_tilingEnabled) {
        return;
    }
    
    if(_tiledView == nil) {
        _tiledView = [[YLTiledView alloc] initWithDocument:_document page:_page];
    }
    
    _tilingEnabled = YES;
    [self addSubview:_tiledView];
    
    NSArray *subviews = [self subviews];
    for(UIView *view in subviews) {
        if([view conformsToProtocol:@protocol(YLAnnotationView)]) {
            [self bringSubviewToFront:view];
        }
    }
}

- (void)disableTiling {
    if(!_tilingEnabled || _forceTiling) {
        return;
    }
    
    NSArray *searchResults = [_document.scanner searchResultsForPage:_page];
    if(searchResults && [searchResults count] > 0) {
        return;
    }
    
    _tilingEnabled = NO;
    if(_tiledView) {
        [_tiledView removeFromSuperview];
    }
}

- (void)showAnnotations {
    if(_annotationViews != nil || _loadingAnnotations) {
        return;
    }
    
    _loadingAnnotations = YES;
    NSArray *annotations = [[_document annotationParser] annotationsForPage:_page];
    _annotationViews = [[NSMutableArray alloc] initWithCapacity:[annotations count]];
    
    if(annotations) {
        BOOL informDelegate = NO;
        if(_pdfViewController.delegate && [_pdfViewController.delegate respondsToSelector:@selector(pdfViewController:willDisplayAnnotationView:onPageView:)]) {
            informDelegate = YES;
        }
        
        for(YLAnnotation *annot in annotations) {
            CGRect viewRect = [annot viewRectForRect:self.bounds];
            if(fabsf(viewRect.size.height) < 6.0) {
                continue;
            }
            
            UIView<YLAnnotationView> *view = [_document.annotationParser viewForAnnotation:annot frame:viewRect];
            if(view) {
                [view setPdfViewController:_pdfViewController];
                [view didShowPage:_page];
                if(informDelegate) {
                    [_pdfViewController.delegate pdfViewController:_pdfViewController willDisplayAnnotationView:view onPageView:self];
                }
                [_annotationViews addObject:view];
                [self addSubview:view];
            }
        }
    }
    
    if(_pdfViewController.datasource && [_pdfViewController.datasource respondsToSelector:@selector(pdfViewController:annotationViewsForPage:viewRect:)]) {
        NSArray *overlays = [_pdfViewController.datasource pdfViewController:_pdfViewController annotationViewsForPage:_page viewRect:self.bounds];
        if(overlays) {
            _customAnnotationViews = [[NSMutableArray alloc] initWithCapacity:overlays.count];
            for(id obj in overlays) {
                if([obj isKindOfClass:[UIView class]] && [obj conformsToProtocol:@protocol(YLAnnotationView)]) {
                    UIView<YLAnnotationView> *view = (UIView<YLAnnotationView>*)obj;
                    [view setPdfViewController:_pdfViewController];
                    [view didShowPage:_page];
                    [_customAnnotationViews addObject:view];
                    [self addSubview:view];
                }
            }
        }
    }
    
    _loadingAnnotations = NO;
}

- (void)hideAnnotations {
    if(_annotationViews) {
        for(UIView<YLAnnotationView> *view in _annotationViews) {
            [view willHidePage:_page];
            [view removeFromSuperview];
        }
        
        [_annotationViews removeAllObjects];
        [_annotationViews release];
        _annotationViews = nil;
    }
    
    if(_customAnnotationViews) {
        for(UIView<YLAnnotationView> *view in _customAnnotationViews) {
            [view willHidePage:_page];
            [view removeFromSuperview];
        }
        
        [_customAnnotationViews removeAllObjects];
        [_customAnnotationViews release];
        _customAnnotationViews = nil;
    }
}


#pragma mark -
#pragma mark YLCacheDelegate Methods
- (void)didCacheDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size image:(UIImage *)image {
    if([_document.uuid isEqualToString:document.uuid] && page == _page && size == YLPDFImageSizeOriginal) {
        if(_operation) {
            [_operation release];
            _operation = nil;
        }
        [_imageView setImage:image];
        if(_forceTiling) {
            [self enableTiling];
        }
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)retryTimerFired:(NSTimer *)timer {
    id pageImage = [[YLCache sharedCache] cachedImageForDocument:_document page:_page size:YLPDFImageSizeOriginal];
    if(pageImage != nil) {
        if([pageImage isKindOfClass:[UIImage class]]) {
            [_imageView setImage:pageImage];
            if(_forceTiling) {
                [self enableTiling];
            }
        } else if([pageImage isKindOfClass:[YLOperation class]]) {
            _operation = [pageImage retain];
        }
        
        _retryTimer = nil;
    } else {
        // The image could be in a write operation while we requested it, so we wait and try again later.
        _retryTimer = [NSTimer scheduledTimerWithTimeInterval:0.1 target:self selector:@selector(retryTimerFired:) userInfo:nil repeats:NO];
    }
}

@end
