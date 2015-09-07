//
//  YLPagesScrollView.m
//
//  Created by Kemal Taskin on 6/13/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPagesScrollView.h"
#import "YLPagesViewController.h"
#import "YLPageViewController.h"
#import "YLPageView.h"
#import "YLGlobal.h"

@interface YLPagesScrollView () {
    YLPagesViewController *_pagesViewController;
    CGFloat _zoomIncrement;
    NSUInteger _prevPortraitPage;
}

- (CGPoint)pointToCenterAfterRotation;
- (CGFloat)scaleToRestoreAfterRotation;
- (void)restoreCenterPoint:(CGPoint)oldCenter scale:(CGFloat)oldScale;
- (void)updateMaxMinZoomForCurrentBounds;
- (void)handleDoubleTap:(UIGestureRecognizer *)gr;

@end

@implementation YLPagesScrollView

@synthesize pagesViewController = _pagesViewController;

- (id)initWithFrame:(CGRect)frame pdfController:(YLPDFViewController *)controller {
    self = [super initWithFrame:frame];
    if (self) {
        [super setPdfViewController:controller];
        
        _prevPortraitPage = 0;
        _pagesViewController = [[YLPagesViewController alloc] initWithPDFController:controller];
        [_pagesViewController addObserver:self forKeyPath:@"page" options:NSKeyValueObservingOptionNew context:NULL];
        
        for(UIGestureRecognizer *g in _pagesViewController.gestureRecognizers) {
            if([g isKindOfClass:[UITapGestureRecognizer class]]) {
                [g setEnabled:NO];
            }
        }
    }

    return self;
}

- (void)dealloc {
    [_pagesViewController removeObserver:self forKeyPath:@"page"];
    [_pagesViewController release];
    
    [super dealloc];
}

- (void)layoutSubviews {
    [super layoutSubviews];
    
    // center the container view as it becomes smaller than the size of the screen
    CGSize boundsSize = self.bounds.size;
    CGRect frameToCenter = _pagesViewController.view.frame;
    
    // center horizontally
    if(frameToCenter.size.width < boundsSize.width) {
        frameToCenter.origin.x = (boundsSize.width - frameToCenter.size.width) / 2;
	} else {
        frameToCenter.origin.x = 0;
	}
    
    // center vertically
    if(frameToCenter.size.height < boundsSize.height) {
        frameToCenter.origin.y = (boundsSize.height - frameToCenter.size.height) / 2;
	} else {
        frameToCenter.origin.y = 0;
	}
    
    _pagesViewController.view.frame = frameToCenter;
}

- (void)observeValueForKeyPath:(NSString *)keyPath ofObject:(id)object change:(NSDictionary *)change context:(void *)context {
    if(object == _pagesViewController) {
        if([keyPath isEqualToString:@"page"]) {
            if(_pagesViewController.view.superview == nil) {
                [self addSubview:_pagesViewController.view];
                
                UITapGestureRecognizer *doubleTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(handleDoubleTap:)];
                [doubleTap setNumberOfTouchesRequired:1];
                [doubleTap setNumberOfTapsRequired:2];
                [_pagesViewController.view addGestureRecognizer:doubleTap];
                [doubleTap release];
                
                [self updateMaxMinZoomForCurrentBounds];
            }
            
            [self updateMaxMinZoomForCurrentBounds];
            self.zoomScale = self.minimumZoomScale;
        }
    }
}


#pragma mark -
#pragma mark UIScrollViewDelegate Methods
- (UIView *)viewForZoomingInScrollView:(UIScrollView *)scrollView {
    return _pagesViewController.view;
}

- (void)scrollViewDidZoom:(UIScrollView *)scrollView {
    if(self.zoomScale == self.minimumZoomScale) {
        NSArray *viewControllers = _pagesViewController.viewControllers;
        for(UIViewController *controller in viewControllers) {
            YLPageViewController *pageController = (YLPageViewController *)controller;
            [pageController.pageView disableTiling];
        }
    } else if(self.zoomScale > self.minimumZoomScale) {
        NSArray *viewControllers = _pagesViewController.viewControllers;
        for(UIViewController *controller in viewControllers) {
            YLPageViewController *pageController = (YLPageViewController *)controller;
            [pageController.pageView enableTiling];
        }
    }
}


#pragma mark -
#pragma mark Instance Methods
- (void)setDocumentMode:(YLDocumentMode)documentMode {
    [super setDocumentMode:documentMode];
    
    if(documentMode == YLDocumentModeDouble) {
        _prevPortraitPage = _pagesViewController.page;
    }
 
    // recreate pages view controller
    NSUInteger currentPage = _pagesViewController.page;
    [_pagesViewController.view removeFromSuperview];
    [_pagesViewController removeObserver:self forKeyPath:@"page"];
    [_pagesViewController release];
    _pagesViewController = nil;
    
    _pagesViewController = [[YLPagesViewController alloc] initWithPDFController:[super pdfViewController]];
    [_pagesViewController addObserver:self forKeyPath:@"page" options:NSKeyValueObservingOptionNew context:NULL];
    for(UIGestureRecognizer *g in _pagesViewController.gestureRecognizers) {
        if([g isKindOfClass:[UITapGestureRecognizer class]]) {
            [g setEnabled:NO];
        }
    }
    
    if(documentMode == YLDocumentModeSingle && (currentPage == (_prevPortraitPage - 1))) {
        currentPage = _prevPortraitPage;
    }
    [_pagesViewController showPage:currentPage animated:NO];
}

- (void)setDocumentLead:(YLDocumentLead)documentLead {
    [super setDocumentLead:documentLead];
    
    [_pagesViewController setDocumentLead:documentLead];
}

- (void)showPage:(NSUInteger)page animated:(BOOL)animated {
    [_pagesViewController showPage:page animated:animated];
}

- (void)displayDocument:(YLDocument *)document withPage:(NSUInteger)page {
    // no-op
}

- (void)updateForSearchResults {
    NSArray *viewControllers = _pagesViewController.viewControllers;
    for(UIViewController *controller in viewControllers) {
        YLPageViewController *pageController = (YLPageViewController *)controller;
        [pageController.pageView updateForSearchResults];
    }
}

- (void)showAnnotations {
    NSArray *viewControllers = _pagesViewController.viewControllers;
    for(UIViewController *controller in viewControllers) {
        YLPageViewController *pageController = (YLPageViewController *)controller;
        [pageController.pageView showAnnotations];
    }
}

- (void)hideAnnotations {
    NSArray *viewControllers = _pagesViewController.viewControllers;
    for(UIViewController *controller in viewControllers) {
        YLPageViewController *pageController = (YLPageViewController *)controller;
        [pageController.pageView hideAnnotations];
    }
}

- (void)zoomInAnimated:(BOOL)animated {
    CGFloat currentZoom = self.zoomScale;
    CGFloat newZoom = currentZoom + _zoomIncrement;
    if(newZoom > self.maximumZoomScale) {
        newZoom = self.maximumZoomScale;
    }
    
    if(newZoom > self.minimumZoomScale) {
        NSArray *viewControllers = _pagesViewController.viewControllers;
        for(UIViewController *controller in viewControllers) {
            YLPageViewController *pageController = (YLPageViewController *)controller;
            [pageController.pageView enableTiling];
        }
    }
    
    [self setZoomScale:newZoom animated:animated];
}

- (void)zoomOutAnimated:(BOOL)animated {
    CGFloat currentZoom = self.zoomScale;
    CGFloat newZoom = currentZoom - _zoomIncrement;
    if(newZoom < self.minimumZoomScale) {
        newZoom = self.minimumZoomScale;
    }
    
    if(newZoom == self.minimumZoomScale) {
        NSArray *viewControllers = _pagesViewController.viewControllers;
        for(UIViewController *controller in viewControllers) {
            YLPageViewController *pageController = (YLPageViewController *)controller;
            [pageController.pageView disableTiling];
        }
    }
    
    [self setZoomScale:newZoom animated:animated];
}

- (void)updateContentViewsWithFrame:(CGRect)frame {
    if(_pagesViewController.page == NSNotFound) {
        return;
    }
    
    CGPoint restorePoint = [self pointToCenterAfterRotation];
    CGFloat restoreScale = [self scaleToRestoreAfterRotation];
    self.frame = frame;
    [self updateMaxMinZoomForCurrentBounds];
    [self restoreCenterPoint:restorePoint scale:restoreScale];
}


#pragma mark -
#pragma mark Private Methods
- (CGPoint)pointToCenterAfterRotation {
    CGPoint boundsCenter = CGPointMake(CGRectGetMidX(self.bounds), CGRectGetMidY(self.bounds));
    return [self convertPoint:boundsCenter toView:_pagesViewController.view];
}

- (CGFloat)scaleToRestoreAfterRotation {
    CGFloat contentScale = self.zoomScale;
    
    if(contentScale <= self.minimumZoomScale + FLT_EPSILON) {
        contentScale = 0;
	}
    
    return contentScale;
}

- (void)restoreCenterPoint:(CGPoint)oldCenter scale:(CGFloat)oldScale {
    // Step 1: restore zoom scale, first making sure it is within the allowable range.
    self.zoomScale = MIN(self.maximumZoomScale, MAX(self.minimumZoomScale, oldScale));
    
    // Step 2: restore center point, first making sure it is within the allowable range.
    // 2a: convert our desired center point back to our own coordinate space
    CGPoint boundsCenter = [self convertPoint:oldCenter fromView:_pagesViewController.view];
    // 2b: calculate the content offset that would yield that center point
    CGPoint offset = CGPointMake(boundsCenter.x - self.bounds.size.width / 2.0, 
                                 boundsCenter.y - self.bounds.size.height / 2.0);
    // 2c: restore offset, adjusted to be within the allowable range
    CGSize contentSize = self.contentSize;
    CGSize boundsSize = self.bounds.size;
    CGPoint maxOffset = CGPointMake(contentSize.width - boundsSize.width, contentSize.height - boundsSize.height);
    CGPoint minOffset = CGPointZero;
    offset.x = MAX(minOffset.x, MIN(maxOffset.x, offset.x));
    offset.y = MAX(minOffset.y, MIN(maxOffset.y, offset.y));
    
    self.contentOffset = offset;
}

- (void)updateMaxMinZoomForCurrentBounds {
    CGSize boundsSize = CGRectInset(self.bounds, SCROLLVIEW_CONTENT_PADDING, SCROLLVIEW_CONTENT_PADDING).size;
    CGSize containerSize = _pagesViewController.view.bounds.size;
    
    // calculate min/max zoomscale
    CGFloat xScale = boundsSize.width / containerSize.width;    // the scale needed to perfectly fit the image width-wise
    CGFloat yScale = boundsSize.height / containerSize.height;  // the scale needed to perfectly fit the image height-wise
    CGFloat minScale = MIN(xScale, yScale);                     // use minimum of these to allow the image to become fully visible
    
    self.minimumZoomScale = minScale;
    self.maximumZoomScale = minScale * ZOOM_LEVELS;
    
    _zoomIncrement = ((self.maximumZoomScale - self.minimumZoomScale) / ZOOM_LEVELS);
}

- (void)handleDoubleTap:(UIGestureRecognizer *)gr {
    if([gr state] == UIGestureRecognizerStateRecognized) {
        if(self.zoomScale != self.minimumZoomScale) {
            // zoom out
            [self resetZoomAnimated:YES];
        } else {
            CGPoint zoomPoint = [gr locationInView:self];
            //Normalize current content size back to content scale of 1.0f
            CGSize contentSize;
            contentSize.width = (self.contentSize.width / self.zoomScale);
            contentSize.height = (self.contentSize.height / self.zoomScale);
            
            //translate the zoom point to relative to the content rect
            zoomPoint.x = (zoomPoint.x / self.bounds.size.width) * contentSize.width;
            zoomPoint.y = (zoomPoint.y / self.bounds.size.height) * contentSize.height;
            
            CGFloat currentZoom = self.zoomScale;
            CGFloat newZoom = currentZoom + (_zoomIncrement * 2);
            if(newZoom > self.maximumZoomScale) {
                newZoom = self.maximumZoomScale;
            }
            
            //derive the size of the region to zoom to
            CGSize zoomSize;
            zoomSize.width = self.bounds.size.width / newZoom;
            zoomSize.height = self.bounds.size.height / newZoom;
            
            //offset the zoom rect so the actual zoom point is in the middle of the rectangle
            CGRect zoomRect;
            zoomRect.origin.x = zoomPoint.x - zoomSize.width / 2.0f;
            zoomRect.origin.y = zoomPoint.y - zoomSize.height / 2.0f;
            zoomRect.size.width = zoomSize.width;
            zoomRect.size.height = zoomSize.height;
            
            NSArray *viewControllers = _pagesViewController.viewControllers;
            for(UIViewController *controller in viewControllers) {
                YLPageViewController *pageController = (YLPageViewController *)controller;
                [pageController.pageView enableTiling];
            }
            
            //apply the resize
            [self zoomToRect:zoomRect animated:YES];
        }
    }
}


@end
