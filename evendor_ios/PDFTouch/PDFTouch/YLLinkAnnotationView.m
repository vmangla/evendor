//
//  YLLinkAnnotationView.m
//
//  Created by Kemal Taskin on 5/16/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLLinkAnnotationView.h"
#import "YLAnnotation.h"
#import <QuartzCore/QuartzCore.h>

@interface YLLinkAnnotationView () {
    UIButton *_button;
}

- (void)buttonTapped;
- (UIImage *)imageFromColor:(UIColor *)color;

@end

@implementation YLLinkAnnotationView

@synthesize annotation = _annotation;
@synthesize pdfViewController = _pdfViewController;
@synthesize borderColor = _borderColor;
@synthesize tapColor = _tapColor;
@synthesize borderRadius = _borderRadius;
@synthesize type = _type;
@synthesize delegate = _delegate;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        _annotation = nil;
        _pdfViewController = nil;
        _delegate = nil;
        _type = kLinkAnnotationViewTypeInvalid;
        
        self.backgroundColor = [UIColor clearColor];
        self.userInteractionEnabled = YES;
        
        _borderColor = [[UIColor colorWithWhite:0.8 alpha:1.0] retain];
        _tapColor = [[UIColor colorWithWhite:0.2 alpha:0.5] retain];
        _borderRadius = 4.0;
        
        _button = [[UIButton alloc] initWithFrame:self.bounds];
        [_button addTarget:self action:@selector(buttonTapped) forControlEvents:UIControlEventTouchUpInside];
        [_button setBackgroundImage:[self imageFromColor:_tapColor]
                           forState:UIControlStateHighlighted];
        _button.backgroundColor = [UIColor clearColor];
        if(frame.size.height < 15) {
            _button.layer.borderWidth = 1.0 / 2.0;
            _button.layer.cornerRadius = _borderRadius / 2.0;
        } else {
            _button.layer.borderWidth = 1.0;
            _button.layer.cornerRadius = _borderRadius;
        }
        _button.layer.masksToBounds = YES;
        _button.layer.borderColor = _borderColor.CGColor;
        [self addSubview:_button];
    }
    
    return self;
}

- (void)dealloc {
    _delegate = nil;
    [_annotation release];
    [_button release];
    [_borderColor release];
    [_tapColor release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)setPdfViewController:(YLPDFViewController *)pdfViewController {
    _pdfViewController = pdfViewController;
    if(_pdfViewController) {
        [self setDelegate:(NSObject<YLLinkAnnotationViewDelegate> *)_pdfViewController];
    }
}

- (void)setAnnotation:(YLAnnotation *)annotation {
    if(_annotation) {
        [_annotation release];
        _annotation = nil;
    }
    
    _annotation = [annotation retain];
    if(_annotation.params) {
        _type = kLinkAnnotationViewTypeMail;
    } else {
        _type = kLinkAnnotationViewTypeWeb;
    }
}

- (void)setBorderColor:(UIColor *)borderColor {
    [_borderColor release];
    _borderColor = [borderColor retain];
    _button.layer.borderColor = _borderColor.CGColor;
}

- (void)setTapColor:(UIColor *)tapColor {
    [_tapColor release];
    _tapColor = [tapColor retain];
    [_button setBackgroundImage:[self imageFromColor:_tapColor] forState:UIControlStateHighlighted];
}

- (void)setBorderRadius:(CGFloat)borderRadius {
    _borderRadius = borderRadius;
    if(self.frame.size.height < 15) {
        _button.layer.cornerRadius = _borderRadius / 2.0;
    } else {
        _button.layer.cornerRadius = _borderRadius;
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)buttonTapped {
    if(_delegate) {
        [_delegate linkAnnotationViewTapped:self];
    }
}

- (UIImage *)imageFromColor:(UIColor *)color {
    CGRect rect = CGRectMake(0, 0, 1, 1);
    UIGraphicsBeginImageContext(rect.size);
    CGContextRef context = UIGraphicsGetCurrentContext();
    CGContextSetFillColorWithColor(context, [color CGColor]);
    CGContextFillRect(context, rect);
    UIImage *img = UIGraphicsGetImageFromCurrentImageContext();
    UIGraphicsEndImageContext();
    return img;
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    
}

- (void)willHidePage:(NSUInteger)page {
    
}

@end
