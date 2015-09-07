//
//  YLScanner.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLScanner.h"
#import "YLFontCollection.h"
#import "YLRenderingStateStack.h"
#import "YLRenderingState.h"
#import "YLStringDetector.h"
#import "YLSelection.h"
#import "YLFontHelper.h"

void setHorizontalScale(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setHorizontalScale:pdfScanner];
}

void setTextLeading(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setTextLeading:pdfScanner];
}

void setFont(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner setFont:pdfScanner];
}

void setTextRise(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setTextRise:pdfScanner];
}

void setCharacterSpacing(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setCharacterSpacing:pdfScanner];
}

void setWordSpacing(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setWordSpacing:pdfScanner];
}

void newLine(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setNewLine:pdfScanner];
}

void newLineWithLeading(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner setNewLine:pdfScanner andSaveLeading:NO];
}

void newLineSetLeading(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner setNewLine:pdfScanner andSaveLeading:YES];
}

void newParagraph(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner setNewParagraph:pdfScanner];
}

void setTextMatrix(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
	[scanner setTextMatrix:pdfScanner];
}

void printString(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner printString:pdfScanner];
}

void printStringNewLine(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner printStringWithNewLine:pdfScanner];
}

void printStringNewLineSetSpacing(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner printStringWithNewLineAndSetSpacing:pdfScanner];
}

void printStringsAndSpaces(CGPDFScannerRef pdfScanner, void *info) {
    YLScanner *scanner = (YLScanner *)info;
    [scanner printStringsAndSpaces:pdfScanner];
}

void pushRenderingState(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner pushRenderingState:pdfScanner];
}

void popRenderingState(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner popRenderingState:pdfScanner];
}

void applyTransformation(CGPDFScannerRef pdfScanner, void *info) {
	YLScanner *scanner = (YLScanner *)info;
    [scanner applyTransformation:pdfScanner];
}

CGPDFStringRef getString(CGPDFScannerRef pdfScanner) {
	CGPDFStringRef pdfString;
	CGPDFScannerPopString(pdfScanner, &pdfString);
	return pdfString;
}

CGPDFReal getNumber(CGPDFScannerRef pdfScanner) {
	CGPDFReal value;
	CGPDFScannerPopNumber(pdfScanner, &value);
	return value;
}

CGPDFArrayRef getArray(CGPDFScannerRef pdfScanner) {
	CGPDFArrayRef pdfArray;
	CGPDFScannerPopArray(pdfScanner, &pdfArray);
	return pdfArray;
}

CGPDFObjectRef getObject(CGPDFArrayRef pdfArray, int index) {
	CGPDFObjectRef pdfObject;
	CGPDFArrayGetObject(pdfArray, index, &pdfObject);
	return pdfObject;
}

CGPDFStringRef getStringValue(CGPDFObjectRef pdfObject) {
	CGPDFStringRef string;
	CGPDFObjectGetValue(pdfObject, kCGPDFObjectTypeString, &string);
	return string;
}

float getNumericalValue(CGPDFObjectRef pdfObject, CGPDFObjectType type) {
	if(type == kCGPDFObjectTypeReal) {
		CGPDFReal tx;
		CGPDFObjectGetValue(pdfObject, kCGPDFObjectTypeReal, &tx);
		return tx;
	} else if (type == kCGPDFObjectTypeInteger) {
		CGPDFInteger tx;
		CGPDFObjectGetValue(pdfObject, kCGPDFObjectTypeInteger, &tx);
		return tx;
	}
    
	return 0;
}

CGAffineTransform getTransform(CGPDFScannerRef pdfScanner) {
	CGAffineTransform transform;
	transform.ty = getNumber(pdfScanner);
	transform.tx = getNumber(pdfScanner);
	transform.d = getNumber(pdfScanner);
	transform.c = getNumber(pdfScanner);
	transform.b = getNumber(pdfScanner);
	transform.a = getNumber(pdfScanner);
	return transform;
}


@interface YLScanner () {
    CGPDFPageRef _page;
	
	YLStringDetector *_stringDetector;
	YLFontCollection *_fontCollection;
	YLRenderingStateStack *_renderingStateStack;
    YLSelection *_currentSelection;
    
	NSMutableString *_content;
    NSMutableArray *_selections;
}

@property (nonatomic, retain) YLStringDetector *stringDetector;
@property (nonatomic, retain) YLFontCollection *fontCollection;
@property (nonatomic, retain) YLRenderingStateStack *renderingStateStack;
@property (nonatomic, readonly) YLRenderingState *renderingState;
@property (nonatomic, retain) YLSelection *currentSelection;
@property (nonatomic, retain) NSMutableString *content;
@property (nonatomic, retain) NSMutableArray *selections;

- (CGPDFOperatorTableRef)newOperatorTable;
- (YLFontCollection *)fontCollectionWithPage:(CGPDFPageRef)page;

- (void)handleString:(CGPDFStringRef)pdfString;
- (void)handleSpace:(float)value;
- (BOOL)isSpace:(float)width;

@end

@implementation YLScanner

@synthesize stringDetector = _stringDetector;
@synthesize fontCollection = _fontCollection;
@synthesize renderingStateStack = _renderingStateStack;
@synthesize renderingState;
@synthesize currentSelection = _currentSelection;
@synthesize content = _content;
@synthesize selections = _selections;

+ (YLScanner *)scannerWithPage:(CGPDFPageRef)page {
	return [[[YLScanner alloc] initWithPage:page] autorelease];
}

- (id)initWithPage:(CGPDFPageRef)page {
    self = [super init];
    if(self) {
		_page = CGPDFPageRetain(page);
		self.fontCollection = [self fontCollectionWithPage:page];
        if(self.fontCollection == nil) {
            NSDictionary *fonts = [[YLFontHelper sharedInstance] fontCollection];
            self.fontCollection = [[YLFontCollection alloc] initWithFonts:fonts];
        }
		self.selections = [NSMutableArray array];
        self.content = [NSMutableString string];
	}
	
	return self;
}

- (void)dealloc {
    CGPDFPageRelease(_page);
	[_fontCollection release];
	[_selections release];
    [_currentSelection release];
	[_renderingStateStack release];
	[_stringDetector release];
	[_content release];
    
	[super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (YLRenderingState *)renderingState {
	return [self.renderingStateStack topRenderingState];
}

- (NSArray *)select:(NSString *)keyword {
	self.stringDetector = [YLStringDetector detectorWithKeyword:keyword delegate:self];
	[self.selections removeAllObjects];
    self.renderingStateStack = [YLRenderingStateStack stack];
    
    if(self.fontCollection) {
        CGPDFOperatorTableRef operatorTable = [self newOperatorTable];
        CGPDFContentStreamRef contentStream = CGPDFContentStreamCreateWithPage(_page);
        CGPDFScannerRef scanner = CGPDFScannerCreate(contentStream, operatorTable, self);
        CGPDFScannerScan(scanner);
        
        CGPDFScannerRelease(scanner);
        CGPDFContentStreamRelease(contentStream);
        CGPDFOperatorTableRelease(operatorTable);
    }
    
    //DLog(@"Finished search on page: %zd", CGPDFPageGetPageNumber(_page));
    //DLog(@"TEXT: %@", self.content);
	
	return [NSArray arrayWithArray:self.selections];
}


#pragma mark -
#pragma mark Text State Operators
- (void)setHorizontalScale:(CGPDFScannerRef)scanner {
    [self.renderingState setHorizontalScaling:getNumber(scanner)];
}

- (void)setTextLeading:(CGPDFScannerRef)scanner {
    [self.renderingState setLeading:getNumber(scanner)];
}

- (void)setFont:(CGPDFScannerRef)scanner {
    CGPDFReal fontSize;
	const char *fontName;
	CGPDFScannerPopNumber(scanner, &fontSize);
	CGPDFScannerPopName(scanner, &fontName);
	
    YLRenderingState *state = [self renderingState];
	YLFont *font = [self.fontCollection fontNamed:[NSString stringWithUTF8String:fontName]];
	[state setFont:font];
	[state setFontSize:fontSize];
}

- (void)setTextRise:(CGPDFScannerRef)scanner {
    [self.renderingState setTextRise:getNumber(scanner)];
}

- (void)setCharacterSpacing:(CGPDFScannerRef)scanner {
    [self.renderingState setCharacterSpacing:getNumber(scanner)];
}

- (void)setWordSpacing:(CGPDFScannerRef)scanner {
    [self.renderingState setWordSpacing:getNumber(scanner)];
}


#pragma mark -
#pragma mark Text Positioning Operators
- (void)setNewLine:(CGPDFScannerRef)scanner {
    if(self.currentSelection) {
        [self.currentSelection finalizeWithState:self.renderingState];
    }
    [self.renderingState newLine];
    if(self.currentSelection) {
        [self.currentSelection startWithState:self.renderingState];
    }
    
    if([self.content length] > 0) {
        NSString *lastCharacter = [NSString stringWithFormat:@"%C", [self.content characterAtIndex:(self.content.length - 1)]];
        if(![lastCharacter isEqualToString:@" "]) {
            [self.stringDetector reset];
            [self.content appendString:@" "];
        }
    }
}

- (void)setNewLine:(CGPDFScannerRef)scanner andSaveLeading:(BOOL)saveLeading {
    CGPDFReal tx, ty;
	CGPDFScannerPopNumber(scanner, &ty);
	CGPDFScannerPopNumber(scanner, &tx);
    if(self.currentSelection && fabsf(ty) > 0) {
        [self.currentSelection finalizeWithState:self.renderingState];
    }
	[self.renderingState newLineWithTy:-ty tx:tx save:saveLeading];
    if(self.currentSelection && fabsf(ty) > 0) {
        [self.currentSelection startWithState:self.renderingState];
    }
    
    if([self.content length] > 0 && fabsf(ty) > 0) {
        NSString *lastCharacter = [NSString stringWithFormat:@"%C", [self.content characterAtIndex:(self.content.length - 1)]];
        if(![lastCharacter isEqualToString:@" "]) {
            [self.stringDetector reset];
            [self.content appendString:@" "];
        }
    }
}

- (void)setNewParagraph:(CGPDFScannerRef)scanner {
    if(self.currentSelection) {
        [self.currentSelection finalizeWithState:self.renderingState];
    }
    [self.renderingState setTextMatrix:CGAffineTransformIdentity replaceLineMatrix:YES];
    if(self.currentSelection) {
        [self.currentSelection startWithState:self.renderingState];
    }
}

- (void)setTextMatrix:(CGPDFScannerRef)scanner {
    if(self.currentSelection) {
        [self.currentSelection finalizeWithState:self.renderingState];
    }
    [self.renderingState setTextMatrix:getTransform(scanner) replaceLineMatrix:YES];
    if(self.currentSelection) {
        [self.currentSelection startWithState:self.renderingState];
    }
}


#pragma mark -
#pragma mark Text Showing Operators
- (void)printString:(CGPDFScannerRef)scanner {
    [self handleString:getString(scanner)];
}

- (void)printStringWithNewLine:(CGPDFScannerRef)scanner {
    [self setNewLine:scanner];
    [self printString:scanner];
}

- (void)printStringWithNewLineAndSetSpacing:(CGPDFScannerRef)scanner {
    [self setWordSpacing:scanner];
    [self setCharacterSpacing:scanner];
    [self printStringWithNewLine:scanner];
}

- (void)printStringsAndSpaces:(CGPDFScannerRef)scanner {
    CGPDFArrayRef array = getArray(scanner);
	for(int i = 0; i < CGPDFArrayGetCount(array); i++) {
		CGPDFObjectRef pdfObject = getObject(array, i);
		CGPDFObjectType valueType = CGPDFObjectGetType(pdfObject);
        
		if(valueType == kCGPDFObjectTypeString) {
            [self handleString:getStringValue(pdfObject)];
		} else {
            [self handleSpace:getNumericalValue(pdfObject, valueType)];
		}
	}
}


#pragma mark -
#pragma mark Graphics State Operators
- (void)pushRenderingState:(CGPDFScannerRef)scanner {
    YLRenderingState *state = [self.renderingState copy];
    if(self.currentSelection) {
        [self.currentSelection finalizeWithState:self.renderingState];
    }
	[self.renderingStateStack pushRenderingState:state];
    if(self.currentSelection) {
        [self.currentSelection startWithState:state];
    }
	[state release];
}

- (void)popRenderingState:(CGPDFScannerRef)scanner {
    if(self.currentSelection) {
        [self.currentSelection finalizeWithState:self.renderingState];
    }
    [self.renderingStateStack popRenderingState];
    if(self.currentSelection) {
        [self.currentSelection startWithState:self.renderingState];
    }
}

- (void)applyTransformation:(CGPDFScannerRef)scanner {
    YLRenderingState *state = self.renderingState;
	state.ctm = CGAffineTransformConcat(getTransform(scanner), state.ctm);
    if(self.currentSelection) {
        [self.currentSelection startWithState:self.renderingState];
    }
}


#pragma mark -
#pragma mark Private Methods
- (CGPDFOperatorTableRef)newOperatorTable {
	CGPDFOperatorTableRef operatorTable = CGPDFOperatorTableCreate();

	// Text-showing operators
	CGPDFOperatorTableSetCallback(operatorTable, "Tj", printString);
	CGPDFOperatorTableSetCallback(operatorTable, "\'", printStringNewLine);
	CGPDFOperatorTableSetCallback(operatorTable, "\"", printStringNewLineSetSpacing);
	CGPDFOperatorTableSetCallback(operatorTable, "TJ", printStringsAndSpaces);
	
	// Text-positioning operators
	CGPDFOperatorTableSetCallback(operatorTable, "Tm", setTextMatrix);
	CGPDFOperatorTableSetCallback(operatorTable, "Td", newLineWithLeading);
	CGPDFOperatorTableSetCallback(operatorTable, "TD", newLineSetLeading);
	CGPDFOperatorTableSetCallback(operatorTable, "T*", newLine);
    CGPDFOperatorTableSetCallback(operatorTable, "BT", newParagraph);
	
	// Text state operators
	CGPDFOperatorTableSetCallback(operatorTable, "Tw", setWordSpacing);
	CGPDFOperatorTableSetCallback(operatorTable, "Tc", setCharacterSpacing);
	CGPDFOperatorTableSetCallback(operatorTable, "TL", setTextLeading);
	CGPDFOperatorTableSetCallback(operatorTable, "Tz", setHorizontalScale);
	CGPDFOperatorTableSetCallback(operatorTable, "Ts", setTextRise);
	CGPDFOperatorTableSetCallback(operatorTable, "Tf", setFont);
	
	// Graphics state operators
	CGPDFOperatorTableSetCallback(operatorTable, "cm", applyTransformation);
	CGPDFOperatorTableSetCallback(operatorTable, "q", pushRenderingState);
	CGPDFOperatorTableSetCallback(operatorTable, "Q", popRenderingState);
	
	return operatorTable;
}

- (YLFontCollection *)fontCollectionWithPage:(CGPDFPageRef)page {
	CGPDFDictionaryRef dict = CGPDFPageGetDictionary(page);
	if(!dict) 	{
		return nil;
	}
	
	CGPDFDictionaryRef resources;
	if(!CGPDFDictionaryGetDictionary(dict, "Resources", &resources)) {
		return nil;
	}

	CGPDFDictionaryRef fonts;
	if(!CGPDFDictionaryGetDictionary(resources, "Font", &fonts)) {
		return nil;
	}

	YLFontCollection *collection = [[YLFontCollection alloc] initWithFontDictionary:fonts];
	return [collection autorelease];
}

- (void)handleString:(CGPDFStringRef)pdfString {
	YLFont *font = self.renderingState.font;
    if(font == nil) {
        return;
    }
	NSString *string = [self.stringDetector appendPDFString:pdfString withFont:font];
	[self.content appendString:string];
}

- (void)handleSpace:(float)value {
    float width = [self.renderingState convertToUserSpace:value];
    [self.renderingState translateTextPosition:CGSizeMake(-width, 0)];
    if([self isSpace:value] && [self.content length] > 0) {
        NSString *lastCharacter = [NSString stringWithFormat:@"%C", [self.content characterAtIndex:(self.content.length - 1)]];
        if(![lastCharacter isEqualToString:@" "]) {
            [self.stringDetector reset];
            [self.content appendString:@" "];
        }
    }
}

- (BOOL)isSpace:(float)width {
    YLFont *font = self.renderingState.font;
    CGFloat spaceWidth = [font widthOfSpaceWithFontSize:self.renderingState.fontSize];
	return (abs(width) >= spaceWidth && abs(width) <= (spaceWidth + 50));
}


#pragma mark -
#pragma mark StringDetectorDelegate Methods
- (void)detector:(YLStringDetector *)detector didScanCharacter:(unichar)character {
    YLFont *font = self.renderingState.font;
    unichar cid = character;
    NSNumber *mapping = [font cidCharacter:character];
    if(mapping) {
        cid = [mapping intValue];
        NSString *orig = [NSString stringWithFormat:@"%C", character];
        NSString *rep = [NSString stringWithFormat:@"%C", cid];
        if([[orig lowercaseString] isEqualToString:[rep lowercaseString]]) {
            // if the mapping results in the uppercase/lowercase we ignore it because
            // it will result in wrong width calculations
            cid = character;
        }
    }

	CGFloat width = [font widthOfCharacter:cid withFontSize:self.renderingState.fontSize];
	width += self.renderingState.characterSpacing;
	if([self.renderingState.font isSpaceCharacter:character]) {
		width += self.renderingState.wordSpacing;
	}
    
	[self.renderingState translateTextPosition:CGSizeMake(width, 0)];
}

- (void)detectorDidStartMatching:(YLStringDetector *)stringDetector {
    if(self.currentSelection == nil) {
        self.currentSelection = [[[YLSelection alloc] init] autorelease];
    } else {
        [self.currentSelection reset];
    }

    [self.currentSelection startWithState:self.renderingState];
}

- (void)detectorFoundString:(YLStringDetector *)detector {
    if(self.currentSelection) {
        [self.currentSelection finalizeWithState:self.renderingState];
        [self.selections addObject:self.currentSelection];
        
        [_currentSelection release];
        _currentSelection = nil;
    }
}

@end
