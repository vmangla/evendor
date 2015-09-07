//
//  YLPagesToolbar.m
//
//  Created by Kemal Taskin on 4/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPagesToolbar.h"
#import "YLPDFViewController.h"
#import "YLDocument.h"
#import "YLPageInfo.h"
#import "NSBundle+PDFTouch.h"
#import <QuartzCore/QuartzCore.h>

#define NUMBER_VIEW_WIDTH      80.0
#define NUMBER_VIEW_HEIGHT     25.0
#define NUMBER_VIEW_PADDING    15.0

#define THUMB_PADDING           5.0
#define THUMB_MARGIN            10.0
#define THUMB_WIDTH             18.0
#define THUMB_HEIGHT            25.0
#define THUMB_BIG_WIDTH         25.0
#define THUMB_BIG_HEIGHT        35.0

@interface YLPagesControl : UIControl {
    CGFloat _value;
    NSUInteger _currentPage;
    NSUInteger _pageCount;
}

@property (nonatomic, readonly) NSUInteger currentPage;

- (id)initWithFrame:(CGRect)frame pageCount:(NSUInteger)pageCount;
- (NSUInteger)pageForValue:(CGFloat)value;
- (CGFloat)boundCheck:(CGFloat)value;

@end

@implementation YLPagesControl

@synthesize currentPage = _currentPage;

- (id)initWithFrame:(CGRect)frame pageCount:(NSUInteger)pageCount {
    self = [super initWithFrame:frame];
    if(self) {
        _value = 0.0;
        _currentPage = 0;
        _pageCount = pageCount;
        
        self.backgroundColor = [UIColor clearColor];
        self.autoresizingMask = UIViewAutoresizingFlexibleWidth;
        self.userInteractionEnabled = YES;
    }
    
    return self;
}

- (void)dealloc {
    [super dealloc];
}

- (BOOL)beginTrackingWithTouch:(UITouch *)touch withEvent:(UIEvent *)event {
    CGPoint point = [touch locationInView:self];
    if([self pointInside:point withEvent:event]) {
        _value = [self boundCheck:point.x];
        NSUInteger page = [self pageForValue:_value];
        if(_currentPage != page) {
            _currentPage = page;
            [self sendActionsForControlEvents:UIControlEventValueChanged];
        }
    }
    
    return [super beginTrackingWithTouch:touch withEvent:event];
}

- (BOOL)continueTrackingWithTouch:(UITouch *)touch withEvent:(UIEvent *)event {
    CGPoint point = [touch locationInView:self];
    if([self pointInside:point withEvent:event]) {
        _value = [self boundCheck:point.x];
        NSUInteger page = [self pageForValue:_value];
        if(_currentPage != page) {
            _currentPage = page;
            [self sendActionsForControlEvents:UIControlEventValueChanged];
        }
    }
    
    return [super continueTrackingWithTouch:touch withEvent:event];
}

- (void)endTrackingWithTouch:(UITouch *)touch withEvent:(UIEvent *)event {
    CGPoint point = [touch locationInView:self];
    if([self pointInside:point withEvent:event]) {
        _value = [self boundCheck:point.x];
        NSUInteger page = [self pageForValue:_value];
        if(_currentPage != page) {
            _currentPage = page;
            [self sendActionsForControlEvents:UIControlEventValueChanged];
        }
    }
    
    return [super endTrackingWithTouch:touch withEvent:event];
}

- (void)cancelTrackingWithEvent:(UIEvent *)event {
    [super cancelTrackingWithEvent:event];
}

- (NSUInteger)pageForValue:(CGFloat)value {
    CGFloat singlePage = (self.bounds.size.width / _pageCount);
    NSUInteger page = (value / singlePage);
    
    return page;
}

- (CGFloat)boundCheck:(CGFloat)value {
    CGFloat minX = 0.0;
    CGFloat maxX = self.bounds.size.width;
    
    CGFloat newValue;
    newValue = MAX(minX, value);
    newValue = MIN(newValue, maxX);
    
    return newValue;
}

@end


@interface YLPagesToolbar () {
    YLDocument *_document;
    YLPDFViewController *_parentViewController;
    
    UIToolbar *_toolbar;
    UIImageView *_currentPageView;
    UIImageView *_nextPageView;
    UIView *_pageNumbersView;
    UILabel *_pageNumbersLabel;
    
    YLPagesControl *_pagesControl;
    NSMutableDictionary *_thumbDic;
    
    BOOL _updateThumbs;
    BOOL _updateCurrentThumb;
    int _thumbWidth;
    int _thumbBigWidth;
    int _thumbHeight;
    int _thumbBigHeight;
    
    YLDocumentMode _documentMode;
    YLDocumentLead _documentLead;
    
    NSObject<YLPagesToolbarDelegate> *_delegate;
}

- (void)setupThumbnails;
- (void)updateCurrentPageView:(NSUInteger)page;
- (void)updateCurrentPageLabel:(NSUInteger)page;
- (void)pageControlValueChanged;
- (void)pageControlValueUpdated;
- (NSUInteger)leftPageForPage:(NSInteger)page;

@end

@implementation YLPagesToolbar

@synthesize parentViewController = _parentViewController;
@synthesize toolbar = _toolbar;
@synthesize delegate = _delegate;

- (id)initWithFrame:(CGRect)frame document:(YLDocument *)document {
    self = [super initWithFrame:frame];
    if (self) {
        _document = [document retain];
        _parentViewController = nil;
        _thumbDic = [[NSMutableDictionary alloc] init];
        _updateThumbs = YES;
        _updateCurrentThumb = YES;
        _documentMode = YLDocumentModeSingle;
        _documentLead = YLDocumentLeadRight;
        
        _thumbWidth = THUMB_WIDTH;
        _thumbHeight = THUMB_HEIGHT;
        _thumbBigWidth = THUMB_BIG_WIDTH;
        _thumbBigHeight = THUMB_BIG_HEIGHT;
        YLPageInfo *pageInfo = [_document pageInfoForPage:0];
        if(pageInfo) {
            CGSize imageSize = pageInfo.rotatedRect.size;
            float scaleX = _thumbWidth / imageSize.width;
            float scaleY = _thumbHeight / imageSize.height;
            float scale = scaleX < scaleY ? scaleX : scaleY;
            
            float offsetX = 0;
            float offsetY = 0;
            
            float targetAspectRatio = _thumbWidth / _thumbHeight;
            float imageAspectRatio = imageSize.width / imageSize.height;
            
            if(imageAspectRatio < targetAspectRatio) {
                offsetX = _thumbWidth - imageSize.width * scale;
            } else { 
                offsetY = _thumbHeight - imageSize.height * scale;
            }
            
            int w = _thumbWidth - offsetX;
            int h = _thumbHeight - offsetY;
            int hw = _thumbWidth / 2;
            int hh = _thumbHeight / 2;
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
            _thumbWidth = w;
            _thumbHeight = h;
            
            scaleX = _thumbBigWidth / imageSize.width;
            scaleY = _thumbBigHeight / imageSize.height;
            scale = scaleX < scaleY ? scaleX : scaleY;
            
            offsetX = 0;
            offsetY = 0;
            
            targetAspectRatio = _thumbBigWidth / _thumbBigHeight;
            if(imageAspectRatio < targetAspectRatio) {
                offsetX = _thumbBigWidth - imageSize.width * scale;
            } else {
                offsetY = _thumbBigHeight - imageSize.height * scale;
            }
            
            w = _thumbBigWidth - offsetX;
            h = _thumbBigHeight - offsetY;
            hw = _thumbBigWidth / 2;
            hh = _thumbBigHeight / 2;
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
            _thumbBigWidth = w;
            _thumbBigHeight = h;
        }
        
        self.backgroundColor = [UIColor clearColor];
        self.userInteractionEnabled = YES;
        self.autoresizesSubviews = YES;
        self.contentMode = UIViewContentModeRedraw;
        self.autoresizingMask = UIViewAutoresizingFlexibleWidth | UIViewAutoresizingFlexibleTopMargin;
        
        _toolbar = [[UIToolbar alloc] initWithFrame:CGRectMake(0, 0, frame.size.width, frame.size.height)];
        [_toolbar setAutoresizesSubviews:YES];
        [_toolbar setUserInteractionEnabled:NO];
        [_toolbar setContentMode:UIViewContentModeScaleToFill];
        [_toolbar setAutoresizingMask:UIViewAutoresizingFlexibleWidth];
        [self addSubview:_toolbar];
        
        _pagesControl = [[YLPagesControl alloc] initWithFrame:CGRectInset(self.bounds, THUMB_MARGIN, 0) pageCount:_document.pageCount];
        [_pagesControl addTarget:self action:@selector(pageControlValueChanged) forControlEvents:UIControlEventValueChanged];
        [_pagesControl addTarget:self action:@selector(pageControlValueUpdated) forControlEvents:UIControlEventTouchUpInside];
        [_pagesControl addTarget:self action:@selector(pageControlValueUpdated) forControlEvents:UIControlEventTouchUpOutside];
        [self addSubview:_pagesControl];
        
        CGFloat x = -999;
        CGFloat y = floorf((_pagesControl.bounds.size.height - _thumbBigHeight) / 2);
        CGRect thumbRect = CGRectMake(x, y, _thumbBigWidth, _thumbBigHeight);
        _currentPageView = [[UIImageView alloc] initWithFrame:thumbRect];
        _currentPageView.backgroundColor = [UIColor clearColor];
        _currentPageView.layer.borderColor = [UIColor blackColor].CGColor;
        _currentPageView.layer.borderWidth = 1.0;
        _currentPageView.layer.zPosition = 1.0;
        _currentPageView.tag = 0;
        [_pagesControl addSubview:_currentPageView];
        
        _nextPageView = [[UIImageView alloc] initWithFrame:thumbRect];
        _nextPageView.backgroundColor = [UIColor clearColor];
        _nextPageView.layer.borderColor = [UIColor blackColor].CGColor;
        _nextPageView.layer.borderWidth = 1.0;
        _nextPageView.layer.zPosition = 1.0;
        _nextPageView.tag = 0;
        
        x = floorf((self.bounds.size.width - NUMBER_VIEW_WIDTH) / 2);
        y = floorf(0.0 - NUMBER_VIEW_HEIGHT - NUMBER_VIEW_PADDING);
        CGRect pageNumbersRect = CGRectMake(x, y, NUMBER_VIEW_WIDTH, NUMBER_VIEW_HEIGHT);
        _pageNumbersView = [[UIView alloc] initWithFrame:pageNumbersRect];
        [_pageNumbersView setAutoresizesSubviews:NO];
        [_pageNumbersView setAutoresizingMask:UIViewAutoresizingFlexibleLeftMargin | UIViewAutoresizingFlexibleRightMargin];
        [_pageNumbersView setUserInteractionEnabled:NO];
        [_pageNumbersView setBackgroundColor:[UIColor colorWithWhite:0.0 alpha:0.4]];
        _pageNumbersView.layer.cornerRadius = 4.0;
        
        CGRect labelRect = CGRectInset(_pageNumbersView.bounds, 2.0, 2.0);
        _pageNumbersLabel = [[UILabel alloc] initWithFrame:labelRect];
        [_pageNumbersLabel setBackgroundColor:[UIColor clearColor]];
        [_pageNumbersLabel setTextAlignment:NSTextAlignmentCenter];
        [_pageNumbersLabel setText:@""];
        [_pageNumbersLabel setTextColor:[UIColor whiteColor]];
        [_pageNumbersLabel setShadowColor:[UIColor colorWithWhite:0.0 alpha:0.8]];
        [_pageNumbersLabel setShadowOffset:CGSizeMake(0, 1)];
        [_pageNumbersLabel setFont:[UIFont systemFontOfSize:16.0]];
        [_pageNumbersLabel setMinimumScaleFactor:0.75];
        [_pageNumbersLabel setAdjustsFontSizeToFitWidth:YES];
        [_pageNumbersLabel setAutoresizesSubviews:NO];
        [_pageNumbersLabel setAutoresizingMask:UIViewAutoresizingNone];
        [_pageNumbersView addSubview:_pageNumbersLabel];
        [self addSubview:_pageNumbersView];
        
        [self updateCurrentPageLabel:_parentViewController.currentPage];
        [self updateCurrentPageView:_parentViewController.currentPage];
        
        [[YLCache sharedCache] addDelegate:self delegateQueue:dispatch_get_main_queue()];
    }
    
    return self;
}

- (void)dealloc {
    [[YLCache sharedCache] removeDelegate:self];
    [_document release];
    _delegate = nil;
    _parentViewController = nil;
    
    [_toolbar release];
    [_currentPageView release];
    [_nextPageView release];
    [_pageNumbersView release];
    [_pageNumbersLabel release];
    [_pagesControl release];
    [_thumbDic release];
    
    [super dealloc];
}

- (void)observeValueForKeyPath:(NSString *)keyPath ofObject:(id)object change:(NSDictionary *)change context:(void *)context {
    if(object == _parentViewController) {
        if([keyPath isEqualToString:@"currentPage"]) {
            [self updateCurrentPageLabel:_parentViewController.currentPage];

            if(self.hidden) {
                _updateCurrentThumb = YES;
                _currentPageView.tag = _parentViewController.currentPage;
            } else {
                [self updateCurrentPageView:_parentViewController.currentPage];
            }
        } else if([keyPath isEqualToString:@"documentMode"]) {
            _documentMode = _parentViewController.documentMode;
            if(self.hidden) {
                _updateCurrentThumb = YES;
                _currentPageView.tag = _parentViewController.currentPage;
            } else {
                [self updateCurrentPageView:_parentViewController.currentPage];
            }
        } else if([keyPath isEqualToString:@"documentLead"]) {
            _documentLead = _parentViewController.documentLead;
            if(self.hidden) {
                _updateCurrentThumb = YES;
                _currentPageView.tag = _parentViewController.currentPage;
            } else {
                [self updateCurrentPageView:_parentViewController.currentPage];
            }
        }
    }
}


#pragma mark -
#pragma mark Instance Methods
- (void)setParentViewController:(YLPDFViewController *)parentViewController {
    _parentViewController = parentViewController;
    if(_parentViewController) {
        _documentMode = _parentViewController.documentMode;
        _documentLead = _parentViewController.documentLead;
    }
}

- (void)setHidden:(BOOL)hidden {
    if(hidden == NO && _updateThumbs) {
        [self setupThumbnails];
    } else if(hidden == NO && _updateCurrentThumb) {
        [self updateCurrentPageView:_currentPageView.tag];
    }
    
    [super setHidden:hidden];
}

- (void)updateThumbnails {
    if(self.hidden) {
        _updateThumbs = YES;
    } else {
        [self setupThumbnails];
    }
}


#pragma mark -
#pragma mark YLCacheDelegate Methods
- (void)didCacheDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size image:(UIImage *)image {
    if([document.uuid isEqualToString:_document.uuid] && size == YLPDFImageSizeSmall) {
        UIImageView *imageView = [_thumbDic objectForKey:[NSNumber numberWithUnsignedInteger:page]];
        if(imageView) {
            [imageView setImage:image];
        }
        
        if(_currentPageView.tag == page) {
            [_currentPageView setImage:image];
        }
        
        if(_nextPageView.tag == page && _nextPageView.superview) {
            [_nextPageView setImage:image];
        }
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)setupThumbnails {
    _updateThumbs = NO;
    
    [_thumbDic enumerateKeysAndObjectsUsingBlock:^(id key, id obj, BOOL *stop) {
        UIImageView *imageView = obj;
        [imageView removeFromSuperview];
    }];
    [_thumbDic removeAllObjects];
    
    CGRect controlRect = CGRectInset(self.bounds, THUMB_MARGIN, 0.0);
    NSUInteger numberOfThumbs = controlRect.size.width / (_thumbWidth + THUMB_PADDING);
    NSUInteger pageCount = _document.pageCount;
    if(numberOfThumbs > pageCount) {
        numberOfThumbs = pageCount;
    }
    
    controlRect.size.width = ((numberOfThumbs - 1) * (_thumbWidth + THUMB_PADDING)) + _thumbWidth;
    controlRect.origin.x = floorf((self.bounds.size.width - controlRect.size.width) / 2);
    _pagesControl.frame = controlRect;
    
    int pageOffset = ceil(pageCount / numberOfThumbs);
    CGFloat thumbY = floorf((_pagesControl.bounds.size.height - _thumbHeight) / 2);
    CGFloat thumbX = floorf(_pagesControl.bounds.origin.x);
    for(int i = 0; i < numberOfThumbs; i++) {
        NSUInteger page = (i * pageOffset);
        if(i == (numberOfThumbs - 1)) {
            page = pageCount - 1;
        }
        CGRect imageFrame = CGRectMake(thumbX + (i * (_thumbWidth + THUMB_PADDING)), thumbY, _thumbWidth, _thumbHeight);
        UIImageView *imageView = [[UIImageView alloc] initWithFrame:imageFrame];
        imageView.backgroundColor = [UIColor whiteColor];
        imageView.layer.borderColor = [UIColor blackColor].CGColor;
        imageView.layer.borderWidth = 1.0;
        [_pagesControl addSubview:imageView];
        [_thumbDic setObject:imageView forKey:[NSNumber numberWithUnsignedInteger:page]];
        UIImage *image = [[YLCache sharedCache] cachedImageForDocument:_document page:page size:YLPDFImageSizeSmall];
        if(image && [image isKindOfClass:[UIImage class]]) {
            [imageView setImage:image];
        }
        [imageView release];
    }
    
    [self updateCurrentPageView:_currentPageView.tag];
}

- (void)updateCurrentPageView:(NSUInteger)page {
    _updateCurrentThumb = NO;
    _currentPageView.tag = page;
    
    UIImageView *imageView = [_thumbDic objectForKey:[NSNumber numberWithUnsignedInteger:page]];
    if(imageView && imageView.image != nil) {
        [_currentPageView setImage:imageView.image];
    } else { 
        UIImage *image = [[YLCache sharedCache] cachedImageForDocument:_document page:page size:YLPDFImageSizeSmall];
        if(image && [image isKindOfClass:[UIImage class]]) {
            [_currentPageView setImage:image];
        }
    }
    
    NSUInteger pageCount = _document.pageCount;
    CGFloat targetSize = _pagesControl.bounds.size.width - _thumbBigWidth;
    
    CGFloat thumbStartX = 0;
    CGFloat offset = pageCount > 1 ? (float)(targetSize / (pageCount - 1)) : targetSize;
    CGRect frame = _currentPageView.frame;
    frame.origin.x = floorf(thumbStartX + (page * offset));
    _currentPageView.frame = frame;
    
    if(_documentMode == YLDocumentModeDouble) {
        NSInteger nextPage = -1;
        if(page == 0 && _documentLead == YLDocumentLeadLeft) {
            nextPage = 1;
        } else if(page > 0) {
            nextPage = page + 1;
        }
        
        if(nextPage != -1 && nextPage < pageCount) {
            UIImage *image = [[YLCache sharedCache] cachedImageForDocument:_document page:nextPage size:YLPDFImageSizeSmall];
            if(image && [image isKindOfClass:[UIImage class]]) {
                [_nextPageView setImage:image];
            }
            
            CGRect frame = _currentPageView.frame;
            frame.origin.x += frame.size.width;
            _nextPageView.frame = frame;
            _nextPageView.tag = nextPage;
            
            // boundary checking
            CGFloat end = frame.origin.x + frame.size.width;
            if(end > (_pagesControl.bounds.size.width + THUMB_MARGIN)) {
                CGFloat margin = end - (_pagesControl.bounds.size.width + THUMB_MARGIN);
                
                frame = _currentPageView.frame;
                frame.origin.x -= margin;
                _currentPageView.frame = frame;
                
                frame = _nextPageView.frame;
                frame.origin.x -= margin;
                _nextPageView.frame = frame;
            }
            
            if(_nextPageView.superview == nil) {
                [_pagesControl addSubview:_nextPageView];
            }
        } else {
            [_nextPageView removeFromSuperview];
        }
    } else {
        [_nextPageView removeFromSuperview];
    }
}

- (void)updateCurrentPageLabel:(NSUInteger)page {
    [_pageNumbersLabel setText:[NSString stringWithFormat:[NSBundle myLocalizedStringForKey:@"%d of %d"], (page + 1), _document.pageCount]];
}

- (void)pageControlValueChanged {
    NSUInteger page = _pagesControl.currentPage;
    [self updateCurrentPageLabel:[self leftPageForPage:page]];
}

- (void)pageControlValueUpdated {
    NSUInteger page = _pagesControl.currentPage;
    page = [self leftPageForPage:page];
    if(page != _currentPageView.tag) {
        if(_delegate) {
            [_delegate pagesToolbar:self tappedOnPage:page];
        }
        
        [self updateCurrentPageView:page];
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

@end
