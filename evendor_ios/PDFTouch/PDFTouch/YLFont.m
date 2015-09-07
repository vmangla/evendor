//
//  YLFont.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLFont.h"
#import "YLType0Font.h"
#import "YLFontHelper.h"
#import <CoreText/CoreText.h>

const char *kPDFFontDescriptorKey = "FontDescriptor";
const char *kPDFFontKey = "Font";
const char *kPDFFontTypeKey = "Type";
const char *kPDFFontSubtypeKey = "Subtype";
const char *kPDFDescendantFontsKey = "DescendantFonts";
const char *kPDFBaseFontKey = "BaseFont";
const char *kPDFFirstCharKey = "FirstChar";
const char *kPDFLastCharKey = "LastChar";
const char *kPDFWidthsKey = "Widths";
const char *kPDFEncodingKey = "Encoding";
const char *kPDFBaseEncodingKey = "BaseEncoding";
const char *kPDFEncodingDifferencesKey = "Differences";
const char *kPDFToUnicodeKey = "ToUnicode";
const char *kPDFFontFileKey = "FontFile";
const char *kPDFFontFile2Key = "FontFile2";
const char *kPDFFontFile3Key = "FontFile3";

@interface YLFont () {
    NSString *_type;
    NSString *_baseFont;
    NSString *_normalizedFontName;
    NSString *_origEncoding;
    
    NSStringEncoding _encoding;
    
    YLCMap *_cmap;
    YLFontDescriptor *_fontDescriptor;
    
    NSRange _widthsRange;
    NSMutableDictionary *_widths;
    CFMutableDictionaryRef _characterMappings;
    CFMutableDictionaryRef _reverseCharacterMappings;
    
    NSMutableDictionary *_ctFontDict;
    CGFloat _ctSpaceWidth;
    CGFloat _ctMinY;
    CGFloat _ctMaxY;
}

@end

@implementation YLFont

@synthesize type = _type;
@synthesize baseFont = _baseFont;
@synthesize origEncoding = _origEncoding;
@synthesize cmap = _cmap;
@synthesize fontDescriptor = _fontDescriptor;
@synthesize widths = _widths;
@synthesize encoding = _encoding;

+ (YLFont *)fontWithDictionary:(CGPDFDictionaryRef)dict {
	const char *subtype = nil;
	CGPDFDictionaryGetName(dict, kPDFFontSubtypeKey, &subtype);
    if(!subtype) {
        return nil;
    }
    
    static NSMutableDictionary *fontDic = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        fontDic = [[NSMutableDictionary alloc] init];
        [fontDic setObject:[YLFont class] forKey:@"Type1"];
        [fontDic setObject:[YLFont class] forKey:@"MMType1"];
        [fontDic setObject:[YLFont class] forKey:@"TrueType"];
        [fontDic setObject:[YLType0Font class] forKey:@"Type0"];
    });

    Class fontClass = [fontDic objectForKey:[NSString stringWithUTF8String:subtype]];
    if(fontClass == nil) {
        return nil;
    }
    
	return [[[fontClass alloc] initWithFontDictionary:dict] autorelease];
}

- (id)initWithFontDictionary:(CGPDFDictionaryRef)dict {
    self = [super init];
    if(self) {
        const char *type = nil;
        if(CGPDFDictionaryGetName(dict, kPDFFontSubtypeKey, &type)) {
            _type = [[NSString stringWithUTF8String:type] retain];
        };
        
		const char *fontName = nil;
		if(CGPDFDictionaryGetName(dict, kPDFBaseFontKey, &fontName)) {
			_baseFont = [[NSString stringWithUTF8String:fontName] retain];
            
            BOOL standardFont = NO;
            NSString *ctName = [[YLFontHelper sharedInstance] coreTextFontNameForFont:_baseFont standardFont:&standardFont];
            if(standardFont) {
                _normalizedFontName = [ctName retain];
                _ctFontDict = [[NSMutableDictionary alloc] init];
            }
		}
        
        _ctMaxY = MAXFLOAT;
        _ctMinY = MAXFLOAT;
        _ctSpaceWidth = MAXFLOAT;
        
        _widths = [[NSMutableDictionary alloc] init];
    
		[self setWidthsWithFontDictionary:dict];
		[self setFontDescriptorWithFontDictionary:dict];
        [self setEncodingWithFontDictionary:dict];
        [self setToUnicodeWithFontDictionary:dict];
	}
    
	return self;
}

- (void)dealloc {
    [_type release];
    [_baseFont release];
    [_normalizedFontName release];
    [_origEncoding release];
    
    [_cmap release];
    [_fontDescriptor release];
    [_widths release];
    if(_characterMappings != NULL) {
        CFRelease(_characterMappings);
    }
    if(_reverseCharacterMappings != NULL) {
        CFRelease(_reverseCharacterMappings);
    }
    [_ctFontDict release];
    
	[super dealloc];
}

- (NSString *)description {
	NSMutableString *string = [NSMutableString string];
	[string appendFormat:@"%@ {\n", self.baseFont];
	[string appendFormat:@"\ttype = %@\n", _type];
	[string appendFormat:@"\tcharacter widths = %lu\n", (unsigned long)[_widths count]];
	[string appendFormat:@"\ttoUnicode = %d\n", (self.cmap != nil)];
	[string appendFormat:@"}\n"];
	return string;
}


- (void)setEncodingWithFontDictionary:(CGPDFDictionaryRef)dict {
    // set default encoding in case the encoding object is missing
    _encoding = NSMacOSRomanStringEncoding;
    
    CGPDFObjectRef encodingObject;
	if(!CGPDFDictionaryGetObject(dict, kPDFEncodingKey, &encodingObject)) {
        return;
    }
    
    [self setEncodingWithEncodingObject:encodingObject];
}

- (void)setEncodingWithEncodingObject:(CGPDFObjectRef)object {
    CGPDFObjectType type = CGPDFObjectGetType(object);
	if(type == kCGPDFObjectTypeDictionary) {
		CGPDFDictionaryRef dict = nil;
		if(!CGPDFObjectGetValue(object, kCGPDFObjectTypeDictionary, &dict)) {
            return;
        }
        
		CGPDFObjectRef baseEncoding = nil;
		if(CGPDFDictionaryGetObject(dict, kPDFBaseEncodingKey, &baseEncoding)) {
            [self setEncodingWithEncodingObject:baseEncoding];
        }
        
        CGPDFArrayRef differences = nil;
		if(CGPDFDictionaryGetArray(dict, kPDFEncodingDifferencesKey, &differences)) {
            NSUInteger length = CGPDFArrayGetCount(differences);
            CGPDFObjectRef currentObject;
            CGPDFObjectType type;
            NSUInteger baseCid = 0;
            YLFontHelper *fontHelper = [YLFontHelper sharedInstance];
            if(_characterMappings == NULL) {
                _characterMappings = CFDictionaryCreateMutable(kCFAllocatorDefault, 0, NULL, NULL);
                _reverseCharacterMappings = CFDictionaryCreateMutable(kCFAllocatorDefault, 0, NULL, NULL);
            }
            
            BOOL useRegEx = NO;
            NSError *error = nil;
            NSRegularExpression *regex = [NSRegularExpression regularExpressionWithPattern:@"^\\w(\\d+)" options:0 error:&error];
            if(error == nil) {
                useRegEx = YES;
            }
            
            for(int i = 0; i < length; ++i) {
                if(!CGPDFArrayGetObject(differences, i, &currentObject)) {
                    continue;
                }
                
                type = CGPDFObjectGetType(currentObject);
                if(type == kCGPDFObjectTypeInteger) {
                    CGPDFObjectGetValue(currentObject, kCGPDFObjectTypeInteger, &baseCid);
                } else if(type == kCGPDFObjectTypeName) {
                    const char *name = nil;
                    CGPDFObjectGetValue(currentObject, kCGPDFObjectTypeName, &name);
                    NSString *glyphName = [NSString stringWithUTF8String:name];
                    
                    if(useRegEx) {
                        NSTextCheckingResult *match = [regex firstMatchInString:glyphName options:0 range:NSMakeRange(0, [glyphName length])];
                        if(match) {
                            NSRange groupOne = [match rangeAtIndex:1];
                            if(!NSEqualRanges(groupOne, NSMakeRange(NSNotFound, 0))) {
                                unichar value = [[glyphName substringWithRange:groupOne] intValue];
                                if(value != 0) {
                                    CFDictionarySetValue(_characterMappings, (void *)baseCid, (void *)&value);
                                    CFDictionarySetValue(_reverseCharacterMappings, (void *)&value, (void *)baseCid++);
                                    continue;
                                }
                            }
                        }
                    }
                    
                    NSNumber *value = [fontHelper unicodeForName:glyphName];
                    if(value) {
                        CFDictionarySetValue(_characterMappings, (void *)baseCid, (void *)[value integerValue]);
                        CFDictionarySetValue(_reverseCharacterMappings, (void *)[value integerValue], (void *)baseCid++);
                    } else {
                        baseCid++;
                    }
                }
            }
        }
	} else if(type == kCGPDFObjectTypeName) {
        const char *name;
        if(CGPDFObjectGetValue(object, kCGPDFObjectTypeName, &name)) {
            _origEncoding = [[NSString stringWithUTF8String:name] retain];
            if(strcmp(name, "MacRomanEncoding") == 0) {
                _encoding = NSMacOSRomanStringEncoding;
            } else if (strcmp(name, "MacExpertEncoding") == 0) {
                _encoding = NSMacOSRomanStringEncoding;
            } else if (strcmp(name, "WinAnsiEncoding") == 0) {
                _encoding = NSWindowsCP1252StringEncoding;
            }
        }
    }
}

- (void)setFontDescriptorWithFontDictionary:(CGPDFDictionaryRef)dict {
	CGPDFDictionaryRef descriptor;
	if(!CGPDFDictionaryGetDictionary(dict, kPDFFontDescriptorKey, &descriptor)) {
        return;
    }
    
	_fontDescriptor = [[YLFontDescriptor alloc] initWithPDFDictionary:descriptor];
}

- (void)setWidthsWithFontDictionary:(CGPDFDictionaryRef)dict {
    CGPDFArrayRef array;
	if(!CGPDFDictionaryGetArray(dict, kPDFWidthsKey, &array)) {
        return;
    }
    
	size_t count = CGPDFArrayGetCount(array);
	CGPDFInteger firstChar, lastChar;
	if(!CGPDFDictionaryGetInteger(dict, kPDFFirstCharKey, &firstChar)) {
        return;
    }
	if(!CGPDFDictionaryGetInteger(dict, kPDFLastCharKey, &lastChar)) {
        return;
    }
    
	_widthsRange = NSMakeRange(firstChar, lastChar-firstChar);
	for(int i = 0; i < count; i++) {
        CGPDFReal width;
		if(!CGPDFArrayGetNumber(array, i, &width)) {
            continue;
        }
      
        [_widths setObject:[NSNumber numberWithFloat:(width / 1000.0)] forKey:[NSNumber numberWithInteger:(firstChar + i)]];
	}
}

- (void)setToUnicodeWithFontDictionary:(CGPDFDictionaryRef)dict {
	CGPDFStreamRef stream;
	if(!CGPDFDictionaryGetStream(dict, kPDFToUnicodeKey, &stream)) {
        return;
    }
    
	_cmap = [[YLCMap alloc] initWithPDFStream:stream];
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
    } else if(_characterMappings) {
        const void *result = CFDictionaryGetValue(_characterMappings, (void *)&character);
        if(result) {
            value = (NSInteger)result;
            found = YES;
        }
    }

    return (found) ? value : [self encodedUnicodeValue:character];
}

- (unichar)encodedUnicodeValue:(unichar)character {
    const char str[] = {character, '\0'};
    NSString *temp = [NSString stringWithCString:str encoding:self.encoding];
    return (temp && [temp length] > 0) ? [temp characterAtIndex:0] : 0;
}

- (NSString *)stringWithPDFString:(CGPDFStringRef)pdfString ligatures:(BOOL *)ligatures {
    if(self.cmap || _characterMappings) {
        const unsigned char *characterCodes = CGPDFStringGetBytePtr(pdfString);
        NSUInteger characterCodeCount = CGPDFStringGetLength(pdfString);
        YLFontHelper *fontHelper = [YLFontHelper sharedInstance];

        NSMutableString *string = [NSMutableString string];
        for(int i = 0; i < characterCodeCount; i++) {
            unichar codeValue = characterCodes[i];
            unichar value = [self mappedUnicodeValue:codeValue];
            if(value == 0xA0) { // replace nbspace with space
                [string appendFormat:@" "];
            } else {
                [string appendFormat:@"%C", value];
            }
            
            NSString *ligature = [fontHelper ligatureForCharacter:value];
            if(ligature) {
                *ligatures = YES;
            }
        }
        
        return [NSString stringWithString:string];
    } else {
        const unsigned char *characterCodes = CGPDFStringGetBytePtr(pdfString);
        NSUInteger characterCodeCount = CGPDFStringGetLength(pdfString);
        YLFontHelper *fontHelper = [YLFontHelper sharedInstance];
        
        NSMutableString *string = [NSMutableString string];
        for(int i = 0; i < characterCodeCount; i++) {
            unichar codeValue = characterCodes[i];
            unichar encodedValue = [self encodedUnicodeValue:codeValue];
            if(encodedValue == 0xA0) {
                [string appendFormat:@" "];
            } else {
                [string appendFormat:@"%C", encodedValue];
            }
            
            NSString *glyphName = [self.fontDescriptor glyphNameForCode:codeValue];
            if(glyphName) {
                NSNumber *code = [fontHelper unicodeForName:glyphName];
                if(code && [fontHelper ligatureForCharacter:[code intValue]]) {
                    *ligatures = YES;
                }
            } else {
                NSString *ligature = [fontHelper ligatureForCharacter:encodedValue];
                if(ligature) {
                    *ligatures = YES;
                }
            }
        }
        
        return [NSString stringWithString:string];
    }
}

- (NSNumber *)cidCharacter:(unichar)character {
    NSNumber *value = nil;
    if(_reverseCharacterMappings) {
        const void *mapping = CFDictionaryGetValue(_reverseCharacterMappings, (void *)&character);
        if(mapping) {
            value = [NSNumber numberWithInteger:(NSInteger)mapping];
            return value;
        }
    }
    
    if(self.cmap) {
        value = [self.cmap cidCharacter:character];
    }
    
    return value;
}

- (CGFloat)minY {
    if(self.fontDescriptor) {
        return [self.fontDescriptor descent];
    } else if(_normalizedFontName) {
        if(_ctMinY != MAXFLOAT) {
            return _ctMinY;
        }
        
        _ctMinY = [[YLFontHelper sharedInstance] minYForFontName:_normalizedFontName];
        return _ctMinY;
    }
    
    return 0.0;
}

- (CGFloat)maxY {
    if(self.fontDescriptor) {
        return [self.fontDescriptor ascent];
    } else if(_normalizedFontName) {
        if(_ctMaxY != MAXFLOAT) {
            return _ctMaxY;
        }
        
        _ctMaxY = [[YLFontHelper sharedInstance] maxYForFontName:_normalizedFontName];
        return _ctMaxY;
    }
    
	return 0.0;
}

- (CGFloat)widthOfCharacter:(unichar)character withFontSize:(CGFloat)fontSize {
    NSNumber *widthNumber = [_widths objectForKey:[NSNumber numberWithInt:character]];
    if(widthNumber) {
        return (float)([widthNumber floatValue] * fontSize);
    } else if(_normalizedFontName) {
        YLFontHelper *fontHelper = [YLFontHelper sharedInstance];
        widthNumber = [fontHelper widthForFontName:_normalizedFontName character:character];
        if(widthNumber) {
            return (float)([widthNumber floatValue] * fontSize);
        }
        
        NSNumber *fontSizeNumber = [NSNumber numberWithFloat:fontSize];
        CTFontRef fontRef = (CTFontRef)[_ctFontDict objectForKey:fontSizeNumber];
        if(fontRef == NULL) {
            fontRef = CTFontCreateWithName((CFStringRef)_normalizedFontName, fontSize, NULL);
            if(fontRef) {
                [_ctFontDict setObject:(id)fontRef forKey:fontSizeNumber];
                CFRelease(fontRef);
                fontRef = (CTFontRef)[_ctFontDict objectForKey:fontSizeNumber];
            }
        }
        
        if(fontRef) {
            NSString *string = [NSString stringWithFormat:@"%C", character];
            CFMutableAttributedStringRef attrStr = CFAttributedStringCreateMutable(kCFAllocatorDefault, 0);
            CFAttributedStringReplaceString(attrStr, CFRangeMake(0, 0), (CFStringRef)string);
            CFAttributedStringSetAttribute(attrStr, CFRangeMake(0, CFAttributedStringGetLength(attrStr)), kCTFontAttributeName, fontRef);
            
            CTLineRef line = CTLineCreateWithAttributedString(attrStr);
            CGFloat ascent;
            CGFloat descent;
            CGFloat width = CTLineGetTypographicBounds(line, &ascent, &descent, NULL);
            [fontHelper setWidth:(float)(width / fontSize) forFontName:_normalizedFontName character:character];
            CFRelease(attrStr);
            CFRelease(line);
            
            return width;
        }
    }

    return 0.0;
}

- (CGFloat)widthOfSpaceWithFontSize:(CGFloat)fontSize {
    unichar cid = 0x20;
    if(self.cmap) {
        NSNumber *mapping = [self.cmap cidCharacter:0x20];
        if(mapping) {
            cid = [mapping intValue];
        }
    } else if(_reverseCharacterMappings) {
        const void *mapping = CFDictionaryGetValue(_reverseCharacterMappings, (void *)&cid);
        if(mapping) {
            cid = (NSInteger)mapping;
        }
    }
    
    if(_ctSpaceWidth != MAXFLOAT) {
        return _ctSpaceWidth;
    }
    
    CGFloat width = [self widthOfCharacter:cid withFontSize:1.0];
    if(_normalizedFontName) {
        _ctSpaceWidth = width * 1000.0;
    }
    
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
    } else if(_reverseCharacterMappings) {
        const void *mapping = CFDictionaryGetValue(_reverseCharacterMappings, (void *)&space);
        if(mapping) {
            space = (NSInteger)mapping;
        }
    }
    
    return (space == character);
}

@end
