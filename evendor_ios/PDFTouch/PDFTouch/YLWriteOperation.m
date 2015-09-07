//
//  YLWriteOperation.m
//
//  Created by Kemal Taskin on 4/5/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLWriteOperation.h"

@implementation YLWriteOperation

- (void)dealloc {
    [super dealloc];
}

- (void)main {
    @autoreleasepool {
        if(self.isCancelled) {
            return;
        }
        
        if(_image == nil) {
            return;
        }
        
        NSData *imageData = UIImageJPEGRepresentation(_image, 0.9);
        if(imageData) {
            [imageData writeToFile:_path atomically:YES];
        }
    }
}

@end
