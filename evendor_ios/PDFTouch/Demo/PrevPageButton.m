//
//  PrevPageButton.m
//
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "PrevPageButton.h"
#import "YLAnnotationView.h"
#import "YLPDFViewController.h"

@implementation PrevPageButton

@synthesize annotation;
@synthesize pdfViewController;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        self.backgroundColor = [UIColor clearColor];
        
        UIButton *b = [UIButton buttonWithType:UIButtonTypeRoundedRect];
        b.frame = self.bounds;
        [b setTitle:@"Previous" forState:UIControlStateNormal];
        [b addTarget:self action:@selector(prevPage) forControlEvents:UIControlEventTouchUpInside];
        [self addSubview:b];
    }
    
    return self;
}

- (void)prevPage {
    [self.pdfViewController scrollToPreviousPageAnimated:YES];
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    
}

- (void)willHidePage:(NSUInteger)page {
    
}

@end
