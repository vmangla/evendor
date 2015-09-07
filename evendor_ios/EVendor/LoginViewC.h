//
//  LoginViewC.h
//  EVendor
//
//  Created by MIPC-52 on 31/10/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface LoginViewC : UIViewController <UITextFieldDelegate, ServerResponseDelegate>
{
    ServerRequestType   currentRequestType;
    NSDictionary *dataDictionary;
}

@property (nonatomic, strong) NSDictionary *dataDictionary;
@property (retain, nonatomic) IBOutlet UIView *controlsView;
@property (retain, nonatomic) IBOutlet UITextField *userNameT;
@property (retain, nonatomic) IBOutlet UITextField *passwordT;


- (IBAction)forgotPasswordClicked:(id)sender;
- (IBAction)registrationClicked:(id)sender;
- (IBAction)loginClicked:(id)sender;

@end
