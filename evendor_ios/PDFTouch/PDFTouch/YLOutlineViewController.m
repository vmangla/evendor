//
//  YLOutlineViewController.m
//
//  Created by Kemal Taskin on 5/1/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLOutlineViewController.h"
#import "YLPDFViewController.h"
#import "YLDocument.h"
#import "YLOutlineItem.h"
#import "YLOutlineParser.h"

@interface YLOutlineViewController () {
    YLPDFViewController *_pdfViewController;
    UIPopoverController *_poController;
    
    NSArray *_outlineArray;
}

- (void)doneButtonTapped;

@end

@implementation YLOutlineViewController

@synthesize popoverController = _poController;

- (id)initWithPDFController:(YLPDFViewController *)controller {
    self = [super initWithStyle:UITableViewStylePlain];
    if (self) {
        _pdfViewController = controller;
        _poController = nil;
        _outlineArray = nil;
        
        self.title = _pdfViewController.document.title;
        
        UIBarButtonItem *doneButton = [[UIBarButtonItem alloc] initWithBarButtonSystemItem:UIBarButtonSystemItemDone
                                                                                    target:self
                                                                                    action:@selector(doneButtonTapped)];
        [self.navigationItem setRightBarButtonItem:doneButton];
        [doneButton release];
        
        self.contentSizeForViewInPopover = CGSizeMake(320, 680);
    }
    
    return self;
}

- (void)dealloc {
    [super dealloc];
}

- (void)setPopoverController:(UIPopoverController *)popoverController {
    _poController = popoverController;
    [self.navigationItem setRightBarButtonItem:nil];
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    if(_outlineArray == nil) {
        _outlineArray = [_pdfViewController.document.outlineParser outline];
        [self.tableView reloadData];
    }
}

- (void)viewDidUnload {
    [super viewDidUnload];
}

#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
- (BOOL)prefersStatusBarHidden {
    return YES;
}
#endif

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return YES;
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    if(_outlineArray) {
        return [_outlineArray count];
    }
    
    return 0;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    static NSString *CellIdentifier = @"Cell";
    UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:CellIdentifier];
    if(cell == nil) {
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:CellIdentifier] autorelease];
        [[cell textLabel] setLineBreakMode:NSLineBreakByTruncatingMiddle];
        [[cell textLabel] setFont:[UIFont boldSystemFontOfSize:16.0]];
    }
    
    YLOutlineItem *item = [_outlineArray objectAtIndex:indexPath.row];
    [cell setIndentationLevel:item.indentation];
    [[cell textLabel] setText:[NSString stringWithFormat:@"%@ (%lu)", item.title, (unsigned long)item.page]];
    
    return cell;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    
    YLOutlineItem *item = [_outlineArray objectAtIndex:indexPath.row];
    [_pdfViewController scrollToPage:(item.page - 1) animated:NO];
    [self doneButtonTapped];
}


#pragma mark -
#pragma mark Private Methods
- (void)doneButtonTapped {
    if(_poController) {
        [_poController dismissPopoverAnimated:YES];
    } else {
        [self dismissViewControllerAnimated:YES completion:nil];
    }
}

@end
