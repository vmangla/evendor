//
//  YLOutlineParser.m
//
//  Created by Kemal Taskin on 5/1/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLOutlineParser.h"
#import "YLOutlineItem.h"
#import "YLDocument.h"

NSComparisonResult OutlineSort(YLOutlineItem *o1, YLOutlineItem *o2, void *context) {
    return [[NSNumber numberWithUnsignedInteger:o1.page] compare:[NSNumber numberWithUnsignedInteger:o2.page]];
}

@interface YLOutlineParser () {
    YLDocument *_document;
    
    NSMutableArray *_outlineArray;
    NSArray *_sortedOutlineArray;
    NSMutableDictionary *_destDic;
    
    BOOL _parsed;
    BOOL _parsing;
    
    NSObject<YLOutlineParserDelegate> *_delegate;
}

- (void)parseOutlineItem:(CGPDFDictionaryRef)item level:(NSUInteger)level document:(CGPDFDocumentRef)document;
- (CGPDFArrayRef)destinationWithName:(const char *)destName document:(CGPDFDocumentRef)document;
- (CGPDFArrayRef)destinationWithName:(const char *)destName inDictionary:(CGPDFDictionaryRef)dic document:(CGPDFDocumentRef)document;

@end

@implementation YLOutlineParser

- (id)initWithDocument:(YLDocument *)document {
    self = [super init];
    if(self) {
        _document = document;
        _outlineArray = [[NSMutableArray alloc] init];
        _sortedOutlineArray = nil;
        _destDic = [[NSMutableDictionary alloc] init];
        _parsed = NO;
        _delegate = nil;
    }
    
    return self;
}

- (void)dealloc {
    _delegate = nil;
    [_outlineArray release];
    [_sortedOutlineArray release];
    [_destDic release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)parse {
    if(_parsed || _parsing || _document == nil) {
        return;
    }
    
    _parsing = YES;
    dispatch_async(dispatch_get_global_queue(DISPATCH_QUEUE_PRIORITY_LOW, 0), ^{
        if(_document == nil) {
            return;
        }
        
        CGPDFDocumentRef document = [_document requestDocumentRef];
        if(document == NULL) {
            return;
        }
        
        CGPDFDocumentRetain(document);
        CGPDFDictionaryRef catalog = CGPDFDocumentGetCatalog(document);
        CGPDFDictionaryRef outlinesDic = NULL;
        if(CGPDFDictionaryGetDictionary(catalog, "Outlines", &outlinesDic)) {
            CGPDFInteger count = 0;
            CGPDFDictionaryGetInteger(outlinesDic, "Count", &count);
            CGPDFDictionaryRef firstOutline = NULL;
            if(CGPDFDictionaryGetDictionary(outlinesDic, "First", &firstOutline)) {
                [self parseOutlineItem:firstOutline level:0 document:document];
                if([_outlineArray count] > 0) {
                    _sortedOutlineArray = [[_outlineArray sortedArrayUsingFunction:OutlineSort context:NULL] retain];
                }
            }
        }
        
        CGPDFDocumentRelease(document);
        _parsed = YES;
        _parsing = NO;
        
        if(self.delegate) {
            dispatch_async(dispatch_get_main_queue(), ^{
               // [self.delegate outlineParsed:self];//sud
            });
        }
    });
}

- (void)cancel {
    _document = nil;
}

- (NSString *)stringForLevel:(NSInteger)level {
    NSMutableString *result = [NSMutableString stringWithCapacity:level];
    for(int i = 0; i < level; i++) {
        [result appendString:@"-"];
    }
    
    return result;
}

- (NSArray *)outline {
    if(!_parsed) {
        return nil;
    }
    
    return _sortedOutlineArray;
}

- (void)setDelegate:(NSObject<YLOutlineParserDelegate> *)delegate {
    _delegate = delegate;
    if(self.delegate != nil && _parsed) {
        dispatch_async(dispatch_get_main_queue(), ^{
            [self.delegate outlineParsed:self];
        });
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)parseOutlineItem:(CGPDFDictionaryRef)item level:(NSUInteger)level document:(CGPDFDocumentRef)document {
    NSString *title = nil;
    CGPDFStringRef titleRef = NULL;
    if(CGPDFDictionaryGetString(item, "Title", &titleRef)) {
        CFStringRef temp = CGPDFStringCopyTextString(titleRef);
        if(temp) {
            title = [NSString stringWithString:(NSString *)temp];
            CFRelease(temp);
        } else {
            const UInt8 *bytes = CGPDFStringGetBytePtr(titleRef);
            CFIndex length = CGPDFStringGetLength(titleRef);
            NSStringEncoding encoding = (length > 1 && bytes[0] == 0xFE && bytes[1] == 0xFF) ? NSUnicodeStringEncoding : NSUTF8StringEncoding;
            title = [[[NSString alloc] initWithBytes:bytes length:length encoding:encoding] autorelease];
        }
    }
    
    NSString *dest = nil;
    CGPDFStringRef destRef = NULL;
    CGPDFArrayRef destArray = NULL;
    if(CGPDFDictionaryGetString(item, "Dest", &destRef)) {
        dest = [NSString stringWithCString:(const char *)CGPDFStringGetBytePtr(destRef) encoding:NSASCIIStringEncoding];
    } else {
        if(!CGPDFDictionaryGetArray(item, "Dest", &destArray)) {
            CGPDFDictionaryRef actionRef = NULL;
            if(CGPDFDictionaryGetDictionary(item, "A", &actionRef)) {
                if(CGPDFDictionaryGetString(actionRef, "D", &destRef)) {
                    dest = [NSString stringWithCString:(const char *)CGPDFStringGetBytePtr(destRef) encoding:NSASCIIStringEncoding];
                } else {
                    CGPDFDictionaryGetArray(actionRef, "D", &destArray);
                }
            }
        }
    }
    
    if(dest && title) {
        CGPDFArrayRef destinationRef = [self destinationWithName:(const char *)CGPDFStringGetBytePtr(destRef) document:document];
        if(destinationRef != NULL) {
            CGPDFDictionaryRef targetDic = NULL;
            if(CGPDFArrayGetDictionary(destinationRef, 0, &targetDic)) {
                NSInteger pageCount = CGPDFDocumentGetNumberOfPages(document);
                for(NSInteger page = 1; page < pageCount; page++) {
                    CGPDFPageRef pageRef = CGPDFDocumentGetPage(document, page);
                    CGPDFDictionaryRef pageDicRef = CGPDFPageGetDictionary(pageRef);
                    if(pageDicRef == targetDic) {
                        YLOutlineItem *outlineItem = [[[YLOutlineItem alloc] initWithTitle:title indentation:level page:page] autorelease];
                        [_outlineArray addObject:outlineItem];
                        break;
                    }
                }
            }
        }
    } else if(destArray && title) {
        // First entry in the array is the page the links points to.
        CGPDFDictionaryRef pageDictionaryFromDestArray = NULL;
        if(CGPDFArrayGetDictionary(destArray, 0, &pageDictionaryFromDestArray)) {
            NSInteger pageCount = CGPDFDocumentGetNumberOfPages(document);
            for(NSInteger page = 1; page <= pageCount; page++) {
                CGPDFPageRef pageRef = CGPDFDocumentGetPage(document, page);
                CGPDFDictionaryRef pageDicRef = CGPDFPageGetDictionary(pageRef);
                if(pageDicRef == pageDictionaryFromDestArray) {
                    YLOutlineItem *outlineItem = [[[YLOutlineItem alloc] initWithTitle:title indentation:level page:page] autorelease];
                    [_outlineArray addObject:outlineItem];
                    break;
                }
            }
        } 
    }
    
    CGPDFDictionaryRef next = NULL;
    if(CGPDFDictionaryGetDictionary(item, "Next", &next)) {
        [self parseOutlineItem:next level:level document:document];
    }
    
    CGPDFDictionaryRef child = NULL;
    if(CGPDFDictionaryGetDictionary(item, "First", &child)) {
        [self parseOutlineItem:child level:(level + 1) document:document];
    }
}

- (CGPDFArrayRef)destinationWithName:(const char *)destName document:(CGPDFDocumentRef)document {
    CGPDFArrayRef destination = NULL;
    
    CGPDFDictionaryRef catalog = CGPDFDocumentGetCatalog(document);
    CGPDFDictionaryRef namesRef = NULL;
    if(CGPDFDictionaryGetDictionary(catalog, "Names", &namesRef)) {
        CGPDFDictionaryRef destsRef = NULL;
        if(CGPDFDictionaryGetDictionary(namesRef, "Dests", &destsRef)) {
            destination = [self destinationWithName:destName inDictionary:destsRef document:document];
        }
    }

    return destination;
}

- (CGPDFArrayRef)destinationWithName:(const char *)destName inDictionary:(CGPDFDictionaryRef)dic document:(CGPDFDocumentRef)document {
    CGPDFArrayRef limitsRef = NULL;
    if(CGPDFDictionaryGetArray(dic, "Limits", &limitsRef)) {
        CGPDFStringRef minLimit = NULL;
        CGPDFStringRef maxLimit = NULL;
        if(CGPDFArrayGetString(limitsRef, 0, &minLimit) && CGPDFArrayGetString(limitsRef, 1, &maxLimit)) {
            const char *mil = (const char *)CGPDFStringGetBytePtr(minLimit);
            const char *mal = (const char *)CGPDFStringGetBytePtr(maxLimit);
            if((strcmp(destName, mil) < 0) || (strcmp(destName, mal) > 0)) {
                return NULL;
            }
        }
    }
    
    CGPDFArrayRef namesRef = NULL;
    if(CGPDFDictionaryGetArray(dic, "Names", &namesRef)) {
        NSInteger namesCount = CGPDFArrayGetCount(namesRef);
        for(NSInteger i = 0; i < namesCount; i += 2) {
            CGPDFStringRef nameRef = NULL;
            if(CGPDFArrayGetString(namesRef, i, &nameRef)) {
                const char *name = (const char *)CGPDFStringGetBytePtr(nameRef);
                if(strcmp(destName, name) == 0) {
                    CGPDFArrayRef destinationRef = NULL;
                    if(!CGPDFArrayGetArray(namesRef, (i + 1), &destinationRef)) {
                        CGPDFDictionaryRef destinationDic = NULL;
                        if(CGPDFArrayGetDictionary(namesRef, (i + 1), &destinationDic)) {
                            CGPDFDictionaryGetArray(destinationDic, "D", &destinationRef);
                        }
                    }
                    
                    return destinationRef;
                }
            }
        }
    }
    
    CGPDFArrayRef kidsRef = NULL;
    if(CGPDFDictionaryGetArray(dic, "Kids", &kidsRef)) {
        NSInteger kidsCount = CGPDFArrayGetCount(kidsRef);
        for(NSInteger i = 0; i < kidsCount; i++) {
            CGPDFDictionaryRef kidRef = NULL;
            if(CGPDFArrayGetDictionary(kidsRef, i, &kidRef)) {
                CGPDFArrayRef destinationRef = [self destinationWithName:destName inDictionary:kidRef document:document];
                if(destinationRef != NULL) {
                    return destinationRef;
                }
            }
        }
    }
    
    return NULL;
}

@end
