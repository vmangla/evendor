//
//  NextPageButton.m
//
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "NextPageButton.h"
#import "YLAnnotationView.h"
#import "YLPDFViewController.h"

@implementation NextPageButton

@synthesize annotation;
@synthesize pdfViewController;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        self.backgroundColor = [UIColor clearColor];
        
        UIButton *b = [UIButton buttonWithType:UIButtonTypeRoundedRect];
        b.frame = self.bounds;
        [b setTitle:@"Next" forState:UIControlStateNormal];
        [b addTarget:self action:@selector(nextPage) forControlEvents:UIControlEventTouchUpInside];
        [self addSubview:b];
    }
    
    return self;
}

- (void)nextPage {
    [self.pdfViewController scrollToNextPageAnimated:YES];
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    
}

- (void)willHidePage:(NSUInteger)page {
    
}

@end
