//
//  YLLoadOperation.m
//
//  Created by Kemal Taskin on 4/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLLoadOperation.h"

@implementation YLLoadOperation

- (void)dealloc {
    [super dealloc];
}

- (void)main {
    @autoreleasepool {
        if(self.isCancelled) {
            return;
        }
        
        UIImage *image = [[UIImage alloc] initWithContentsOfFile:_path];
        if(image == nil) {
            return;
        }
        
        if(self.isCancelled) {
            [image release];
            return;
        }
        
        CGImageRef imageRef = [image CGImage];
        CGRect rect = CGRectMake(0.f, 0.f, CGImageGetWidth(imageRef), CGImageGetHeight(imageRef));
        CGContextRef bitmapContext = CGBitmapContextCreate(NULL,
                                                           rect.size.width,
                                                           rect.size.height,
                                                           CGImageGetBitsPerComponent(imageRef),
                                                           CGImageGetBytesPerRow(imageRef),
                                                           CGImageGetColorSpace(imageRef),
                                                           kCGImageAlphaPremultipliedFirst | kCGBitmapByteOrder32Little
                                                           );
        CGContextDrawImage(bitmapContext, rect, imageRef);
        CGImageRef decompressedImageRef = CGBitmapContextCreateImage(bitmapContext);
        CGFloat scale = [[UIScreen mainScreen] scale];
        _image = [[UIImage alloc] initWithCGImage:decompressedImageRef scale:scale orientation:UIImageOrientationUp];
        CGImageRelease(decompressedImageRef);
        CGContextRelease(bitmapContext);
        [image release];
    }
}

@end
