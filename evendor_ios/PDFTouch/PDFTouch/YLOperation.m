//
//  YLOperation.m
//
//  Created by Kemal Taskin on 4/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLOperation.h"
#import "YLDocument.h"

@interface YLOperation () {
    // we're using a BOOL for performance reasons since only YLCache will perform KVO
    // on YLOperation objects
    BOOL _observerAdded;
}

@end

@implementation YLOperation

@synthesize document = _document;
@synthesize page = _page;
@synthesize size = _size;
@synthesize path = _path;
@synthesize image = _image;

- (id)initWithDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size path:(NSString *)path {
    self = [super init];
    if(self) {
        _document = [document retain];
        _page = page;
        _size = size;
        _path = [path retain];
        _image = nil;
        _observerAdded = NO;
    }
    
    return self;
}

- (void)dealloc {
    [_document release];
    [_path release];
    [_image release];
    
    [super dealloc];
}

- (void)addObserver:(NSObject *)observer forKeyPath:(NSString *)keyPath options:(NSKeyValueObservingOptions)options context:(void *)context {
    if(_observerAdded) {
        return;
    }
    
    _observerAdded = YES;
    [super addObserver:observer forKeyPath:keyPath options:options context:context];
}

- (void)removeObserver:(NSObject *)observer forKeyPath:(NSString *)keyPath {
    if(!_observerAdded) {
        return;
    }
    
    _observerAdded = NO;
    [super removeObserver:observer forKeyPath:keyPath];
}

@end
