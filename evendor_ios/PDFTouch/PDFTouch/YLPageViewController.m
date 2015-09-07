//
//  YLPageViewController.m
//
//  Created by Kemal Taskin on 6/13/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLPageViewController.h"
#import "YLPDFViewController.h"
#import "YLPageView.h"

@interface YLPageViewController () {
    YLPDFViewController *_pdfViewController;
    YLPageView *_pageView;
    
    NSUInteger _page;
}

@end

@implementation YLPageViewController

@synthesize pageView = _pageView;
@synthesize page = _page;

- (id)initWithPDFController:(YLPDFViewController *)controller page:(NSUInteger)page {
    self = [super init];
    if (self) {
        _pdfViewController = controller;
        _pageView = nil;
        _page = page;
    }
    
    return self;
}

- (void)dealloc {
    [_pageView release];
    
    [super dealloc];
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    if(_page != NSNotFound) {
        _pageView = [[YLPageView alloc] initWithDocument:_pdfViewController.document page:_page];
        [_pageView setPdfViewController:_pdfViewController];
        self.view.frame = _pageView.bounds;
        [self.view addSubview:_pageView];
    }
}

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    
    if(_page != NSNotFound) {
        [_pageView showAnnotations];
    }
}

- (void)viewDidUnload {
    [super viewDidUnload];
    
    if(_pageView) {
        [_pageView release];
        _pageView = nil;
    }
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return YES;
}

@end
