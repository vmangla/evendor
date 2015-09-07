//
//  YLPageRenderer.m
//
//  Created by Kemal Taskin on 3/26/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//
//  Copyright (c) 2011 Sorin Nistor. All rights reserved. This software is provided 'as-is', 
//  without any express or implied warranty. In no event will the authors be held liable for 
//  any damages arising from the use of this software. Permission is granted to anyone to 
//  use this software for any purpose, including commercial applications, and to alter it 
//  and redistribute it freely, subject to the following restrictions:
//  1. The origin of this software must not be misrepresented; you must not claim that you 
//  wrote the original software. If you use this software in a product, an acknowledgment 
//  in the product documentation would be appreciated but is not required.
//  2. Altered source versions must be plainly marked as such, and must not be misrepresented 
//  as being the original software.
//  3. This notice may not be removed or altered from any source distribution.

#import "YLPageRenderer.h"
#import "YLPageInfo.h"

#import "YLSearchResult.h"
#import "YLSelection.h"

@implementation YLPageRenderer

+ (void)renderPage:(CGPDFPageRef)page inContext:(CGContextRef)context atPoint:(CGPoint)point withZoom:(float)zoom pageInfo:(YLPageInfo *)pageInfo search:(NSArray *)searchResults {
    CGRect cropBox = pageInfo.origRect;
    int rotation = pageInfo.rotation;
	
	CGContextSaveGState(context);
	
	// Setup the coordinate system.
	// Top left corner of the displayed page must be located at the point specified by the 'point' parameter.
	CGContextTranslateCTM(context, point.x, point.y);
	
	// Scale the page to desired zoom level.
	CGContextScaleCTM(context, zoom, zoom);
	
	// The coordinate system must be set to match the PDF coordinate system.
	switch (rotation) {
		case 0:
			CGContextTranslateCTM(context, 0, cropBox.size.height);
			CGContextScaleCTM(context, 1, -1);
			break;
		case 90:
			CGContextScaleCTM(context, 1, -1);
			CGContextRotateCTM(context, -M_PI / 2);
			break;
		case 180:
		case -180:
			CGContextScaleCTM(context, 1, -1);
			CGContextTranslateCTM(context, cropBox.size.width, 0);
			CGContextRotateCTM(context, M_PI);
			break;
		case 270:
		case -90:
			CGContextTranslateCTM(context, cropBox.size.height, cropBox.size.width);
			CGContextRotateCTM(context, M_PI / 2);
			CGContextScaleCTM(context, -1, 1);
			break;
	}
	
	// The CropBox defines the page visible area, clip everything outside it.
	CGRect clipRect = CGRectMake(0, 0, cropBox.size.width, cropBox.size.height);
	CGContextAddRect(context, clipRect);
	CGContextClip(context);
	
	CGContextSetRGBFillColor(context, 1.0, 1.0, 1.0, 1.0);
	CGContextFillRect(context, clipRect);
	
	CGContextTranslateCTM(context, -cropBox.origin.x, -cropBox.origin.y);
	
    CGContextSetRenderingIntent(context, kCGRenderingIntentDefault);
    CGContextSetInterpolationQuality(context, kCGInterpolationHigh);
	CGContextDrawPDFPage(context, page);
    
#ifdef DEMO
    CGContextSaveGState(context);
    CGContextSetRGBFillColor(context, 0.0, 0.0, 0.0, 0.8);
    CGContextSetLineWidth(context, 2.0);
    CGContextSelectFont(context, "Helvetica Neue Bold", 16.0, kCGEncodingMacRoman);
    CGContextSetCharacterSpacing(context, 1.5);
    CGContextSetTextDrawingMode(context, kCGTextFill);
    CGContextShowTextAtPoint(context, 20.0, 40.0, "PDFTouch SDK DEMO", 17);
    CGContextRestoreGState(context);
#endif
    
    if(searchResults) {
        CGContextSetFillColorWithColor(context, [[UIColor yellowColor] CGColor]);
        CGContextSetBlendMode(context, kCGBlendModeMultiply);
        
        for(YLSearchResult *s in searchResults) {
            for(YLSelectionSegment *segment in s.selection.segments) {
                CGContextSaveGState(context);
                CGContextConcatCTM(context, segment.transform);
                CGContextFillRect(context, segment.frame);
                CGContextRestoreGState(context);
            }
        }
    }
	
	CGContextRestoreGState(context);
}

+ (void)renderPage:(CGPDFPageRef)page inContext:(CGContextRef)context inRectangle:(CGRect)rectangle pageInfo:(YLPageInfo *)pageInfo search:(NSArray *)searchResults {
    if ((rectangle.size.width == 0) || (rectangle.size.height == 0)) {
        return;
    }
    
    CGRect rotatedBox = pageInfo.rotatedRect;
    CGSize pageVisibleSize = CGSizeMake(rotatedBox.size.width, rotatedBox.size.height);
    
    float scaleX = rectangle.size.width / pageVisibleSize.width;
    float scaleY = rectangle.size.height / pageVisibleSize.height;
    float scale = scaleX < scaleY ? scaleX : scaleY;
    
    // Offset relative to top left corner of rectangle where the page will be displayed
    float offsetX = 0;
    float offsetY = 0;
    
    float rectangleAspectRatio = rectangle.size.width / rectangle.size.height;
    float pageAspectRatio = pageVisibleSize.width / pageVisibleSize.height;
    
    if (pageAspectRatio < rectangleAspectRatio) {
        // The page is narrower than the rectangle, we place it at center on the horizontal
        offsetX = (rectangle.size.width - pageVisibleSize.width * scale) / 2;
    } else { 
        // The page is wider than the rectangle, we place it at center on the vertical
        offsetY = (rectangle.size.height - pageVisibleSize.height * scale) / 2;
    }
    
    CGPoint topLeftPage = CGPointMake(rectangle.origin.x + offsetX, rectangle.origin.y + offsetY);
    [YLPageRenderer renderPage:page inContext:context atPoint:topLeftPage withZoom:scale pageInfo:pageInfo search:searchResults];
}

@end
