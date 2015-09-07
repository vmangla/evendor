//
//  BookmarkViewC.m
//  EVendor
//
//  Created by MIPC-52 on 28/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "BookmarkViewC.h"
#import "EPubViewController.h"

@interface BookmarkViewC ()

@end

@implementation BookmarkViewC
@synthesize delegate;

- (id)initWithBookmarksArray:(NSArray*) arr
{
    self = [super initWithNibName:@"BookmarkViewC" bundle:nil];
    if (self) {
        // Custom initialization
        ReleaseObj(mainArray);
        mainArray = [[NSMutableArray alloc] initWithArray:arr];
    }
    return self;
}

- (void)dealloc
{
    ReleaseObj(mainArray);
    [_mTableView release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    [self.mTableView reloadData];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


#pragma mark - Table view data source

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
    return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    return [mainArray count];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *CellIdentifier = @"Cell";
    
    UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:CellIdentifier];
    if (cell == nil) {
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:CellIdentifier] autorelease];
    }
    cell.textLabel.numberOfLines = 2;
    cell.textLabel.lineBreakMode = NSLineBreakByTruncatingTail;
    cell.textLabel.adjustsFontSizeToFitWidth = YES;
    cell.textLabel.text = [[mainArray objectAtIndex:indexPath.row] objectForKey:kCurrentPageText];
    return cell;
}


#pragma mark - Table view delegate

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    if(self.delegate)
        [self.delegate getSelectedBookmark:[mainArray objectAtIndex:indexPath.row]];
}



@end
