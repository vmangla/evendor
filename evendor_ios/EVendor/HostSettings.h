//
//  HostSettings.h
//  EVendor
//
//  Created by MIPC-52 on 30/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface HostSettings : NSObject
{
    
    id delegate;
}

@property (nonatomic, assign) id delegate;
@property(nonatomic, retain) NSMutableData *responseData;

@property(nonatomic, retain) NSString      *identifier;

+ (HostSettings*)host;
- (void) requestWithUrl:(NSString*) aUrl;

@end
