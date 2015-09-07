//
//  YLStringDetector.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLStringDetector.h"
#import "YLFontHelper.h"

@interface YLStringDetector () {
    NSString *_keyword;
    NSMutableString *_unicodeContent;
    NSUInteger _keywordPosition;
    
    id<YLStringDetectorDelegate> _delegate;
}

@property (nonatomic, retain) NSString *keyword;

- (NSString *)appendString:(NSString *)inputString hasLigatures:(BOOL)ligatures font:(YLFont *)font;
- (NSString *)appendStringWithoutMatching:(NSString *)inputString hasLigatures:(BOOL)ligatures font:(YLFont *)font;

@end

@implementation YLStringDetector

@synthesize keyword = _keyword;
@synthesize unicodeContent = _unicodeContent;
@synthesize delegate = _delegate;

+ (YLStringDetector *)detectorWithKeyword:(NSString *)keyword delegate:(id<YLStringDetectorDelegate>)delegate {
	YLStringDetector *detector = [[YLStringDetector alloc] initWithKeyword:keyword];
	detector.delegate = delegate;
	return [detector autorelease];
}

- (id)initWithKeyword:(NSString *)string {
    self = [super init];
    if(self) {
        self.keyword = [string lowercaseString];
        self.unicodeContent = [NSMutableString string];
	}

	return self;
}

- (void)dealloc {
    _delegate = nil;
    [_keyword release];
    [_unicodeContent release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)reset {
    [_unicodeContent appendString:@" "];
    if(_keywordPosition != 0) { // if we're in the middle of matching
        unichar nextChar = [_keyword characterAtIndex:_keywordPosition];
        // only reset the keyword position if the next character candidate is not a space
        if(![[NSString stringWithFormat:@"%C", nextChar] isEqualToString:@" "]) {
            _keywordPosition = 0;
        } else {
            _keywordPosition++;
        }
    }
}

- (NSString *)appendPDFString:(CGPDFStringRef)string withFont:(YLFont *)font {
    BOOL ligatures = NO;
    NSString *decodedString = [font stringWithPDFString:string ligatures:&ligatures];
    return [self appendString:decodedString hasLigatures:ligatures font:font];
}

- (NSString *)appendString:(NSString *)inputString hasLigatures:(BOOL)ligatures font:(YLFont *)font {
    if(self.keyword == nil) {
        return [self appendStringWithoutMatching:inputString hasLigatures:ligatures font:font];
    }
    
    if(self.delegate == nil) {
       // DLog(@"Delegate should not be nil!");
        return @"";
    }
    
	NSString *lowercaseString = [inputString lowercaseString];
    NSMutableString *expandedString = [NSMutableString string];
    int position = 0;
    if(lowercaseString) {
        [_unicodeContent appendString:lowercaseString];
    }
    
    if(ligatures) {
        YLFontHelper *fontHelper = [YLFontHelper sharedInstance];
        while(position < inputString.length) {
            unichar inputCharacter = [inputString characterAtIndex:position];
            unichar actualCharacter = [lowercaseString characterAtIndex:position++];
            unichar expectedCharacter = [self.keyword characterAtIndex:_keywordPosition];

            NSInteger keywordOffset;
            NSString *ligature = [fontHelper ligatureForCharacter:actualCharacter];
            if(ligature == nil) { // look in the FontFile mapping in FontDescriptor
                NSString *glyphName = [font.fontDescriptor glyphNameForCode:actualCharacter];
                if(glyphName) {
                    NSNumber *code = [fontHelper unicodeForName:glyphName];
                    if(code) {
                        ligature = [fontHelper ligatureForCharacter:[code intValue]];
                    }
                }
            }
            
            if(ligature) {
                [expandedString appendString:ligature];
                int i = 0;
                for( ; i < ligature.length; i++) {
                    if([ligature characterAtIndex:i] != [self.keyword characterAtIndex:(_keywordPosition + i)]) {
                        break;
                    }
                }
                
                if(i == ligature.length) {
                    keywordOffset = ligature.length;
                } else {
                    _keywordPosition = 0;
                    [self.delegate detector:self didScanCharacter:inputCharacter];
                    
                    continue;
                }
            } else {
                [expandedString appendFormat:@"%C", inputCharacter];
                keywordOffset = 1;
                
                if(actualCharacter != expectedCharacter) {
                    _keywordPosition = 0;
                    [self.delegate detector:self didScanCharacter:inputCharacter];
                    
                    continue;
                }
            }
            
            if(_keywordPosition == 0) {
                [self.delegate detectorDidStartMatching:self];
            }
            
            [self.delegate detector:self didScanCharacter:inputCharacter];
            
            _keywordPosition += keywordOffset;
            if(_keywordPosition < self.keyword.length) {
                // Keep matching keyword
                continue;
            }
            
            // Reset keyword position
            _keywordPosition = 0;
            [self.delegate detectorFoundString:self];
        }
        
        return [NSString stringWithString:expandedString];
    } else {
        while(position < inputString.length) {
            unichar inputCharacter = [inputString characterAtIndex:position];
            unichar actualCharacter = [lowercaseString characterAtIndex:position++];
            unichar expectedCharacter = [self.keyword characterAtIndex:_keywordPosition];
            
            if(actualCharacter != expectedCharacter) {
                // Reset keyword position
                _keywordPosition = 0;
                [self.delegate detector:self didScanCharacter:inputCharacter];
                
                continue;
            }
            
            if(_keywordPosition == 0) {
                [self.delegate detectorDidStartMatching:self];
            }
            
            [self.delegate detector:self didScanCharacter:inputCharacter];
            
            if(++_keywordPosition < self.keyword.length) {
                // Keep matching keyword
                continue;
            }
            
            // Reset keyword position
            _keywordPosition = 0;
            [self.delegate detectorFoundString:self];
        }
        
        return inputString;
    }
}

- (NSString *)appendStringWithoutMatching:(NSString *)inputString hasLigatures:(BOOL)ligatures font:(YLFont *)font {
    NSString *lowercaseString = [inputString lowercaseString];
    NSMutableString *expandedString = [NSMutableString string];
    int position = 0;
    if(lowercaseString) {
        [_unicodeContent appendString:lowercaseString];
    }
    
    if(ligatures) {
        YLFontHelper *fontHelper = [YLFontHelper sharedInstance];
        while(position < inputString.length) {
            unichar inputCharacter = [inputString characterAtIndex:position];
            unichar actualCharacter = [lowercaseString characterAtIndex:position++];
            
            NSString *ligature = [fontHelper ligatureForCharacter:actualCharacter];
            if(ligature == nil) { // look in the FontFile mapping in FontDescriptor
                NSString *glyphName = [font.fontDescriptor glyphNameForCode:actualCharacter];
                if(glyphName) {
                    NSNumber *code = [fontHelper unicodeForName:glyphName];
                    if(code) {
                        ligature = [fontHelper ligatureForCharacter:[code intValue]];
                    }
                }
            }
            
            if(ligature) {
                [expandedString appendString:ligature];
            } else {
                [expandedString appendFormat:@"%C", inputCharacter];
            }
            
            [self.delegate detector:self didScanCharacter:inputCharacter];
        }
        
        return [NSString stringWithString:expandedString];
    } else {
        while(position < inputString.length) {
            unichar inputCharacter = [inputString characterAtIndex:position++];
            [self.delegate detector:self didScanCharacter:inputCharacter];
        }
        
        return inputString;
    }
}


@end
