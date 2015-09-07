//
//  YLFontCollection.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLFontCollection.h"
#import "YLFontHelper.h"

@interface YLFontCollection () {
    NSMutableDictionary *_fonts;
    NSArray *_names;
}

@end

@implementation YLFontCollection

- (id)initWithFontDictionary:(CGPDFDictionaryRef)dict {
    self = [super init];
    if(self) {
		_fonts = [[NSMutableDictionary alloc] init];
		// Enumerate the Font resource dictionary
		CGPDFDictionaryApplyFunction(dict, didScanFont, _fonts);
        
		NSMutableArray *namesArray = [NSMutableArray array];
		for(NSString *name in [_fonts allKeys]) {
			[namesArray addObject:name];
		}
		_names = [[namesArray sortedArrayUsingSelector:@selector(compare:)] retain];
	}
    
	return self;
}

- (id)initWithFonts:(NSDictionary *)fonts {
    if(fonts == nil) {
        return nil;
    }
    
    self = [super init];
    if(self) {
        _fonts = [[NSMutableDictionary alloc] initWithDictionary:fonts];
        
        NSMutableArray *namesArray = [NSMutableArray array];
		for(NSString *name in [_fonts allKeys]) {
			[namesArray addObject:name];
		}
		_names = [[namesArray sortedArrayUsingSelector:@selector(compare:)] retain];
    }
    
    return self;
}

- (void)dealloc {
    [_fonts release];
    [_names release];
    
    [super dealloc];
}

void didScanFont(const char *key, CGPDFObjectRef object, void *collection) {
	if(!CGPDFObjectGetType(object) == kCGPDFObjectTypeDictionary) {
        return;
    }
    
	CGPDFDictionaryRef dict;
	if(!CGPDFObjectGetValue(object, kCGPDFObjectTypeDictionary, &dict)) {
        return;
    }
    
    YLFont *font = [YLFont fontWithDictionary:dict];
	if(!font) {
        return;
    }
    
	NSString *name = [NSString stringWithUTF8String:key];
	[(NSMutableDictionary *)collection setObject:font forKey:name];
    
    // Needs some more testing
    /*const char *fontName = nil;
    NSString *baseFont = nil;
    if(CGPDFDictionaryGetName(dict, kPDFBaseFontKey, &fontName)) {
        baseFont = [NSString stringWithUTF8String:fontName];
    }
    
    NSString *name = [NSString stringWithUTF8String:key];
    YLFont *font = [[YLFontHelper sharedInstance] fontWithKey:name];
	if(font == nil) {
        font = [YLFont fontWithDictionary:dict];
        if(font == nil) {
            return;
        } else {
            [[YLFontHelper sharedInstance] setFont:font key:name];
        }
    } else {
        // the font is using the same key but their baseFont is different
        if(![font.baseFont isEqualToString:baseFont]) {
            font = [YLFont fontWithDictionary:dict];
            if(font == nil) {
                return;
            }
        }
    }
    
	[(NSMutableDictionary *)collection setObject:font forKey:name];*/
}

- (NSDictionary *)fonts {
    return [NSDictionary dictionaryWithDictionary:_fonts];
}

- (YLFont *)fontNamed:(NSString *)fontName {
	return [_fonts objectForKey:fontName];
}

@end
