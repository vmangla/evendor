//
//  YLFontDescriptor.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLFontDescriptor.h"
#import "YLFont.h"

@interface YLFontDescriptor () {
    CGFloat _descent;
	CGFloat _ascent;
	CGFloat _leading;
	CGFloat _averageWidth;
	CGFloat _maxWidth;
	CGFloat _missingWidth;
	CGFloat _italicAngle;
	CGRect _bounds;
	NSUInteger _flags;
	NSString *_fontName;
    
    NSMutableDictionary *_mappingDict;
}

- (void)parseFontFile:(CGPDFStreamRef)stream;

@end

@implementation YLFontDescriptor

@synthesize descent = _descent;
@synthesize ascent = _ascent;
@synthesize leading = _leading;
@synthesize capHeight = _capHeight;
@synthesize xHeight = _xHeight;
@synthesize averageWidth = _averageWidth;
@synthesize maxWidth = _maxWidth;
@synthesize missingWidth = _missingWidth;
@synthesize verticalStemWidth = _verticalStemWidth;
@synthesize horizontalStemWidth = _horizontalStemHeigth;
@synthesize italicAngle = _italicAngle;
@synthesize bounds = _bounds;
@synthesize flags = _flags;
@synthesize fontName = _fontName;

- (id)initWithPDFDictionary:(CGPDFDictionaryRef)dict {
	const char *type = nil;
	CGPDFDictionaryGetName(dict, kPDFFontTypeKey, &type);
	if(!type || strcmp(type, kPDFFontDescriptorKey) != 0) {
		return nil;
	}

    self = [super init];
    if(self) {
		CGPDFInteger ascentValue = 0L;
		CGPDFInteger descentValue = 0L;
		CGPDFInteger leadingValue = 0L;
		CGPDFInteger averageWidthValue = 0L;
		CGPDFInteger maxWidthValue = 0L;
		CGPDFInteger missingWidthValue = 0L;
		CGPDFInteger flagsValue = 0L;
		CGPDFInteger italicAngleValue = 0L;
		const char *fontNameString = nil;
		CGPDFArrayRef bboxValue = nil;

		CGPDFDictionaryGetInteger(dict, "Ascent", &ascentValue);
        CGPDFDictionaryGetInteger(dict, "Descent", &descentValue);
        CGPDFDictionaryGetInteger(dict, "Leading", &leadingValue);
		CGPDFDictionaryGetInteger(dict, "AvgWidth", &averageWidthValue);
		CGPDFDictionaryGetInteger(dict, "MaxWidth", &maxWidthValue);
		CGPDFDictionaryGetInteger(dict, "MissingWidth", &missingWidthValue);
		CGPDFDictionaryGetInteger(dict, "Flags", &flagsValue);
        CGPDFDictionaryGetName(dict, "FontName", &fontNameString);
		CGPDFDictionaryGetArray(dict, "FontBBox", &bboxValue);
        
        _ascent = ascentValue;
        _descent = descentValue;
        _leading = leadingValue;
        _averageWidth = averageWidthValue;
		_maxWidth = maxWidthValue;
        _missingWidth = missingWidthValue;
        _flags = flagsValue;
        _italicAngle = italicAngleValue;
        _fontName = [[NSString stringWithUTF8String:fontNameString] retain];

		if(CGPDFArrayGetCount(bboxValue) == 4) {
			CGPDFInteger x = 0, y = 0, width = 0, height = 0;
			CGPDFArrayGetInteger(bboxValue, 0, &x);
			CGPDFArrayGetInteger(bboxValue, 1, &y);
			CGPDFArrayGetInteger(bboxValue, 2, &width);
			CGPDFArrayGetInteger(bboxValue, 3, &height);
			_bounds = CGRectMake(x, y, width, height);
		}
        
        CGPDFStreamRef fontFileStream;
		if(CGPDFDictionaryGetStream(dict, kPDFFontFileKey, &fontFileStream)) {
            [self parseFontFile:fontFileStream];
		} else if(CGPDFDictionaryGetStream(dict, kPDFFontFile2Key, &fontFileStream)) {
            [self parseFontFile:fontFileStream];
        } else if(CGPDFDictionaryGetStream(dict, kPDFFontFile3Key, &fontFileStream)) {
            [self parseFontFile:fontFileStream];
        }
	}
    
	return self;
}

- (void)dealloc {
    [_fontName release];
    [_mappingDict release];
    
    [super dealloc];
}

- (BOOL)isSymbolic {
	return ((self.flags & FontSymbolic) > 0) && ((self.flags & FontNonSymbolic) == 0);
}

- (NSString *)glyphNameForCode:(unichar)code {
    if(_mappingDict == nil) {
        return nil;
    }
    
    return [_mappingDict objectForKey:[NSNumber numberWithInt:code]];
}


#pragma mark -
#pragma mark Private Methods
- (void)parseFontFile:(CGPDFStreamRef)stream {
    if(_mappingDict == nil) {
        _mappingDict = [[NSMutableDictionary alloc] init];
    }
    
    CGPDFDataFormat format;
    CFDataRef data = CGPDFStreamCopyData(stream, &format);
    NSString *metadata = [[[NSString alloc] initWithData:(NSData *)data encoding:NSASCIIStringEncoding] autorelease];
    CFRelease(data);
    
    if(metadata == nil || [metadata length] == 0) {
        return;
    }
    
    NSScanner *scanner = [NSScanner scannerWithString:metadata];
    NSCharacterSet *delimiterSet = [NSCharacterSet whitespaceAndNewlineCharacterSet];
    NSString *buffer, *name;
    int code;
    while(![scanner isAtEnd]) {
        [scanner scanUpToCharactersFromSet:delimiterSet intoString:&buffer];
        if([buffer isEqualToString:@"dup"]) {
            if([scanner scanInt:&code]) {
                [scanner scanUpToCharactersFromSet:delimiterSet intoString:&name];
                if(name) {
                    name = [name stringByReplacingOccurrencesOfString:@"/" withString:@""];
                    [_mappingDict setObject:name forKey:[NSNumber numberWithInt:code]];
                }
            }
        }
    }
}

@end
