//
//  ServerResponseDelegate.h
//  EVendor
//
//  Created by MIPC-52 on 06/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>

@protocol ServerResponseDelegate <NSObject>
@optional
-(void) getResult:(NSDictionary*) dataDict;
-(void) didFailToGetResult;

@end
