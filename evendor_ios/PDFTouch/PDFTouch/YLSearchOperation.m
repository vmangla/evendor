//
//  YLSearchOperation.m
//
//  Created by Kemal Taskin on 5/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLSearchOperation.h"
#import "YLDocument.h"
#import "YLDocumentScanner.h"
#import "YLScanner.h"
#import "YLSelection.h"
#import "YLSearchResult.h"
#import "YLPageInfo.h"
#import "YLFontCollection.h"
#import "YLFontHelper.h"

@interface YLSearchOperation () {
    YLDocument *_document;
    NSString *_keyword;
    
    NSObject<YLSearchDelegate> *_delegate;
    NSMutableArray *_searchResults;
}

@end

@implementation YLSearchOperation

@synthesize keyword = _keyword;
@synthesize searchResults = _searchResults;
@synthesize delegate = _delegate;

- (id)initWithDocument:(YLDocument *)document keyword:(NSString *)keyword {
    self = [super init];
    if(self) {
        _document = [document retain];
        _keyword = [keyword copy];
        _searchResults = [[NSMutableArray alloc] init];
        _delegate = nil;
    }
    
    return self;
}

- (void)dealloc {
    _delegate = nil;
    [_document release];
    [_keyword release];
    [_searchResults release];
    
    [super dealloc];
}

- (void)main {
    @autoreleasepool {
        CGPDFDocumentRef document = [_document requestDocumentRef];
        if(document == NULL) {
            goto finished;
        }
        
        CGPDFDocumentRetain(document);
        
        CGPDFDictionaryRef catalog = CGPDFDocumentGetCatalog(document);
        CGPDFDictionaryRef pagesDic = NULL;
        if(CGPDFDictionaryGetDictionary(catalog, "Pages", &pagesDic)) {
            CGPDFDictionaryRef resourcesDic = NULL;
            if(CGPDFDictionaryGetDictionary(pagesDic, "Resources", &resourcesDic)) {
                CGPDFDictionaryRef fontDic = NULL;
                if(CGPDFDictionaryGetDictionary(resourcesDic, "Font", &fontDic)) {
                    // Some PDF files have a global Resources dictionary where they put font information. The following
                    // line with create a font collection and put each font in YLFontHelper where individual pages can access it.
                    YLFontCollection *fontCollection = [[YLFontCollection alloc] initWithFontDictionary:fontDic];
                    NSDictionary *fonts = fontCollection.fonts;
                    if(fonts) {
                        YLFontHelper *fontHelper = [YLFontHelper sharedInstance];
                        for(NSString *name in [fonts allKeys]) {
                            [fontHelper setFont:[fonts objectForKey:name] key:name];
                        }
                    }
                    [fontCollection release];
                }
            }
        }
        
        NSUInteger pageCount = _document.pageCount;
        YLDocumentScanner *documentScanner = _document.scanner;
        for(NSUInteger i = 1; i <= pageCount; i++) {
            if(self.isCancelled) {
                goto finished;
            }
            
            @autoreleasepool {
                NSString *textContent = [documentScanner textContentForPage:(i-1)];
                BOOL performSearch = YES;
                BOOL saveTextContent = YES;
                if(textContent) {
                    saveTextContent = NO;
                    NSRange match = [textContent rangeOfString:_keyword options:NSCaseInsensitiveSearch];
                    if(match.location == NSNotFound) {
                        performSearch = NO;
                    }
                }
                
                if(!performSearch) {
                    continue;
                }
                
                CGPDFPageRef pageRef = CGPDFDocumentGetPage(document, i);
                YLScanner *scanner = [YLScanner scannerWithPage:pageRef];
                NSMutableString *rawText = scanner.content;
                NSArray *selections = [scanner select:_keyword];
                if(saveTextContent) {
                    [documentScanner setTextContent:[NSString stringWithString:scanner.content] forPage:(i-1)];
                }
                if([selections count] > 0) {
                    NSString *rawTextLowercase = [rawText lowercaseString];
                    NSString *keywordLowercase = [_keyword lowercaseString];
                    for(YLSelection *s in selections) {
                        NSRange keywordRange = [rawTextLowercase rangeOfString:keywordLowercase];
                        if(keywordRange.location == NSNotFound) {
                            continue;
                        }
                        
                        NSInteger prefixStart = keywordRange.location - 20;
                        if(prefixStart < 0) {
                            prefixStart = 0;
                        }
                        NSInteger suffixStop = keywordRange.location + _keyword.length + 20;
                        if(suffixStop >= [rawText length]) {
                            suffixStop = [rawText length];
                        }
                        
                        NSString *shortText = [rawText substringWithRange:NSMakeRange(prefixStart, (suffixStop - prefixStart))];
                        shortText = [shortText stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceCharacterSet]];
                        shortText = [shortText stringByTrimmingCharactersInSet:[NSCharacterSet punctuationCharacterSet]];
                        NSRange boldRange = [[shortText lowercaseString] rangeOfString:keywordLowercase];
                        [rawText deleteCharactersInRange:NSMakeRange(0, (keywordRange.location + keywordRange.length))];
                        rawTextLowercase = [rawText lowercaseString];
                        YLSearchResult *result = [[[YLSearchResult alloc] initWithPage:i shortText:shortText] autorelease];
                        [result setBoldRange:boldRange];
                        [result setSelection:s];
                        
                        [_searchResults addObject:result];
                    }
                    
                    if(_delegate && [_searchResults count] > 0) {
                        dispatch_async(dispatch_get_main_queue(), ^{
                            [_delegate searchOperation:self didUpdateResults:_searchResults];
                        });
                    }
                }
            }
        }
        
    finished:
        if(document != NULL) {
            CGPDFDocumentRelease(document);
        }
        
        if(_delegate) {
            if([NSThread isMainThread]) {
                [_delegate searchOperationFinished:self];
            } else {
                dispatch_sync(dispatch_get_main_queue(), ^{
                    [_delegate searchOperationFinished:self];
                });
            }
        }
    }
}

- (NSArray *)searchResults {
    return _searchResults;
}

@end
