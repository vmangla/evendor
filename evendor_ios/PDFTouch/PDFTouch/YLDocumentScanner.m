//
//  YLDocumentScanner.m
//
//  Created by Kemal Taskin on 5/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLDocumentScanner.h"
#import "YLDocument.h"
#import "YLSearchResult.h"
#import "YLCache.h"

@interface YLDocumentScanner () {
    YLDocument *_document;
    
    NSString *_lastSearchText;
    NSArray *_searchResults;
    NSMutableDictionary *_textContentDict;
    
    NSObject<YLSearchDelegate> *_delegate;
    
    NSOperationQueue *_searchQueue;
    YLSearchOperation *_searchOperation;
}

- (void)cleanupSearchOperation;

@end

@implementation YLDocumentScanner

@synthesize lastSearchText = _lastSearchText;
@synthesize searchResults = _searchResults;
@synthesize searchDelegate = _delegate;

- (id)initWithDocument:(YLDocument *)document {
    self = [super init];
    if(self) {
        _document = document;
        _lastSearchText = nil;
        _searchResults = nil;
        _delegate = nil;
        
        _searchQueue = nil;
        _searchOperation = nil;
    }
    
    return self;
}

- (void)dealloc {
    _delegate = nil;
    [_lastSearchText release];
    [_searchResults release];
    [_textContentDict release];
    [self cleanupSearchOperation];
    [_searchQueue release];

    [super dealloc];
}

- (void)observeValueForKeyPath:(NSString *)keyPath ofObject:(id)object change:(NSDictionary *)change context:(void *)context {
    if(object == _searchOperation) {
        if([keyPath isEqualToString:@"isFinished"]) {
            if(!_searchOperation.isCancelled) {
                if(_lastSearchText) {
                    [_lastSearchText release];
                    _lastSearchText = nil;
                }
                if(_searchResults) {
                    [_searchResults release];
                    _searchResults = nil;
                }
                
                _lastSearchText = [_searchOperation.keyword retain];
                _searchResults = [_searchOperation.searchResults retain];
            }
            
            [self cleanupSearchOperation];
        }
    }
}


#pragma mark -
#pragma mark Instance Methods
- (void)searchText:(NSString *)text {
    [self cleanupSearchOperation];
    
    if(_searchQueue == nil) {
        _searchQueue = [[NSOperationQueue alloc] init];
    }
    
    [[YLCache sharedCache] pauseCaching:@"Search"];
    
    _searchOperation = [[YLSearchOperation alloc] initWithDocument:_document keyword:text];
    [_searchOperation addObserver:self forKeyPath:@"isFinished" options:NSKeyValueObservingOptionNew context:NULL];
    [_searchOperation setDelegate:_delegate];
    [_searchQueue addOperation:_searchOperation];
}

- (NSArray *)searchResultsForPage:(NSUInteger)page {
    if(_searchResults == nil) {
        return nil;
    }
    
    NSMutableArray *results = [NSMutableArray array];
    for(YLSearchResult *s in _searchResults) {
        if(s.page == (page + 1)) {
            [results addObject:s];
        }
        
        if(s.page > (page + 1)) {
            break;
        }
    }
    
    return results;
}

- (void)cancelSearch {
    [self cleanupSearchOperation];
}

- (NSString *)textContentForPage:(NSUInteger)page {
    if(_textContentDict == nil || page >= _document.pageCount) {
        return nil;
    }
    
    return [_textContentDict objectForKey:[NSNumber numberWithUnsignedInteger:page]];
}

- (void)setTextContent:(NSString *)content forPage:(NSUInteger)page {
    if(page >= _document.pageCount) {
        return;
    }
    
    if(_textContentDict == nil) {
        _textContentDict = [[NSMutableDictionary alloc] initWithCapacity:_document.pageCount];
    }
    
    [_textContentDict setObject:content forKey:[NSNumber numberWithUnsignedInteger:page]];
}


#pragma mark -
#pragma mark Private Methods
- (void)cleanupSearchOperation {
    if(_searchOperation) {
        [_searchOperation removeObserver:self forKeyPath:@"isFinished"];
        [_searchOperation cancel];
        [_searchOperation setDelegate:nil];
        [_searchOperation release];
        _searchOperation = nil;
        
        [[YLCache sharedCache] resumeCaching:@"Search"];
    }
}

@end
