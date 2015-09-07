//
//  RegistrationViewC.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface RegistrationViewC : UIViewController
{
    BOOL    isAccept;
}

@property (retain, nonatomic) IBOutlet UIView *controlsView;
@property (retain, nonatomic) IBOutlet UITextField *firstNameT;
@property (retain, nonatomic) IBOutlet UITextField *lastNameT;
@property (retain, nonatomic) IBOutlet UITextField *userNameT;
@property (retain, nonatomic) IBOutlet UITextField *passwordT;

@property (retain, nonatomic) IBOutlet UILabel     *termsConditionLbl;



- (IBAction)cancelClicked:(id)sender;
- (IBAction)signupClicked:(id)sender;
- (IBAction)acceptClicked:(id)sender;

@end
