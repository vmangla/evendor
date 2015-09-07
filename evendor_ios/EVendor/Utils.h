//
//  Utils.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface Utils : NSObject
{
    
}
+ (BOOL) isiOS8;
+ (void) startActivityIndicatorWithMessage:(NSString*)aMessage onView:(UIView*) aView;
+ (void) stopActivityIndicator:(UIView*) aView;
+ (void) showAlertView :(NSString*)title message:(NSString*)msg delegate:(id)delegate
      cancelButtonTitle:(NSString*)CbtnTitle otherButtonTitles:(NSString*)otherBtnTitles;
+ (void) showAlertViewWithTag:(NSInteger)tag title:(NSString*)title message:(NSString*)msg delegate:(id)delegate
            cancelButtonTitle:(NSString*)CbtnTitle otherButtonTitles:(NSString*)otherBtnTitles;
+ (void) showOKAlertWithTitle:(NSString*)aTitle message:(NSString*)aMsg;
+ (void) setRoundWithLayer:(UIView*) aLayer cornerRadius:(CGFloat) aRadius borderWidth:(CGFloat) aWidth borderColor:(UIColor*) aColor maskBounds:(BOOL) aBool;
+ (void) makeRoundRectInView:(UIView*) aView;
+ (BOOL) isPortrait;
+ (BOOL) isInternetAvailable;
+ (UIImage*) getImageFromResource:(NSString*) fileName;
+(BOOL)emailValidate:(NSString *)email;
+ (UIButton *)newButtonWithTarget:(id)target  selector:(SEL)selector frame:(CGRect)frame
							image:(UIImage *)image
					selectedImage:(UIImage *)selectedImage
							  tag:(NSInteger)aTag;
+ (UILabel*) createNewLabelWithTag:(NSInteger)aTag aRect:(CGRect)aRect text:(NSString*)aText noOfLines:(NSInteger)noOfLine color:(UIColor*)color withFont:(UIFont*)font;
+ (UITextField*) createTextFieldWithTag:(NSInteger) aTag aFrame:(CGRect) aFrame aFont:(UIFont*) aFont aPlaceholder:(NSString*) aPlaceholder aTextColor:(UIColor*) aTextColor aKeyboardType:(UIKeyboardType) aKeyboardType;
+ (BOOL) isiOS7;

+(void)addNavigationItm:(id)controller;
@end
