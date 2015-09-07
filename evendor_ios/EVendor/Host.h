//
//  Host.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface Host : NSObject
{
    id delegate;
}

@property (nonatomic, assign) id delegate;
@property(nonatomic, retain) NSMutableData *responseData;

@property(nonatomic, retain) NSString      *identifier;

+ (Host*)host;
- (void) requestWithUrl:(NSString*) aUrl;
- (void) purchaseUpdateWithUrl:(NSString*) aUrl;

@end
