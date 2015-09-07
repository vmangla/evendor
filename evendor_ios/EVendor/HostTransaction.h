//
//  HostTransaction.h
//  EVendor
//
//  Created by MIPC-52 on 30/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface HostTransaction : NSObject
{
    
    id delegate;
}

@property (nonatomic, assign) id delegate;
@property(nonatomic, retain) NSMutableData *responseData;

@property(nonatomic, retain) NSString      *identifier;

+ (HostTransaction*)host;
- (void) requestWithUrl:(NSString*) aUrl;


@end
