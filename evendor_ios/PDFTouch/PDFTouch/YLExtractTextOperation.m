//
//  YLExtractTextOperation.m
//
//  Created by Kemal Taskin on 3/8/13.
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLExtractTextOperation.h"
#import "YLDocument.h"
#import "YLDocumentScanner.h"
#import "YLScanner.h"

@implementation YLExtractTextOperation

- (id)initWithDocument:(YLDocument *)document {
    self = [super initWithDocument:document page:0 size:0 path:nil];
    if(self) {
        
    }
    
    return self;
}

- (void)dealloc {
    [super dealloc];
}

- (void)main {
    @autoreleasepool {
        CGPDFDocumentRef document = [_document requestDocumentRef];
        if(document == NULL) {
            goto finished;
        }
        
        CGPDFDocumentRetain(document);
        NSUInteger pageCount = _document.pageCount;
        YLDocumentScanner *documentScanner = _document.scanner;
        for(NSUInteger i = 1; i <= pageCount; i++) {
            if(self.isCancelled) {
                goto finished;
            }
            
            @autoreleasepool {
                NSString *textContent = [documentScanner textContentForPage:(i-1)];
                BOOL performSearch = YES;
                if(textContent) {
                    performSearch = NO;
                }
                
                if(!performSearch) {
                    continue;
                }
                
                CGPDFPageRef pageRef = CGPDFDocumentGetPage(document, i);
                YLScanner *scanner = [YLScanner scannerWithPage:pageRef];
                [scanner select:nil];
                [documentScanner setTextContent:[NSString stringWithString:scanner.content] forPage:(i-1)];
            }
        }
        
    finished:
        if(document != NULL) {
            CGPDFDocumentRelease(document);
        }
    }
}

@end
