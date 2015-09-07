//
//  EVendorProtocol.h
//  EVendor
//
//  Created by MIPC-52 on 18/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>

@protocol EVendorProtocol <NSObject>

@optional
- (void) popoverCancel;
- (void) purchaseBook:(id)delegateMethod;
- (void) createNewShelfWithTitle:(NSString*) title andColor:(NSString*) color;
- (void) addBookWithShelfId:(NSString*) aId;
- (void) selectedPickerValue:(NSDictionary*) aDic;
- (void) plusBtnClicked;
- (void) shelfUpdate;

- (void) readBook;
- (void) rateBook;
- (void) moveBook;
- (void) deleteBook;

- (void) moveBookToShelf:(NSString*) aId;
@end
