//
//  YLRenderingState.m
//
//  Copyright (c) 2013 Yakamoz Labs. All rights reserved.
//

#import "YLRenderingState.h"

#define kGlyphSpaceScale 1000

@implementation YLRenderingState

@synthesize characterSpacing, wordSpacing, leading, textRise, horizontalScaling, font, fontSize, lineMatrix, textMatrix, ctm;

- (id)init {
    self = [super init];
    if(self) {
		// Default values
		self.textMatrix = CGAffineTransformIdentity;
		self.lineMatrix = CGAffineTransformIdentity;
        self.ctm = CGAffineTransformIdentity;
		self.horizontalScaling = 1.0;
        self.characterSpacing = 0.0;
        self.wordSpacing = 0.0;
    }
    
    return self;
}

- (id)copyWithZone:(NSZone *)zone {
    YLRenderingState *copy = [[self class] allocWithZone:zone];
    if(copy) {
        copy.lineMatrix = self.lineMatrix;
        copy.textMatrix = self.textMatrix;
        copy.leading = self.leading;
        copy.wordSpacing = self.wordSpacing;
        copy.characterSpacing = self.characterSpacing;
        copy.horizontalScaling = self.horizontalScaling;
        copy.textRise = self.textRise;
        copy.font = self.font;
        copy.fontSize = self.fontSize;
        copy.ctm = self.ctm;
    }
    
	return copy;
}

- (void)dealloc {
    [font release];
    
    [super dealloc];
}

- (void)setTextMatrix:(CGAffineTransform)matrix replaceLineMatrix:(BOOL)replace {
	self.textMatrix = matrix;
	if(replace) {
		self.lineMatrix = matrix;
	}
}

- (void)translateTextPosition:(CGSize)size {
	self.textMatrix = CGAffineTransformTranslate(self.textMatrix, size.width, size.height);
}

- (void)newLineWithTy:(CGFloat)ty tx:(CGFloat)tx save:(BOOL)save {
	CGAffineTransform t = CGAffineTransformTranslate(self.lineMatrix, tx, -ty);
	[self setTextMatrix:t replaceLineMatrix:YES];
	if(save) {
		self.leading = ty;
	}
}

- (void)newLineWithTy:(CGFloat)ty save:(BOOL)save {
	[self newLineWithTy:ty tx:0 save:save];
}

- (void)newLine {
	[self newLineWithTy:self.leading save:NO];
}

- (CGFloat)convertToUserSpace:(CGFloat)value {
    return (value * (self.fontSize / kGlyphSpaceScale));
}

- (CGSize)convertSizeToUserSpace:(CGSize)aSize {
	aSize.width = [self convertToUserSpace:aSize.width];
	aSize.height = [self convertToUserSpace:aSize.height];
	return aSize;
}

@end
