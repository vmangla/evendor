//
//  AddBookShelfViewC.h
//  EVendor
//
//  Created by MIPC-52 on 18/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface AddBookShelfViewC : UIViewController
{
    NSMutableArray  *mainArray;
    NSInteger   selectedShelf;
}

@property (retain, nonatomic) IBOutlet UITableView *mTableView;
@property (nonatomic, assign) id delegate;
@property (retain, nonatomic) IBOutlet UIButton *doneBtn;
@property (retain, nonatomic) IBOutlet UIButton *addBtn;
@property (nonatomic, assign) BOOL  isMove;

- (IBAction)addShelfClicked:(id)sender;
- (IBAction)doneClicked:(id)sender;
- (IBAction)cancelClicked:(id)sender;
- (id)initWithArray:(NSArray*) arr;

@end
