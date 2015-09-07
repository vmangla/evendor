//
//  YLThumbnailCell.m
//
//  Created by Kemal Taskin on 4/7/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLThumbnailCell.h"
#import "YLDocument.h"
#import "YLOperation.h"
#import "YLRenderOperation.h"
#import "YLLoadOperation.h"

@interface YLThumbnailCell () {
    UIImageView *_imageView;
    UILabel *_pageLabel;
    
    YLDocument *_document;
    NSUInteger _page;
    YLOperation *_operation;
}

@end

@implementation YLThumbnailCell

@synthesize document = _document;
@synthesize page = _page;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if(self) {
        _document = nil;
        _page = NSNotFound;
        _imageView = nil;
        _pageLabel = nil;
        _operation = nil;
        
        [[YLCache sharedCache] addDelegate:self delegateQueue:dispatch_get_main_queue()];
    }
    
    return self;
}

- (void)dealloc {
    [[YLCache sharedCache] removeDelegate:self];
    
    [_imageView release];
    [_pageLabel release];
    [_document release];
    if(_operation) {
        [[YLCache sharedCache] cancelOperation:_operation];
        [_operation release];
        _operation = nil;
    }
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)setContentView:(UIView *)contentView {
    [super setContentView:contentView];
    
    CGRect contentRect = contentView.frame;
    _imageView = [[UIImageView alloc] initWithFrame:CGRectMake(0, 0, contentRect.size.width, contentRect.size.height)];
    [self.contentView addSubview:_imageView];
    
    _pageLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, contentRect.size.height - 22, contentRect.size.width, 22)];
    [_pageLabel setBackgroundColor:[UIColor colorWithWhite:0.1 alpha:0.85]];
    [_pageLabel setTextColor:[UIColor whiteColor]];
    [_pageLabel setShadowColor:[UIColor blackColor]];
    [_pageLabel setShadowOffset:CGSizeMake(0, 1)];
    [_pageLabel setTextAlignment:NSTextAlignmentCenter];
    [_pageLabel setFont:[UIFont boldSystemFontOfSize:14]];
    [self.contentView addSubview:_pageLabel];
}

- (void)prepareForReuse {
    [super prepareForReuse];
    
    if(_operation) {
        [[YLCache sharedCache] cancelOperation:_operation];
        [_operation release];
        _operation = nil;
    }
    
    [_imageView setImage:nil];
    [_pageLabel setText:@""];
    _page = NSNotFound;
}

- (void)setPage:(NSUInteger)page {
    if(page != _page) {
        _page = page;
        [_pageLabel setText:[NSString stringWithFormat:@"%lu", (unsigned long)(_page + 1)]];
        if(_document) {
            id image = [[YLCache sharedCache] cachedImageForDocument:_document page:_page size:YLPDFImageSizeThumbnail];
            if(image == nil) {
                return;
            }
            
            if([image isKindOfClass:[UIImage class]]) {
                [_imageView setImage:image];
            } else if([image isKindOfClass:[YLOperation class]]) {
                _operation = [image retain];
            }
        }
    }
}


#pragma mark -
#pragma mark YLCacheDelegate Methods
- (void)didCacheDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size image:(UIImage *)image {
    if(_document && _page != NSNotFound) {
        if([_document.uuid isEqualToString:document.uuid] && _page == page && size == YLPDFImageSizeThumbnail) {
            if(_operation) {
                [_operation release];
                _operation = nil;
            }
            
            [_imageView setImage:image];
        }
    }
}

@end
