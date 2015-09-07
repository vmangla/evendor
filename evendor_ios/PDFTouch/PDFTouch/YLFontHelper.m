//
//  YLFontHelper.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLFontHelper.h"
#import "NSBundle+PDFTouch.h"
#import "YLCMap.h"
#import "YLFont.h"

@interface YLFontHelper () {
    NSMutableDictionary *_adobeGlyphDict;
    NSMutableDictionary *_ligaturesDict;
    NSMutableDictionary *_cmapDict;
    NSMutableDictionary *_cmapNameMapping;
    NSMutableDictionary *_minYDict;
    NSMutableDictionary *_maxYDict;
    NSMutableDictionary *_widthDict;
    NSMutableDictionary *_fontDict;
}

@property (nonatomic, readonly) NSMutableDictionary *adobeGlyphDict;

- (void)onMemoryWarning;

@end

@implementation YLFontHelper

@synthesize adobeGlyphDict = _adobeGlyphDict;

+ (YLFontHelper *)sharedInstance {
    static YLFontHelper *sharedInstance = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedInstance = [[YLFontHelper alloc] init];
    });
    
    return sharedInstance;
}

- (id)init {
    self = [super init];
    if(self) {
        _ligaturesDict = [[NSMutableDictionary alloc] init];
        [_ligaturesDict setObject:@"ff" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb00]];
        [_ligaturesDict setObject:@"fi" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb01]];
        [_ligaturesDict setObject:@"fl" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb02]];
        [_ligaturesDict setObject:@"ffi" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb03]];
        [_ligaturesDict setObject:@"ffl" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb04]];
        [_ligaturesDict setObject:@"ft" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb05]];
        [_ligaturesDict setObject:@"st" forKey:[NSString stringWithFormat:@"%C", (unichar)0xfb06]];
        [_ligaturesDict setObject:@"ij" forKey:[NSString stringWithFormat:@"%C", (unichar)0x0133]];
        [_ligaturesDict setObject:@"oe" forKey:[NSString stringWithFormat:@"%C", (unichar)0x0153]];
        [_ligaturesDict setObject:@"ae" forKey:[NSString stringWithFormat:@"%C", (unichar)0x00E6]];
        
        _cmapNameMapping = [[NSMutableDictionary alloc] init];
        [_cmapNameMapping setObject:@"Adobe-CNS1-UCS2" forKey:@"Adobe-CNS1"];
        [_cmapNameMapping setObject:@"Adobe-GB1-UCS2" forKey:@"Adobe-GB1"];
        [_cmapNameMapping setObject:@"Adobe-Japan1-UCS2" forKey:@"Adobe-Japan1"];
        [_cmapNameMapping setObject:@"Adobe-Korea1-UCS2" forKey:@"Adobe-Korea1"];
        
        [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(onMemoryWarning) name:UIApplicationDidReceiveMemoryWarningNotification object:nil];
    }
    
    return self;
}

- (void)dealloc {
    [[NSNotificationCenter defaultCenter] removeObserver:self];
    [_adobeGlyphDict release];
    [_ligaturesDict release];
    [_cmapDict release];
    [_cmapNameMapping release];
    [_minYDict release];
    [_maxYDict release];
    [_widthDict release];
    [_fontDict release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (NSMutableDictionary *)adobeGlyphDict {
    if(_adobeGlyphDict == nil) {
        _adobeGlyphDict = [[NSMutableDictionary alloc] init];
        
        NSString *filePath = [[NSBundle myLibraryResourcesBundle] pathForResource:@"glyphlist" ofType:@"txt"];
        if(![[NSFileManager defaultManager] fileExistsAtPath:filePath]) {
            return _adobeGlyphDict;
        }
        
        NSString *list = [NSString stringWithContentsOfFile:filePath encoding:NSUTF8StringEncoding error:nil];
        if(list == nil) {
            return _adobeGlyphDict;
        }
        
        NSScanner *scanner = [NSScanner scannerWithString:list];
        NSString *nextToken = nil;
        NSCharacterSet *newlineSet = [NSCharacterSet newlineCharacterSet];
        while([scanner scanUpToCharactersFromSet:newlineSet intoString:&nextToken]) {
            if(![nextToken hasPrefix:@"#"]) {
                NSArray *components = [nextToken componentsSeparatedByString:@";"];
                if([components count] == 2) {
                    NSString *name = [components objectAtIndex:0];
                    unsigned int code;
                    [[NSScanner scannerWithString:[components objectAtIndex:1]] scanHexInt:&code];
                    [_adobeGlyphDict setObject:[NSNumber numberWithInt:code] forKey:name];
                }
            }
        }
    }
    
    return _adobeGlyphDict;
}

- (YLCMap *)cmapWithRegistry:(NSString *)registry ordering:(NSString *)ordering {
    if(registry == nil || ordering == nil) {
        return nil;
    }
    
    NSString *name = [NSString stringWithFormat:@"%@-%@", registry, ordering];
    if(_cmapDict && [_cmapDict objectForKey:name]) {
        return [_cmapDict objectForKey:name];
    }
    
    NSString *filename = [_cmapNameMapping objectForKey:name];
    if(filename == nil) {
        return nil;
    }

    NSString *filePath = [[NSBundle myLibraryResourcesBundle] pathForResource:filename ofType:nil];
    if(![[NSFileManager defaultManager] fileExistsAtPath:filePath]) {
        return nil;
    }
    
    NSString *cmapString = [NSString stringWithContentsOfFile:filePath encoding:NSUTF8StringEncoding error:nil];
    if(cmapString == nil) {
        return nil;
    }
    
    YLCMap *cmap = [[YLCMap alloc] initWithString:cmapString];
    if(cmap == nil) {
        return nil;
    }
    
    if(_cmapDict == nil) {
        _cmapDict = [[NSMutableDictionary alloc] init];
    }
    [_cmapDict setObject:cmap forKey:name];
    [cmap release];
    
    return [_cmapDict objectForKey:name];
}

- (YLCMap *)cmapWithName:(NSString *)name {
    if(name == nil) {
        return nil;
    }
    
    if(_cmapDict && [_cmapDict objectForKey:name]) {
        return [_cmapDict objectForKey:name];
    }
    
    NSString *filePath = [[NSBundle myLibraryResourcesBundle] pathForResource:name ofType:nil];
    if(![[NSFileManager defaultManager] fileExistsAtPath:filePath]) {
        return nil;
    }
    
    NSString *cmapString = [NSString stringWithContentsOfFile:filePath encoding:NSUTF8StringEncoding error:nil];
    if(cmapString == nil) {
        return nil;
    }
    
    YLCMap *cmap = [[YLCMap alloc] initWithString:cmapString];
    if(cmap == nil) {
        return nil;
    }
    
    if(_cmapDict == nil) {
        _cmapDict = [[NSMutableDictionary alloc] init];
    }
    [_cmapDict setObject:cmap forKey:name];
    [cmap release];
    
    return [_cmapDict objectForKey:name];
}

- (NSNumber *)unicodeForName:(NSString *)name {
    NSNumber *n = [self.adobeGlyphDict objectForKey:name];
    return n;
}

- (NSString *)ligatureForCharacter:(unichar)character {
    // comparing the character boundaries results in a small performance improvement
    if((character >= 0xfb00 && character <= 0xfb06) || character == 0x0133 || character == 0x0153 || character == 0x00E6) {
        NSString *key = [NSString stringWithFormat:@"%C", character];
        return [_ligaturesDict objectForKey:key];
    }
    
    return nil;
}

- (NSString *)coreTextFontNameForFont:(NSString *)name standardFont:(BOOL *)standardFont {
    NSRange plusSign = [name rangeOfString:@"+"];
    if(plusSign.location != NSNotFound) {
        name = [name substringFromIndex:(plusSign.location+1)];
    }
    
    if([name hasPrefix:@"Times"] ||
       [name hasPrefix:@"Helvetica"] ||
       [name hasPrefix:@"Courier"]) {
        *standardFont = YES;
        return name;
    }
    
    *standardFont = NO;
    return @"";
}

- (CGFloat)minYForFontName:(NSString *)name {
    if(_minYDict == nil) {
        _minYDict = [[NSMutableDictionary alloc] init];
    }
    
    NSNumber *minYValue = [_minYDict objectForKey:name];
    if(minYValue) {
        return [minYValue floatValue];
    }
    
    CGFontRef fontRef = CGFontCreateWithFontName((CFStringRef)name);
    if(fontRef) {
        CGFloat minY = CGFontGetDescent(fontRef);
        [_minYDict setObject:[NSNumber numberWithFloat:minY] forKey:name];
        CGFontRelease(fontRef);
        
        return minY;
    }
    
    return 0.0;
}

- (CGFloat)maxYForFontName:(NSString *)name {
    if(_maxYDict == nil) {
        _maxYDict = [[NSMutableDictionary alloc] init];
    }
    
    NSNumber *maxYValue = [_maxYDict objectForKey:name];
    if(maxYValue) {
        return [maxYValue floatValue];
    }
    
    CGFontRef fontRef = CGFontCreateWithFontName((CFStringRef)name);
    if(fontRef) {
        CGFloat maxY = CGFontGetXHeight(fontRef) - CGFontGetLeading(fontRef);
        [_maxYDict setObject:[NSNumber numberWithFloat:maxY] forKey:name];
        CGFontRelease(fontRef);
        
        return maxY;
    }
    
    return 0.0;
}

- (NSNumber *)widthForFontName:(NSString *)name character:(unichar)character {
    if(_widthDict == nil) {
        return nil;
    }
    
    NSMutableDictionary *widths = [_widthDict objectForKey:name];
    if(widths == nil) {
        return nil;
    }
    
    return [widths objectForKey:[NSNumber numberWithInt:character]];
}

- (void)setWidth:(CGFloat)width forFontName:(NSString *)name character:(unichar)character {
    if(_widthDict == nil) {
        _widthDict = [[NSMutableDictionary alloc] init];
    }
    
    NSMutableDictionary *widths = [_widthDict objectForKey:name];
    if(widths == nil) {
        widths = [[[NSMutableDictionary alloc] init] autorelease];
        [_widthDict setObject:widths forKey:name];
    }
    
    [widths setObject:[NSNumber numberWithFloat:width] forKey:[NSNumber numberWithInt:character]];
}

- (YLFont *)fontWithKey:(NSString *)key {
    if(_fontDict == nil) {
        return nil;
    }
    
    return [_fontDict objectForKey:key];
}

- (void)setFont:(YLFont *)font key:(NSString *)key {
    if(_fontDict == nil) {
        _fontDict = [[NSMutableDictionary alloc] init];
    }
    
    [_fontDict setObject:font forKey:key];
}

- (NSDictionary *)fontCollection {
    if(_fontDict == nil) {
        return nil;
    }
    
    return [NSDictionary dictionaryWithDictionary:_fontDict];
}

- (void)clearCache {
    if(_minYDict) {
        [_minYDict removeAllObjects];
    }
    if(_maxYDict) {
        [_maxYDict removeAllObjects];
    }
    if(_widthDict) {
        [_widthDict removeAllObjects];
    }
    if(_fontDict) {
        [_fontDict removeAllObjects];
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)onMemoryWarning {
    [self clearCache];
}

@end
