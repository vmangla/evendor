//
//  YLCMap.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLCMap.h"
#import "YLFontHelper.h"

@interface YLCMap () {
	NSMutableArray *_codeSpaceRanges;
	NSMutableDictionary *_characterMappings;
    NSMutableDictionary *_reverseCharacterMappings;
}

- (BOOL)isInCodeSpaceRange:(unichar)cid;
- (void)parse:(NSString *)cmapString;

@end

@implementation YLCMap

@synthesize codeSpaceRanges = _codeSpaceRanges;
@synthesize characterMappings = _characterMappings;
@synthesize reverseCharacterMappings = _reverseCharacterMappings;

- (id)initWithString:(NSString *)string {
    self = [super init];
    if(self) {
		[self parse:string];
	}
    
	return self;
}

- (id)initWithPDFStream:(CGPDFStreamRef)stream {
	NSData *data = (NSData *)CGPDFStreamCopyData(stream, nil);
	NSString *text = [[NSString alloc] initWithData:data encoding:NSUTF8StringEncoding];
    id obj = [self initWithString:text];
    [text release];
    [data release];
    
    return obj;
}

- (void)dealloc {
    [_codeSpaceRanges release];
    [_characterMappings release];
    [_reverseCharacterMappings release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (NSNumber *)unicodeCharacter:(unichar)cid {
	if(![self isInCodeSpaceRange:cid]) {
        return nil;
    }
	
    NSNumber *unicode = [self.characterMappings objectForKey:[NSNumber numberWithInt:cid]];
    return unicode;
}

- (NSNumber *)cidCharacter:(unichar)unicode {
    NSNumber *cid = [self.reverseCharacterMappings objectForKey:[NSNumber numberWithInt:unicode]];
    return cid;
}


#pragma mark -
#pragma mark Private Methods
- (NSMutableArray *)codeSpaceRanges {
    if(_codeSpaceRanges == nil) {
        _codeSpaceRanges = [[NSMutableArray alloc] init];
    }
    
    return _codeSpaceRanges;
}

- (NSMutableDictionary *)characterMappings {
    if(_characterMappings == nil) {
        _characterMappings = [[NSMutableDictionary alloc] init];
    }
    
    return _characterMappings;
}

- (NSMutableDictionary *)reverseCharacterMappings {
    if(_reverseCharacterMappings == nil) {
        _reverseCharacterMappings = [[NSMutableDictionary alloc] init];
    }
    
    return _reverseCharacterMappings;
}

- (NSCharacterSet *)tagSet {
    static NSCharacterSet *sharedTagSet = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedTagSet = [[NSCharacterSet characterSetWithCharactersInString:@"<>"] retain];
    });

	return sharedTagSet;
}

- (NSCharacterSet *)tokenDelimiterSet {
    static NSCharacterSet *sharedTokenDelimiterSet = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedTokenDelimiterSet = [[NSCharacterSet whitespaceAndNewlineCharacterSet] retain];
    });
    
    return sharedTokenDelimiterSet;
}

- (BOOL)isInCodeSpaceRange:(unichar)cid {
	for(NSValue *rangeValue in self.codeSpaceRanges) {
		NSRange range = [rangeValue rangeValue];
		if(cid >= range.location && cid <= NSMaxRange(range)) {
			return YES;
		}
	}
    
	return NO;
}

- (NSString *)nextToken:(NSScanner *)scanner {
	NSString *token = nil;
	[scanner scanUpToCharactersFromSet:self.tokenDelimiterSet intoString:&token];
	
	return token;
}

- (void)parse:(NSString *)cmapString {
	NSScanner *scanner = [NSScanner scannerWithString:cmapString];
	NSString *token = nil;
    NSString *previousToken = nil;
	while(![scanner isAtEnd]) {
		token = [self nextToken:scanner];
        if([token isEqualToString:@"usecmap"]) {
            YLCMap *subCmap = [[YLFontHelper sharedInstance] cmapWithName:previousToken];
            if(subCmap) {
                [self.codeSpaceRanges addObjectsFromArray:subCmap.codeSpaceRanges];
                [self.characterMappings addEntriesFromDictionary:subCmap.characterMappings];
                [self.reverseCharacterMappings addEntriesFromDictionary:subCmap.reverseCharacterMappings];
            }
        } else if([token isEqualToString:@"begincodespacerange"]) {
            int count = [previousToken intValue];
            NSString *nextToken;
            NSArray *components;
            unsigned int startValue;
            unsigned int endValue;
            for(int i = 0; i < count; ++i) {
                nextToken = [self nextToken:scanner];
                components = [nextToken componentsSeparatedByString:@"<"];
                if([components count] > 2) {
                    [[NSScanner scannerWithString:[[components objectAtIndex:1] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&startValue];
                    [[NSScanner scannerWithString:[[components objectAtIndex:2] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&endValue];
                } else {
                    [[NSScanner scannerWithString:[nextToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&startValue];
                    [[NSScanner scannerWithString:[[self nextToken:scanner] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&endValue];
                }
                
                NSValue *rangeValue = [NSValue valueWithRange:NSMakeRange(startValue, (endValue - startValue))];
                [self.codeSpaceRanges addObject:rangeValue];
            }
        } else if([token isEqualToString:@"beginbfchar"]) {
            int count = [previousToken intValue];
            NSString *nextToken;
            NSString *secondToken;
            NSArray *components;
            unsigned int srcCode;
            for(int i = 0; i < count; ++i) {
                nextToken = [self nextToken:scanner];
                components = [nextToken componentsSeparatedByString:@"<"];
                if([components count] > 2) {
                    [[NSScanner scannerWithString:[[components objectAtIndex:1] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCode];
                    secondToken = [components objectAtIndex:2];
                    NSRange closeBracketRange = [secondToken rangeOfString:@">"];
                    if(closeBracketRange.location != NSNotFound) {
                        unsigned int dstCode;
                        [[NSScanner scannerWithString:[secondToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&dstCode];
                        NSNumber *src = [NSNumber numberWithInt:srcCode];
                        NSNumber *dst = [NSNumber numberWithInt:dstCode];
                        if([src intValue] == [dst intValue]) {
                            continue;
                        }
                        [self.characterMappings setObject:dst forKey:src];
                        [self.reverseCharacterMappings setObject:src forKey:dst];
                    } else {
                        while(closeBracketRange.location == NSNotFound) {
                            unsigned int dstCode;
                            [[NSScanner scannerWithString:[secondToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&dstCode];
                            NSNumber *src = [NSNumber numberWithInt:srcCode];
                            NSNumber *dst = [NSNumber numberWithInt:dstCode];
                            if([src intValue] == [dst intValue]) {
                                continue;
                            }
                            [self.characterMappings setObject:dst forKey:src];
                            [self.reverseCharacterMappings setObject:src forKey:dst];
                            
                            secondToken = [self nextToken:scanner];
                            closeBracketRange = [secondToken rangeOfString:@">"];
                        }
                        
                        unsigned int dstCode;
                        [[NSScanner scannerWithString:[secondToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&dstCode];
                        NSNumber *src = [NSNumber numberWithInt:srcCode];
                        NSNumber *dst = [NSNumber numberWithInt:dstCode];
                        if([src intValue] == [dst intValue]) {
                            continue;
                        }
                        [self.characterMappings setObject:dst forKey:src];
                        [self.reverseCharacterMappings setObject:src forKey:dst];
                    }
                } else {
                    [[NSScanner scannerWithString:[nextToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCode];
                    secondToken = [self nextToken:scanner];
                    
                    if([secondToken hasPrefix:@"<"]) { // hex code
                        unsigned int dstCode;
                        [[NSScanner scannerWithString:[secondToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&dstCode];
                        NSNumber *src = [NSNumber numberWithInt:srcCode];
                        NSNumber *dst = [NSNumber numberWithInt:dstCode];
                        if([src intValue] == [dst intValue]) {
                            continue;
                        }
                        [self.characterMappings setObject:dst forKey:src];
                        [self.reverseCharacterMappings setObject:src forKey:dst];
                    } else { // character name
                        secondToken = [secondToken substringFromIndex:1];
                        [self.characterMappings setObject:secondToken forKey:[NSNumber numberWithInt:srcCode]];
                        [self.reverseCharacterMappings setObject:[NSNumber numberWithInt:srcCode] forKey:secondToken];
                    }
                }
            }
        } else if([token isEqualToString:@"beginbfrange"]) {
            int count = [previousToken intValue];
            NSString *nextToken;
            NSArray *components;
            unsigned int srcCodeLow;
            unsigned int srcCodeHigh;
            unsigned int dstCode;
            for(int i = 0; i < count; ++i) {
                nextToken = [self nextToken:scanner];
                components = [nextToken componentsSeparatedByString:@"<"];
                if([components count] > 3) {
                    [[NSScanner scannerWithString:[[components objectAtIndex:1] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCodeLow];
                    [[NSScanner scannerWithString:[[components objectAtIndex:2] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCodeHigh];
                    [[NSScanner scannerWithString:[[components objectAtIndex:3] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&dstCode];
                } else {
                    [[NSScanner scannerWithString:[nextToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCodeLow];
                    [[NSScanner scannerWithString:[[self nextToken:scanner] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCodeHigh];
                    [[NSScanner scannerWithString:[[self nextToken:scanner] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&dstCode];
                }
                
                int range = srcCodeHigh - srcCodeLow;
                for(int r = 0; r <= range; ++r) {
                    NSNumber *from = [NSNumber numberWithInt:(srcCodeLow + r)];
                    NSNumber *to = [NSNumber numberWithInt:(dstCode + r)];
                    if([from intValue] == [to intValue]) {
                        continue;
                    }
                    
                    [self.characterMappings setObject:to forKey:from];
                    [self.reverseCharacterMappings setObject:from forKey:to];
                }
            }
        } else if([token isEqualToString:@"begincidrange"]) {
            int count = [previousToken intValue];
            NSString *nextToken;
            unsigned int srcCodeLow;
            unsigned int srcCodeHigh;
            int dstCode;
            for(int i = 0; i < count; ++i) {
                nextToken = [self nextToken:scanner];
                [[NSScanner scannerWithString:[nextToken stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCodeLow];
                [[NSScanner scannerWithString:[[self nextToken:scanner] stringByTrimmingCharactersInSet:self.tagSet]] scanHexInt:&srcCodeHigh];
                [[NSScanner scannerWithString:[[self nextToken:scanner] stringByTrimmingCharactersInSet:self.tagSet]] scanInt:&dstCode];
                
                int range = srcCodeHigh - srcCodeLow;
                for(int r = 0; r <= range; ++r) {
                    NSNumber *from = [NSNumber numberWithInt:(srcCodeLow + r)];
                    NSNumber *to = [NSNumber numberWithInt:(dstCode + r)];
                    if([from intValue] == [to intValue]) {
                        continue;
                    }
                    
                    [self.characterMappings setObject:to forKey:from];
                    [self.reverseCharacterMappings setObject:from forKey:to];
                }
            }
        }
        
        previousToken = token;
        continue;
	}
}

@end
