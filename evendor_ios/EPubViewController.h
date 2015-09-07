//
//  DetailViewController.h
//  AePubReader
//
//  Created by Federico Frappi on 04/04/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "ZipArchive.h"
#import "EPub.h"
#import "Chapter.h"

@class SearchResultsViewController;
@class SearchResult;
@class BookmarkViewC;

@interface EPubViewController : UIViewController <UIWebViewDelegate, ChapterDelegate, UISearchBarDelegate> {
    
    UIToolbar *toolbar;
        
	UIWebView *webView;
    
    UIBarButtonItem* chapterListButton;
	
	UIBarButtonItem* decTextSizeButton;
	UIBarButtonItem* incTextSizeButton;
    
    UISlider* pageSlider;
    UILabel* currentPageLabel;
			
	EPub* loadedEpub;
	int currentSpineIndex;
	int currentPageInSpineIndex;
	int pagesInCurrentSpineCount;
	int currentTextSize;
	int totalPagesCount;
    
    BOOL epubLoaded;
    BOOL paginating;
    BOOL searching;
    
    UIPopoverController* chaptersPopover;
    UIPopoverController* searchResultsPopover;

    SearchResultsViewController* searchResViewController;
    SearchResult* currentSearchResult;
    
    UISlider *slider;
    
    NSMutableArray  *bookMarksArray;
    BOOL isFirstBookMark;
    BookmarkViewC   *objBookmarkViewC;
}

@property(nonatomic, retain) UIPopoverController *popoverController;

- (IBAction) showChapterIndex:(id)sender;
- (IBAction) increaseTextSizeClicked:(id)sender;
- (IBAction) decreaseTextSizeClicked:(id)sender;
- (IBAction) slidingStarted:(id)sender;
- (IBAction) slidingEnded:(id)sender;
- (IBAction) doneClicked:(id)sender;

- (void) loadSpine:(int)spineIndex atPageIndex:(int)pageIndex highlightSearchResult:(SearchResult*)theResult;

- (void) loadEpub:(NSURL*) epubURL;

@property (nonatomic, retain) EPub* loadedEpub;

@property (nonatomic, retain) SearchResult* currentSearchResult;

@property (nonatomic, retain) IBOutlet UIToolbar *toolbar;

@property (nonatomic, retain) IBOutlet UIWebView *webView;

@property (nonatomic, retain) IBOutlet UIBarButtonItem *chapterListButton;

@property (nonatomic, retain) IBOutlet UIBarButtonItem *decTextSizeButton;
@property (nonatomic, retain) IBOutlet UIBarButtonItem *incTextSizeButton;

@property (nonatomic, retain) IBOutlet UISlider *pageSlider;
@property (nonatomic, retain) IBOutlet UILabel *currentPageLabel;

@property BOOL searching;

@property (nonatomic) NSInteger currentBookId;
@property (retain, nonatomic) IBOutlet UIBarButtonItem *dayNightBtn;

- (IBAction)dayClicked:(id)sender;
- (IBAction)nightClicked:(id)sender;
- (IBAction)brightnessClicked:(id)sender;
- (IBAction)nextClicked:(id)sender;
- (IBAction)previousClicked:(id)sender;
- (IBAction)bookMarkClicked:(id)sender;
- (IBAction)allBookmarksClicked:(id)sender;
- (void) getSelectedBookmark: (NSDictionary*) aDic;


@property (retain, nonatomic) IBOutlet UIButton *bookMarkBtn;
@property (nonatomic, retain) NSString  *currentPageText;

@end
