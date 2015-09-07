//
//  YLCompositeFont.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLCompositeFont.h"
#import "YLFontHelper.h"

@interface YLCompositeFont () {
    CGFloat _defaultWidth;
}

@end


@implementation YLCompositeFont

@synthesize defaultWidth = _defaultWidth;

- (id)initWithFontDictionary:(CGPDFDictionaryRef)dict {
    self = [super initWithFontDictionary:dict];
    if(self) {
        
    }
    
    return self;
}

- (void)dealloc {
    [super dealloc];
}

- (void)setToUnicodeWithFontDictionary:(CGPDFDictionaryRef)dict {
    [super setToUnicodeWithFontDictionary:dict];
    if(self.cmap == nil) {
        CGPDFDictionaryRef cidSystemInfo;
        if(!CGPDFDictionaryGetDictionary(dict, "CIDSystemInfo", &cidSystemInfo)) {
            return;
        }
        
        NSString *ordering = nil;
        NSString *registry = nil;
        CGPDFStringRef temp;
        if(CGPDFDictionaryGetString(cidSystemInfo, "Ordering", &temp)) {
            ordering = [[[NSString alloc] initWithBytes:CGPDFStringGetBytePtr(temp) length:CGPDFStringGetLength(temp) encoding:NSUTF8StringEncoding] autorelease];
        }
        if(CGPDFDictionaryGetString(cidSystemInfo, "Registry", &temp)) {
            registry = [[[NSString alloc] initWithBytes:CGPDFStringGetBytePtr(temp) length:CGPDFStringGetLength(temp) encoding:NSUTF8StringEncoding] autorelease];
        }
        
        if(ordering && registry) {
            self.cmap = [[YLFontHelper sharedInstance] cmapWithRegistry:registry ordering:ordering];
        }
    }
}

- (void)setWidthsWithFontDictionary:(CGPDFDictionaryRef)dict {
	CGPDFArrayRef widthsArray;
	if(CGPDFDictionaryGetArray(dict, "W", &widthsArray)) {
        [self setWidthsWithArray:widthsArray];
    }

	CGPDFInteger defaultWidthValue;
	if(CGPDFDictionaryGetInteger(dict, "DW", &defaultWidthValue)) {
		_defaultWidth = (float)(defaultWidthValue / 1000.0);
	}
}

- (void)setWidthsWithArray:(CGPDFArrayRef)widthsArray {
    NSUInteger length = CGPDFArrayGetCount(widthsArray);
    int idx = 0;
    while(idx < length) {
        CGPDFInteger baseCid = 0;
        CGPDFArrayGetInteger(widthsArray, idx++, &baseCid);

        CGPDFObjectRef integerOrArray = nil;
		CGPDFArrayGetObject(widthsArray, idx++, &integerOrArray);
		if(CGPDFObjectGetType(integerOrArray) == kCGPDFObjectTypeInteger) {
            // [ first last width ]
			CGPDFInteger maxCid;
			CGPDFInteger glyphWidth;
			CGPDFObjectGetValue(integerOrArray, kCGPDFObjectTypeInteger, &maxCid);
			CGPDFArrayGetInteger(widthsArray, idx++, &glyphWidth);
			[self setWidthsFrom:baseCid to:maxCid width:glyphWidth];
		} else {
            // [ first list-of-widths ]
			CGPDFArrayRef glyphWidths;
            if(CGPDFObjectGetValue(integerOrArray, kCGPDFObjectTypeArray, &glyphWidths)) {
                [self setWidthsWithBase:baseCid array:glyphWidths];
            }
        }
	}
}

- (void)setWidthsFrom:(CGPDFInteger)cid to:(CGPDFInteger)maxCid width:(CGPDFInteger)width {
    while(cid <= maxCid) {
        [self.widths setObject:[NSNumber numberWithFloat:(width / 1000.0)] forKey:[NSNumber numberWithInteger:cid++]];
    }
}

- (void)setWidthsWithBase:(CGPDFInteger)base array:(CGPDFArrayRef)array {
    NSInteger count = CGPDFArrayGetCount(array);
    CGPDFReal width;
    for(int index = 0; index < count ; index++) {
        if(CGPDFArrayGetNumber(array, index, &width)) {
            [self.widths setObject:[NSNumber numberWithFloat:(width / 1000.0)] forKey:[NSNumber numberWithInteger:(base+index)]];
        }
    }
}

- (CGFloat)widthOfCharacter:(unichar)character withFontSize:(CGFloat)fontSize {
    NSNumber *width = [self.widths objectForKey:[NSNumber numberWithInt:character]];
    if(width == nil) {
		return (float)(_defaultWidth * fontSize);
	}
    
	return (float)([width floatValue] * fontSize);
}

- (unichar)mappedUnicodeValue:(unichar)character {
    unichar value = 0;
    BOOL found = NO;
    if(self.cmap) {
        NSNumber *result = [self.cmap unicodeCharacter:character];
        if(result) {
            value = [result intValue];
            found = YES;
        }
    }
    
    return (found) ? value : [self encodedUnicodeValue:character];
}

- (unichar)encodedUnicodeValue:(unichar)character {
    NSString *temp = [NSString stringWithFormat:@"%C", character];
    return (temp && [temp length] > 0) ? [temp characterAtIndex:0] : 0;
}

- (NSString *)stringWithPDFString:(CGPDFStringRef)pdfString ligatures:(BOOL *)ligatures {
    const unsigned char *characterCodes = CGPDFStringGetBytePtr(pdfString);
    NSUInteger characterCodeCount = CGPDFStringGetLength(pdfString);
    NSMutableString *unicodeString = [NSMutableString string];
    
    for(int i = 0; i < characterCodeCount; i+=2) {
        unichar characterCode = characterCodes[i] << 8 | characterCodes[i+1];
        unichar characterSelector = [self mappedUnicodeValue:characterCode];
        [unicodeString appendFormat:@"%C", characterSelector];
    }
    
    return unicodeString;
}

- (CGFloat)widthOfSpaceWithFontSize:(CGFloat)fontSize {
    unichar cid = 0x20;
    if(self.cmap) {
        NSNumber *mapping = [self.cmap cidCharacter:0x20];
        if(mapping) {
            cid = [mapping intValue];
        }
    }
    
    CGFloat width = [self widthOfCharacter:cid withFontSize:1.0];
    if(width == 0.0) {
        width = fabsf(([self.fontDescriptor averageWidth] / 3.0) * 1000.0);
    } else {
        width *= 1000.0;
    }
    
    return width;
}

- (BOOL)isSpaceCharacter:(unichar)character {
    unichar space = 0x20;
    if(self.cmap) {
        NSNumber *mapping = [self.cmap cidCharacter:space];
        if(mapping) {
            space = [mapping intValue];
        }
    }
    
    return (space == character);
}

@end
