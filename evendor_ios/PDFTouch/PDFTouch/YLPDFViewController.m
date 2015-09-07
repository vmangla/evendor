//
//  YLPDFViewController.m
//
//  Created by Kemal Taskin on 3/26/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPDFViewController.h"
#import "YLPDFViewControllerDelegate.h"
#import "YLPDFViewControllerDataSource.h"
#import "YLOutlineViewController.h"
#import "YLSearchViewController.h"
#import "YLDocument.h"
#import "YLOutlineParser.h"
#import "YLPageInfo.h"
#import "YLScrollView.h"
#import "YLPagesToolbar.h"
#import "YLCache.h"
#import "YLGlobal.h"
#import "GMGridView.h"
#import "YLThumbnailCell.h"
#import "YLLinkAnnotationView.h"
#import "YLAnnotation.h"
#import "YLPagesScrollView.h"
#import "YLPagesViewController.h"
#import "YLPatch.h"
#import "UIImage+PDFTouch.h"
#import "NSBundle+PDFTouch.h"
#import <QuartzCore/QuartzCore.h>
#import <MessageUI/MessageUI.h>

#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
@interface YLPDFViewController () <UIScrollViewDelegate,UIPopoverControllerDelegate,UIGestureRecognizerDelegate,UIAlertViewDelegate,
UINavigationControllerDelegate,GMGridViewDataSource,GMGridViewActionDelegate,YLPagesToolbarDelegate,YLLinkAnnotationViewDelegate,
    YLOutlineParserDelegate, MFMailComposeViewControllerDelegate,UIToolbarDelegate> {
#else
@interface YLPDFViewController () <UIScrollViewDelegate,UIPopoverControllerDelegate,UIGestureRecognizerDelegate,UIAlertViewDelegate,
UINavigationControllerDelegate,GMGridViewDataSource,GMGridViewActionDelegate,YLPagesToolbarDelegate,YLLinkAnnotationViewDelegate,
    YLOutlineParserDelegate, MFMailComposeViewControllerDelegate> {
#endif
    YLDocument *_document;
    NSUInteger _currentPage;
    NSUInteger _prevPortraitPage;
    YLDocumentMode _documentMode;
    YLDocumentLead _documentLead;
    YLViewMode _viewMode;
    YLDismissButtonStyle _dismissButtonStyle;
    NSString *_dismissButtonText;
    BOOL _hideDismissButton;
    BOOL _hideNavigationBar;
    BOOL _pageCurlEnabled;
    BOOL _autoLayoutEnabled;
    BOOL _searchEnabled;
    BOOL _tocEnabled;
    BOOL _bookmarksEnabled;
    BOOL _viewLoaded;
    BOOL _needsLayout;
    BOOL _didDisplayDocument;
    BOOL _observerAdded;
    BOOL _statusBarHidden;
    BOOL _presentingModalViewController;
    
    NSObject<YLPDFViewControllerDelegate> *_delegate;
    NSObject<YLPDFViewControllerDataSource> *_datasource;
    
    UIToolbar *_toolbar;
    YLPagesToolbar *_pagesToolbar;
    UIScrollView *_scrollview;
    GMGridView *_gridview;
    CGSize _gridCellSize;
    
    YLPagesScrollView *_pagesScrollView;
    
    UIStatusBarStyle _statusBarStyle;
    UIBarButtonItem *_doneButton;
    UIBarButtonItem *_searchButton;
    UIBarButtonItem *_tocButton;
    UIBarButtonItem *_bookmarkButton;
    UIBarButtonItem *_segmentButton;
    UIBarButtonItem *_titleButton;
    
    UISegmentedControl *_segmentedControl;
    UILabel *_titleLabel;
    
    UIPopoverController *_poController;
    NSInteger _poControllerTag;
    
    int _firstVisiblePageIndexBeforeRotation;
    
    NSMutableSet *_visiblePages;
    NSMutableSet *_recycledPages;
}

- (void)doneButtonTapped;
- (void)searchButtonTapped;
- (void)tocButtonTapped;
- (void)bookmarkButtonTapped;
- (void)segmentButtonChanged;
- (void)handleSingleTap:(UITapGestureRecognizer *)gr;
- (void)showDocumentView;
- (BOOL)showGridView;
- (NSArray *)toolbarItemsForViewMode:(YLViewMode)viewMode;
- (CGSize)contentSizeForScrollView;
- (void)tilePages;
- (YLScrollView *)dequeueRecycledPage;
- (BOOL)isDisplayingPageViewForPage:(NSUInteger)page;
- (void)configurePageView:(YLScrollView *)pageView forPage:(NSUInteger)page;
- (CGRect)frameForPageViewAtPage:(NSUInteger)page;
- (CGPoint)originForPageViewAtPage:(NSUInteger)page;
// maps actual pdf page number to position of YLScrollView in the master UIScrollView
- (NSUInteger)positionForMode:(YLDocumentMode)mode lead:(YLDocumentLead)lead page:(NSInteger)page;
// maps position of UIScrollView to actual pdf page number
- (NSUInteger)pageForMode:(YLDocumentMode)mode lead:(YLDocumentLead)lead position:(NSInteger)position;
// returns the left page number based on document mode and lead
- (NSUInteger)leftPageForPage:(NSInteger)page;
- (void)setDocumentModeInternal:(YLDocumentMode)documentMode;

@end

@implementation YLPDFViewController

@synthesize document = _document;
@synthesize currentPage = _currentPage;
@synthesize viewMode = _viewMode;
@synthesize documentMode = _documentMode;
@synthesize documentLead = _documentLead;
@synthesize topToolbar = _toolbar;
@synthesize dismissButtonStyle = _dismissButtonStyle;
@synthesize dismissButtonText = _dismissButtonText;
@synthesize hideDismissButton = _hideDismissButton;
@synthesize hideNavigationBar = _hideNavigationBar;
@synthesize pageCurlEnabled = _pageCurlEnabled;
@synthesize autoLayoutEnabled = _autoLayoutEnabled;
@synthesize searchEnabled = _searchEnabled;
@synthesize tocEnabled = _tocEnabled;
@synthesize bookmarksEnabled = _bookmarksEnabled;
@synthesize delegate = _delegate;
@synthesize datasource = _datasource;

- (id)initWithDocument:(YLDocument *)document {
    if(document == nil) {
        return nil;
    }
    
    self = [super init];
    if(self) {
        _document = [document retain];
        _delegate = nil;
        _datasource = nil;
        _currentPage = 0;
        _prevPortraitPage = 0;
        _viewMode = YLViewModeDocument;
        _documentMode = YLDocumentModeSingle;
        _documentLead = YLDocumentLeadRight;
        _dismissButtonStyle = YLDismissButtonStyleDone;
        _dismissButtonText = [[NSBundle myLocalizedStringForKey:@"Done"] retain];
        _hideDismissButton = NO;
        _hideNavigationBar = YES;
        _pageCurlEnabled = NO;
        _autoLayoutEnabled = NO;
        _searchEnabled = YES;
        _tocEnabled = YES;
        _bookmarksEnabled = YES;
        _viewLoaded = NO;
        _needsLayout = NO;
        _didDisplayDocument = NO;
        _observerAdded = NO;
        _poControllerTag = -1;
        
        _firstVisiblePageIndexBeforeRotation = 0;
        if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
            _gridCellSize = CGSizeMake(160, 220);
        } else {
            _gridCellSize = CGSizeMake(80, 110);
        }
        
        YLPageInfo *pageInfo = [_document pageInfoForPage:0];
        if(pageInfo) {
            CGSize imageSize = pageInfo.rotatedRect.size;
            float scaleX = _gridCellSize.width / imageSize.width;
            float scaleY = _gridCellSize.height / imageSize.height;
            float scale = scaleX < scaleY ? scaleX : scaleY;
            
            float offsetX = 0;
            float offsetY = 0;
            
            float targetAspectRatio = _gridCellSize.width / _gridCellSize.height;
            float imageAspectRatio = imageSize.width / imageSize.height;
            
            if(imageAspectRatio < targetAspectRatio) {
                offsetX = _gridCellSize.width - imageSize.width * scale;
            } else { 
                offsetY = _gridCellSize.height - imageSize.height * scale;
            }
            
            int w = _gridCellSize.width - offsetX;
            int h = _gridCellSize.height - offsetY;
            int hw = _gridCellSize.width / 2;
            int hh = _gridCellSize.height / 2;
            w &= ~1;
            h &= ~1;
            if(w < hw) {
                w = hw;
                w &= ~1;
            }
            if(h < hh) {
                h = hh;
                h &= ~1;
            }
            _gridCellSize.width = w;
            _gridCellSize.height = h;
        }
        
        _visiblePages = [[NSMutableSet alloc] init];
        _recycledPages = [[NSMutableSet alloc] init];
        
        [_document.outlineParser setDelegate:self];
        
        static dispatch_once_t onceToken;
        dispatch_once(&onceToken, ^{
            patchUIKit();
        });
    }
    
    return self;
}

- (void)dealloc {
    _delegate = nil;
    _datasource = nil;
    [_document release];
    [_visiblePages release];
    [_recycledPages release];
    
    [_toolbar release];
    if(_observerAdded) {
        [self removeObserver:_pagesToolbar forKeyPath:@"currentPage"];
        [self removeObserver:_pagesToolbar forKeyPath:@"documentMode"];
        [self removeObserver:_pagesToolbar forKeyPath:@"documentLead"];
    }
    [_pagesToolbar release];
    [_scrollview release];
    [_pagesScrollView release];
    if(_gridview && [_gridview.scrollView isDecelerating]) {
        [_gridview.scrollView scrollRectToVisible:CGRectMake(0, 0, 100, 100) animated:NO];
    }
    if(_gridview.superview) {
        [_gridview removeFromSuperview];
    }
    [_gridview release];
    [_doneButton release];
    [_searchButton release];
    [_tocButton release];
    [_bookmarkButton release];
    [_segmentButton release];
    [_titleButton release];
    [_segmentedControl release];
    [_titleLabel release];
    [_dismissButtonText release];
    if(_poController) {
        [_poController dismissPopoverAnimated:NO];
        [_poController release];
    }
    
    [super dealloc];
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    if(_pageCurlEnabled) {
        _pagesScrollView = [[YLPagesScrollView alloc] initWithFrame:self.view.bounds pdfController:self];
        [self.view addSubview:_pagesScrollView];
        
        UITapGestureRecognizer *singleTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(handleSingleTap:)];
        [singleTap setDelegate:self];
        [singleTap setNumberOfTapsRequired:1];
        [singleTap setNumberOfTouchesRequired:1];
        [_pagesScrollView addGestureRecognizer:singleTap];
        
        UITapGestureRecognizer *doubleTap = [[UITapGestureRecognizer alloc] initWithTarget:nil action:nil];
        [doubleTap setDelegate:self];
        [doubleTap setNumberOfTouchesRequired:1];
        [doubleTap setNumberOfTapsRequired:2];
        [singleTap requireGestureRecognizerToFail:doubleTap];
        [_pagesScrollView addGestureRecognizer:doubleTap];
        [singleTap release];
        [doubleTap release];
    } else {
        _scrollview = [[UIScrollView alloc] initWithFrame:self.view.bounds];
        [_scrollview setDelegate:self];
        [_scrollview setPagingEnabled:YES];
        [_scrollview setScrollsToTop:NO];
        [_scrollview setShowsVerticalScrollIndicator:NO];
        [_scrollview setShowsHorizontalScrollIndicator:NO];
        [_scrollview setAutoresizesSubviews:YES];
        [_scrollview setAutoresizingMask:UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleHeight];
        [_scrollview setUserInteractionEnabled:YES];
        [_scrollview setDelaysContentTouches:YES];
        [_scrollview setContentSize:[self contentSizeForScrollView]];
        [self.view addSubview:_scrollview];
        
        UITapGestureRecognizer *singleTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(handleSingleTap:)];
        [singleTap setDelegate:self];
        [singleTap setNumberOfTapsRequired:1];
        [singleTap setNumberOfTouchesRequired:1];
        [_scrollview addGestureRecognizer:singleTap];
        
        UITapGestureRecognizer *doubleTap = [[UITapGestureRecognizer alloc] initWithTarget:nil action:nil];
        [doubleTap setDelegate:self];
        [doubleTap setNumberOfTouchesRequired:1];
        [doubleTap setNumberOfTapsRequired:2];
        [_scrollview addGestureRecognizer:doubleTap];
        [singleTap requireGestureRecognizerToFail:doubleTap];
        [singleTap release];
        [doubleTap release];
    }
    
    if(!_hideDismissButton) {
        if(_dismissButtonStyle == YLDismissButtonStyleDone) {
            _doneButton = [[UIBarButtonItem alloc] initWithTitle:_dismissButtonText
                                                           style:UIBarButtonItemStyleDone 
                                                          target:self 
                                                          action:@selector(doneButtonTapped)];
        } else {
#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
            if(YLIsIOS7OrGreater()) {
                _doneButton = [[UIBarButtonItem alloc] initWithTitle:_dismissButtonText
                                                               style:UIBarButtonItemStyleDone
                                                              target:self
                                                              action:@selector(doneButtonTapped)];
            } else {
                UIButton *b = [UIButton buttonWithType:UIButtonTypeCustom];
                UIImage *normal = [[UIImage myLibraryImageNamed:@"back.png"] stretchableImageWithLeftCapWidth:14 topCapHeight:0];
                UIImage *pressed = [[UIImage myLibraryImageNamed:@"back_pressed.png"] stretchableImageWithLeftCapWidth:13 topCapHeight:0];
                [b setBackgroundImage:normal forState:UIControlStateNormal];
                [b setBackgroundImage:pressed forState:UIControlStateHighlighted];
                [b addTarget:self action:@selector(doneButtonTapped) forControlEvents:UIControlEventTouchUpInside];
                [b setTitle:_dismissButtonText forState:UIControlStateNormal];
                [b setTitle:_dismissButtonText forState:UIControlStateHighlighted];
                b.titleLabel.font = [UIFont boldSystemFontOfSize:[UIFont smallSystemFontSize]];
                b.titleLabel.shadowColor = [UIColor blackColor];
                b.titleLabel.shadowOffset = CGSizeMake(0, -1);
                b.titleLabel.textAlignment = NSTextAlignmentCenter;
                b.titleEdgeInsets = UIEdgeInsetsMake(0, 14, 0, 6);
                NSInteger width = ([_dismissButtonText sizeWithFont:b.titleLabel.font].width + b.titleEdgeInsets.right + b.titleEdgeInsets.left);
                [b setFrame:CGRectMake(0, 0, width, 30)];
                _doneButton = [[UIBarButtonItem alloc] initWithCustomView:b];
            }
#else
            UIButton *b = [UIButton buttonWithType:UIButtonTypeCustom];
            UIImage *normal = [[UIImage myLibraryImageNamed:@"back.png"] stretchableImageWithLeftCapWidth:14 topCapHeight:0];
            UIImage *pressed = [[UIImage myLibraryImageNamed:@"back_pressed.png"] stretchableImageWithLeftCapWidth:13 topCapHeight:0];
            [b setBackgroundImage:normal forState:UIControlStateNormal];
            [b setBackgroundImage:pressed forState:UIControlStateHighlighted];
            [b addTarget:self action:@selector(doneButtonTapped) forControlEvents:UIControlEventTouchUpInside];
            [b setTitle:_dismissButtonText forState:UIControlStateNormal];
            [b setTitle:_dismissButtonText forState:UIControlStateHighlighted];
            b.titleLabel.font = [UIFont boldSystemFontOfSize:[UIFont smallSystemFontSize]];
            b.titleLabel.shadowColor = [UIColor blackColor];
            b.titleLabel.shadowOffset = CGSizeMake(0, -1);
            b.titleLabel.textAlignment = UITextAlignmentCenter;
            b.titleEdgeInsets = UIEdgeInsetsMake(0, 14, 0, 6);
            NSInteger width = ([_dismissButtonText sizeWithFont:b.titleLabel.font].width + b.titleEdgeInsets.right + b.titleEdgeInsets.left);
            [b setFrame:CGRectMake(0, 0, width, 30)];
            _doneButton = [[UIBarButtonItem alloc] initWithCustomView:b];
#endif
        }
    }
    
    _searchButton = [[UIBarButtonItem alloc] initWithImage:[UIImage myLibraryImageNamed:@"magnifier.png"]
                                                     style:UIBarButtonItemStylePlain
                                                    target:self
                                                    action:@selector(searchButtonTapped)];
    _searchButton.imageInsets = UIEdgeInsetsMake(3, 0, -3, 0);
    _tocButton = [[UIBarButtonItem alloc] initWithImage:[UIImage myLibraryImageNamed:@"toc.png"]
                                                  style:UIBarButtonItemStylePlain
                                                 target:self
                                                 action:@selector(tocButtonTapped)];
    _tocButton.imageInsets = UIEdgeInsetsMake(2, 0, -2, 0);
    _bookmarkButton = [[UIBarButtonItem alloc] initWithImage:[UIImage myLibraryImageNamed:@"bookmark_on.png"]
                                                       style:UIBarButtonItemStylePlain
                                                      target:self
                                                      action:@selector(bookmarkButtonTapped)];
    _bookmarkButton.imageInsets = UIEdgeInsetsMake(3, 0, -3, 0);
    _segmentedControl = [[UISegmentedControl alloc] initWithItems:[NSArray arrayWithObjects:[UIImage myLibraryImageNamed:@"single_page.png"], [UIImage myLibraryImageNamed:@"grid.png"], nil]];
    [_segmentedControl setSegmentedControlStyle:UISegmentedControlStyleBar];
    [_segmentedControl setSelectedSegmentIndex:0];
    [_segmentedControl addTarget:self action:@selector(segmentButtonChanged) forControlEvents:UIControlEventValueChanged];
    _segmentButton = [[UIBarButtonItem alloc] initWithCustomView:_segmentedControl];
    
    if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
        _titleLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 0, 300, 30)];
        [_titleLabel setBackgroundColor:[UIColor clearColor]];
        [_titleLabel setFont:[UIFont boldSystemFontOfSize:18]];
        [_titleLabel setMinimumScaleFactor:0.78];
        [_titleLabel setText:_document.title];
        [_titleLabel setTextAlignment:NSTextAlignmentCenter];
#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
        if(YLIsIOS7OrGreater()) {
            [_titleLabel setTextColor:[UIColor blackColor]];
        } else {
            [_titleLabel setTextColor:[UIColor whiteColor]];
            [_titleLabel setShadowColor:[UIColor colorWithWhite:0.0 alpha:0.8]];
            [_titleLabel setShadowOffset:CGSizeMake(0, 1)];
        }
#else
        [_titleLabel setTextColor:[UIColor whiteColor]];
        [_titleLabel setShadowColor:[UIColor colorWithWhite:0.0 alpha:0.8]];
        [_titleLabel setShadowOffset:CGSizeMake(0, 1)];
#endif
        [_titleLabel setAdjustsFontSizeToFitWidth:YES];
        
        _titleButton = [[UIBarButtonItem alloc] init];
        [_titleButton setCustomView:_titleLabel];
    }
    
    self.view.backgroundColor = [UIColor scrollViewTexturedBackgroundColor];
    CGSize viewSize = self.view.bounds.size;
    _toolbar = [[UIToolbar alloc] initWithFrame:CGRectMake(0, 0, viewSize.width, 44.0)];
    if(YLIsIOS7OrGreater()) {
        _toolbar.delegate = self;
    }
    [_toolbar setAutoresizesSubviews:YES];
    [_toolbar setContentMode:UIViewContentModeScaleToFill];
    [_toolbar setAutoresizingMask:UIViewAutoresizingFlexibleWidth];
    if(!YLIsIOS7OrGreater()) {
        [_toolbar setBarStyle:UIBarStyleBlackTranslucent];
    }
    NSArray *toolbarItems = [self toolbarItemsForViewMode:YLViewModeDocument];
    [_toolbar setItems:toolbarItems];
    [self.view addSubview:_toolbar];
    
    _pagesToolbar = [[YLPagesToolbar alloc] initWithFrame:CGRectMake(0, (viewSize.height - 44.0), viewSize.width, 44.0)
                                                 document:_document];
    [_pagesToolbar setParentViewController:self];
    [_pagesToolbar setDelegate:self];
    if(!YLIsIOS7OrGreater()) {
        [[_pagesToolbar toolbar] setBarStyle:UIBarStyleBlackTranslucent];
    }
    [self addObserver:_pagesToolbar forKeyPath:@"currentPage" options:NSKeyValueObservingOptionNew context:NULL];
    [self addObserver:_pagesToolbar forKeyPath:@"documentMode" options:NSKeyValueObservingOptionNew context:NULL];
    [self addObserver:_pagesToolbar forKeyPath:@"documentLead" options:NSKeyValueObservingOptionNew context:NULL];
    _observerAdded = YES;
    [self.view addSubview:_pagesToolbar];
    
    _poController = nil;
    _viewLoaded = YES;
    
    if(!_document.isValidPDF || _document.isLocked) {
        [_tocButton setEnabled:NO];
        [_searchButton setEnabled:NO];
        [_bookmarkButton setEnabled:NO];
        [_segmentButton setEnabled:NO];
        [_pagesToolbar setHidden:YES];
        
        if(_pageCurlEnabled) {
            [_pagesScrollView removeFromSuperview];
        } else {
            [_scrollview removeFromSuperview];
        }
    }
}

- (void)viewDidUnload {
    [super viewDidUnload];
    
    [_visiblePages removeAllObjects];
    [_recycledPages removeAllObjects];
    [_scrollview release]; _scrollview = nil;
    [_pagesScrollView release]; _pagesScrollView = nil;
    [_toolbar release]; _toolbar = nil;
    if(_observerAdded) {
        [self removeObserver:_pagesToolbar forKeyPath:@"currentPage"];
        [self removeObserver:_pagesToolbar forKeyPath:@"documentMode"];
        [self removeObserver:_pagesToolbar forKeyPath:@"documentLead"];
        _observerAdded = NO;
    }
    [_pagesToolbar release]; _pagesToolbar = nil;
    [_doneButton release]; _doneButton = nil;
    [_searchButton release]; _searchButton = nil;
    [_tocButton release]; _tocButton = nil;
    [_bookmarkButton release]; _bookmarkButton = nil;
    [_segmentButton release]; _segmentButton = nil;
    [_titleButton release]; _titleButton = nil;
    [_segmentedControl release]; _segmentedControl = nil;
    [_titleLabel release]; _titleLabel = nil;
    if(_poController) {
        [_poController dismissPopoverAnimated:NO];
        [_poController release];
        _poController = nil;
    }
    
    _viewLoaded = NO;
}

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    
    _presentingModalViewController = NO;
    
    if(self.hideNavigationBar && self.navigationController) {
        [self.navigationController setNavigationBarHidden:YES animated:animated];
    }

    UIApplication *app = [UIApplication sharedApplication];
    _statusBarStyle = [app statusBarStyle];
    [app setStatusBarStyle:UIStatusBarStyleBlackOpaque animated:YES];

    if(_document.isValidPDF && !_document.isLocked) {
        [[YLCache sharedCache] startCachingDocument:_document startPage:0 size:YLPDFImageSizeOriginal];
        
        // notify observers of current page
        [self willChangeValueForKey:@"currentPage"];
        // workaround for Xcode warning
        NSUInteger temp = _currentPage;
        _currentPage = temp;
        [self didChangeValueForKey:@"currentPage"];
        
        if(_pageCurlEnabled) {
            [_pagesScrollView showPage:_currentPage animated:YES];
        } else {
            CGSize contentSize = _scrollview.contentSize;
            CGRect bounds = self.view.bounds;
            if(contentSize.height != bounds.size.height) {
                [_scrollview setContentSize:[self contentSizeForScrollView]];
                
                // adjust frames and configuration of each visible page
                for(YLScrollView *pageView in _visiblePages) {
                    [pageView updateContentViewsWithFrame:[self frameForPageViewAtPage:pageView.page]];
                }
                for(YLScrollView *pageView in _recycledPages) {
                    [pageView updateContentViewsWithFrame:[self frameForPageViewAtPage:pageView.page]];
                }
                
                CGFloat pageWidth = _scrollview.bounds.size.width;
                CGFloat newOffset = (_firstVisiblePageIndexBeforeRotation * pageWidth);
                [_scrollview setContentOffset:CGPointMake(newOffset, 0)];
                
                [_pagesToolbar updateThumbnails];
            }
            
            if(!_autoLayoutEnabled || (_autoLayoutEnabled && UIInterfaceOrientationIsLandscape(self.interfaceOrientation))) {
                [self tilePages];
            }
        }
        
        if(_autoLayoutEnabled) {
            if(UIInterfaceOrientationIsPortrait(self.interfaceOrientation)) {
                [self setDocumentModeInternal:YLDocumentModeSingle];
            } else {
                [self setDocumentModeInternal:YLDocumentModeDouble];
            }
        }
    }
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
    
    if(_document.isValidPDF && !_document.isLocked) {
        [_pagesToolbar updateThumbnails];
        if(_delegate && [_delegate respondsToSelector:@selector(pdfViewController:didDisplayDocument:)] && !_didDisplayDocument) {
            _didDisplayDocument = YES;
            [_delegate pdfViewController:self didDisplayDocument:_document];
        }
    } else if(_document.isLocked) {
        UIAlertView *alert = [[[UIAlertView alloc] initWithTitle:[NSBundle myLocalizedStringForKey:@"Error"]
                                                         message:[NSBundle myLocalizedStringForKey:@"Document is password protected!"]
                                                        delegate:nil
                                               cancelButtonTitle:nil
                                               otherButtonTitles:[NSBundle myLocalizedStringForKey:@"OK"], nil] autorelease];
        [alert show];
    } else {
        UIAlertView *alert = [[[UIAlertView alloc] initWithTitle:[NSBundle myLocalizedStringForKey:@"Error"]
                                                         message:[NSBundle myLocalizedStringForKey:@"Document is not a valid PDF file!"]
                                                        delegate:nil
                                               cancelButtonTitle:nil
                                               otherButtonTitles:[NSBundle myLocalizedStringForKey:@"OK"], nil] autorelease];
        [alert show];
    }
}

- (void)viewWillDisappear:(BOOL)animated {
    [super viewWillDisappear:animated];
    
    if(!_presentingModalViewController) {
        if(self.hideNavigationBar && self.navigationController) {
            [self.navigationController setNavigationBarHidden:NO animated:animated];
        }
        
        [[UIApplication sharedApplication] setStatusBarStyle:_statusBarStyle animated:YES];
    }
}

#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
- (void)viewDidLayoutSubviews {
    [super viewDidLayoutSubviews];
    
    
    if(YLIsIOS7OrGreater()) {
        CGRect frame = _toolbar.frame;
        frame.origin.y = self.topLayoutGuide.length;
        _toolbar.frame = frame;
    }
}

- (UIStatusBarStyle)preferredStatusBarStyle {
    return UIStatusBarStyleDefault;
}
    
- (BOOL)prefersStatusBarHidden {
    return YES;
}
#endif

- (void)didReceiveMemoryWarning {
    [[YLCache sharedCache] clearCache];
    
    [super didReceiveMemoryWarning];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return YES;
}

- (void)willRotateToInterfaceOrientation:(UIInterfaceOrientation)toInterfaceOrientation duration:(NSTimeInterval)duration {
    if(!_pageCurlEnabled) {
        CGFloat offset = _scrollview.contentOffset.x;
        CGFloat pageWidth = _scrollview.bounds.size.width;
        
        if(offset >= 0) {
            _firstVisiblePageIndexBeforeRotation = floorf(offset / pageWidth);
        } else {
            _firstVisiblePageIndexBeforeRotation = 0;
        }
    }
}

- (void)willAnimateRotationToInterfaceOrientation:(UIInterfaceOrientation)toInterfaceOrientation duration:(NSTimeInterval)duration {
    if(_viewMode == YLViewModeDocument) {
        if(_pageCurlEnabled) {
            [_pagesScrollView updateContentViewsWithFrame:_pagesScrollView.frame];
        } else {
            [_scrollview setContentSize:[self contentSizeForScrollView]];
            
            // adjust frames and configuration of each visible page
            for(YLScrollView *pageView in _visiblePages) {
                [pageView updateContentViewsWithFrame:[self frameForPageViewAtPage:pageView.page]];
            }
            for(YLScrollView *pageView in _recycledPages) {
                [pageView updateContentViewsWithFrame:[self frameForPageViewAtPage:pageView.page]];
            }
            
            CGFloat pageWidth = _scrollview.bounds.size.width;
            CGFloat newOffset = (_firstVisiblePageIndexBeforeRotation * pageWidth);
            [_scrollview setContentOffset:CGPointMake(newOffset, 0)];
        }
        
        if(_autoLayoutEnabled) {
            if(UIInterfaceOrientationIsPortrait(toInterfaceOrientation)) {
                [self setDocumentModeInternal:YLDocumentModeSingle];
            } else {
                [self setDocumentModeInternal:YLDocumentModeDouble];
            }
        }
    } else {
        _needsLayout = YES;
    }
    
    [_pagesToolbar updateThumbnails];
}

- (void)observeValueForKeyPath:(NSString *)keyPath ofObject:(id)object change:(NSDictionary *)change context:(void *)context {
    if(object == _pagesScrollView.pagesViewController) {
        if([keyPath isEqualToString:@"page"]) {
            if(_pagesScrollView.pagesViewController.page != _currentPage) {
                [self willChangeValueForKey:@"currentPage"];
                _currentPage = _pagesScrollView.pagesViewController.page;
                [self didChangeValueForKey:@"currentPage"];
            }
        }
    }
}

- (void)didChangeValueForKey:(NSString *)key {
    [super didChangeValueForKey:key];
    
    if([key isEqualToString:@"currentPage"]) {
        NSUInteger page = [self leftPageForPage:_currentPage];
        if([_document hasBookmarkForPage:page]) {
            [_bookmarkButton setImage:[UIImage myLibraryImageNamed:@"bookmark_on.png"]];
        } else {
            [_bookmarkButton setImage:[UIImage myLibraryImageNamed:@"bookmark_off.png"]];
        }
    }
}


#pragma mark -
#pragma mark Instance Methods
- (UIToolbar *)bottomToolbar {
    return [_pagesToolbar toolbar];
}

- (void)setViewMode:(YLViewMode)viewMode {
    if(viewMode == _viewMode) {
        return;
    }
    
    YLViewMode previousMode = _viewMode;
    _viewMode = viewMode;
    if(_viewMode == YLViewModeDocument) {
        [self showDocumentView];
    } else {
        if(_pageCurlEnabled) {
            [_pagesScrollView hideAnnotations];
        } else {
            for(YLScrollView *pageView in _visiblePages) {
                [pageView hideAnnotations];
            }
        }
        
        BOOL initialLoad = NO;
        if(previousMode == YLViewModeDocument) {
            initialLoad = [self showGridView];
        }
        
        if(!initialLoad) {
            [_gridview reloadData];
        }
        
        if(_viewMode == YLViewModeThumbnails) {
            [_gridview scrollToObjectAtIndex:_currentPage animated:NO];
        }
    }
}

- (void)setDocumentMode:(YLDocumentMode)documentMode {
    if(_documentMode == documentMode) {
        return;
    }
    
    [self willChangeValueForKey:@"documentMode"];
    _documentMode = documentMode;
    [self didChangeValueForKey:@"documentMode"];
    
    if(_documentMode == YLDocumentModeSingle) {
        _autoLayoutEnabled = NO;
    } else {
        // Switching to double page mode, so remember the current page in portrait mode.
        // If we immediately switch back to single page mode _prevPortraitPage will tell us
        // if the user was reading the page on the left or the right in portrait mode.
        _prevPortraitPage = _currentPage;
    }

    if(_pageCurlEnabled) {
        NSUInteger leftPage = [self leftPageForPage:_currentPage];
        if(_documentMode == YLDocumentModeSingle && (leftPage == (_prevPortraitPage - 1))) {
            leftPage = _prevPortraitPage;
        }
        [self willChangeValueForKey:@"currentPage"];
        _currentPage = leftPage;
        [self didChangeValueForKey:@"currentPage"];
        
        [_pagesScrollView setDocumentMode:_documentMode];
    } else {
        if(_scrollview) {
            [_scrollview setContentSize:[self contentSizeForScrollView]];
           // DLog(@"content size for scrollview: %@", NSStringFromCGSize([self contentSizeForScrollView]));
            
            for(YLScrollView* pageView in _visiblePages) {
                [_recycledPages addObject:pageView];
                [pageView invalidate];
                [pageView removeFromSuperview];
            }
            
            [_visiblePages minusSet:_recycledPages];
            [self tilePages];
            
            NSUInteger leftPage = [self leftPageForPage:_currentPage];
            if(_documentMode == YLDocumentModeSingle && (leftPage == (_prevPortraitPage - 1))) {
                leftPage = _prevPortraitPage;
            }
            [self willChangeValueForKey:@"currentPage"];
            _currentPage = leftPage;
            [self didChangeValueForKey:@"currentPage"];
            
            NSUInteger position = [self positionForMode:_documentMode lead:_documentLead page:_currentPage];
            CGFloat pageWidth = _scrollview.frame.size.width;
            CGFloat pageOffset = position * pageWidth;
            [_scrollview setContentOffset:CGPointMake(pageOffset, 0) animated:NO];
        }
    }
}

- (void)setDocumentLead:(YLDocumentLead)documentLead {
    if(_documentLead == documentLead) {
        return;
    }
    
    [self willChangeValueForKey:@"documentLead"];
    _documentLead = documentLead;
    [self didChangeValueForKey:@"documentLead"];
    
    if(_pageCurlEnabled) {
        NSUInteger leftPage = [self leftPageForPage:_currentPage];
        [self willChangeValueForKey:@"currentPage"];
        _currentPage = leftPage;
        [self didChangeValueForKey:@"currentPage"];
        
        [_pagesScrollView setDocumentLead:_documentLead];
    } else {
        if(_scrollview) {
            [_scrollview setContentSize:[self contentSizeForScrollView]];
           // DLog(@"content size for scrollview: %@", NSStringFromCGSize([self contentSizeForScrollView]));
            
            for(YLScrollView* pageView in _visiblePages) {
                [_recycledPages addObject:pageView];
                [pageView invalidate];
                [pageView removeFromSuperview];
            }
            
            [_visiblePages minusSet:_recycledPages];
            [self tilePages];
            
            NSUInteger leftPage = [self leftPageForPage:_currentPage];
            [self willChangeValueForKey:@"currentPage"];
            _currentPage = leftPage;
            [self didChangeValueForKey:@"currentPage"];
            
            NSUInteger position = [self positionForMode:_documentMode lead:_documentLead page:_currentPage];
            CGFloat pageWidth = _scrollview.frame.size.width;
            CGFloat pageOffset = position * pageWidth;
            [_scrollview setContentOffset:CGPointMake(pageOffset, 0) animated:NO];
        }
    }
}

- (void)setPageCurlEnabled:(BOOL)pageCurlEnabled {
     if(_pageCurlEnabled == pageCurlEnabled) {
         return;
     }
     
     _pageCurlEnabled = pageCurlEnabled;
     if(_viewLoaded) {
         if(_pageCurlEnabled) {
             if(_scrollview) {
                 [_scrollview removeFromSuperview];
                 [_visiblePages removeAllObjects];
                 [_recycledPages removeAllObjects];
                 [_scrollview release];
                 _scrollview = nil;
             }
             
             if(_pagesScrollView == nil) {
                 _pagesScrollView = [[YLPagesScrollView alloc] initWithFrame:self.view.bounds pdfController:self];
                 [self.view addSubview:_pagesScrollView];
                 
                 UITapGestureRecognizer *singleTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(handleSingleTap:)];
                 [singleTap setDelegate:self];
                 [singleTap setNumberOfTapsRequired:1];
                 [singleTap setNumberOfTouchesRequired:1];
                 [_pagesScrollView addGestureRecognizer:singleTap];
                 
                 UITapGestureRecognizer *doubleTap = [[UITapGestureRecognizer alloc] initWithTarget:nil action:nil];
                 [doubleTap setDelegate:self];
                 [doubleTap setNumberOfTouchesRequired:1];
                 [doubleTap setNumberOfTapsRequired:2];
                 [singleTap requireGestureRecognizerToFail:doubleTap];
                 [_pagesScrollView addGestureRecognizer:doubleTap];
                 [singleTap release];
                 [doubleTap release];
             }
             
             [_pagesScrollView showPage:_currentPage animated:YES];
             [self.view bringSubviewToFront:_toolbar];
             [self.view bringSubviewToFront:_pagesToolbar];
         } else {
             if(_pagesScrollView) {
                 [_pagesScrollView removeFromSuperview];
                 [_pagesScrollView release];
                 _pagesScrollView = nil;
             }
             
             if(_scrollview == nil) {
                 _scrollview = [[UIScrollView alloc] initWithFrame:self.view.bounds];
                 [_scrollview setDelegate:self];
                 [_scrollview setPagingEnabled:YES];
                 [_scrollview setScrollsToTop:NO];
                 [_scrollview setShowsVerticalScrollIndicator:NO];
                 [_scrollview setShowsHorizontalScrollIndicator:NO];
                 [_scrollview setAutoresizesSubviews:YES];
                 [_scrollview setAutoresizingMask:UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleHeight];
                 [_scrollview setUserInteractionEnabled:YES];
                 [_scrollview setDelaysContentTouches:YES];
                 [_scrollview setContentSize:[self contentSizeForScrollView]];
                 [self.view addSubview:_scrollview];
                 
                 UITapGestureRecognizer *singleTap = [[UITapGestureRecognizer alloc] initWithTarget:self action:@selector(handleSingleTap:)];
                 [singleTap setDelegate:self];
                 [singleTap setNumberOfTapsRequired:1];
                 [singleTap setNumberOfTouchesRequired:1];
                 [_scrollview addGestureRecognizer:singleTap];
                 
                 UITapGestureRecognizer *doubleTap = [[UITapGestureRecognizer alloc] initWithTarget:nil action:nil];
                 [doubleTap setDelegate:self];
                 [doubleTap setNumberOfTouchesRequired:1];
                 [doubleTap setNumberOfTapsRequired:2];
                 [_scrollview addGestureRecognizer:doubleTap];
                 [singleTap requireGestureRecognizerToFail:doubleTap];
                 [singleTap release];
                 [doubleTap release];
             }
             
             [self.view bringSubviewToFront:_toolbar];
             [self.view bringSubviewToFront:_pagesToolbar];
             
             NSUInteger position = [self positionForMode:_documentMode lead:_documentLead page:_currentPage];
             if(position == 0) {
                 [self tilePages];
             } else {
                 CGFloat pageWidth = _scrollview.frame.size.width;
                 CGFloat pageOffset = position * pageWidth;
                 [_scrollview setContentOffset:CGPointMake(pageOffset, 0) animated:NO];
             }
         }
     }
}

- (void)setAutoLayoutEnabled:(BOOL)autoLayoutEnabled {
    _autoLayoutEnabled = autoLayoutEnabled;
    if(_autoLayoutEnabled && _documentMode == YLDocumentModeSingle) {
        // auto layout only works with double page mode!!!
        _autoLayoutEnabled = NO;
    }
}
    
- (void)setSearchEnabled:(BOOL)searchEnabled {
    if(searchEnabled == _searchEnabled) {
        return;
    }
    
    _searchEnabled = searchEnabled;
    if(_toolbar) {
        [_toolbar setItems:[self toolbarItemsForViewMode:_viewMode]];
    }
}

- (void)setTocEnabled:(BOOL)tocEnabled {
    if(tocEnabled == _tocEnabled) {
        return;
    }
    
    _tocEnabled = tocEnabled;
    if(_toolbar) {
        [_toolbar setItems:[self toolbarItemsForViewMode:_viewMode]];
    }
}

- (void)setBookmarksEnabled:(BOOL)bookmarksEnabled {
    if(bookmarksEnabled == _bookmarksEnabled) {
        return;
    }
    
    _bookmarksEnabled = bookmarksEnabled;
    if(_toolbar) {
        [_toolbar setItems:[self toolbarItemsForViewMode:_viewMode]];
    }
}

- (void)showToolbarsAnimated:(BOOL)animated {
    if(![_toolbar isHidden]) {
        return;
    }
    
    [_toolbar setHidden:NO];
    [_pagesToolbar setHidden:NO];
    
    [UIView animateWithDuration:0.25
                     animations:^{
                         [_toolbar setAlpha:1.0];
                         [_pagesToolbar setAlpha:1.0];
                     }];
}

- (void)hideToolbarsAnimated:(BOOL)animated {
    if([_toolbar isHidden]) {
        return;
    }
    
    [UIView animateWithDuration:0.25
                     animations:^{
                         [_toolbar setAlpha:0.0];
                         [_pagesToolbar setAlpha:0.0];
                     } 
                     completion:^(BOOL finished) {
                         [_toolbar setHidden:YES];
                         [_pagesToolbar setHidden:YES];
                     }];
}

- (void)toggleToolbarsAnimated:(BOOL)animated {
    if([_toolbar isHidden]) {
        [self showToolbarsAnimated:animated];
    } else {
        [self hideToolbarsAnimated:animated];
    }
}

- (void)scrollToPage:(NSUInteger)page animated:(BOOL)animated {
    if(page >= [_document pageCount]) {
        return;
    }
    
    NSUInteger leftPage = [self leftPageForPage:page];
    if(leftPage == _currentPage) {
        // update current page to display search results if available
        if(_pageCurlEnabled) {
            [_pagesScrollView updateForSearchResults];
        } else {
            for(YLScrollView *pageView in _visiblePages) {
                if(pageView.page == leftPage) {
                    [pageView updateForSearchResults];
                }
            }
        }
        
        return;
    }
    
    [self willChangeValueForKey:@"currentPage"];
    _currentPage = leftPage;
    [self didChangeValueForKey:@"currentPage"];
    
    if(_pageCurlEnabled) {
        [_pagesScrollView showPage:_currentPage animated:animated];
    } else {
        NSUInteger position = [self positionForMode:_documentMode lead:_documentLead page:_currentPage];
        CGFloat pageWidth = _scrollview.frame.size.width;
        CGFloat pageOffset = position * pageWidth;
        [_scrollview setContentOffset:CGPointMake(pageOffset, 0) animated:animated];
    }
}

- (void)scrollToNextPageAnimated:(BOOL)animated {
    NSUInteger newPage = (_documentMode == YLDocumentModeSingle) ? (_currentPage + 1) : (_currentPage + 2);
    if(newPage < [_document pageCount]) {
        [self scrollToPage:newPage animated:animated];
    }
}

- (void)scrollToPreviousPageAnimated:(BOOL)animated {
    NSInteger newPage = (_documentMode == YLDocumentModeSingle) ? (_currentPage - 1) : (_currentPage - 2);
    if(_documentMode == YLDocumentModeDouble && _documentLead == YLDocumentLeadRight && newPage == -1) {
        newPage = 0;
    }
    
    if(newPage >= 0) {
        [self scrollToPage:newPage animated:animated];
    }
}

- (void)presentModalViewController:(UIViewController *)modalViewController animated:(BOOL)animated {
    CGFloat offset = _scrollview.contentOffset.x;
    CGFloat pageWidth = _scrollview.bounds.size.width;
    
    if(offset >= 0) {
        _firstVisiblePageIndexBeforeRotation = floorf(offset / pageWidth);
    } else {
        _firstVisiblePageIndexBeforeRotation = 0;
    }
    
    [super presentViewController:modalViewController animated:animated completion:nil];
}

- (void)clearSearchResults {
    if(_pageCurlEnabled) {
        [_pagesScrollView updateForSearchResults];
    } else {
        for(YLScrollView *pageView in _visiblePages) {
            [pageView updateForSearchResults];
        }
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)doneButtonTapped {
    if(_document) {
        [[YLCache sharedCache] stopCachingDocument:_document];
        double delayInSeconds = 0.5;
        dispatch_time_t popTime = dispatch_time(DISPATCH_TIME_NOW, delayInSeconds * NSEC_PER_SEC);
        dispatch_after(popTime, dispatch_get_main_queue(), ^(void){
            [[YLCache sharedCache] clearCache];
        });
    }
    
    if(_poController) {
        [_poController dismissPopoverAnimated:NO];
        [_poController release];
        _poController = nil;
    }
    
    if(_pageCurlEnabled) {
        [_pagesScrollView hideAnnotations];
    } else {
        for(YLScrollView *pageView in _visiblePages) {
            [pageView hideAnnotations];
        }
    }
    
    if(_delegate && [_delegate respondsToSelector:@selector(pdfViewController:willDismissDocument:)]) {
        [_delegate pdfViewController:self willDismissDocument:_document];
    }
    
    if(self.navigationController) {
        [self.navigationController popViewControllerAnimated:YES];
    } else {
        [self dismissViewControllerAnimated:YES completion:nil];
    }
}

- (void)searchButtonTapped {
    if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
        if(_poController) {
            if([_poController isPopoverVisible]) {
                if(_poControllerTag == 2) { // tapped on search button again, so we dismiss popover
                    [_poController dismissPopoverAnimated:YES];
                } else { // tapped on another button
                    [_poController dismissPopoverAnimated:NO];
                }
            } else {
                // popover was already dismissed by search view controller
                _poControllerTag = -1;
            }
            
            [_poController release];
            _poController = nil;
            
            if(_poControllerTag == 2) {
                _poControllerTag = -1;
                return;
            }
        }
    }
    
    YLSearchViewController *s = [[[YLSearchViewController alloc] initWithPDFController:self] autorelease];
    if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
        _poController = [[UIPopoverController alloc] initWithContentViewController:s];
        _poController.delegate = self;
        _poControllerTag = 2;
        [s setPopoverController:_poController];
        [_poController presentPopoverFromBarButtonItem:_searchButton permittedArrowDirections:UIPopoverArrowDirectionUp animated:YES];
    } else {
        _presentingModalViewController = YES;
        [self presentViewController:s animated:YES completion:nil];
    }
}

- (void)tocButtonTapped {
    if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
        if(_poController) {
            if([_poController isPopoverVisible]) {
                if(_poControllerTag == 1) { // tapped on toc button again, so we dismiss popover
                    [_poController dismissPopoverAnimated:YES];
                } else { // tapped on another button
                    [_poController dismissPopoverAnimated:NO];
                }
            } else {
                _poControllerTag = -1;
            }
            
            [_poController release];
            _poController = nil;
            
            if(_poControllerTag == 1) {
                _poControllerTag = -1;
                return;
            }
        }
    }
    
    YLOutlineViewController *o = [[[YLOutlineViewController alloc] initWithPDFController:self] autorelease];
    UINavigationController *n = [[[UINavigationController alloc] initWithRootViewController:o] autorelease];
    if(!YLIsIOS7OrGreater()) {
        [[n navigationBar] setBarStyle:UIBarStyleBlack];
    }
    if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
        _poController = [[UIPopoverController alloc] initWithContentViewController:n];
        _poController.delegate = self;
        _poControllerTag = 1;
        [o setPopoverController:_poController];
        [_poController presentPopoverFromBarButtonItem:_tocButton permittedArrowDirections:UIPopoverArrowDirectionUp animated:YES];
    } else {
        _presentingModalViewController = YES;
        [self presentViewController:n animated:YES completion:nil];
    }
}

- (void)bookmarkButtonTapped {
    NSUInteger page = [self leftPageForPage:_currentPage];
    if([_document hasBookmarkForPage:page]) {
        if([_document removeBookmarkForPage:page]) {
            [_bookmarkButton setImage:[UIImage myLibraryImageNamed:@"bookmark_off.png"]];
        } else {
           // DLog(@"Cannot remove bookmark for page: %lu", (unsigned long)page);
        }
    } else {
        if([_document addBookmarkForPage:page]) {
            [_bookmarkButton setImage:[UIImage myLibraryImageNamed:@"bookmark_on.png"]];
        } else {
            //DLog(@"Cannot add bookmark for page: %lu", (unsigned long)page);
        }
    }
}

- (void)segmentButtonChanged {
    if([_segmentedControl selectedSegmentIndex] == 0) {
        [self setViewMode:YLViewModeDocument];
    } else if([_segmentedControl selectedSegmentIndex] == 1) {
        if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
            if(_poController) {
                [_poController dismissPopoverAnimated:NO];
                [_poController release];
                _poController = nil;
                _poControllerTag = -1;
            }
        }
        
        [self setViewMode:YLViewModeThumbnails];
    } else {
        [self setViewMode:YLViewModeBookmarks];
    }
}

- (void)handleSingleTap:(UITapGestureRecognizer *)gr {
    if([gr state] == UIGestureRecognizerStateRecognized) {
        CGRect tapViewRect = self.view.bounds;
        CGPoint tapPoint = [gr locationInView:self.view];
        int tapAreaWidth = tapViewRect.size.width / 10;
        
        CGRect contentRect = CGRectInset(tapViewRect, tapAreaWidth, 0);
        if(CGRectContainsPoint(contentRect, tapPoint)) {
            [self toggleToolbarsAnimated:YES];
            return;
        }
        
        CGRect prevRect = tapViewRect;
        prevRect.size.width = tapAreaWidth;
        if(CGRectContainsPoint(prevRect, tapPoint)) {
            [self scrollToPreviousPageAnimated:YES];
        }
        
        CGRect nextRect = tapViewRect;
        nextRect.origin.x = tapViewRect.size.width - tapAreaWidth;
        if(CGRectContainsPoint(nextRect, tapPoint)) {
            [self scrollToNextPageAnimated:YES];
        }
    }
}

- (void)showDocumentView {
    if(_gridview) {
        [_gridview removeFromSuperview];
    }
    
    if(_pageCurlEnabled) {
        [_pagesScrollView setFrame:self.view.bounds];
        [self.view addSubview:_pagesScrollView];
        
        if(_needsLayout) {
            _needsLayout = NO;
            [_pagesScrollView updateContentViewsWithFrame:_pagesScrollView.frame];
        }
    } else {
        [_scrollview setFrame:self.view.bounds];
        [_scrollview setContentSize:[self contentSizeForScrollView]];
        [self.view addSubview:_scrollview];
        
        if(_needsLayout) {
            _needsLayout = NO;
            
            [_scrollview setContentSize:[self contentSizeForScrollView]];
            
            // adjust frames and configuration of each visible page
            for(YLScrollView *pageView in _visiblePages) {
                [pageView updateContentViewsWithFrame:[self frameForPageViewAtPage:pageView.page]];
            }
            for(YLScrollView *pageView in _recycledPages) {
                [pageView updateContentViewsWithFrame:[self frameForPageViewAtPage:pageView.page]];
            }
            
            CGFloat pageWidth = _scrollview.bounds.size.width;
            CGFloat newOffset = (_firstVisiblePageIndexBeforeRotation * pageWidth);
            [_scrollview setContentOffset:CGPointMake(newOffset, 0)];
        }
    }
    
    [_toolbar setItems:[self toolbarItemsForViewMode:YLViewModeDocument]];
    if(_segmentedControl.numberOfSegments == 3) {
        [_segmentedControl removeSegmentAtIndex:2 animated:NO];
        [_segmentedControl sizeToFit];
    }
    [_segmentedControl setSelectedSegmentIndex:0];
    [self.view bringSubviewToFront:_toolbar];
    [_pagesToolbar setHidden:NO];
    [self.view bringSubviewToFront:_pagesToolbar];
}

- (BOOL)showGridView {
    BOOL initialLoad = NO;
    if(_gridview == nil) {
        initialLoad = YES;
        _gridview = [[GMGridView alloc] initWithFrame:self.view.bounds];
        [_gridview setAutoresizingMask:UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleHeight];
        [_gridview setBackgroundColor:[UIColor scrollViewTexturedBackgroundColor]];
        [_gridview setStyle:GMGridViewStyleSwap];
        [_gridview setDataSource:self];
        [_gridview setActionDelegate:self];
        [_gridview setItemSpacing:25];
        [_gridview setCenterGrid:YES];
        [_gridview setMinEdgeInsets:UIEdgeInsetsMake(20 + _toolbar.frame.size.height, 10, 20, 10)];
    }

    if(_pageCurlEnabled) {
        [_pagesScrollView removeFromSuperview];
    } else {
        [_scrollview removeFromSuperview];
    }
    
    [_toolbar setItems:[self toolbarItemsForViewMode:YLViewModeThumbnails]];
    if(_segmentedControl.numberOfSegments == 2 && _bookmarksEnabled) {
        [_segmentedControl insertSegmentWithImage:[UIImage myLibraryImageNamed:@"bookmark_small.png"]
                                          atIndex:2
                                         animated:NO];
        [_segmentedControl sizeToFit];
    }
    [_segmentedControl setSelectedSegmentIndex:1];
    [_gridview setFrame:self.view.bounds];
    [self.view addSubview:_gridview];
    [self.view bringSubviewToFront:_toolbar];
    [_pagesToolbar setHidden:YES];
    
    return initialLoad;
}

- (NSArray *)toolbarItemsForViewMode:(YLViewMode)viewMode {
    NSMutableArray *toolbarItems = [NSMutableArray array];
    
    if(viewMode == YLViewModeDocument) {
        UIBarButtonItem *spacer = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil] autorelease];
        UIBarButtonItem *fixedSpace = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFixedSpace target:nil action:nil] autorelease];
        [fixedSpace setWidth:10];
        if(_hideDismissButton) {
            if(_tocEnabled) {
                [toolbarItems addObject:_tocButton];
            }
            if(_searchEnabled) {
                [toolbarItems addObject:_searchButton];
            }
            if(_bookmarksEnabled) {
                [toolbarItems addObject:_bookmarkButton];
            }
            if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:spacer, _titleButton, spacer, _segmentButton, nil]];
            } else {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:spacer, _segmentButton, nil]];
            }
        } else {
            if(_dismissButtonStyle == YLDismissButtonStyleBack) {
                UIBarButtonItem *negativeSpace = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFixedSpace target:nil action:nil] autorelease];
                negativeSpace.width = -5;
                [toolbarItems addObject:negativeSpace];
                [toolbarItems addObject:_doneButton];
            } else {
                [toolbarItems addObject:_doneButton];
            }
            
            if(_tocEnabled) {
                [toolbarItems addObject:_tocButton];
            }
            if(_searchEnabled) {
                [toolbarItems addObject:_searchButton];
            }
            if(_bookmarksEnabled) {
                [toolbarItems addObject:_bookmarkButton];
            }
            
            if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:fixedSpace, spacer, _titleButton, spacer, _segmentButton, nil]];
            } else {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:fixedSpace, spacer, _segmentButton, nil]];
            }
        }
    } else if(viewMode == YLViewModeThumbnails) {
        UIBarButtonItem *spacer = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFlexibleSpace target:nil action:nil] autorelease];
        if(_hideDismissButton) {
            if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:spacer, _titleButton, spacer, _segmentButton, nil]];
            } else {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:spacer, _segmentButton, nil]];
            }
        } else {
            if(_dismissButtonStyle == YLDismissButtonStyleBack) {
                UIBarButtonItem *negativeSpace = [[[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemFixedSpace target:nil action:nil] autorelease];
                negativeSpace.width = -5;
                [toolbarItems addObject:negativeSpace];
                [toolbarItems addObject:_doneButton];
            } else {
                [toolbarItems addObject:_doneButton];
            }
            
            if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:spacer, _titleButton, spacer, _segmentButton, nil]];
            } else {
                [toolbarItems addObjectsFromArray:[NSArray arrayWithObjects:spacer, _segmentButton, nil]];
            }
        }
    }
    
    return [NSArray arrayWithArray:toolbarItems];
}

- (CGSize)contentSizeForScrollView {
    NSInteger pageCount = 1;
    if(_document) {
        pageCount = [_document pageCount];
    }
    
    if(_documentMode == YLDocumentModeDouble) {
        if(_documentLead == YLDocumentLeadLeft) {
            pageCount = 1 + ((pageCount - 2) <= 0 ? 0 : ceilf((pageCount - 2) / 2.0));
        } else {
            pageCount = 1 + ((pageCount - 1) <= 0 ? 0 : ceilf((pageCount - 1) / 2.0));
        }
    }
    
    CGRect scrollBounds = _scrollview.bounds;
    CGSize contentSize = CGSizeMake(scrollBounds.size.width * pageCount, scrollBounds.size.height);
    return contentSize;
}

- (void)tilePages {
    // Calculate which pages are visible
    CGRect visibleBounds = _scrollview.bounds;
    int firstNeededPageIndex = floorf(CGRectGetMinX(visibleBounds) / CGRectGetWidth(visibleBounds));
    int lastNeededPageIndex  = ceilf((CGRectGetMaxX(visibleBounds) - 1) / CGRectGetWidth(visibleBounds));
    //int currentPage = floorf((_scrollview.contentOffset.x + (CGRectGetWidth(visibleBounds) / 2)) / CGRectGetWidth(visibleBounds));
    firstNeededPageIndex = MAX(firstNeededPageIndex, 0);
    lastNeededPageIndex = MIN(lastNeededPageIndex, floorf(_scrollview.contentSize.width / CGRectGetWidth(visibleBounds)) - 1);
    
    // Recycle no-longer-visible pages 
    NSUInteger firstPage = [self pageForMode:_documentMode lead:_documentLead position:firstNeededPageIndex];
    NSUInteger lastPage = [self pageForMode:_documentMode lead:_documentLead position:lastNeededPageIndex];
    for(YLScrollView* pageView in _visiblePages) {
        if(pageView.page < firstPage || pageView.page > lastPage) {
            [_recycledPages addObject:pageView];
            [pageView invalidate];
            [pageView removeFromSuperview];
        }
    }
    [_visiblePages minusSet:_recycledPages];
    
    // add missing pages
    for(int index = firstNeededPageIndex; index <= lastNeededPageIndex; index++) {
        NSUInteger calculatedPage = [self pageForMode:_documentMode lead:_documentLead position:index];
        if(![self isDisplayingPageViewForPage:calculatedPage]) {
            YLScrollView *pageView = [self dequeueRecycledPage];
            if(pageView == nil) {
                pageView = [[[YLScrollView alloc] initWithFrame:_scrollview.bounds] autorelease];
                [pageView setPdfViewController:self];
            }
            [pageView setDocumentMode:_documentMode];
            [pageView setDocumentLead:_documentLead];
            //DLog(@"configure page view for page: %d", calculatedPage);
            [self configurePageView:pageView forPage:calculatedPage];
            [_scrollview addSubview:pageView];
            if([_visiblePages count] == 0) {
                // show annotations for initial page
                [pageView showAnnotations];
            }
            [_visiblePages addObject:pageView];
        }
    }
}

- (YLScrollView *)dequeueRecycledPage {
    YLScrollView *pageView = [_recycledPages anyObject];
    if(pageView) {
        [[pageView retain] autorelease];
        [_recycledPages removeObject:pageView];
    }
    
    return pageView;
}

- (BOOL)isDisplayingPageViewForPage:(NSUInteger)page {
    for(YLScrollView *pageView in _visiblePages) {
        if(pageView.page == page) {
            return YES;
        }
    }
    
    return NO;
}

- (void)configurePageView:(YLScrollView *)pageView forPage:(NSUInteger)page {
    [pageView displayDocument:_document withPage:page];
    CGRect pageFrame = pageView.frame;
    CGPoint origin = [self originForPageViewAtPage:page];
    pageFrame.origin = origin;
    pageView.frame = pageFrame;
}

- (CGRect)frameForPageViewAtPage:(NSUInteger)page {
    CGRect bounds = _scrollview.bounds;
    CGRect pageFrame = bounds;
    NSUInteger calculatedPage = [self positionForMode:_documentMode lead:_documentLead page:page];
    pageFrame.origin.x = (bounds.size.width * calculatedPage);
	
    return pageFrame;
}
                                            
- (CGPoint)originForPageViewAtPage:(NSUInteger)page {
    CGFloat pageWidth = _scrollview.bounds.size.width;
    NSUInteger calculatedPage = [self positionForMode:_documentMode lead:_documentLead page:page];
    return CGPointMake(pageWidth * calculatedPage, 0);
}

- (NSUInteger)positionForMode:(YLDocumentMode)mode lead:(YLDocumentLead)lead page:(NSInteger)page {
    if(mode == YLDocumentModeSingle) {
        return page;
    } else {
        if(lead == YLDocumentLeadLeft) {
            return (page - 2) >= 0 ? (1 + floorf((page - 2) / 2.0)) : 0;
        } else {
            return (page - 1) >= 0 ? (1 + floorf((page - 1) / 2.0)) : 0;
        }
    }
}

- (NSUInteger)pageForMode:(YLDocumentMode)mode lead:(YLDocumentLead)lead position:(NSInteger)position {
    if(mode == YLDocumentModeSingle) {
        return position;
    } else {
        if(lead == YLDocumentLeadLeft) {
            return position * 2;
        } else {
            return (position <= 0 ? 0 : (position + (position - 1)));
        }
    }
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

- (void)setDocumentModeInternal:(YLDocumentMode)documentMode {
    [self setDocumentMode:documentMode];
    if(_documentMode == YLDocumentModeSingle) {
        // setting the document mode to YLDocumentModeSingle using the above method will set autoLayoutEnabled to NO
        // so we set it back to YES here since it was not set to NO outside of YLPDFViewController
        _autoLayoutEnabled = YES;
    }
}


#pragma mark -
#pragma mark UIGestureRecognizerDelegate Methods
- (BOOL)gestureRecognizer:(UIGestureRecognizer *)gestureRecognizer shouldReceiveTouch:(UITouch *)touch {
    BOOL receive = ![touch.view isKindOfClass:[UIButton class]];
    return receive;
}


#pragma mark -
#pragma mark UIAlertViewDelegate Methods
- (void)alertView:(UIAlertView *)alertView didDismissWithButtonIndex:(NSInteger)buttonIndex {
    if(buttonIndex == alertView.firstOtherButtonIndex) {
        NSURL *url = [NSURL URLWithString:alertView.message];
        if(url) {
            [[UIApplication sharedApplication] openURL:url];
        }
    }
}


#pragma mark -
#pragma mark MFMailComposeViewControllerDelegate Methods
- (void)mailComposeController:(MFMailComposeViewController *)controller didFinishWithResult:(MFMailComposeResult)result error:(NSError *)error {
    [self dismissViewControllerAnimated:YES completion:nil];
}


#pragma mark -
#pragma mark YLLinkAnnotationViewDelegate Methods
- (void)linkAnnotationViewTapped:(YLLinkAnnotationView *)annotationView {
    BOOL linkProcessed = NO;
    if(_delegate && [_delegate respondsToSelector:@selector(pdfViewController:tappedOnAnnotation:)]) {
        linkProcessed = [_delegate pdfViewController:self tappedOnAnnotation:annotationView.annotation];
    }
    
    if(!linkProcessed) {
        if(annotationView.annotation.type == kAnnotationTypePage) {
            NSNumber *page = annotationView.annotation.targetPage;
            if(page) {
                [self scrollToPage:page.intValue animated:YES];
            }
        } else if(annotationView.annotation.type == kAnnotationTypeLink) {
            if(annotationView.type == kLinkAnnotationViewTypeWeb) {
                NSString *link = annotationView.annotation.uri;
                if(link) {
                    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:nil
                                                                    message:link
                                                                   delegate:self
                                                          cancelButtonTitle:[NSBundle myLocalizedStringForKey:@"Cancel"]
                                                          otherButtonTitles:[NSBundle myLocalizedStringForKey:@"Open"], nil];
                    [alert show];
                    [alert release];
                }
            } else if(annotationView.type == kLinkAnnotationViewTypeMail) {
                if(![MFMailComposeViewController canSendMail]) {
                    return;
                }
                
                NSString *email = [annotationView.annotation.params objectForKey:@"email"];
                NSString *subject = [annotationView.annotation.params objectForKey:@"subject"];
                if(email == nil) {
                    return;
                }
                
                MFMailComposeViewController *m = [[MFMailComposeViewController alloc] init];
                [m setMailComposeDelegate:self];
                [m setToRecipients:[NSArray arrayWithObject:email]];
                [m setSubject:[subject stringByReplacingPercentEscapesUsingEncoding:NSUTF8StringEncoding]];
                [self presentModalViewController:m animated:YES];
            }
        }
    }
}
        
        
#pragma mark -
#pragma mark YLOutlineParserDelegate Methods
- (void)outlineParsed:(YLOutlineParser *)parser {
    if(parser.outline == nil) {
        // No TOC available
        [self setTocEnabled:NO];
    }
}


#pragma mark -
#pragma mark YLPagesToolbarDelegate Methods
- (void)pagesToolbar:(YLPagesToolbar *)toolbar tappedOnPage:(NSUInteger)page {
    BOOL animated = NO;
    if(_pageCurlEnabled) {
        animated = YES;
    }
    
    [self scrollToPage:page animated:animated];
}


#pragma mark -
#pragma mark GMGridViewDataSource Methods
- (NSInteger)numberOfItemsInGMGridView:(GMGridView *)gridView {
    if(_viewMode == YLViewModeThumbnails) {
        return _document.pageCount;
    } else {
        return _document.bookmarkedPageNumbers.count;
    }
}

- (CGSize)sizeForItemsInGMGridView:(GMGridView *)gridView {
    return _gridCellSize;
}

- (GMGridViewCell *)GMGridView:(GMGridView *)gridView cellForItemAtIndex:(NSInteger)index {
    CGSize size = [self sizeForItemsInGMGridView:gridView];
    
    YLThumbnailCell *cell = (YLThumbnailCell *)[gridView dequeueReusableCell];
    if(cell == nil) {
        cell = [[[YLThumbnailCell alloc] init] autorelease];
        
        UIView *view = [[UIView alloc] initWithFrame:CGRectMake(0, 0, size.width, size.height)];
        view.backgroundColor = [UIColor whiteColor];
        view.layer.masksToBounds = NO;
        view.layer.shadowColor = [UIColor blackColor].CGColor;
        view.layer.shadowOffset = CGSizeMake(0, 0);
        view.layer.shadowPath = [UIBezierPath bezierPathWithRect:view.bounds].CGPath;
        view.layer.shadowRadius = 4;
        view.layer.shadowOpacity = 1.0;
        
        cell.contentView = view;
        [view release];
    }
    
    [cell setDocument:_document];
    if(_viewMode == YLViewModeThumbnails) {
        [cell setPage:index];
    } else {
        NSUInteger page = [[_document.bookmarkedPageNumbers objectAtIndex:index] intValue];
        [cell setPage:page];
    }
    
    return cell;
}


#pragma mark -
#pragma mark GMGridViewActionDelegate Methods
- (void)GMGridView:(GMGridView *)gridView didTapOnItemAtIndex:(NSInteger)position {
    if(_viewMode == YLViewModeThumbnails) {
        [self setViewMode:YLViewModeDocument];
        [self scrollToPage:position animated:NO];
    } else {
        [self setViewMode:YLViewModeDocument];
        NSUInteger page = [[_document.bookmarkedPageNumbers objectAtIndex:position] intValue];
        [self scrollToPage:page animated:NO];
    }
}


#pragma mark -
#pragma mark UIScrollViewDelegate Methods
- (void)scrollViewDidEndDecelerating:(UIScrollView *)scrollView {
    CGPoint offset = _scrollview.contentOffset;
    CGFloat pageWidth = _scrollview.bounds.size.width;
    [self willChangeValueForKey:@"currentPage"];
    NSUInteger position = offset.x / pageWidth;
    _currentPage = [self pageForMode:_documentMode lead:_documentLead position:position];
    [self didChangeValueForKey:@"currentPage"];
    
    for(YLScrollView *pageView in _visiblePages) {
        if(pageView.page == _currentPage) {
            [pageView showAnnotations];
        } else {
            [pageView hideAnnotations];
        }
    }
    
    for(YLScrollView *pageView in _recycledPages) {
        [pageView hideAnnotations];
    }
}

- (void)scrollViewDidEndScrollingAnimation:(UIScrollView *)scrollView {
    for(YLScrollView *pageView in _visiblePages) {
        if(pageView.page == _currentPage) {
            [pageView showAnnotations];
        } else {
            [pageView hideAnnotations];
        }
    }
    
    for(YLScrollView *pageView in _recycledPages) {
        [pageView hideAnnotations];
    }
}

- (void)scrollViewWillBeginDragging:(UIScrollView *)scrollView {
    [self hideToolbarsAnimated:YES];
}

- (void)scrollViewDidScroll:(UIScrollView *)scrollView {
    [self tilePages];
}


#pragma mark -
#pragma mark UIPopoverControllerDelegate Methods
- (void)popoverControllerDidDismissPopover:(UIPopoverController *)popoverController {
    [_poController release];
    _poController = nil;
    _poControllerTag = -1;
}


#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
#pragma mark -
#pragma mark UIToolbarDelegate Methods
- (UIBarPosition)positionForBar:(id<UIBarPositioning>)bar {
    return UIBarPositionTopAttached;
}
#endif

@end
