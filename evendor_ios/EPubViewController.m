//
//  DetailViewController.m
//  AePubReader
//
//  Created by Federico Frappi on 04/04/11.
//  Copyright 2011 __MyCompanyName__. All rights reserved.
//

#import "EPubViewController.h"
#import "ChapterListViewController.h"
#import "SearchResultsViewController.h"
#import "SearchResult.h"
#import "UIWebView+SearchWebView.h"
#import "Chapter.h"
#import "BookmarkViewC.h"

@interface EPubViewController()


- (void) gotoNextSpine;
- (void) gotoPrevSpine;
- (void) gotoNextPage;
- (void) gotoPrevPage;

- (int) getGlobalPageCount;

- (void) gotoPageInCurrentSpine: (int)pageIndex;
- (void) updatePagination;

- (void) loadSpine:(int)spineIndex atPageIndex:(int)pageIndex;


@end

@implementation EPubViewController

@synthesize loadedEpub, toolbar, webView; 
@synthesize chapterListButton, decTextSizeButton, incTextSizeButton;
@synthesize currentPageLabel, pageSlider, searching;
@synthesize currentSearchResult;
@synthesize popoverController;
@synthesize currentBookId;
@synthesize currentPageText;

#pragma mark -

- (void) loadEpub:(NSURL*) epubURL{
    currentSpineIndex = 0;
    currentPageInSpineIndex = 0;
    pagesInCurrentSpineCount = 0;
    totalPagesCount = 0;
	searching = NO;
    epubLoaded = NO;
    self.loadedEpub = [[EPub alloc] initWithEPubPath:[epubURL path]];
    epubLoaded = YES;
    NSLog(@"loadEpub");
    isFirstBookMark = NO;
	[self updatePagination];
}

- (void) chapterDidFinishLoad:(Chapter *)chapter{
    totalPagesCount+=chapter.pageCount;

	if(chapter.chapterIndex + 1 < [loadedEpub.spineArray count]){
		[[loadedEpub.spineArray objectAtIndex:chapter.chapterIndex+1] setDelegate:self];
		[[loadedEpub.spineArray objectAtIndex:chapter.chapterIndex+1] loadChapterWithWindowSize:webView.bounds fontPercentSize:currentTextSize];
		[currentPageLabel setText:[NSString stringWithFormat:@"?/%d", totalPagesCount]];
	} else {
		[currentPageLabel setText:[NSString stringWithFormat:@"%d/%d",[self getGlobalPageCount], totalPagesCount]];
		[pageSlider setValue:(float)100*(float)[self getGlobalPageCount]/(float)totalPagesCount animated:YES];
		paginating = NO;
		//NSLog(@"Pagination Ended!");
	}
}

- (int) getGlobalPageCount{
	int pageCount = 0;
	for(int i=0; i<currentSpineIndex; i++){
		pageCount+= [[loadedEpub.spineArray objectAtIndex:i] pageCount]; 
	}
	pageCount+=currentPageInSpineIndex+1;
	return pageCount;
}

- (void) loadSpine:(int)spineIndex atPageIndex:(int)pageIndex {
	[self loadSpine:spineIndex atPageIndex:pageIndex highlightSearchResult:nil];
}

- (void) loadSpine:(int)spineIndex atPageIndex:(int)pageIndex highlightSearchResult:(SearchResult*)theResult{
	
	webView.hidden = YES;
	
	self.currentSearchResult = theResult;

	[chaptersPopover dismissPopoverAnimated:YES];
	[searchResultsPopover dismissPopoverAnimated:YES];
	
	NSURL* url = [NSURL fileURLWithPath:[[loadedEpub.spineArray objectAtIndex:spineIndex] spinePath]];
	[webView loadRequest:[NSURLRequest requestWithURL:url]];
	currentPageInSpineIndex = pageIndex;
	currentSpineIndex = spineIndex;
	if(!paginating){
		[currentPageLabel setText:[NSString stringWithFormat:@"%d/%d",[self getGlobalPageCount], totalPagesCount]];
		[pageSlider setValue:(float)100*(float)[self getGlobalPageCount]/(float)totalPagesCount animated:YES];	
	}
    
    // Add Book Mark
    [self addBookMark];
}

- (void) gotoPageInCurrentSpine:(int)pageIndex{
	if(pageIndex>=pagesInCurrentSpineCount){
		pageIndex = pagesInCurrentSpineCount - 1;
		currentPageInSpineIndex = pagesInCurrentSpineCount - 1;	
	}
	
	float pageOffset = pageIndex*webView.bounds.size.width;

	NSString* goToOffsetFunc = [NSString stringWithFormat:@" function pageScroll(xOffset){ window.scroll(xOffset,0); } "];
	NSString* goTo =[NSString stringWithFormat:@"pageScroll(%f)", pageOffset];
	
	[webView stringByEvaluatingJavaScriptFromString:goToOffsetFunc];
	[webView stringByEvaluatingJavaScriptFromString:goTo];
	
	if(!paginating){
		[currentPageLabel setText:[NSString stringWithFormat:@"%d/%d",[self getGlobalPageCount], totalPagesCount]];
		[pageSlider setValue:(float)100*(float)[self getGlobalPageCount]/(float)totalPagesCount animated:YES];	
	}
	
	webView.hidden = NO;
	
    // Add Book Mark
    [self addBookMark];
}

- (void) nextPageAnimation
{
    CATransition *animation = [CATransition animation];
    [animation setDelegate:self];
    [animation setDuration:0.5f];
    [animation setType:@"pageCurl"];
    [animation setSubtype:@"fromRight"];
    [[webView layer] addAnimation:animation forKey:@"WebPageCurl"];
}

- (void) previousPageAnimation
{
    CATransition *animation = [CATransition animation];
    [animation setDelegate:self];
    [animation setDuration:0.5f];
//    [animation setType:@"pageUnCurl"];
//    [animation setSubtype:@"fromRight"];
//    [[webView layer] addAnimation:animation forKey:@"WebPageUnCurl"];
    
    [animation setType:@"pageCurl"];
    [animation setSubtype:@"fromLeft"];
    [[webView layer] addAnimation:animation forKey:@"WebPageCurl"];
}

- (void) gotoNextSpine {
	if(!paginating){
		if(currentSpineIndex+1<[loadedEpub.spineArray count]){
			[self loadSpine:++currentSpineIndex atPageIndex:0];
		}
	}
}

- (void) gotoPrevSpine {
	if(!paginating){
		if(currentSpineIndex-1>=0){
			[self loadSpine:--currentSpineIndex atPageIndex:0];
		}	
	}
}

- (void) gotoNextPage {
	if(!paginating){
        [self nextPageAnimation];
		if(currentPageInSpineIndex+1<pagesInCurrentSpineCount){
			[self gotoPageInCurrentSpine:++currentPageInSpineIndex];
		} else {
			[self gotoNextSpine];
		}		
	}
}

- (void) gotoPrevPage {
	if (!paginating) {
        //[self previousPageAnimation];
		if(currentPageInSpineIndex-1>=0){
            [self previousPageAnimation];
			[self gotoPageInCurrentSpine:--currentPageInSpineIndex];
		} else {
			if(currentSpineIndex!=0){
                [self previousPageAnimation];
				int targetPage = [[loadedEpub.spineArray objectAtIndex:(currentSpineIndex-1)] pageCount];
				[self loadSpine:--currentSpineIndex atPageIndex:targetPage-1];
			}
		}
	}
}


- (IBAction) increaseTextSizeClicked:(id)sender{
	if(!paginating){
		if(currentTextSize+25<=200){
			currentTextSize+=25;
			[self updatePagination];
			if(currentTextSize == 200){
				[incTextSizeButton setEnabled:NO];
			}
			[decTextSizeButton setEnabled:YES];
		}
	}
}
- (IBAction) decreaseTextSizeClicked:(id)sender{
	if(!paginating){
		if(currentTextSize-25>=50){
			currentTextSize-=25;
			[self updatePagination];
			if(currentTextSize==50){
				[decTextSizeButton setEnabled:NO];
			}
			[incTextSizeButton setEnabled:YES];
		}
	}
}

- (IBAction) doneClicked:(id)sender{
    
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory, NSUserDomainMask, YES);
    NSString *documentsDirectory = [paths objectAtIndex:0];
    NSString *writablePath = [documentsDirectory stringByAppendingPathComponent:@"UnzippedEpub"];
    NSError *error = nil;
    [[NSFileManager defaultManager] removeItemAtPath:writablePath error:&error];
    
    
    [self dismissViewControllerAnimated:YES completion:nil];
}


- (IBAction) slidingStarted:(id)sender{
    int targetPage = ((pageSlider.value/(float)100)*(float)totalPagesCount);
    if (targetPage==0) {
        targetPage++;
    }
	[currentPageLabel setText:[NSString stringWithFormat:@"%d/%d", targetPage, totalPagesCount]];
}

- (IBAction) slidingEnded:(id)sender{
	int targetPage = (int)((pageSlider.value/(float)100)*(float)totalPagesCount);
    if (targetPage==0) {
        targetPage++;
    }
	int pageSum = 0;
	int chapterIndex = 0;
	int pageIndex = 0;
	for(chapterIndex=0; chapterIndex<[loadedEpub.spineArray count]; chapterIndex++){
		pageSum+=[[loadedEpub.spineArray objectAtIndex:chapterIndex] pageCount];
//		NSLog(@"Chapter %d, targetPage: %d, pageSum: %d, pageIndex: %d", chapterIndex, targetPage, pageSum, (pageSum-targetPage));
		if(pageSum>=targetPage){
			pageIndex = [[loadedEpub.spineArray objectAtIndex:chapterIndex] pageCount] - 1 - pageSum + targetPage;
			break;
		}
	}
	[self loadSpine:chapterIndex atPageIndex:pageIndex];
}

- (IBAction) showChapterIndex:(id)sender{
	if(chaptersPopover==nil){
		ChapterListViewController* chapterListView = [[ChapterListViewController alloc] initWithNibName:@"ChapterListViewController" bundle:[NSBundle mainBundle]];
		[chapterListView setEpubViewController:self];
		chaptersPopover = [[UIPopoverController alloc] initWithContentViewController:chapterListView];
		[chaptersPopover setPopoverContentSize:CGSizeMake(400, 600)];
		[chapterListView release];
	}
	if ([chaptersPopover isPopoverVisible]) {
		[chaptersPopover dismissPopoverAnimated:YES];
	}else{
		[chaptersPopover presentPopoverFromBarButtonItem:chapterListButton permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];		
	}
}


- (void)webViewDidFinishLoad:(UIWebView *)theWebView{
	
	NSString *varMySheet = @"var mySheet = document.styleSheets[0];";
	
	NSString *addCSSRule =  @"function addCSSRule(selector, newRule) {"
	"if (mySheet.addRule) {"
	"mySheet.addRule(selector, newRule);"								// For Internet Explorer
	"} else {"
	"ruleIndex = mySheet.cssRules.length;"
	"mySheet.insertRule(selector + '{' + newRule + ';}', ruleIndex);"   // For Firefox, Chrome, etc.
	"}"
	"}";
	
	NSString *insertRule1 = [NSString stringWithFormat:@"addCSSRule('html', 'padding: 0px; height: %fpx; -webkit-column-gap: 0px; -webkit-column-width: %fpx;')", webView.frame.size.height, webView.frame.size.width];
	NSString *insertRule2 = [NSString stringWithFormat:@"addCSSRule('p', 'text-align: justify;')"];
	NSString *setTextSizeRule = [NSString stringWithFormat:@"addCSSRule('body', '-webkit-text-size-adjust: %d%%;')", currentTextSize];
	NSString *setHighlightColorRule = [NSString stringWithFormat:@"addCSSRule('highlight', 'background-color: yellow;')"];

	
	[webView stringByEvaluatingJavaScriptFromString:varMySheet];
	
	[webView stringByEvaluatingJavaScriptFromString:addCSSRule];
		
	[webView stringByEvaluatingJavaScriptFromString:insertRule1];
	
	[webView stringByEvaluatingJavaScriptFromString:insertRule2];
	
	[webView stringByEvaluatingJavaScriptFromString:setTextSizeRule];
	
	[webView stringByEvaluatingJavaScriptFromString:setHighlightColorRule];
	
	if(currentSearchResult!=nil){
	//	NSLog(@"Highlighting %@", currentSearchResult.originatingQuery);
        [webView highlightAllOccurencesOfString:currentSearchResult.originatingQuery];
	}
	
	
	int totalWidth = [[webView stringByEvaluatingJavaScriptFromString:@"document.documentElement.scrollWidth"] intValue];
	pagesInCurrentSpineCount = (int)((float)totalWidth/webView.bounds.size.width);
	
	[self gotoPageInCurrentSpine:currentPageInSpineIndex];
    
    NSString *isDayMode = [[NSUserDefaults standardUserDefaults] objectForKey:@"isDayMode"];
    if([@"NO" isEqualToString:isDayMode])
    {
        //[self nightClicked:nil];
        [webView setOpaque:NO];
        [webView setBackgroundColor:[UIColor blackColor]];
        NSString *jsString = [[NSString alloc] initWithFormat:@"document.getElementsByTagName('body')[0].style.webkitTextFillColor= 'white'"];
        [webView stringByEvaluatingJavaScriptFromString:jsString];

    }
    else if([@"YES" isEqualToString:isDayMode])
    {
        //[self dayClicked:nil];
        [webView setOpaque:NO];
        [webView setBackgroundColor:[UIColor whiteColor]];
        NSString *jsString = [[NSString alloc] initWithFormat:@"document.getElementsByTagName('body')[0].style.webkitTextFillColor= 'black'"];
        [webView stringByEvaluatingJavaScriptFromString:jsString];
    }
    
    CGFloat brightness = [UIScreen mainScreen].brightness;
    [[UIScreen mainScreen] setBrightness:brightness];
}

- (void) updatePagination{
	if(epubLoaded){
        if(!paginating){
            //NSLog(@"Pagination Started!");
            paginating = YES;
            totalPagesCount=0;
            [self loadSpine:currentSpineIndex atPageIndex:currentPageInSpineIndex];
            [[loadedEpub.spineArray objectAtIndex:0] setDelegate:self];
            [[loadedEpub.spineArray objectAtIndex:0] loadChapterWithWindowSize:webView.bounds fontPercentSize:currentTextSize];
            [currentPageLabel setText:@"?/?"];
        }
	}
}


- (void)searchBarSearchButtonClicked:(UISearchBar *)searchBar{
	if(searchResultsPopover==nil){
		searchResultsPopover = [[UIPopoverController alloc] initWithContentViewController:searchResViewController];
		[searchResultsPopover setPopoverContentSize:CGSizeMake(400, 600)];
	}
	if (![searchResultsPopover isPopoverVisible]) {
		[searchResultsPopover presentPopoverFromRect:searchBar.bounds inView:searchBar permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
	}
//	NSLog(@"Searching for %@", [searchBar text]);
	if(!searching){
		searching = YES;
		[searchResViewController searchString:[searchBar text]];
        [searchBar resignFirstResponder];
	}
}


#pragma mark -
#pragma mark Rotation support

// Ensure that the view controller supports rotation and that the split view can therefore show in both portrait and landscape.
- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    NSLog(@"shouldAutorotate");
    [self updatePagination];
	return YES;
}

#pragma mark -
#pragma mark View lifecycle

 // Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
- (void)viewDidLoad {
    [super viewDidLoad];
	[webView setDelegate:self];
		
	UIScrollView* sv = nil;
	for (UIView* v in  webView.subviews) {
		if([v isKindOfClass:[UIScrollView class]]){
			sv = (UIScrollView*) v;
			sv.scrollEnabled = NO;
			sv.bounces = NO;
		}
	}
	currentTextSize = 100;	 
	
	UISwipeGestureRecognizer* rightSwipeRecognizer = [[[UISwipeGestureRecognizer alloc] initWithTarget:self action:@selector(gotoNextPage)] autorelease];
	[rightSwipeRecognizer setDirection:UISwipeGestureRecognizerDirectionLeft];
	
	UISwipeGestureRecognizer* leftSwipeRecognizer = [[[UISwipeGestureRecognizer alloc] initWithTarget:self action:@selector(gotoPrevPage)] autorelease];
	[leftSwipeRecognizer setDirection:UISwipeGestureRecognizerDirectionRight];
	
	[webView addGestureRecognizer:rightSwipeRecognizer];
	[webView addGestureRecognizer:leftSwipeRecognizer];
	
	//[pageSlider setThumbImage:[UIImage imageNamed:@"slider_ball.png"] forState:UIControlStateNormal];
	//[pageSlider setMinimumTrackImage:[[UIImage imageNamed:@"orangeSlide.png"] stretchableImageWithLeftCapWidth:10 topCapHeight:0] forState:UIControlStateNormal];
	//[pageSlider setMaximumTrackImage:[[UIImage imageNamed:@"yellowSlide.png"] stretchableImageWithLeftCapWidth:10 topCapHeight:0] forState:UIControlStateNormal];
    
	searchResViewController = [[SearchResultsViewController alloc] initWithNibName:@"SearchResultsViewController" bundle:[NSBundle mainBundle]];
	searchResViewController.epubViewController = self;
    
    //UIPinchGestureRecognizer *pinchZoom = [[UIPinchGestureRecognizer alloc] initWithTarget:self action:@selector(handlePinch:)];
    //[webView addGestureRecognizer:pinchZoom];
    //[pinchZoom release];
    
    [self setDayNightTitle];
    
    [self fetchAllBookMarks];
}

//- (void) handlePinch:(UIPinchGestureRecognizer *)recognizer
//{
//    recognizer.view.transform = CGAffineTransformScale(recognizer.view.transform, recognizer.scale, recognizer.scale);
//    recognizer.scale = 1;
//}

- (void)viewDidUnload {
	self.toolbar = nil;
	self.webView = nil;
	self.chapterListButton = nil;
	self.decTextSizeButton = nil;
	self.incTextSizeButton = nil;
	self.pageSlider = nil;
	self.currentPageLabel = nil;	
}

- (void) viewDidLayoutSubviews
{
    UIButton    *prevBtn = (UIButton*)[self.view viewWithTag:200];
    UIButton    *nextBtn = (UIButton*)[self.view viewWithTag:201];
    if(UIInterfaceOrientationIsLandscape(self.interfaceOrientation))
    {
        prevBtn.frame = CGRectMake(20, self.pageSlider.frame.origin.y, 61, 30);
        nextBtn.frame = CGRectMake(702+256, self.pageSlider.frame.origin.y, 46, 30);
        self.bookMarkBtn.frame = CGRectMake(987, 47, 40, 40);
    }
    else
    {
        prevBtn.frame = CGRectMake(20, self.pageSlider.frame.origin.y, 61, 30);
        nextBtn.frame = CGRectMake(702, self.pageSlider.frame.origin.y, 46, 30);
        self.bookMarkBtn.frame = CGRectMake(731, 47, 40, 40);
    }
}


#pragma mark -
#pragma mark Memory management

/*
- (void)didReceiveMemoryWarning {
    // Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
    
    // Release any cached data, images, etc that aren't in use.
}
*/

- (void)dealloc
{
    ReleaseObj(objBookmarkViewC);
    [currentPageText release];
    [bookMarksArray release];
    [slider release];
    [popoverController release];
    self.toolbar = nil;
	self.webView = nil;
	self.chapterListButton = nil;
	self.decTextSizeButton = nil;
	self.incTextSizeButton = nil;
	self.pageSlider = nil;
	self.currentPageLabel = nil;
	[loadedEpub release];
	[chaptersPopover release];
	[searchResultsPopover release];
	[searchResViewController release];
	[currentSearchResult release];
    [_bookMarkBtn release];
    [_dayNightBtn release];
    [super dealloc];
}

- (void) setDayNightTitle
{
    NSString *isDayMode = [[NSUserDefaults standardUserDefaults] objectForKey:@"isDayMode"];
    if([@"NO" isEqualToString:isDayMode])
    {
        
        self.dayNightBtn.title = @"Day";
        //self.dayNightBtn.title = @"Night";

    }
    else if([@"YES" isEqualToString:isDayMode])
    {
        
        self.dayNightBtn.title = @"Night";
        //self.dayNightBtn.title = @"Day";

    }
}


- (IBAction)dayClicked:(id)sender
{
    NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];
    //[userDefaults setObject:@"YES" forKey:@"isDayMode"];
    //[userDefaults synchronize];
    
//    [webView setOpaque:NO];
//    [webView setBackgroundColor:[UIColor whiteColor]];
//    NSString *jsString = [[NSString alloc] initWithFormat:@"document.getElementsByTagName('body')[0].style.webkitTextFillColor= 'black'"];
//    [webView stringByEvaluatingJavaScriptFromString:jsString];
    
    if([@"Day" isEqualToString:self.dayNightBtn.title])
    {
        //[userDefaults setObject:@"NO" forKey:@"isDayMode"];

        [userDefaults setObject:@"YES" forKey:@"isDayMode"];
        [userDefaults synchronize];

        // Night
        //self.dayNightBtn.title = @"Day";

        self.dayNightBtn.title = @"Night";
        [webView setOpaque:NO];
        [webView setBackgroundColor:[UIColor whiteColor]];
        NSString *jsString = [[NSString alloc] initWithFormat:@"document.getElementsByTagName('body')[0].style.webkitTextFillColor= 'black'"];
        [webView stringByEvaluatingJavaScriptFromString:jsString];
    }
    else if([@"Night" isEqualToString:self.dayNightBtn.title])
    {
        [userDefaults setObject:@"NO" forKey:@"isDayMode"];
        //[userDefaults setObject:@"YES" forKey:@"isDayMode"];

        [userDefaults synchronize];

        // Day
        self.dayNightBtn.title = @"Day";
        //self.dayNightBtn.title = @"Night";

                [webView setOpaque:NO];
        [webView setBackgroundColor:[UIColor blackColor]];
        NSString *jsString = [[NSString alloc] initWithFormat:@"document.getElementsByTagName('body')[0].style.webkitTextFillColor= 'white'"];
        [webView stringByEvaluatingJavaScriptFromString:jsString];
    }
}

//- (IBAction)nightClicked:(id)sender
//{
//    NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];
//    [userDefaults setObject:@"NO" forKey:@"isDayMode"];
//    [userDefaults synchronize];
//    
//    [webView setOpaque:NO];
//    [webView setBackgroundColor:[UIColor blackColor]];
//    NSString *jsString = [[NSString alloc] initWithFormat:@"document.getElementsByTagName('body')[0].style.webkitTextFillColor= 'white'"];
//    [webView stringByEvaluatingJavaScriptFromString:jsString];
//}

- (IBAction)brightnessClicked:(id)sender
{
    UIViewController* popoverContent = [[UIViewController alloc]
										init];
	UIView* popoverView = [[UIView alloc] initWithFrame:CGRectMake(500, 0, 200, 100)];
	popoverView.backgroundColor = [UIColor clearColor];
    
    CGRect frame = CGRectMake(25.0, 20.0, 200.0, 10.0);
    slider = [[UISlider alloc] initWithFrame:frame];
    [slider addTarget:self action:@selector(sliderAction:) forControlEvents:UIControlEventValueChanged];
    [slider setBackgroundColor:[UIColor clearColor]];
    slider.minimumValue = 0.0;
    slider.maximumValue = 1.0;
    slider.continuous = YES;
    CGFloat brightness = [UIScreen mainScreen].brightness;;
    slider.value = brightness;
    
    [popoverView addSubview:slider];
	popoverContent.view = popoverView;
	popoverContent.contentSizeForViewInPopover = CGSizeMake(250, 50);
    //popoverContent.preferredContentSize = CGSizeMake(250, 50);
    ReleaseObj(popoverController);
	self.popoverController = [[UIPopoverController alloc] initWithContentViewController:popoverContent];
    [self.popoverController presentPopoverFromBarButtonItem:sender permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
    self.popoverController.backgroundColor = kBgColor;
}

- (IBAction)nextClicked:(id)sender
{
    [self gotoNextPage];
}

- (IBAction)previousClicked:(id)sender
{
    [self gotoPrevPage];
}

#pragma mark - BookMark
- (void) bookmarkText
{
    // For Bookmark
    NSString    *aText = [webView stringByEvaluatingJavaScriptFromString:@"document.body.innerText"];
    if(aText.length == 0 || [@"(null)" isEqualToString:aText] || [@"<null>" isEqualToString:aText])
        aText = [webView stringByEvaluatingJavaScriptFromString:@"document.documentElement.innerText"];
    NSArray *arr = [aText componentsSeparatedByString:@" "];
    //NSLog(@"Array == %@",arr);
    if([arr count]>=3)
        aText = [NSString stringWithFormat:@"%@\n%@\n%@...page:%d",[arr objectAtIndex:0],[arr objectAtIndex:1], [self addArrayString:arr],[self getGlobalPageCount]];//[arr objectAtIndex:2]
    if(aText.length == 0 || [@"(null)" isEqualToString:aText] || [@"<null>" isEqualToString:aText])
    {
        if([arr count]>=2)
            aText = [NSString stringWithFormat:@"%@\n%@...page:%d",[arr objectAtIndex:0],[arr objectAtIndex:1],[self getGlobalPageCount]];
    }
    if(aText.length == 0 || [@"(null)" isEqualToString:aText] || [@"<null>" isEqualToString:aText])
    {
        if([arr count]>=1)
            aText = [NSString stringWithFormat:@"%@...page:%d",[arr objectAtIndex:0],[self getGlobalPageCount]];
    }
    
    if(aText.length == 0 || [@"(null)" isEqualToString:aText] || [@"<null>" isEqualToString:aText])
        aText = @"Home";
    aText = [aText stringByReplacingOccurrencesOfString:@"\n" withString:@" "];
    self.currentPageText = aText;
    //NSLog(@"currentPageText = %@", self.currentPageText);
}

-(NSString *)addArrayString:(NSArray *)tmpArray{
    NSMutableString *tmpString = [[NSMutableString alloc] init];
    for (int i = 2; i < [tmpArray count]; i++) {
        [tmpString appendString:[tmpArray objectAtIndex:i]];
        if (tmpString.length >= 30) {
            break;
        }
    }
    return tmpString;
}
- (IBAction)bookMarkClicked:(id)sender
{
    [self bookmarkText];
    
    UIButton    *aBtn = (UIButton*) sender;
    aBtn.selected = !aBtn.selected;
    
    if(aBtn.selected == YES)
    {
        if(isFirstBookMark == NO)
        {
            [Utils showOKAlertWithTitle:@"Alert" message:@"Please do not change font size, the bookmark will be lost"];
        }
        isFirstBookMark = YES;
        // Insert/Update BookMark
        if(self.currentBookId>0)
        {
            [[LocalDatabase sharedDatabase] insertBookMarkWithBookId:self.currentBookId andCurrentSpineIndex:currentSpineIndex andCurrentPageInSpineIndex:currentPageInSpineIndex andCurrentTextSize:currentTextSize andcurrentPageText:self.currentPageText];
            [self fetchAllBookMarks];
        }
    }
    else
    {
        BOOL isDeleted = NO;
        // Delete BookMark
        if(self.currentBookId>0)
        {
            NSInteger   isMarked = [self isBookMarked];
            if(isMarked>=0)
            {
                NSDictionary    *aDic = [bookMarksArray objectAtIndex:isMarked];
                isDeleted = [[LocalDatabase sharedDatabase] deleteBookMarkWithId:[[aDic objectForKey:kId] integerValue]];
                if(isDeleted == YES)
                    [self fetchAllBookMarks];
            }
        }
    }
}


- (void) addBookMark
{
    if([self isBookMarked]>=0)
        self.bookMarkBtn.selected = YES;
    else
        self.bookMarkBtn.selected = NO;
}


- (NSInteger) isBookMarked
{
    NSInteger isMarked = -1;
    for (int i=0; i<[bookMarksArray count]; i++)
    {
        NSInteger   local_currentSpineIndex = [[[bookMarksArray objectAtIndex:i] objectForKey:kCurrentSpineIndex] integerValue];
        NSInteger   local_currentPageInSpineIndex = [[[bookMarksArray objectAtIndex:i] objectForKey:kCurrentPageInSpineIndex] integerValue];
        if(currentSpineIndex==local_currentSpineIndex && currentPageInSpineIndex==local_currentPageInSpineIndex)
        {
            isMarked = i;
            break;
        }
    }
    return isMarked;
}

- (void) fetchAllBookMarks
{
    // Fetch All BookMarks
    if(self.currentBookId<=0)
        return;
    ReleaseObj(bookMarksArray);
    bookMarksArray = [[NSMutableArray alloc] initWithArray:[[LocalDatabase sharedDatabase] allBookMarksWithBookId:self.currentBookId]];
    //NSLog(@"BookMarks: %@", bookMarksArray);
}

- (void) sliderAction:(id) sender
{
    CGFloat brightness = [(UISlider*) sender value];
    //[[NSUserDefaults standardUserDefaults] setFloat:brightness forKey:@"brightnessValue"];
    [[UIScreen mainScreen] setBrightness:brightness];
}

- (IBAction)allBookmarksClicked:(id)sender
{
    ReleaseObj(objBookmarkViewC);
    objBookmarkViewC = [[BookmarkViewC alloc] initWithBookmarksArray:bookMarksArray];
    objBookmarkViewC.delegate = self;
    ReleaseObj(popoverController);
    self.popoverController = [[UIPopoverController alloc] initWithContentViewController:objBookmarkViewC];
    [self.popoverController presentPopoverFromBarButtonItem:sender permittedArrowDirections:UIPopoverArrowDirectionAny animated:YES];
}

- (void) getSelectedBookmark: (NSDictionary*) aDic
{
    NSInteger spineIndex = [[aDic objectForKey:kCurrentSpineIndex] integerValue];
    NSInteger pageIndex = [[aDic objectForKey:kCurrentPageInSpineIndex] integerValue];
    [self loadSpine:spineIndex atPageIndex:pageIndex];
    [self.popoverController dismissPopoverAnimated:YES];
}

- (void) viewWillLayoutSubviews
{
    [self gotoPageInCurrentSpine:currentPageInSpineIndex];
}


@end
