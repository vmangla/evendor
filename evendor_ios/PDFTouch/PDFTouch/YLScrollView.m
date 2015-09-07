//
//  YLScrollView.m
//
//  Created by Kemal Taskin on 3/27/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLScrollView.h"
#import "YLPageView.h"
#import "YLDocument.h"
#import "YLGlobal.h"
#import "YLAnnotationParser.h"
#import "YLAnnotation.h"
#import "YLAnnotationView.h"
#import "YLLinkAnnotationView.h"
#import "YLPDFViewController.h"
#import <QuartzCore/QuartzCore.h>

@interface YLScrollView () {
    YLDocument *_document;
    NSUInteger _page;
    YLDocumentMode _documentMode;
    YLDocumentLead _documentLead;
    
    UIView *_containerView;
    YLPageView *_leftPageView;
    YLPageView *_rightPageView;
    
    YLPDFViewController *_pdfViewController;
    CGFloat _zoomIncrement;
}

- (CGPoint)pointToCenterAfterRotation;
- (CGFloat)scaleToRestoreAfterRotation;
- (void)restoreCenterPoint:(CGPoint)oldCenter scale:(CGFloat)oldScale;
- (void)updateMaxMinZoomForCurrentBounds;
- (void)handleDoubleTap:(UIGestureRecognizer *)gr;
- (void)createContainerView;
- (void)reset;

@end

@implementation YLScrollView

@synthesize page = _page;
@synthesize documentMode = _documentMode;
@synthesize documentLead = _documentLead;
@synthesize leftPageView = _leftPageView;
@synthesize rightPageView = _rightPageView;
@synthesize pdfViewController = _pdfViewController;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        _document = nil;
        _page = NSNotFound;
        _documentMode = YLDocumentModeSingle;
        _documentLead = YLDocumentLeadRight;
        _pdfViewController = nil;
        
        self.backgroundColor = [UIColor clearColor];
        self.scrollsToTop = NO;
        self.showsVerticalScrollIndicator = NO;
        self.showsHorizontalScrollIndicator = NO;
        self.delaysContentTouches = YES;
        self.autoresizesSubviews = NO;
        self.autoresizingMask = UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleHeight;
        self.userInteractionEnabled = YES;
        self.contentMode = UIViewContentModeRedraw;
        self.bouncesZoom = YES;
        self.decelerationRate = UIScrollViewDecelerationRateFast;
        self.delegate = self;
        
        _containerView = nil;
        _leftPageView = nil;
        _rightPageView = nil;
    }
    
    return self;
}

- (void)dealloc {
    [_document release];
    
    [_containerView release];
    [_leftPageView release];
    [_rightPageView release];
    
    [super dealloc];
}

- (void)layoutSubviews {
    [super layoutSubviews];
    
    // center the container view as it becomes smaller than the size of the screen
    CGSize boundsSize = self.bounds.size;
    CGRect frameToCenter = _containerView.frame;
    
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
    
    _containerView.frame = frameToCenter;
}


#pragma mark -
#pragma mark UIScrollViewDelegate Methods
- (UIView *)viewForZoomingInScrollView:(UIScrollView *)scrollView {
    return _containerView;
}

- (void)scrollViewDidZoom:(UIScrollView *)scrollView {
    if(self.zoomScale == self.minimumZoomScale) {
        if(_leftPageView) {
            [_leftPageView disableTiling];
        }
        if(_rightPageView) {
            [_rightPageView disableTiling];
        }
    } else if(self.zoomScale > self.minimumZoomScale) {
        if(_leftPageView) {
            [_leftPageView enableTiling];
        }
        if(_rightPageView) {
            [_rightPageView enableTiling];
        }
    }
}


#pragma mark -
#pragma mark Instance Methods
- (void)displayDocument:(YLDocument *)document withPage:(NSUInteger)page {
    if(_page == page) {
        return;
    }
    
    _page = page;
    if(_document == nil) {
        _document = [document retain];
    }
    
    // cleanup previous pages
    [self reset];
    
    if(_documentMode == YLDocumentModeSingle) {
        _leftPageView = [[YLPageView alloc] initWithDocument:_document page:_page];
        [_leftPageView setPdfViewController:_pdfViewController];
        [self createContainerView];
        [_containerView addSubview:_leftPageView];
    } else {
        if(page == 0 && _documentLead == YLDocumentLeadRight) {
            _rightPageView = [[YLPageView alloc] initWithDocument:_document page:page];
            [_rightPageView setPdfViewController:_pdfViewController];
            [self createContainerView];
            CGRect frame = _rightPageView.frame;
            frame.origin.x = floorf(_containerView.frame.size.width / 2.0);
            _rightPageView.frame = frame;
            [_containerView addSubview:_rightPageView];
        } else {
            if((page + 1) >= _document.pageCount) { // show left single page only
                _leftPageView = [[YLPageView alloc] initWithDocument:_document page:page];
                [_leftPageView setPdfViewController:_pdfViewController];
                [self createContainerView];
                [_containerView addSubview:_leftPageView];
            } else { // show double pages
                _leftPageView = [[YLPageView alloc] initWithDocument:_document page:page];
                [_leftPageView setPdfViewController:_pdfViewController];
                _rightPageView = [[YLPageView alloc] initWithDocument:_document page:(page + 1)];
                [_rightPageView setPdfViewController:_pdfViewController];
                [self createContainerView];
                
                CGSize leftPageSize = _leftPageView.bounds.size;
                CGSize rightPageSize = _rightPageView.bounds.size;
                if(leftPageSize.width != rightPageSize.width || leftPageSize.height != rightPageSize.height) {
                    CGSize containerSize = _containerView.bounds.size;
                    CGFloat lx = floorf((containerSize.width / 2.0) - leftPageSize.width);
                    CGFloat ly = floorf(containerSize.height - leftPageSize.height) / 2.0;
                    CGRect frame = _leftPageView.frame;
                    frame.origin.x = lx;
                    frame.origin.y = ly;
                    _leftPageView.frame = frame;
                    [_containerView addSubview:_leftPageView];
                    
                    frame = _rightPageView.frame;
                    frame.origin.y = floorf((containerSize.height - rightPageSize.height) / 2.0);
                    frame.origin.x = floorf(containerSize.width / 2.0);
                    _rightPageView.frame = frame;
                    [_containerView addSubview:_rightPageView];
                } else {
                    [_containerView addSubview:_leftPageView];
                    CGRect frame = _rightPageView.frame;
                    frame.origin.x = floorf(_containerView.frame.size.width / 2.0);
                    _rightPageView.frame = frame;
                    [_containerView addSubview:_rightPageView];
                }
            }
        }
    }
    
    [self updateMaxMinZoomForCurrentBounds];
    self.zoomScale = self.minimumZoomScale;
}

- (void)invalidate {
    _page = NSNotFound;
    [self reset];
}

- (void)updateForSearchResults {
    if(_leftPageView) {
        [_leftPageView updateForSearchResults];
    }
    if(_rightPageView) {
        [_rightPageView updateForSearchResults];
    }
}

- (void)showAnnotations {
    if(_leftPageView) {
        [_leftPageView showAnnotations];
    }
    if(_rightPageView) {
        [_rightPageView showAnnotations];
    }
}

- (void)hideAnnotations {
    if(_leftPageView) {
        [_leftPageView hideAnnotations];
    }
    if(_rightPageView) {
        [_rightPageView hideAnnotations];
    }
}

- (void)zoomInAnimated:(BOOL)animated {
    CGFloat currentZoom = self.zoomScale;
    CGFloat newZoom = currentZoom + _zoomIncrement;
    if(newZoom > self.maximumZoomScale) {
        newZoom = self.maximumZoomScale;
    }
    
    if(newZoom > self.minimumZoomScale) {
        if(_leftPageView) {
            [_leftPageView enableTiling];
        }
        if(_rightPageView) {
            [_rightPageView enableTiling];
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
        if(_leftPageView) {
            [_leftPageView disableTiling];
        }
        if(_rightPageView) {
            [_rightPageView disableTiling];
        }
    }
    
    [self setZoomScale:newZoom animated:animated];
}

- (void)updateContentViewsWithFrame:(CGRect)frame {
    CGPoint restorePoint = [self pointToCenterAfterRotation];
    CGFloat restoreScale = [self scaleToRestoreAfterRotation];
    self.frame = frame;
    [self updateMaxMinZoomForCurrentBounds];
    [self restoreCenterPoint:restorePoint scale:restoreScale];
}

- (void)resetZoomAnimated:(BOOL)animated {
    if(self.zoomScale != self.minimumZoomScale) {
        [self setZoomScale:self.minimumZoomScale animated:animated];
    }
}


#pragma mark -
#pragma mark Private Methods
- (CGPoint)pointToCenterAfterRotation {
    CGPoint boundsCenter = CGPointMake(CGRectGetMidX(self.bounds), CGRectGetMidY(self.bounds));
    return [self convertPoint:boundsCenter toView:_containerView];
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
    CGPoint boundsCenter = [self convertPoint:oldCenter fromView:_containerView];
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
    CGSize containerSize = _containerView.bounds.size;
    
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
            
            if(_leftPageView) {
                [_leftPageView enableTiling];
            }
            if(_rightPageView) {
                [_rightPageView enableTiling];
            }
            
            //apply the resize
            [self zoomToRect:zoomRect animated:YES];
        }
    }
}

- (void)createContainerView {
    if(_containerView) {
        return;
    }
    
    CGRect containerFrame = CGRectZero;
    UIBezierPath *shadowPath = nil;
    if(_documentMode == YLDocumentModeSingle) {
        if(_leftPageView) {
            containerFrame = _leftPageView.bounds;
            shadowPath = [UIBezierPath bezierPathWithRect:containerFrame];
        }
    } else {
        CGSize pageSize = CGSizeZero;
        if(_leftPageView && _rightPageView) {
            CGSize leftPageSize = _leftPageView.bounds.size;
            CGSize rightPageSize = _rightPageView.bounds.size;
            CGFloat maxWidth = MAX(leftPageSize.width, rightPageSize.width);
            CGFloat maxHeigth = MAX(leftPageSize.height, rightPageSize.height);
            pageSize = CGSizeMake(maxWidth * 2.0, maxHeigth);
            
            if(leftPageSize.width != rightPageSize.width || leftPageSize.height != rightPageSize.height) {
                CGFloat lx = floorf(maxWidth - leftPageSize.width);
                CGFloat ly = floorf((maxHeigth - leftPageSize.height) / 2.0);
                CGFloat rx = maxWidth;
                CGFloat ry = floorf((maxHeigth - rightPageSize.height) / 2.0);

                shadowPath = [UIBezierPath bezierPath];
                [shadowPath moveToPoint:CGPointMake(lx, ly)];
                [shadowPath addLineToPoint:CGPointMake(rx, ly)];
                [shadowPath addLineToPoint:CGPointMake(rx, ry)];
                [shadowPath addLineToPoint:CGPointMake(rx + rightPageSize.width, ry)];
                [shadowPath addLineToPoint:CGPointMake(rx + rightPageSize.width, ry + rightPageSize.height)];
                [shadowPath addLineToPoint:CGPointMake(rx, ry + rightPageSize.height)];
                [shadowPath addLineToPoint:CGPointMake(rx, ly + leftPageSize.height)];
                [shadowPath addLineToPoint:CGPointMake(lx, ly + leftPageSize.height)];
                [shadowPath closePath];
            } else {
                shadowPath = [UIBezierPath bezierPathWithRect:CGRectMake(0, 0, maxWidth * 2.0, maxHeigth)];
            }
        } else if(_leftPageView) {
            pageSize = _leftPageView.bounds.size;
            shadowPath = [UIBezierPath bezierPathWithRect:CGRectMake(0, 0, pageSize.width, pageSize.height)];
            pageSize.width = pageSize.width * 2.0;
        } else if(_rightPageView) {
            pageSize = _rightPageView.bounds.size;
            shadowPath = [UIBezierPath bezierPathWithRect:CGRectMake(pageSize.width, 0, pageSize.width, pageSize.height)];
            pageSize.width = pageSize.width * 2.0;
        }
        
        containerFrame = CGRectMake(0, 0, pageSize.width, pageSize.height);
    }
    
    _containerView = [[UIView alloc] initWithFrame:containerFrame];
    _containerView.backgroundColor = [UIColor clearColor];
    _containerView.userInteractionEnabled = YES;
    _containerView.autoresizesSubviews = NO;
    _containerView.autoresizingMask = UIViewAutoresizingNone;
    _containerView.contentMode = UIViewContentModeRedraw;
    _containerView.layer.shadowOffset = CGSizeMake(SCROLLVIEW_SHADOW_OFFSET, SCROLLVIEW_SHADOW_OFFSET);
    _containerView.layer.shadowRadius = SCROLLVIEW_SHADOW_RADIUS;
    _containerView.layer.shadowOpacity = SCROLLVIEW_SHADOW_OPACITY;
    // Omitting the shadowPath will result in a performance hit! Tiling will be very slow!
    if(shadowPath) {
        _containerView.layer.shadowPath = shadowPath.CGPath;
    }
    
    UITapGestureRecognizer *doubleTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(handleDoubleTap:)];
    [doubleTap setNumberOfTouchesRequired:1];
    [doubleTap setNumberOfTapsRequired:2];
    [_containerView addGestureRecognizer:doubleTap];
    [doubleTap release];
    
    self.contentSize = _containerView.bounds.size;
    [self addSubview:_containerView];
}

- (void)reset {
    if(_leftPageView) {
        [_leftPageView removeFromSuperview];
        [_leftPageView release];
        _leftPageView = nil;
    }
    
    if(_rightPageView) {
        [_rightPageView removeFromSuperview];
        [_rightPageView release];
        _rightPageView = nil;
    }
    
    [_containerView removeFromSuperview];
    [_containerView release];
    _containerView = nil;
}

@end
