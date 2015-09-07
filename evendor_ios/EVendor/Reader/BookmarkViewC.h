//
//  BookmarkViewC.h
//  EVendor
//
//  Created by MIPC-52 on 28/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface BookmarkViewC : UIViewController
{
    NSMutableArray  *mainArray;
}

@property (retain, nonatomic) IBOutlet UITableView *mTableView;
@property (nonatomic, assign) id delegate;

- (id)initWithBookmarksArray:(NSArray*) arr;


@end
