//
//  YLSearchViewController.m
//
//  Created by Kemal Taskin on 5/2/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLSearchViewController.h"
#import "YLPDFViewController.h"
#import "YLDocument.h"
#import "YLDocumentScanner.h"
#import "YLSearchResultCell.h"
#import "YLSearchStatusCell.h"
#import "YLSearchResult.h"
#import "YLGlobal.h"
#import "NSBundle+PDFTouch.h"

typedef enum {
    kSearchStateIdle,
    kSearchStateSearching,
    kSearchStateResults
} SearchState;

@interface YLSearchViewController () {
    YLPDFViewController *_pdfViewController;
    UIPopoverController *_poController;
    
    SearchState _searchState;
    
    UISearchBar *_searchBar;
    NSArray *_searchResults;
}

@end

@implementation YLSearchViewController

@synthesize popoverController = _poController;

- (id)initWithPDFController:(YLPDFViewController *)controller {
    self = [super initWithStyle:UITableViewStylePlain];
    if (self) {
        _pdfViewController = controller;
        _poController = nil;
        _searchResults = nil;
        
        self.contentSizeForViewInPopover = CGSizeMake(320, 44);
    }
    
    return self;
}

- (void)dealloc {
    [_searchBar release];
    [_searchResults release];
    
    [super dealloc];
}

- (void)setPopoverController:(UIPopoverController *)popoverController {
    _poController = popoverController;
    if(_searchBar) {
        _searchBar.showsCancelButton = NO;
    }
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
    if(YLIsIOS7OrGreater()) {
        self.edgesForExtendedLayout = UIRectEdgeNone;
    }
#endif
    
    _searchBar = [[UISearchBar alloc] initWithFrame:CGRectMake(0, 0, self.tableView.frame.size.width, 44)];
    _searchBar.delegate = self;
    _searchBar.showsBookmarkButton = NO;
    _searchBar.showsSearchResultsButton = NO;
    if(_poController == nil) {
        _searchBar.showsCancelButton = YES;
    } else {
        _searchBar.showsCancelButton = NO;
    }
    if(!YLIsIOS7OrGreater()) {
        _searchBar.barStyle = UIBarStyleBlack;
    }
    _searchBar.placeholder = [NSBundle myLocalizedStringForKey:@"Search Document"];
    self.tableView.tableHeaderView = _searchBar;
    
    for(UIView *v in _searchBar.subviews) {
        if ([v isKindOfClass:[UITextField class]]) {
            UITextField *tf = (UITextField *)v;
            tf.delegate = self;
            break;
        }
    }
    
    NSArray *prevSearchResults = _pdfViewController.document.scanner.searchResults;
    NSString *prevSearchText = _pdfViewController.document.scanner.lastSearchText;
    if(prevSearchText && prevSearchResults && [prevSearchResults count] > 0) {
        _searchState = kSearchStateResults;
        _searchResults = [prevSearchResults retain];
        [_searchBar setText:prevSearchText];
        
        self.contentSizeForViewInPopover = CGSizeMake(320, 680);
        [self.tableView reloadData];
    } else {
        _searchState = kSearchStateIdle;
    }
    
    [_searchBar becomeFirstResponder];
}

- (void)viewDidUnload {
    [super viewDidUnload];
    
    [_searchBar release]; _searchBar = nil;
}

#if __IPHONE_OS_VERSION_MAX_ALLOWED >= 70000
- (BOOL)prefersStatusBarHidden {
    return YES;
}
#endif

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    
    if(self.navigationController) {
        [self.navigationController setNavigationBarHidden:YES animated:animated];
    }
}

- (void)viewWillDisappear:(BOOL)animated {
    [super viewWillDisappear:animated];
    
    [_pdfViewController.document.scanner cancelSearch];
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return YES;
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return 1;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    if(_searchState == kSearchStateIdle) {
        return 0;
    } else {
        if(_searchResults) {
            return (1 + [_searchResults count]);
        } else {
            return 1;
        }
    }
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    static NSString *ResultCellIdentifier = @"ResultCell";
    static NSString *StatusCellIdentifier = @"StatusCell";
    
    NSInteger rowCount = [self.tableView numberOfRowsInSection:0];
    
    if(indexPath.row == (rowCount - 1)) { // status cell
        YLSearchStatusCell *cell = [tableView dequeueReusableCellWithIdentifier:StatusCellIdentifier];
        if(cell == nil) {
            cell = [[[YLSearchStatusCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:StatusCellIdentifier] autorelease];
            [cell setSelectionStyle:UITableViewCellSelectionStyleNone];
        }
        
        if(_searchState == kSearchStateSearching) {
            [cell setSearchStatus:kSearchStatusSearching];
        } else {
            [cell setSearchStatus:kSearchStatusStopped];
            NSInteger resultsCount = 0;
            if(_searchResults) {
                resultsCount = [_searchResults count];
            }
            [[cell detailLabel] setText:[NSString stringWithFormat:[NSBundle myLocalizedStringForKey:@"%d results found"], resultsCount]];
        }
        
        return cell;
    } else { // result cell
        YLSearchResultCell *cell = [tableView dequeueReusableCellWithIdentifier:ResultCellIdentifier];
        if(cell == nil) {
            cell = [[[YLSearchResultCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:ResultCellIdentifier] autorelease];
        }
        
        YLSearchResult *result = [_searchResults objectAtIndex:indexPath.row];
        [cell setSearchResult:result];
        
        return cell;
    }
    
    return nil;
}

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    [tableView deselectRowAtIndexPath:indexPath animated:YES];
    
    NSInteger rowCount = [self.tableView numberOfRowsInSection:0];
    if(indexPath.row < (rowCount - 1)) {
        [_pdfViewController.document.scanner setSearchResults:_searchResults];
        YLSearchResult *result = [_searchResults objectAtIndex:indexPath.row];
        [_pdfViewController scrollToPage:(result.page - 1) animated:NO];
        
        if(_poController) {
            [_poController dismissPopoverAnimated:YES];
        } else {
            [self dismissViewControllerAnimated:YES completion:nil];
        }
    }
}


#pragma mark -
#pragma mark YLSearchDelegate Methods
- (void)searchOperationCanceled:(YLSearchOperation *)operation {
    _searchState = kSearchStateIdle;
    if(_searchResults) {
        [_searchResults release];
        _searchResults = nil;
    }
    
    [self.tableView reloadData];
}

- (void)searchOperationFinished:(YLSearchOperation *)operation {
    _searchState = kSearchStateResults;
    [self.tableView reloadData];
}

- (void)searchOperation:(YLSearchOperation *)operation didUpdateResults:(NSArray *)results {
    _searchState = kSearchStateSearching;
    self.contentSizeForViewInPopover = CGSizeMake(320, 680);
    
    if(_searchResults) {
        [_searchResults release];
        _searchResults = nil;
    }
    
    _searchResults = [operation.searchResults retain];
    [self.tableView reloadData];
}


#pragma mark -
#pragma mark UISearchBarDelegate Methods
- (void)searchBarCancelButtonClicked:(UISearchBar *)searchBar {
    if(_searchState == kSearchStateSearching) {
        [_pdfViewController.document.scanner cancelSearch];
    }
    
    if(_poController) {
        [_poController dismissPopoverAnimated:YES];
    } else {
        [self dismissViewControllerAnimated:YES completion:nil];
    }
}

- (void)searchBarSearchButtonClicked:(UISearchBar *)searchBar {
    if(_searchState == kSearchStateSearching) {
        return;
    }
    
    if([_searchBar.text length] < 3) {
        return;
    }
    
    _searchState = kSearchStateSearching;
    self.contentSizeForViewInPopover = CGSizeMake(320, 100);
    
    if(_searchResults) {
        [_searchResults release];
        _searchResults = nil;
    }
    [self.tableView reloadData];
    
    [_pdfViewController.document.scanner setSearchDelegate:self];
    [_pdfViewController.document.scanner searchText:_searchBar.text];
}


#pragma mark -
#pragma mark UITextFieldDelegate Methods
- (BOOL)textFieldShouldClear:(UITextField *)textField {
    if(_searchState == kSearchStateSearching) {
        [_pdfViewController.document.scanner cancelSearch];
    }
    
    // remove previous search results to stop highlighting search results in pdf pages
    [_pdfViewController.document.scanner setSearchResults:nil];
    [_pdfViewController clearSearchResults];
    
    _searchState = kSearchStateIdle;
    [self.tableView reloadData];
    
    return YES;
}

@end
