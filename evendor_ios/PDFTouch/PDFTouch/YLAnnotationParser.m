//
//  YLAnnotationParser.m
//
//  Created by Kemal Taskin on 5/16/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLAnnotationParser.h"
#import "YLDocument.h"
#import "YLAnnotation.h"
#import "YLAnnotationView.h"
#import "YLLinkAnnotationView.h"
#import "YLVideoAnnotationView.h"
#import "YLWebAnnotationView.h"
#import "YLAudioAnnotationView.h"
#import "YLMapAnnotationView.h"

@interface YLAnnotationParser () {
    YLDocument *_document;
    NSMutableDictionary *_annotationDic;
}

@end

@implementation YLAnnotationParser

- (id)initWithDocument:(YLDocument *)document {
    self = [super init];
    if(self) {
        _document = document;
        _annotationDic = [[NSMutableDictionary alloc] init];
    }
    
    return self;
}

- (void)dealloc {
    [_annotationDic release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (NSArray *)annotationsForPage:(NSUInteger)page {
    NSArray *annotations = [_annotationDic objectForKey:[NSNumber numberWithUnsignedInteger:page]];
    if(annotations == nil) {
        CGPDFDocumentRef document = [_document requestDocumentRef];
        if(document != NULL) {
            CGPDFDocumentRetain(document);
            CGPDFPageRef pageRef = CGPDFDocumentGetPage(document, (page + 1));
            if(pageRef != NULL) {
                CGPDFDictionaryRef pageDictionary = CGPDFPageGetDictionary(pageRef);
                CGPDFArrayRef annotsArray = NULL;
                CGPDFDictionaryGetArray(pageDictionary, "Annots", &annotsArray);
                if(annotsArray != NULL) {
                    NSMutableArray *annotationsArray = [NSMutableArray array];
                    NSInteger annotsCount = CGPDFArrayGetCount(annotsArray);
                    
                    for(NSInteger i = 0; i < annotsCount; i++) {
                        CGPDFDictionaryRef annotationDictionary = NULL;            
                        if(CGPDFArrayGetDictionary(annotsArray, i, &annotationDictionary)) {
                            const char *annotationType;
                            CGPDFDictionaryGetName(annotationDictionary, "Subtype", &annotationType);
                            
                            if(strcmp(annotationType, "Link") == 0) {
                                YLAnnotation *annotation = [[YLAnnotation alloc] initWithDocument:document dictionary:annotationDictionary page:page];
                                [annotation setDocument:_document];
                                [annotationsArray addObject:annotation];
                                [annotation release];
                            }
                        }
                    }
                    
                    NSNumber *pageNumber = [NSNumber numberWithUnsignedInteger:page];
                    [_annotationDic setObject:annotationsArray forKey:pageNumber];
                    annotations = [_annotationDic objectForKey:pageNumber];
                }
            }
            
            CGPDFDocumentRelease(document);
        }
    }
    
    return annotations;
}

- (UIView<YLAnnotationView> *)viewForAnnotation:(YLAnnotation *)annotation frame:(CGRect)frame {
    if(annotation.type == kAnnotationTypePage || annotation.type == kAnnotationTypeLink) {
        YLLinkAnnotationView *view = [[YLLinkAnnotationView alloc] initWithFrame:frame];
        [view setAnnotation:annotation];
        return [view autorelease];
    } else if(annotation.type == kAnnotationTypeVideo) {
        YLVideoAnnotationView *view = [[YLVideoAnnotationView alloc] initWithFrame:frame];
        [view setAnnotation:annotation];
        return [view autorelease];
    } else if(annotation.type == kAnnotationTypeAudio) {
        YLAudioAnnotationView *view = [[YLAudioAnnotationView alloc] initWithFrame:frame];
        [view setAnnotation:annotation];
        return [view autorelease];
    } else if(annotation.type == kAnnotationTypeWeb) {
        YLWebAnnotationView *view = [[YLWebAnnotationView alloc] initWithFrame:frame];
        [view setAnnotation:annotation];
        return [view autorelease];
    } else if(annotation.type == kAnnotationTypeMap) {
        YLMapAnnotationView *view = [[YLMapAnnotationView alloc] initWithFrame:frame];
        [view setAnnotation:annotation];
        return [view autorelease];
    } else {
        return nil;
    }
}
    
@end
