//
//  YLWebAnnotationView.m
//
//  Created by Kemal Taskin on 5/23/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLWebAnnotationView.h"
#import "YLAnnotation.h"
#import "YLWebViewController.h"
#import "YLPDFViewController.h"

@interface YLWebAnnotationView () {
    YLWebViewController *_webViewController;
    UIWebView *_webView;
    UIButton *_button;
    
    BOOL _modal;
}

- (void)buttonTapped;

@end

@implementation YLWebAnnotationView

@synthesize annotation = _annotation;
@synthesize pdfViewController = _pdfViewController;
@synthesize webView = _webView;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        _annotation = nil;
        _pdfViewController = nil;
        
        _webView = nil;
        _webViewController = nil;
        _button = nil;
        _modal = NO;
    }
    
    return self;
}

- (void)dealloc {
    [_annotation release];
    [_webView release];
    [_webViewController release];
    [_button release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)setAnnotation:(YLAnnotation *)annotation {
    if(_annotation) {
        [_annotation release];
        _annotation = nil;
    }
    
    _annotation = [annotation retain];
    if(_annotation) {
        if(_annotation.params) {
            NSString *modal = [_annotation.params objectForKey:@"modal"];
            if(modal && [modal isEqualToString:@"1"]) {
                _modal = YES;
            }
        }
        
        if(_modal) {
            self.userInteractionEnabled = YES;
            
            _button = [[UIButton alloc] initWithFrame:self.bounds];
            [_button addTarget:self action:@selector(buttonTapped) forControlEvents:UIControlEventTouchUpInside];
            [self addSubview:_button];
        } else {
            _webView = [[UIWebView alloc] initWithFrame:self.bounds];
            if([[_webView subviews] count] > 0) {
                // hide the shadows
                for(UIView *shadowView in [[[_webView subviews] objectAtIndex:0] subviews]) {
                    [shadowView setHidden:YES];
                }
                // show the content
                [[[[[_webView subviews] objectAtIndex:0] subviews] lastObject] setHidden:NO];
            }
            _webView.backgroundColor = [UIColor whiteColor];
            [self addSubview:_webView];
        }
    }
}

- (UIWebView *)webView {
    if(_webView) {
        return _webView;
    } else if(_webViewController) {
        return _webViewController.webView;
    } else {
        return nil;
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)buttonTapped {
    if(_pdfViewController) {
        if(_webViewController == nil) {
            _webViewController = [[YLWebViewController alloc] initWithURL:_annotation.url];
        }
        
        UINavigationController *n = [[[UINavigationController alloc] initWithRootViewController:_webViewController] autorelease];
        [[n navigationBar] setBarStyle:UIBarStyleBlackOpaque];
        [_pdfViewController presentModalViewController:n animated:YES];
    }
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    if(page == _annotation.page) {
        if(!_modal && _annotation.url) {
            [_webView loadRequest:[NSURLRequest requestWithURL:_annotation.url]];
        }
    }
}

- (void)willHidePage:(NSUInteger)page {
    if(page == _annotation.page && !_modal) {
        [_webView stopLoading];
    }
}

@end
