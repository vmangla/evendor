//
//  YLAnnotation.m
//
//  Created by Kemal Taskin on 5/16/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//
//  Rect Parsing
//  ------------
//  Copyright (c) 2011 Sorin Nistor. All rights reserved. This software is provided 'as-is', 
//  without any express or implied warranty. In no event will the authors be held liable for 
//  any damages arising from the use of this software. Permission is granted to anyone to 
//  use this software for any purpose, including commercial applications, and to alter it 
//  and redistribute it freely, subject to the following restrictions:
//  1. The origin of this software must not be misrepresented; you must not claim that you 
//  wrote the original software. If you use this software in a product, an acknowledgment 
//  in the product documentation would be appreciated but is not required.
//  2. Altered source versions must be plainly marked as such, and must not be misrepresented 
//  as being the original software.
//  3. This notice may not be removed or altered from any source distribution.

#import "YLAnnotation.h"
#import "YLDocument.h"
#import "YLPageInfo.h"

@interface YLAnnotation () {
    YLDocument *_document;
    
    AnnotationType _type;
    
    CGRect _rect;
    NSUInteger _page;
    NSNumber *_targetPage;
    NSString *_uri;
    NSURL *_url;
    BOOL _local;
    NSString *_filename;
    NSDictionary *_params;
}

- (void)setupAnnotationFromDictionary:(CGPDFDictionaryRef)dictionary document:(CGPDFDocumentRef)document;
- (void)parseStringTarget;

- (CGPoint)convertPDFPointToViewPoint:(CGPoint)pdfPoint renderRect:(CGRect)renderRect;
- (CGPoint)convertViewPointToPDFPoint:(CGPoint)viewPoint renderRect:(CGRect)renderRect;

- (id)getAnnotationTarget:(CGPDFDictionaryRef)annotationDictionary document:(CGPDFDocumentRef)document;;
- (CGPDFArrayRef)findDestinationByName:(const char *)destinationName inDestsTree:(CGPDFDictionaryRef)node inDocument:(CGPDFDocumentRef)document;

@end

@implementation YLAnnotation

@synthesize rect = _rect;
@synthesize page = _page;
@synthesize targetPage = _targetPage;
@synthesize uri = _uri;
@synthesize url = _url;
@synthesize params = _params;
@synthesize local = _local;
@synthesize document = _document;
@synthesize type = _type;

+ (CGPoint)convertPDFPointToViewPoint:(CGPoint)pdfPoint renderRect:(CGRect)renderRect pageInfo:(YLPageInfo *)pageInfo {
    CGPoint viewPoint = CGPointMake(0, 0);
    if(pageInfo == nil) {
        return viewPoint;
    }
    
    CGRect cropBox = pageInfo.origRect;
    int rotation = pageInfo.rotation;
    
    switch (rotation) {
        case 90:
        case -270:
            viewPoint.x = renderRect.size.width * (pdfPoint.y - cropBox.origin.y) / cropBox.size.height;
            viewPoint.y = renderRect.size.height * (pdfPoint.x - cropBox.origin.x) / cropBox.size.width;
            break;
        case 180:
        case -180:
            viewPoint.x = renderRect.size.width * (cropBox.size.width - (pdfPoint.x - cropBox.origin.x)) / cropBox.size.width;
            viewPoint.y = renderRect.size.height * (pdfPoint.y - cropBox.origin.y) / cropBox.size.height;
            break;
        case -90:
        case 270:
            viewPoint.x = renderRect.size.width * (cropBox.size.height - (pdfPoint.y - cropBox.origin.y)) / cropBox.size.height;
            viewPoint.y = renderRect.size.height * (cropBox.size.width - (pdfPoint.x - cropBox.origin.x)) / cropBox.size.width;
            break;
        case 0:
        default:
            viewPoint.x = renderRect.size.width * (pdfPoint.x - cropBox.origin.x) / cropBox.size.width;
            viewPoint.y = renderRect.size.height * (cropBox.size.height - pdfPoint.y) / cropBox.size.height;
            break;
    }
    
    viewPoint.x = viewPoint.x + renderRect.origin.x;
    viewPoint.y = viewPoint.y + renderRect.origin.y;
    
    return viewPoint;
}

+ (CGPoint)convertViewPointToPDFPoint:(CGPoint)viewPoint renderRect:(CGRect)renderRect pageInfo:(YLPageInfo *)pageInfo {
    CGPoint pdfPoint = CGPointMake(0, 0);
    if(pageInfo == nil) {
        return viewPoint;
    }
    
    CGRect cropBox = pageInfo.origRect;
    int rotation = pageInfo.rotation;
    
    switch (rotation) {
        case 90:
        case -270:
            pdfPoint.x = cropBox.size.width * (viewPoint.y - renderRect.origin.y) / renderRect.size.height;
            pdfPoint.y = cropBox.size.height * (viewPoint.x - renderRect.origin.x) / renderRect.size.width;
            break;
        case 180:
        case -180:
            pdfPoint.x = cropBox.size.width * (renderRect.size.width - (viewPoint.x - renderRect.origin.x)) / renderRect.size.width;
            pdfPoint.y = cropBox.size.height * (viewPoint.y - renderRect.origin.y) / renderRect.size.height;
            break;
        case -90:
        case 270:
            pdfPoint.x = cropBox.size.width * (renderRect.size.height - (viewPoint.y - renderRect.origin.y)) / renderRect.size.height;
            pdfPoint.y = cropBox.size.height * (renderRect.size.width - (viewPoint.x - renderRect.origin.x)) / renderRect.size.width;
            break;
        case 0:
        default:
            pdfPoint.x = cropBox.size.width * (viewPoint.x - renderRect.origin.x) / renderRect.size.width;
            pdfPoint.y = cropBox.size.height * (renderRect.size.height - (viewPoint.y - renderRect.origin.y)) / renderRect.size.height;
            break;
    }
    
    pdfPoint.x = pdfPoint.x + cropBox.origin.x;
    pdfPoint.y = pdfPoint.y+ cropBox.origin.y;
    
    return pdfPoint;
}

+ (CGRect)convertPDFRect:(CGRect)pdfRect forRect:(CGRect)renderRect pageInfo:(YLPageInfo *)pageInfo {
    CGPoint pt1 = [YLAnnotation convertPDFPointToViewPoint:pdfRect.origin renderRect:renderRect pageInfo:pageInfo];
    CGPoint pt2 = CGPointMake(pdfRect.origin.x + pdfRect.size.width, pdfRect.origin.y + pdfRect.size.height);
    pt2 = [YLAnnotation convertPDFPointToViewPoint:pt2 renderRect:renderRect pageInfo:pageInfo];
    
    CGRect rectangle = CGRectMake(pt1.x, pt1.y, pt2.x - pt1.x, pt2.y - pt1.y);
    rectangle = CGRectStandardize(rectangle);
    
    return rectangle;
}

- (id)initWithDocument:(CGPDFDocumentRef)document dictionary:(CGPDFDictionaryRef)dictionary page:(NSUInteger)page {
    self = [super init];
    if(self) {
        _page = page;
        _targetPage = nil;
        _uri = nil;
        _url = nil;
        _local = NO;
        _filename = nil;
        _params = nil;
        _document = nil;
        _type = kAnnotationTypeInvalid;
        
        [self setupAnnotationFromDictionary:dictionary document:document];
    }
    
    return self;
}

- (void)dealloc {
    [_targetPage release];
    [_uri release];
    [_filename release];
    [_url release];
    [_params release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)setDocument:(YLDocument *)document {
    _document = document;
    if(_document && _filename) {
        NSString *parentDir = [[document path] stringByDeletingLastPathComponent];
        NSString *path = [parentDir stringByAppendingPathComponent:_filename];
        if([[NSFileManager defaultManager] fileExistsAtPath:path]) {
            _url = [[NSURL fileURLWithPath:path] retain];
        } else {
           // DLog(@"Annotation for local resource %@ does not exist!", path);
        }
    }
}

- (CGRect)viewRectForRect:(CGRect)rect {
    CGPoint pt1 = [self convertPDFPointToViewPoint:_rect.origin renderRect:rect];
    CGPoint pt2 = CGPointMake(_rect.origin.x + _rect.size.width, _rect.origin.y + _rect.size.height);
    pt2 = [self convertPDFPointToViewPoint:pt2 renderRect:rect];
    
    CGRect rectangle = CGRectMake(pt1.x, pt1.y, pt2.x - pt1.x, pt2.y - pt1.y);
    YLPageInfo *pageInfo = [_document pageInfoForPage:_page];
    if(pageInfo && pageInfo.origRect.origin.y != 0.0) {
        CGRect origRect = [YLAnnotation convertPDFRect:pageInfo.origRect forRect:rect pageInfo:pageInfo];
        rectangle.origin.y += fabsf(origRect.origin.y);
    }
    rectangle = CGRectStandardize(rectangle);
    rectangle = CGRectInset(rectangle, -0.5, 0);
    
    return rectangle;
}


#pragma mark -
#pragma mark Private Methods
- (void)setupAnnotationFromDictionary:(CGPDFDictionaryRef)dictionary document:(CGPDFDocumentRef)document {
    CGPDFArrayRef rectArray = NULL;
    CGPDFDictionaryGetArray(dictionary, "Rect", &rectArray);
    if(rectArray != NULL) {
        CGPDFReal llx = 0;
        CGPDFArrayGetNumber(rectArray, 0, &llx);
        CGPDFReal lly = 0;
        CGPDFArrayGetNumber(rectArray, 1, &lly);
        CGPDFReal urx = 0;
        CGPDFArrayGetNumber(rectArray, 2, &urx);
        CGPDFReal ury = 0;
        CGPDFArrayGetNumber(rectArray, 3, &ury);
        
        if(llx > urx) {
            CGPDFReal temp = llx;
            llx = urx;
            urx = temp;
        }
        
        if(lly > ury) {
            CGPDFReal temp = lly;
            lly = ury;
            ury = temp;
        }
        
        _rect = CGRectMake(llx, lly, urx - llx, ury - lly);
    }
    
    id target = [self getAnnotationTarget:dictionary document:document];
    if(target) {
        if([target isKindOfClass:[NSNumber class]]) {
            _targetPage = [target retain];
            _type = kAnnotationTypePage;
            //DLog(@"Annotation type: PAGE to %d", [_targetPage intValue]);
        } else if([target isKindOfClass:[NSString class]]) {
            _uri = [target retain];
            [self parseStringTarget];
        }
    } else {
        _type = kAnnotationTypeInvalid;
        //DLog(@"Annotation type: INVALID");
    }
}

- (void)parseStringTarget {
    NSURL *url = [NSURL URLWithString:_uri];
    NSString *scheme = url.scheme;
    if([scheme isEqualToString:@"http"] || [scheme isEqualToString:@"https"] || [scheme isEqualToString:@"mailto"]) {
        _type = kAnnotationTypeLink;
        if([scheme isEqualToString:@"mailto"]) {
            NSError *error = nil;
            NSString *email = nil;
            NSString *subject = nil;
            NSRegularExpression *regex = [NSRegularExpression regularExpressionWithPattern:@"^mailto:([^?]*)(\\?subject=(.*))?$"
                                                                                   options:0
                                                                                     error:&error];
            if(error == nil) {
                NSTextCheckingResult *match = [regex firstMatchInString:_uri options:0 range:NSMakeRange(0, [_uri length])];
                if(match && [match numberOfRanges] >= 4) {
                    NSRange emailRange = [match rangeAtIndex:1];
                    if(!NSEqualRanges(emailRange, NSMakeRange(NSNotFound, 0))) {
                        email = [_uri substringWithRange:emailRange];
                    }
                    
                    NSRange subjectRange = [match rangeAtIndex:3];
                    if(!NSEqualRanges(subjectRange, NSMakeRange(NSNotFound, 0))) {
                        subject = [_uri substringWithRange:subjectRange];
                    }
                    
                    //DLog(@"Annotation type: MAIL LINK to: %@, subject: %@", email, subject ? subject : @"");
                    _params = [[NSDictionary dictionaryWithObjectsAndKeys:email, @"email", subject, @"subject", nil] retain];
                }
            }
            
            if(email == nil && subject == nil) {
               // DLog(@"Warning: email annotation type could not be parsed: %@", _uri);
                _type = kAnnotationTypeInvalid;
            }
        } else {
            //DLog(@"Annotation type: WEB LINK to %@", _uri);
        }
    } else if([scheme isEqualToString:@"ylvideo"]) {
        _type = kAnnotationTypeVideo;
        NSString *host = url.host;
        NSString *path = url.path;
        if(host) {
            if([host isEqualToString:@"localhost"]) {
                _local = YES;
                if(path) {
                    _filename = [path copy];
                }
            } else {
                if(path) {
                    _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@%@", host, path]] retain];
                } else {
                    _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@", host]] retain];
                }
            }
        }
        
        NSString *query = url.query;
        if(query) {
            NSArray *components = [query componentsSeparatedByString:@"&"];
            if(components && [components count] > 0) {
                NSMutableDictionary *temp = [NSMutableDictionary dictionary];
                for(NSString *q in components) {
                    NSArray *splitted = [q componentsSeparatedByString:@"="];
                    if(splitted && [splitted count] == 2) {
                        [temp setObject:[splitted objectAtIndex:1] forKey:[splitted objectAtIndex:0]];
                    }
                }
                
                _params = [[NSDictionary dictionaryWithDictionary:temp] retain];
            }
        }
        
        if(_local && _filename) {
            //DLog(@"Annotation type: VIDEO to %@", _filename);
        } else {
            //DLog(@"Annotation type: VIDEO to %@", [_url description]);
        }
        
        if(_params) {
            //DLog(@"Annotation params: %@", [_params description]);
        }
    } else if([scheme isEqualToString:@"ylaudio"]) {
        _type = kAnnotationTypeAudio;
        NSString *host = url.host;
        NSString *path = url.path;
        if(host) {
            if([host isEqualToString:@"localhost"]) {
                _local = YES;
                if(path) {
                    _filename = [path copy];
                }
            } else {
                if(path) {
                    _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@%@", host, path]] retain];
                } else {
                    _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@", host]] retain];
                }
            }
        }
        
        NSString *query = url.query;
        if(query) {
            NSArray *components = [query componentsSeparatedByString:@"&"];
            if(components && [components count] > 0) {
                NSMutableDictionary *temp = [NSMutableDictionary dictionary];
                for(NSString *q in components) {
                    NSArray *splitted = [q componentsSeparatedByString:@"="];
                    if(splitted && [splitted count] == 2) {
                        [temp setObject:[splitted objectAtIndex:1] forKey:[splitted objectAtIndex:0]];
                    }
                }
                
                _params = [[NSDictionary dictionaryWithDictionary:temp] retain];
            }
        }
        
        if(_local && _filename) {
            //DLog(@"Annotation type: AUDIO to %@", _filename);
        } else {
            //DLog(@"Annotation type: AUDIO to %@", [_url description]);
        }
        
        if(_params) {
            //DLog(@"Annotation params: %@", [_params description]);
        }
    } else if([scheme isEqualToString:@"ylweb"]) {
        _type = kAnnotationTypeWeb;
        NSString *host = url.host;
        NSString *path = url.path;
        if(host) {
            if(path) {
                _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@%@", host, path]] retain];
            } else {
                _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@", host]] retain];
            }
        }
        
        NSString *query = url.query;
        if(query) {
            NSArray *components = [query componentsSeparatedByString:@"&"];
            if(components && [components count] > 0) {
                NSMutableDictionary *temp = [NSMutableDictionary dictionary];
                for(NSString *q in components) {
                    NSArray *splitted = [q componentsSeparatedByString:@"="];
                    if(splitted && [splitted count] == 2) {
                        [temp setObject:[splitted objectAtIndex:1] forKey:[splitted objectAtIndex:0]];
                    }
                }
                
                _params = [[NSDictionary dictionaryWithDictionary:temp] retain];
                // Url has other query components besides modal, so remove modal and add the original query components back to the url
                if([_params count] > 1 && [_params objectForKey:@"modal"]) {
                    NSMutableString *q = [NSMutableString string];
                    [_params enumerateKeysAndObjectsUsingBlock:^(id key, id obj, BOOL *stop) {
                        if(![key isEqualToString:@"modal"]) {
                            [q appendFormat:@"&%@=%@", key, obj];
                        }
                    }];
                    [q replaceCharactersInRange:NSMakeRange(0, 1) withString:@"?"];
                    
                    [_url release];
                    _url = [[NSURL URLWithString:[NSString stringWithFormat:@"http://%@%@%@", host, path, q]] retain];
                }
            }
        }
        
        //DLog(@"Annotation type: WEB to %@", [_url description]);
        if(_params) {
            //DLog(@"Annotation params: %@", [_params description]);
        }
    } else if([scheme isEqualToString:@"ylmap"]) {
        _type = kAnnotationTypeMap;
        
        NSMutableDictionary *tempParams = [NSMutableDictionary dictionary];
        NSString *type = url.host;
        if(![type isEqualToString:@"standard"] && ![type isEqualToString:@"hybrid"] && ![type isEqualToString:@"satellite"]) {
            type = @"standard";
        }
        [tempParams setObject:type forKey:@"type"];
        
        NSString *query = url.query;
        if(query) {
            NSArray *components = [query componentsSeparatedByString:@"&"];
            if(components && [components count] > 0) {
                for(NSString *q in components) {
                    NSArray *splitted = [q componentsSeparatedByString:@"="];
                    if(splitted && [splitted count] == 2) {
                        [tempParams setObject:[splitted objectAtIndex:1] forKey:[splitted objectAtIndex:0]];
                    }
                }
            }
        }
        
        if([tempParams objectForKey:@"lat"] == nil) {
            [tempParams setObject:@"37.3230556" forKey:@"lat"];
        }
        if([tempParams objectForKey:@"lon"] == nil) {
            [tempParams setObject:@"-122.0311111" forKey:@"lon"];
        }
        if([tempParams objectForKey:@"slat"] == nil) {
            [tempParams setObject:@"0.10" forKey:@"slat"];
        }
        if([tempParams objectForKey:@"slon"] == nil) {
            [tempParams setObject:@"0.10" forKey:@"slon"];
        }
        
        _params = [[NSDictionary dictionaryWithDictionary:tempParams] retain];
        //DLog(@"Annotation type: MAP to %@, %@", [_params objectForKey:@"lat"], [_params objectForKey:@"lon"]);
        //DLog(@"Annotation params: %@", [_params description]);
    } else {
        _type = kAnnotationTypeCustom;
        //DLog(@"Annotation type: CUSTOM");
    }
}

- (CGPoint)convertPDFPointToViewPoint:(CGPoint)pdfPoint renderRect:(CGRect)renderRect {
    if(_document == nil) {
        return CGPointMake(0, 0);
    }
    
    YLPageInfo *pageInfo = [_document pageInfoForPage:_page];
    return [YLAnnotation convertPDFPointToViewPoint:pdfPoint renderRect:renderRect pageInfo:pageInfo];
}

- (CGPoint)convertViewPointToPDFPoint:(CGPoint)viewPoint renderRect:(CGRect)renderRect {
    if(_document == nil) {
        return CGPointMake(0, 0);
    }
    
    YLPageInfo *pageInfo = [_document pageInfoForPage:_page];
    return [YLAnnotation convertViewPointToPDFPoint:viewPoint renderRect:renderRect pageInfo:pageInfo];
}

- (id)getAnnotationTarget:(CGPDFDictionaryRef)annotationDictionary document:(CGPDFDocumentRef)document {
    id target = nil;
    
    // Link target can be stored either in A entry or in Dest entry in annotation dictionary.
    // Dest entry is the destination array that we're looking for. It can be a direct array definition
    // or a name. If it is a name, we need to search recursively for the corresponding destination array 
    // in document's Dests tree.
    // A entry is an action dictionary. There are many action types, we're looking for GoTo and URI actions.
    // GoTo actions are used for links within the same document. The GoTo action has a D entry which is the
    // destination array, same format like Dest entry in annotation dictionary or a destination name.
    // URI actions are used for links to web resources. The URI action has a 
    // URI entry which is the destination URL.
    // If both entries are present, A entry takes precedence.
    
    CGPDFArrayRef destArray = NULL;
    CGPDFStringRef destName = NULL;
    CGPDFDictionaryRef actionDictionary = NULL;
    
    if(CGPDFDictionaryGetDictionary(annotationDictionary, "A", &actionDictionary)) {
        const char* actionType;
        if(CGPDFDictionaryGetName(actionDictionary, "S", &actionType)) {
            if(strcmp(actionType, "GoTo") == 0) {
                // D entry can be either a string object or an array object.
                if(!CGPDFDictionaryGetArray(actionDictionary, "D", &destArray)) {
                    CGPDFDictionaryGetString(actionDictionary, "D", &destName);
                }
            }
            if(strcmp(actionType, "URI") == 0) {
                CGPDFStringRef uriRef = NULL;
                if(CGPDFDictionaryGetString(actionDictionary, "URI", &uriRef)) {
                    const char *uri = (const char *)CGPDFStringGetBytePtr(uriRef);
                    target = [[NSString alloc] initWithCString: uri encoding: NSASCIIStringEncoding];
                }
            }
        }
    } else {
        // Dest entry can be either a string object or an array object.
        if(!CGPDFDictionaryGetArray(annotationDictionary, "Dest", &destArray)) {
            CGPDFDictionaryGetString(annotationDictionary, "Dest", &destName);
        }
    }
    
    if(destName != NULL) {
        // Traverse the Dests tree to locate the destination array.
        CGPDFDictionaryRef catalogDictionary = CGPDFDocumentGetCatalog(document);
        CGPDFDictionaryRef namesDictionary = NULL;
        if(CGPDFDictionaryGetDictionary(catalogDictionary, "Names", &namesDictionary)) {
            CGPDFDictionaryRef destsDictionary = NULL;
            if(CGPDFDictionaryGetDictionary(namesDictionary, "Dests", &destsDictionary)) {
                const char *destinationName = (const char *)CGPDFStringGetBytePtr(destName);
                destArray = [self findDestinationByName:destinationName inDestsTree:destsDictionary inDocument:document];
            }
        }
    }
    
    if(destArray != NULL) {
        NSInteger targetPageNumber = 0;
        // First entry in the array is the page the links points to.
        CGPDFDictionaryRef pageDictionaryFromDestArray = NULL;
        if(CGPDFArrayGetDictionary(destArray, 0, &pageDictionaryFromDestArray)) {
            size_t documentPageCount = CGPDFDocumentGetNumberOfPages(document);
            for(int i = 1; i <= documentPageCount; i++) {
                CGPDFPageRef page = CGPDFDocumentGetPage(document, i);
                CGPDFDictionaryRef pageDictionaryFromPage = CGPDFPageGetDictionary(page);
                if(pageDictionaryFromPage == pageDictionaryFromDestArray) {
                    targetPageNumber = i;
                    break;
                }
            }
        } else {
            // Some PDF generators use incorrectly the page number as the first element of the array 
            // instead of a reference to the actual page.
            CGPDFInteger pageNumber = 0;
            if(CGPDFArrayGetInteger(destArray, 0, &pageNumber)) {
                targetPageNumber = pageNumber + 1;
            }
        }
        
        if(targetPageNumber > 0) {
            target = [[NSNumber alloc] initWithInteger:(targetPageNumber - 1)];
        }
    }
    
    return [target autorelease];
}

- (CGPDFArrayRef)findDestinationByName:(const char *)destinationName inDestsTree:(CGPDFDictionaryRef)node inDocument:(CGPDFDocumentRef)document {
    CGPDFArrayRef destinationArray = NULL;
    CGPDFArrayRef limitsArray = NULL;
    if(CGPDFDictionaryGetArray(node, "Limits", &limitsArray)) {
        CGPDFStringRef lowerLimit = NULL;
        CGPDFStringRef upperLimit = NULL;
        if(CGPDFArrayGetString(limitsArray, 0, &lowerLimit)) {
            if(CGPDFArrayGetString(limitsArray, 1, &upperLimit)) {
                const unsigned char *ll = CGPDFStringGetBytePtr(lowerLimit);
                const unsigned char *ul = CGPDFStringGetBytePtr(upperLimit);
                if((strcmp(destinationName, (const char*)ll) < 0) ||
                   (strcmp(destinationName, (const char*)ul) > 0)) {
                    return NULL;
                }
            }
        }
    }
    
    CGPDFArrayRef namesArray = NULL;
    if(CGPDFDictionaryGetArray(node, "Names", &namesArray)) {
        size_t namesCount = CGPDFArrayGetCount(namesArray);
        for(int i = 0; i < namesCount; i = i + 2) {
            CGPDFStringRef destName;
            if(CGPDFArrayGetString(namesArray, i, &destName)) {
                const unsigned char *dn = CGPDFStringGetBytePtr(destName);
                if(strcmp((const char*)dn, destinationName) == 0) {
                    if(!CGPDFArrayGetArray(namesArray, i + 1, &destinationArray)) {
                        CGPDFDictionaryRef destinationDictionary = NULL;
                        if(CGPDFArrayGetDictionary(namesArray, i + 1, &destinationDictionary)) {
                            CGPDFDictionaryGetArray(destinationDictionary, "D", &destinationArray);
                        }
                    }
                    
                    return destinationArray;
                }
            }
        }
    }
    
    CGPDFArrayRef kidsArray = NULL;
    if(CGPDFDictionaryGetArray(node, "Kids", &kidsArray)) {
        size_t kidsCount = CGPDFArrayGetCount(kidsArray);
        for(int i = 0; i < kidsCount; i++) {
            CGPDFDictionaryRef kidNode = NULL;
            if(CGPDFArrayGetDictionary(kidsArray, i, &kidNode)) {
                destinationArray = [self findDestinationByName: destinationName inDestsTree: kidNode inDocument:document];
                if(destinationArray != NULL) {
                    return destinationArray;
                }
            }
        }
    }
    
    return NULL;
}

@end
