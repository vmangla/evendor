//
//  YLPagesViewController.m
//
//  Created by Kemal Taskin on 6/13/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPagesViewController.h"
#import "YLDocument.h"
#import "YLPageViewController.h"
#import "YLPageView.h"
#import "YLGlobal.h"
#import <QuartzCore/QuartzCore.h>

#define ylFloatEpsilon                      1.0001
#define ylEqualFloats(f1, f2, epsilon)      (fabs((f1) - (f2)) < epsilon)
#define ylNotEqualFloats(f1, f2, epsilon)   (!ylEqualFloats(f1, f2, epsilon))

typedef enum {
    YLShadowModeNone,
    YLShadowModeFull,
    YLShadowModeLeft,
    YLShadowModeRight
} YLShadowMode;

@interface YLPagesViewController () {
    YLPDFViewController *_pdfViewController;
    
    NSUInteger _page;
    NSUInteger _newPage;
    YLDocumentMode _documentMode;
    YLDocumentLead _documentLead;
    BOOL _animating;
    BOOL _ignoreFirstCall;
}

- (void)showPage:(NSUInteger)page animated:(BOOL)animated forced:(BOOL)forced;
- (NSArray *)viewControllersForPage:(NSUInteger)page;
- (NSUInteger)leftPageForPage:(NSInteger)page;
- (void)updateShadowFrameForMode:(YLShadowMode)mode;
- (YLShadowMode)shadowModeForPage:(NSUInteger)page;

@end

@implementation YLPagesViewController

@synthesize page = _page;
@synthesize documentLead = _documentLead;

- (id)initWithPDFController:(YLPDFViewController *)controller {
    NSDictionary *options = nil;
    if(controller.documentMode == YLDocumentModeDouble) {
        options = [NSDictionary dictionaryWithObject:[NSNumber numberWithInt:UIPageViewControllerSpineLocationMid]
                                              forKey:UIPageViewControllerOptionSpineLocationKey];
    }
    
    self = [super initWithTransitionStyle:UIPageViewControllerTransitionStylePageCurl
                    navigationOrientation:UIPageViewControllerNavigationOrientationHorizontal
                                  options:options];
    if (self) {
        _pdfViewController = controller;
        if(_pdfViewController) {
            [self addObserver:_pdfViewController forKeyPath:@"page" options:NSKeyValueObservingOptionNew context:NULL];
        }
        _documentMode = _pdfViewController.documentMode;
        if(_documentMode == YLDocumentModeDouble) {
            self.doubleSided = YES;
        }
        _documentLead = _pdfViewController.documentLead;
        _page = NSNotFound;
        _newPage = NSNotFound;
        _ignoreFirstCall = YES;
        
        [self setDelegate:self];
        [self setDataSource:self];
        
        self.view.layer.shadowOffset = CGSizeMake(SCROLLVIEW_SHADOW_OFFSET, SCROLLVIEW_SHADOW_OFFSET);
        self.view.layer.shadowRadius = SCROLLVIEW_SHADOW_RADIUS;
        self.view.layer.shadowOpacity = SCROLLVIEW_SHADOW_OPACITY;
        // Omitting the shadowPath will result in a performance hit! Tiling will be very slow!
        self.view.layer.shadowPath = [UIBezierPath bezierPathWithRect:self.view.bounds].CGPath;
    }
    
    return self;
}

- (void)dealloc {
    if(_pdfViewController) {
        [self removeObserver:_pdfViewController forKeyPath:@"page"];
    }
    
    [super dealloc];
}

- (void)viewDidLoad {
    [super viewDidLoad];
}

- (void)viewDidUnload {
    [super viewDidUnload];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return YES;
}

- (BOOL)gestureRecognizerShouldBegin:(UIGestureRecognizer *)gestureRecognizer {
    if([gestureRecognizer isKindOfClass:[UIPanGestureRecognizer class]]) {
        UIPanGestureRecognizer *panGestureRecognizer = (UIPanGestureRecognizer *)gestureRecognizer;
        NSUInteger pageCount = _pdfViewController.document.pageCount;
        // velocity > 0.0 = pan to right (backward page curl)
        // velocity < 0.0 = pan to left (forward page curl)
        CGFloat velocity = [panGestureRecognizer velocityInView:gestureRecognizer.view].x;
        if(pageCount <= 1) {
            return NO;
        } else if(_page == 0 && velocity > 0.0f) {
            return NO;
        } else if(_documentMode == YLDocumentModeSingle && _page == (pageCount - 1) && velocity < 0.0f) {
            return NO;
        } else if(_documentMode == YLDocumentModeDouble && velocity < 0.0f) {
            if(_page == 0 && _documentLead == YLDocumentLeadRight) {
                return YES;
            } else if(_page == (pageCount - 1) || _page == (pageCount - 2)) {
                return NO;
            }
        }
    }
    
    return YES;
}


#pragma mark -
#pragma mark Instance Methods
- (void)setDocumentLead:(YLDocumentLead)documentLead {
    if(documentLead == _documentLead) {
        return;
    }
    
    _documentLead = documentLead;
    [self showPage:[self leftPageForPage:_page] animated:NO forced:YES];
}

- (void)showPage:(NSUInteger)page animated:(BOOL)animated {
    [self showPage:page animated:animated forced:NO];
}


#pragma mark -
#pragma mark Private Methods
- (void)showPage:(NSUInteger)page animated:(BOOL)animated forced:(BOOL)forced {
    if(_animating) {
        return;
    }
    
    page = [self leftPageForPage:page];
    if(!forced && page == _page) {
        return;
    }
    
    UIPageViewControllerNavigationDirection direction = UIPageViewControllerNavigationDirectionForward;
    if(page < _page) {
        direction = UIPageViewControllerNavigationDirectionReverse;
    }
    
    NSArray *previousControllers = [self viewControllers];
    for(int i = 0; i < [previousControllers count]; i++) {
        YLPageViewController *p = (YLPageViewController *)[previousControllers objectAtIndex:i];
        [p.pageView hideAnnotations];
    }
    
    NSArray *array = [self viewControllersForPage:page];
    
    __block BOOL updateShadows = YES;
    if(_pdfViewController.document.pageCount > 1 && (page == 0 || (page + 1) == _pdfViewController.document.pageCount)) {
        [self updateShadowFrameForMode:[self shadowModeForPage:page]];
        updateShadows = NO;
    }
    
    _animating = YES;
    [self setViewControllers:array
                   direction:direction
                    animated:animated
                  completion:^(BOOL finished) {
                      if(finished) {
                          _animating = NO;
                          YLPageViewController *p1 = nil;
                          YLPageViewController *p2 = nil;
                          if([array count] > 1) {
                              p1 = [array objectAtIndex:0];
                              p2 = [array objectAtIndex:1];
                              if(p1 && p2 && (p1.page != NSNotFound) && (p2.page != NSNotFound)) {
                                  CGSize leftPageSize = p1.pageView.bounds.size;
                                  CGSize rightPageSize = p2.pageView.bounds.size;
                                  CGFloat maxWidth = MAX(leftPageSize.width, rightPageSize.width);
                                  CGFloat maxHeigth = MAX(leftPageSize.height, rightPageSize.height);
                                  CGSize pageSize = CGSizeMake(maxWidth * 2.0, maxHeigth);
                                  
                                  if(leftPageSize.width != rightPageSize.width || leftPageSize.height != rightPageSize.height) {
                                      CGFloat lx = floorf((pageSize.width / 2.0) - leftPageSize.width);
                                      CGFloat ly = floorf(pageSize.height - leftPageSize.height) / 2.0;
                                      CGRect frame = p1.pageView.frame;
                                      frame.origin.x = lx;
                                      frame.origin.y = ly;
                                      p1.pageView.frame = frame;
                                      
                                      frame = p2.pageView.frame;
                                      frame.origin.y = floorf((pageSize.height - rightPageSize.height) / 2.0);
                                      p2.pageView.frame = frame;
                                  }
                              }
                          }
                          
                          [self willChangeValueForKey:@"page"];
                          _page = page;
                          [self didChangeValueForKey:@"page"];
                          
                          if(updateShadows) {
                              [self updateShadowFrameForMode:[self shadowModeForPage:_page]];
                          }
                      }
                  }];
}

- (NSArray *)viewControllersForPage:(NSUInteger)page {
    NSArray *array;
    
    if(_documentMode == YLDocumentModeSingle) {
        YLPageViewController *p = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:page] autorelease];
        CGSize pageSize = p.view.bounds.size;
        CGSize boundsSize = self.view.bounds.size;
        if(ylNotEqualFloats(boundsSize.width, pageSize.width, ylFloatEpsilon) ||
           ylNotEqualFloats(boundsSize.height, pageSize.height, ylFloatEpsilon)) {
            self.view.bounds = p.view.bounds;
        }
        
        array = [NSArray arrayWithObject:p];
    } else {
        if(page == 0 && _documentLead == YLDocumentLeadRight) {
            YLPageViewController *p1 = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:NSNotFound] autorelease];
            YLPageViewController *p2 = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:page] autorelease];
            
            CGSize pageSize = p2.view.bounds.size;
            self.view.bounds = CGRectMake(0, 0, pageSize.width * 2.0, pageSize.height);
            
            CGRect frame = p2.view.frame;
            frame.origin.x = pageSize.width;
            p2.view.frame = frame;
            
            array = [NSArray arrayWithObjects:p1, p2, nil];
        } else {
            if((page + 1) >= _pdfViewController.document.pageCount) { // show left single page only
                YLPageViewController *p1 = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:page] autorelease];
                YLPageViewController *p2 = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:NSNotFound] autorelease];
                
                CGSize pageSize = p1.view.bounds.size;
                self.view.bounds = CGRectMake(0, 0, pageSize.width * 2.0, pageSize.height);
                
                array = [NSArray arrayWithObjects:p1, p2, nil];
            } else { // show double pages
                YLPageViewController *p1 = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:page] autorelease];
                YLPageViewController *p2 = [[[YLPageViewController alloc] initWithPDFController:_pdfViewController page:(page + 1)] autorelease];
                
                CGSize leftPageSize = p1.view.bounds.size;
                CGSize rightPageSize = p2.view.bounds.size;
                CGFloat maxWidth = MAX(leftPageSize.width, rightPageSize.width);
                CGFloat maxHeigth = MAX(leftPageSize.height, rightPageSize.height);
                CGSize pageSize = CGSizeMake(maxWidth * 2.0, maxHeigth);
                CGSize boundsSize = self.view.bounds.size;
                if(ylNotEqualFloats(boundsSize.width, pageSize.width, ylFloatEpsilon) ||
                   ylNotEqualFloats(boundsSize.height, pageSize.height, ylFloatEpsilon)) {
                    self.view.bounds = CGRectMake(0, 0, pageSize.width, pageSize.height);
                }
                
                array = [NSArray arrayWithObjects:p1, p2, nil];
            }
        }
    }
    
    return array;
}

- (NSUInteger)leftPageForPage:(NSInteger)page {
    if(_documentMode == YLDocumentModeSingle) {
        return page;
    } else {
        if(_documentLead == YLDocumentLeadLeft) {
            return ((page / 2) * 2);
        } else {
            if(page == 0) {
                return 0;
            } else {
                return ((page % 2) == 0) ? (page - 1) : page;
            }
        }
    }
}

- (void)updateShadowFrameForMode:(YLShadowMode)mode {
    UIBezierPath *shadowPath = nil;
    if(mode == YLShadowModeLeft) {
        CGRect frame = self.view.bounds;
        frame.size.width = frame.size.width / 2.0;
        shadowPath = [UIBezierPath bezierPathWithRect:frame];
    } else if(mode == YLShadowModeRight) {
        CGRect frame = self.view.bounds;
        frame.size.width = frame.size.width / 2.0;
        frame.origin.x = frame.size.width;
        shadowPath = [UIBezierPath bezierPathWithRect:frame];
    } else if(mode == YLShadowModeNone) {
        shadowPath = [UIBezierPath bezierPathWithRect:CGRectZero];
    } else { // YLShadowModeFull
        shadowPath = [UIBezierPath bezierPathWithRect:self.view.bounds];
        
        YLPageViewController *p1 = nil;
        YLPageViewController *p2 = nil;
        NSArray *viewControllers = [self viewControllers];
        if([viewControllers count] > 1) {
            p1 = [viewControllers objectAtIndex:0];
            p2 = [viewControllers objectAtIndex:1];
            if(p1 && p2 && (p1.page != NSNotFound) && (p2.page != NSNotFound)) {
                CGSize leftPageSize = p1.pageView.bounds.size;
                CGSize rightPageSize = p2.pageView.bounds.size;
                CGFloat maxWidth = MAX(leftPageSize.width, rightPageSize.width);
                CGFloat maxHeigth = MAX(leftPageSize.height, rightPageSize.height);
                
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
                }
            }
        }
    }
    
    self.view.layer.shadowPath = shadowPath.CGPath;
}

- (YLShadowMode)shadowModeForPage:(NSUInteger)page {
    YLShadowMode shadowMode;
    if(_documentMode == YLDocumentModeSingle) {
        shadowMode = YLShadowModeFull;
    } else {
        if(_documentLead == YLDocumentLeadRight) {
            if(_pdfViewController.document.pageCount <= 2) {
                shadowMode = YLShadowModeNone;
            } else if(page == 0 || page == 1) {
                shadowMode = YLShadowModeRight;
            } else if(((page + 1) == _pdfViewController.document.pageCount) ||
                      ((page + 3) == _pdfViewController.document.pageCount)) {
                shadowMode = YLShadowModeLeft;
            } else {
                shadowMode = YLShadowModeFull;
            }
        } else { // document lead left
            if(_pdfViewController.document.pageCount == 1) {
                shadowMode = YLShadowModeNone;
            } else if(_pdfViewController.document.pageCount == 2) {
                shadowMode = YLShadowModeFull;
            } else if((page + 3) == _pdfViewController.document.pageCount) {
                shadowMode = YLShadowModeLeft;
            } else {
                shadowMode = YLShadowModeFull;
            }
        }
    }
    
    return shadowMode;
}


#pragma mark -
#pragma mark UIPageViewControllerDelegate Methods
- (UIPageViewControllerSpineLocation)pageViewController:(UIPageViewController *)pageViewController spineLocationForInterfaceOrientation:(UIInterfaceOrientation)orientation {
    if(YLIsIOS7OrGreater()) {
        if(_ignoreFirstCall) {
            _ignoreFirstCall = NO;
            
            if(_documentMode == YLDocumentModeDouble) {
                return UIPageViewControllerSpineLocationMid;
            } else {
                return UIPageViewControllerSpineLocationMin;
            }
        }
    }

    UIPageViewControllerNavigationDirection direction = UIPageViewControllerNavigationDirectionForward;
    
    NSArray *array = [self viewControllersForPage:_page];
    [self setViewControllers:array
                   direction:direction
                    animated:NO
                  completion:^(BOOL finished) {

                  }];

    if(_documentMode == YLDocumentModeDouble) {
        return UIPageViewControllerSpineLocationMid;
    } else {
        return UIPageViewControllerSpineLocationMin;
    }
}

- (void)pageViewController:(UIPageViewController *)pageViewController willTransitionToViewControllers:(NSArray *)pendingViewControllers {
    _animating = YES;
}

- (void)pageViewController:(UIPageViewController *)pageViewController didFinishAnimating:(BOOL)finished previousViewControllers:(NSArray *)previousViewControllers transitionCompleted:(BOOL)completed {
    if(finished || completed) {
        _animating = NO;
    }
    
    if(completed) {
        if(_documentMode == YLDocumentModeSingle) {
            YLPageViewController *old = nil;
            YLPageViewController *current = nil;
            if([previousViewControllers count] > 0) {
                old = [previousViewControllers objectAtIndex:0];
            }
            current = [self.childViewControllers objectAtIndex:0];
            
            if(old && current) {
                CGSize oldSize = old.pageView.bounds.size;
                CGSize newSize = current.pageView.bounds.size;
                if(oldSize.width != newSize.width || oldSize.height != newSize.width) {
                    self.view.bounds = current.pageView.bounds;
                }
            }
        } else {
            YLPageViewController *p1 = nil;
            YLPageViewController *p2 = nil;
            NSArray *viewControllers = [self viewControllers];
            if([viewControllers count] > 1) {
                p1 = [viewControllers objectAtIndex:0];
                p2 = [viewControllers objectAtIndex:1];
                CGSize leftPageSize;
                CGSize rightPageSize;
                if(p1.page == NSNotFound) {
                    rightPageSize = p2.pageView.bounds.size;
                    leftPageSize = rightPageSize;
                } else if(p2.page == NSNotFound) {
                    leftPageSize = p1.pageView.bounds.size;
                    rightPageSize = leftPageSize;
                } else {
                    leftPageSize = p1.pageView.bounds.size;
                    rightPageSize = p2.pageView.bounds.size;
                }
                
                CGFloat maxWidth = MAX(leftPageSize.width, rightPageSize.width);
                CGFloat maxHeigth = MAX(leftPageSize.height, rightPageSize.height);
                self.view.bounds = CGRectMake(0, 0, maxWidth * 2.0, maxHeigth);
                
                if(leftPageSize.width != rightPageSize.width || leftPageSize.height != rightPageSize.height) {
                    CGSize pageSize = CGSizeMake(maxWidth * 2.0, maxHeigth);
                    CGFloat lx = floorf((pageSize.width / 2.0) - leftPageSize.width);
                    CGFloat ly = floorf(pageSize.height - leftPageSize.height) / 2.0;
                    CGRect frame = p1.pageView.frame;
                    frame.origin.x = lx;
                    frame.origin.y = ly;
                    p1.pageView.frame = frame;
                    
                    frame = p2.pageView.frame;
                    frame.origin.y = floorf((pageSize.height - rightPageSize.height) / 2.0);
                    p2.pageView.frame = frame;
                }
            }
        }
        
        [self willChangeValueForKey:@"page"];
        _page = _newPage;
        [self didChangeValueForKey:@"page"];
        
        [self updateShadowFrameForMode:[self shadowModeForPage:_page]];
    }
}


#pragma mark -
#pragma mark UIPageViewControllerDatasource Methods
- (UIViewController *)pageViewController:(UIPageViewController *)pageViewController viewControllerBeforeViewController:(UIViewController *)viewController {
    if(_animating) {
        return nil;
    }
    YLPageViewController *prevPageController = nil;
    
    YLPageViewController *pageController = (YLPageViewController *)viewController;
    if(pageController.page == NSNotFound) {
        return nil;
    }
    
    NSInteger prevPage = pageController.page - 1;
    if(prevPage >= 0) { 
        [pageController.pageView hideAnnotations];
        prevPageController = [[YLPageViewController alloc] initWithPDFController:_pdfViewController page:prevPage];
        _newPage = [self leftPageForPage:prevPage];
    } else if(prevPage == -1 && _documentMode == YLDocumentModeDouble) { // return empty page for double page mode with right lead (special case)
        [pageController.pageView hideAnnotations];
        prevPageController = [[YLPageViewController alloc] initWithPDFController:_pdfViewController page:NSNotFound];
    }
    
    return [prevPageController autorelease];
}

- (UIViewController *)pageViewController:(UIPageViewController *)pageViewController viewControllerAfterViewController:(UIViewController *)viewController {
    if(_animating) {
        return nil;
    }
    YLPageViewController *nextPageController = nil;
    
    YLPageViewController *pageController = (YLPageViewController *)viewController;
    if(pageController.page == NSNotFound) {
        return nil;
    }
    
    NSUInteger nextPage = pageController.page + 1;
    if(nextPage < _pdfViewController.document.pageCount) { //nextPage is starting from zero
        [pageController.pageView hideAnnotations];
        nextPageController = [[YLPageViewController alloc] initWithPDFController:_pdfViewController page:nextPage];
        _newPage = [self leftPageForPage:nextPage];
    } else if(nextPage == _pdfViewController.document.pageCount && _documentMode == YLDocumentModeDouble) {
        // return empty page for double page mode with only 1 page at the end (special case)
        [pageController.pageView hideAnnotations];
        nextPageController = [[YLPageViewController alloc] initWithPDFController:_pdfViewController page:NSNotFound];
    }
    
    return [nextPageController autorelease];
}


@end
