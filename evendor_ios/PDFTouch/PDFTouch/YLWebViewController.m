//
//  YLWebViewController.m
//
//  Created by Kemal Taskin on 5/24/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLWebViewController.h"

@interface YLWebViewController () {
    UIWebView *_webView;
    NSURL *_url;
}

- (void)doneButtonTapped;

@end

@implementation YLWebViewController

@synthesize webView = _webView;

- (id)initWithURL:(NSURL *)url {
    self = [super init];
    if (self) {
        _url = [url retain];
        
        UIBarButtonItem *doneButton = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone target:self action:@selector(doneButtonTapped)];
        [self.navigationItem setRightBarButtonItem:doneButton];
        [doneButton release];
    }
    
    return self;
}

- (void)dealloc {
    [_webView release];
    [_url release];
    
    [super dealloc];
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    _webView = [[UIWebView alloc] initWithFrame:self.view.bounds];
    _webView.delegate = self;
    [_webView setAutoresizingMask:UIViewAutoresizingFlexibleTopMargin|UIViewAutoresizingFlexibleRightMargin|UIViewAutoresizingFlexibleBottomMargin|
     UIViewAutoresizingFlexibleLeftMargin|UIViewAutoresizingFlexibleWidth|UIViewAutoresizingFlexibleHeight];
    [self.view addSubview:_webView];
    
    [_webView loadRequest:[NSURLRequest requestWithURL:_url]];
}

- (void)viewDidUnload {
    [super viewDidUnload];
    
    [_webView release];
    _webView = nil;
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return YES;
}


#pragma mark -
#pragma mark Private Methods
- (void)doneButtonTapped {
    [self dismissViewControllerAnimated:YES completion:nil];
}


#pragma mark -
#pragma mark UIWebViewDelegate Methods
- (void)webViewDidFinishLoad:(UIWebView *)webView {
    self.title = [webView stringByEvaluatingJavaScriptFromString:@"document.title"];
}

@end
