//
//  YLType0Font.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLType0Font.h"
#import "YLFontHelper.h"

@interface YLType0Font () {
    NSMutableArray *_descendantFonts;
    BOOL _performCidMapping;
}

@end

@implementation YLType0Font

- (id)initWithFontDictionary:(CGPDFDictionaryRef)dict {
    CGPDFArrayRef fontsArray;
    if(!CGPDFDictionaryGetArray(dict, kPDFDescendantFontsKey, &fontsArray)) {
        return nil;
    }
    
    NSUInteger count = CGPDFArrayGetCount(fontsArray);
    NSMutableArray *fonts = [NSMutableArray array];
    for(int i = 0; i < count; i++) {
        CGPDFDictionaryRef fontDict;
        if(!CGPDFArrayGetDictionary(fontsArray, i, &fontDict)) {
            continue;
        }
        
        const char *subtype;
        if(!CGPDFDictionaryGetName(fontDict, kPDFFontSubtypeKey, &subtype)) {
            continue;
        }
        
        if(strcmp(subtype, "CIDFontType0") == 0 || strcmp(subtype, "CIDFontType2") == 0) {
            YLCompositeFont *font = [[YLCompositeFont alloc] initWithFontDictionary:fontDict];
            [fonts addObject:font];
            [font release];
        }
    }
    
    if([fonts count] == 0) {
        return nil;
    }
    
    self = [super initWithFontDictionary:dict];
    if(self) {
		_descendantFonts = [fonts retain];
	}
    
	return self;
}

- (void)dealloc {
    [_descendantFonts release];
    
    [super dealloc];
}

- (void)setToUnicodeWithFontDictionary:(CGPDFDictionaryRef)dict {
    _performCidMapping = NO;
    
	CGPDFStreamRef stream;
	if(CGPDFDictionaryGetStream(dict, kPDFToUnicodeKey, &stream)) {
        self.cmap = [[[YLCMap alloc] initWithPDFStream:stream] autorelease];
    } else {
        // Some encoding's use an external cmap file for cid mapping
        self.cmap = [[YLFontHelper sharedInstance] cmapWithName:self.origEncoding];
        if(self.cmap) {
            _performCidMapping = YES;
        }
    }
}

- (CGFloat)widthOfCharacter:(unichar)character withFontSize:(CGFloat)fontSize {
    YLFont *descendantFont = [_descendantFonts lastObject];
    return [descendantFont widthOfCharacter:character withFontSize:fontSize];
}

- (YLFontDescriptor *)fontDescriptor {
	YLFont *descendantFont = [_descendantFonts lastObject];
	return descendantFont.fontDescriptor;
}

- (CGFloat)minY {
	YLFont *descendantFont = [_descendantFonts lastObject];
	return [descendantFont.fontDescriptor descent];
}

- (CGFloat)maxY {
	YLFont *descendantFont = [_descendantFonts lastObject];
	return [descendantFont.fontDescriptor ascent];
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
    if(_performCidMapping) {
        size_t stringLength = CGPDFStringGetLength(pdfString);
		const unsigned char *characterCodes = CGPDFStringGetBytePtr(pdfString);
		NSMutableString *unicodeString = [NSMutableString string];
		
        for(int i = 0; i < stringLength; i+=2) {
			unichar characterCode = characterCodes[i] << 8 | characterCodes[i+1];
			unichar characterSelector = [self mappedUnicodeValue:characterCode];
            [unicodeString appendFormat:@"%C", characterSelector];
		}
        
        NSMutableString *result = [NSMutableString string];
        YLFont *descendantFont = [_descendantFonts lastObject];
        for(int i = 0; i < [unicodeString length]; i++) {
            unichar decoded = [[descendantFont.cmap unicodeCharacter:[unicodeString characterAtIndex:i]] intValue];
            [result appendFormat:@"%C", decoded];
        }
        
		return result;
    } else if(self.cmap) {
		size_t stringLength = CGPDFStringGetLength(pdfString);
		const unsigned char *characterCodes = CGPDFStringGetBytePtr(pdfString);
		NSMutableString *unicodeString = [NSMutableString string];
		
        for(int i = 0; i < stringLength; i+=2) {
			unichar characterCode = characterCodes[i] << 8 | characterCodes[i+1];
			unichar characterSelector = [self mappedUnicodeValue:characterCode];
            [unicodeString appendFormat:@"%C", characterSelector];
		}
        
		return unicodeString;
	} else {
		YLFont *descendantFont = [_descendantFonts lastObject];
        return [descendantFont stringWithPDFString:pdfString ligatures:ligatures];
	}
}

- (CGFloat)widthOfSpaceWithFontSize:(CGFloat)fontSize {
    YLFont *descendantFont = [_descendantFonts lastObject];
    return [descendantFont widthOfSpaceWithFontSize:fontSize];
}

- (BOOL)isSpaceCharacter:(unichar)character {
    YLFont *descendantFont = [_descendantFonts lastObject];
    return [descendantFont isSpaceCharacter:character];
}

@end
