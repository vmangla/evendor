//
//  ZeeDownloadsViewController.m
//  ZeeDownloadManager
//
//  Created by Muhammad Zeeshan on 2/3/13.
//
//  Copyright (c) 2013 Muhammad Zeeshan. All rights reserved.
//

#import "ZeeDownloadsViewController.h"
#import "UIImageView+WebCache.h"

@interface ZeeDownloadsViewController ()

@end

#define ImageTag    101
#define TitleTag    102
#define SizeTag     103
#define ProgressTag 104
#define PauseTag    105
#define CloseTag    106
#define PercentTag  107

#define fontNameUsed @"Helvetica"
#define fontSizeUsed 13.0f
#define textColorOfLabels [UIColor darkGrayColor]
#define temporaryFileDestination [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/Temporary Files"]
#define fileDestination [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/Downloaded Files"]
#define interruptedDownloadsArrayFileDestination [NSHomeDirectory() stringByAppendingPathComponent:@"/Documents/InterruptedDownloadsFile/interruptedDownloads.txt"]
#define keyForTitle @"fileTitle"
#define keyForFileHandler @"filehandler"
#define keyForTimeInterval @"timeInterval"
#define keyForTotalFileSize @"totalfilesize"
#define keyForFileSizeInUnits @"fileSizeInUnits"
#define keyForRemainingFileSize @"remainigFileSize"

@implementation ZeeDownloadsViewController
@synthesize delegate, currentInfoDict;

-(id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self)
    {
        // Custom initialization
        [self initializeDownloadingArrayIfNot];
    }
    return self;
}

-(void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    self.navigationItem.title = @"Downloading Books";
}

- (void) viewWillAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
}

- (NSInteger) downloadingCount
{
    return [downloadingArray count];
}

-(void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}
-(void)dealloc
{
    [currentInfoDict release];
    [zeeDownloadTableView release];
    [downloadingArray release];
    [downloadingRequestsQueue release];
    [super dealloc];
}

#pragma mark - Tableview delegate and dateasource -
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    return 130.0;
}

-(NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    return downloadingArray.count;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    NSString *cellIdentifier = [NSString stringWithFormat:@"Cell"];
    if(downloadingArray.count != 0)
        cellIdentifier = [NSString stringWithFormat:@"Cell-%d-%d%@",indexPath.section,indexPath.row,[[NSProcessInfo processInfo] globallyUniqueString]];
    
    UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==NULL)
    {
        NSString *nibName = @"DownloadCell";
        NSArray *nib = [[NSBundle mainBundle]loadNibNamed:nibName owner:self options:nil];
        cell = [nib objectAtIndex:0];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
        cell.accessoryType = UITableViewCellAccessoryNone;
    }
    if (indexPath.row%2 != 0)
        cell.contentView.backgroundColor = [UIColor lightGrayColor];
    
    //NSDictionary    *aDic = [downloadingArray objectAtIndex:indexPath.row];
    // Book Image
    UIImageView *bookImage = (UIImageView*)[cell.contentView viewWithTag:ImageTag];
    bookImage.image = nil;
    // Title
    UILabel *title = (UILabel*) [cell.contentView viewWithTag:TitleTag];
    title.text = @"";
    // Size
    UILabel *size = (UILabel*) [cell.contentView viewWithTag:SizeTag];
    size.text = @"Calculating...";
    // Progress Bar
    UIProgressView  *progressBar = (UIProgressView*)[cell.contentView viewWithTag:ProgressTag];
    progressBar.progress = 0.0;
    
    //sud
    UIButton *pauseBtn = (UIButton*)[cell.contentView viewWithTag:PauseTag];
    pauseBtn.hidden = YES;
    // Pause Btn
//    UIButton *pauseBtn = (UIButton*)[cell.contentView viewWithTag:PauseTag];
//    pauseBtn.tag = indexPath.row;
//    [pauseBtn addTarget:self action:@selector(pauseClicked:) forControlEvents:UIControlEventTouchUpInside];
//    // Close Btn
    UIButton *closeBtn = (UIButton*)[cell.contentView viewWithTag:CloseTag];
    closeBtn.tag = indexPath.row;
    [closeBtn addTarget:self action:@selector(closeClicked:) forControlEvents:UIControlEventTouchUpInside];
    
  //  UIButton *closeBtn = (UIButton*)[cell.contentView viewWithTag:CloseTag];
   // closeBtn.hidden = YES;
    // Size
    UILabel *percentage = (UILabel*) [cell.contentView viewWithTag:PercentTag];
    percentage.text = [NSString stringWithFormat:@"%.2f%% Done", 0.0];
    
    if(downloadingArray.count != 0)
        [self updateProgressForCell:cell withRequest:[downloadingArray objectAtIndex:indexPath.row]];
    
    return cell;
}

#pragma mark - Pause/Close
- (void) pauseClicked:(id) sender
{
    UIButton *aBtn = (UIButton*)sender;
    UITableViewCell *cell = (UITableViewCell*)[aBtn findSuperViewWithClass:[UITableViewCell class]];
    NSIndexPath *indexPath = [zeeDownloadTableView indexPathForCell:cell];
    
    aBtn.selected = !aBtn.selected;
    if(aBtn.selected == YES)
    {
        // Pause
        if([self.delegate respondsToSelector:@selector(downloadRequestPaused:)])
            [self.delegate downloadRequestPaused:[downloadingArray objectAtIndex:indexPath.row]];
        if(downloadingArray.count != 0)
            [[downloadingArray objectAtIndex:indexPath.row] cancel];
    }
    else
    {
        [self resumeInterruptedDownloads:indexPath :[[[downloadingArray objectAtIndex:indexPath.row]url]absoluteString]];
    }
}

- (void) closeClicked:(id) sender
{
    UIButton *aBtn = (UIButton*)sender;
    UITableViewCell *cell = (UITableViewCell*)[aBtn findSuperViewWithClass:[UITableViewCell class]];
    NSIndexPath *indexPath = [zeeDownloadTableView indexPathForCell:cell];

    [self removeURLStringFromInterruptedDownloadFileIfRequestCancelByTheUser:[[[downloadingArray objectAtIndex:indexPath.row]url]absoluteString]];
    NSError *error=NULL;
    [[NSFileManager defaultManager] removeItemAtPath:[[downloadingArray objectAtIndex:indexPath.row] temporaryFileDownloadPath] error:&error];
    if(error)
        NSLog(@"Error while deleting filehandle %@",error);
    
    if([self.delegate respondsToSelector:@selector(downloadRequestCanceled:)])
        [self.delegate downloadRequestCanceled:[downloadingArray objectAtIndex:indexPath.row]];
    if(downloadingArray.count != 0)
        [[downloadingArray objectAtIndex:indexPath.row] cancel];
    if(downloadingArray.count != 0)
        [self removeRequest:[downloadingArray objectAtIndex:indexPath.row] :indexPath];
}


#pragma mark -
-(void)initializeDownloadingArrayIfNot
{
    if(!downloadingArray)
        downloadingArray = [[NSMutableArray alloc] init];
}

-(void)createDirectoryIfNotExistAtPath:(NSString *)path
{
    //NSLog(@"Directory path %@",path);
    NSError *error = NULL;
    if (![[NSFileManager defaultManager] fileExistsAtPath:path])
        [[NSFileManager defaultManager] createDirectoryAtPath:path withIntermediateDirectories:NO attributes:nil error:&error];
    if(error)
        NSLog(@"Error while creating directory %@",[error localizedDescription]);
}

-(void)createTemporaryFile:(NSString *)path
{
    //NSLog(@"Directory path %@",path);
    if(![[NSFileManager defaultManager] fileExistsAtPath:path])
    {
        BOOL success = [[NSFileManager defaultManager] createFileAtPath:path contents:Nil attributes:Nil];
        if(!success)
            NSLog(@"Failed to create file");
//        else
//            NSLog(@"success");
    }
}


-(ASIHTTPRequest *)initializeRequestAndSetProperties:(NSString *)urlString isResuming:(BOOL)isResuming
{
    NSURL *url = [NSURL URLWithString:urlString];
    ASIHTTPRequest *request = [[ASIHTTPRequest alloc] initWithURL:url];
    
    [request setDelegate:self];
    [request setDownloadProgressDelegate:self];
    [request setAllowResumeForFileDownloads:YES];
    [request setShouldContinueWhenAppEntersBackground:YES];
    [request setNumberOfTimesToRetryOnTimeout:3];
    [request setTimeOutSeconds:180.0];
    if(!request.userInfo)
        request.userInfo = [[NSMutableDictionary alloc] initWithDictionary:self.currentInfoDict];
    NSString *fileName = [request.userInfo objectForKey:keyForTitle];
    if(!fileName)
    {
        fileName = [request.url.absoluteString lastPathComponent];
        [request.userInfo setValue:fileName forKey:keyForTitle];
    }
    /////
    NSString *newstr =[[NSString alloc] init];
    NSRange aRange = [[request.userInfo objectForKey:@"file_name"] rangeOfString:@"pdf" options:NSCaseInsensitiveSearch];
    if (aRange.location == NSNotFound)
        newstr = [fileName stringByAppendingString:@".epub"];
    else
        newstr = [fileName stringByAppendingString:@".pdf"];
    NSString *temporaryDestinationPath = [NSString stringWithFormat:@"%@/%@",temporaryFileDestination,newstr];
    /////
    //NSString *temporaryDestinationPath = [NSString stringWithFormat:@"%@/%@.download",temporaryFileDestination,fileName];
    [request setTemporaryFileDownloadPath:temporaryDestinationPath];
    if(!isResuming)
        [self createTemporaryFile:request.temporaryFileDownloadPath];

    [request setDownloadDestinationPath:[NSString stringWithFormat:@"%@/%@",fileDestination,newstr]];
    //[request setDownloadDestinationPath:[NSString stringWithFormat:@"%@/%@",fileDestination,fileName]];
    [request setDidFinishSelector:@selector(requestDone:)];
    [request setDidFailSelector:@selector(requestWentWrong:)];
    [self initializeDownloadingRequestsQueueIfNot];
    return request;
}

-(void)addDownloadRequest:(NSString *)urlString
{
    [self initializeDownloadingArrayIfNot];
    [self createDirectoryIfNotExistAtPath:temporaryFileDestination];
    [self createDirectoryIfNotExistAtPath:fileDestination];
    
    [self createDirectoryIfNotExistAtPath:[interruptedDownloadsArrayFileDestination stringByDeletingLastPathComponent]];
    [self createTemporaryFile:interruptedDownloadsArrayFileDestination];
    [self writeURLStringToFileIfNotExistForResumingPurpose:urlString];
    
    [self insertTableviewCellForRequest:[self initializeRequestAndSetProperties:urlString isResuming:NO]];
}
-(void)initializeDownloadingRequestsQueueIfNot
{
    if(!downloadingRequestsQueue)
        downloadingRequestsQueue = [[NSOperationQueue alloc] init];
}
//-(void)updateProgressForCell:(UITableViewCell *)cell withRequest:(ASIHTTPRequest *)request
//{
//    NSFileHandle *fileHandle = [request.userInfo objectForKey:keyForFileHandler];
//    if(fileHandle)
//    {
//        unsigned long long partialContentLength = [fileHandle offsetInFile];
//        unsigned long long totalContentLenght = [[request.userInfo objectForKey:keyForTotalFileSize] unsignedLongLongValue];
//        unsigned long long remainingContentLength = totalContentLenght - partialContentLength;
//        
//        NSTimeInterval downloadTime = -1 * [[request.userInfo objectForKey:keyForTimeInterval] timeIntervalSinceNow];
//        
//        float speed = (partialContentLength - (totalContentLenght - [[request.userInfo objectForKey:keyForRemainingFileSize] unsignedLongLongValue])) / downloadTime;
//        
//        int remainingTime = (int)(remainingContentLength / speed);
//		int hours = remainingTime / 3600;
//		int minutes = (remainingTime - hours * 3600) / 60;
//		int seconds = remainingTime - hours * 3600 - minutes * 60;
//        
//        NSString *remainingTimeStr = [NSString stringWithFormat:@""];
//        
//        if(hours>0)
//            remainingTimeStr = [remainingTimeStr stringByAppendingFormat:@"%d Hours ",hours];
//        if(minutes>0)
//            remainingTimeStr = [remainingTimeStr stringByAppendingFormat:@"%d Min ",minutes];
//        if(seconds>0)
//            remainingTimeStr = [remainingTimeStr stringByAppendingFormat:@"%d sec",seconds];
//        
//        float percentComplete = (float)partialContentLength/totalContentLenght*100;
//        float progressForProgressView = percentComplete / 100;
//        
//        // Size
//        NSString *fileSizeInUnits = [request.userInfo objectForKey:keyForFileSizeInUnits];
//        if(!fileSizeInUnits)
//        {
//            fileSizeInUnits = [NSString stringWithFormat:@"%.2f %@",
//                               [self calculateFileSizeInUnit:totalContentLenght],
//                               [self calculateUnit:totalContentLenght]];
//            [request.userInfo setValue:fileSizeInUnits forKey:keyForFileSizeInUnits];
//        }
//        UILabel *size = (UILabel*) [cell.contentView viewWithTag:SizeTag];
//        size.text = [NSString stringWithFormat:@"%@", fileSizeInUnits];
//        //NSLog(@"fileSizeInUnits = %@", fileSizeInUnits);
//        // Percent
//        UILabel *percent = (UILabel*) [cell.contentView viewWithTag:PercentTag];
//        percent.text = [NSString stringWithFormat:@"%.2f%% Done", percentComplete];
//        //NSLog(@"percentComplete = %.2f%%", percentComplete)
//        // Progress
//        UIProgressView  *progressBar = (UIProgressView*)[cell.contentView viewWithTag:ProgressTag];
//        progressBar.progress = progressForProgressView;
//        //NSLog(@"progressForProgressView = %.2f", progressForProgressView);
//        // Image
//        UIImageView *imgView = (UIImageView*) [cell.contentView viewWithTag:ImageTag];
//        [imgView setImageWithURL:[NSURL  URLWithString:[request.userInfo objectForKey:kProductThumbnail]]];
//        // Title
//        UILabel *title = (UILabel*) [cell.contentView viewWithTag:TitleTag];
//        title.text = [NSString stringWithFormat:@"%@", [request.userInfo objectForKey:kTitle]];
//    }
//}

-(void)updateProgressForCell:(UITableViewCell *)cell withRequest:(ASIHTTPRequest *)request
{
    NSFileHandle *fileHandle = [request.userInfo objectForKey:keyForFileHandler];
    if(fileHandle)
    {
        unsigned long long partialContentLength = [fileHandle offsetInFile];
        unsigned long long totalContentLenght = [[request.userInfo objectForKey:keyForTotalFileSize] unsignedLongLongValue];
        
        float percentComplete = (float)partialContentLength/totalContentLenght*100.0;
        float progressForProgressView = percentComplete / 100.0;
        
        // Size
        NSString *fileSizeInUnits = [request.userInfo objectForKey:keyForFileSizeInUnits];
        if(!fileSizeInUnits)
        {
            fileSizeInUnits = [NSString stringWithFormat:@"%.2f %@",
                               [self calculateFileSizeInUnit:totalContentLenght],
                               [self calculateUnit:totalContentLenght]];
            [request.userInfo setValue:fileSizeInUnits forKey:keyForFileSizeInUnits];
        }
        
        UILabel *size = (UILabel*) [cell.contentView viewWithTag:SizeTag];
        size.text = [NSString stringWithFormat:@"%@", fileSizeInUnits];
        //NSLog(@"fileSizeInUnits = %@", fileSizeInUnits);
        // Percent
        UILabel *percent = (UILabel*) [cell.contentView viewWithTag:PercentTag];
        percent.text = [NSString stringWithFormat:@"%.2f%% Done", percentComplete];
        //NSLog(@"percentComplete = %.2f%%", percentComplete)
        // Progress
        UIProgressView  *progressBar = (UIProgressView*)[cell.contentView viewWithTag:ProgressTag];
        progressBar.progress = progressForProgressView;
        //NSLog(@"progressForProgressView = %.2f", progressForProgressView);
        // Image
        UIImageView *imgView = (UIImageView*) [cell.contentView viewWithTag:ImageTag];
        NSString *urlString = [request.userInfo objectForKey:kProductThumbnail];
       //19 jul
//        dispatch_async(dispatch_get_global_queue(0,0), ^{
//            
//            NSData * data = [[NSData alloc] initWithContentsOfURL: [NSURL  URLWithString:urlString]];
//            if ( data == nil )
//            {
//                imgView.image  = [UIImage imageNamed:@"NoImageFound.png"];
//                //[self.bookImageV setContentMode:UIViewContentModeScaleAspectFit];
//            }
//            else
//            {
//                dispatch_async(dispatch_get_main_queue(), ^{
//                    //img = [UIImage imageWithData: data];
//                    imgView.image = [UIImage imageWithData: data];
//                    //[self.bookImageV setContentMode:UIViewContentModeScaleAspectFit];
//                });
//            }
//            
//        });
        [imgView sd_setImageWithURL:[NSURL URLWithString:urlString]
                     placeholderImage:[UIImage imageNamed:@"placeholder.png"] options:SDWebImageRefreshCached];
       // [imgView setImageWithURL:];
        // Title
        UILabel *title = (UILabel*) [cell.contentView viewWithTag:TitleTag];
        title.text = [NSString stringWithFormat:@"%@", [request.userInfo objectForKey:kTitle]];
    }
}

-(void)resumeInterruptedDownloads:(NSIndexPath *)indexPath :(NSString *)urlString
{
    ASIHTTPRequest *request = [self initializeRequestAndSetProperties:urlString isResuming:YES];
    unsigned long long size = [[[NSFileManager defaultManager] attributesOfItemAtPath:request.temporaryFileDownloadPath error:Nil] fileSize];
    if(size != 0)
    {
        NSString* range = @"bytes=";
        range = [range stringByAppendingString:[[NSNumber numberWithInt:(int)size] stringValue]];
        range = [range stringByAppendingString:@"-"];
        [request addRequestHeader:@"Range" value:range];
    }
    if(indexPath)
    {
        [downloadingArray replaceObjectAtIndex:indexPath.row withObject:request];
        [downloadingRequestsQueue addOperation:request];
    }
    else
        [self insertTableviewCellForRequest:request];
}

-(void)insertTableviewCellForRequest:(ASIHTTPRequest *)request
{
    if(downloadingArray.count == 0)
    {
        [downloadingArray addObject:request];
        [downloadingRequestsQueue addOperation:request];
        [zeeDownloadTableView reloadData];
    }
    else
    {
        [downloadingArray addObject:request];
        [downloadingRequestsQueue addOperation:request];
        [zeeDownloadTableView insertRowsAtIndexPaths:[NSArray arrayWithObject:[NSIndexPath indexPathForRow:downloadingArray.count-1 inSection:0]] withRowAnimation:UITableViewRowAnimationAutomatic];
    }
}
-(float)calculateFileSizeInUnit:(unsigned long long)contentLength
{
    if(contentLength >= pow(1024, 3))
        return (float) (contentLength / pow(1024, 3));
    else if(contentLength >= pow(1024, 2))
        return (float) (contentLength / pow(1024, 2));
    else if(contentLength >= 1024)
        return (float) (contentLength / 1024);
    else
        return (float) (contentLength);
}
-(NSString *)calculateUnit:(unsigned long long)contentLength
{
    if(contentLength >= pow(1024, 3))
        return @"GB";
    else if(contentLength >= pow(1024, 2))
        return @"MB";
    else if(contentLength >= 1024)
        return @"KB";
    else
        return @"Bytes";
}
-(void)writeURLStringToFileIfNotExistForResumingPurpose:(NSString *)urlString
{
    NSMutableArray *interruptedDownloads = [NSMutableArray arrayWithContentsOfFile:interruptedDownloadsArrayFileDestination];
    if(!interruptedDownloads)
        interruptedDownloads = [[NSMutableArray alloc] init];
    if(![interruptedDownloads containsObject:urlString])
    {
        [interruptedDownloads addObject:urlString];
        [interruptedDownloads writeToFile:interruptedDownloadsArrayFileDestination atomically:YES];
    }
}

- (BOOL)isRequestExistInQueue:(NSString *)urlStringRequest{
    NSMutableArray *interruptedDownloads = [NSMutableArray arrayWithContentsOfFile:interruptedDownloadsArrayFileDestination];
    
    if([interruptedDownloads containsObject:urlStringRequest])
    {
        return YES;
    }
    return NO;
}

-(void)removeURLStringFromInterruptedDownloadFileIfRequestCancelByTheUser:(NSString *)urlString
{
    NSMutableArray *interruptedDownloads = [NSMutableArray arrayWithContentsOfFile:interruptedDownloadsArrayFileDestination];
    [interruptedDownloads removeObject:urlString];
    [interruptedDownloads writeToFile:interruptedDownloadsArrayFileDestination atomically:YES];
}
-(void)removeRequest:(ASIHTTPRequest *)request :(NSIndexPath *)indexPath
{
    [downloadingArray removeObject:request];
//    if(downloadingArray.count == 0)
//        [zeeDownloadTableView reloadRowsAtIndexPaths:[NSArray arrayWithObject:indexPath] withRowAnimation:UITableViewRowAnimationAutomatic];
//    else
        [zeeDownloadTableView deleteRowsAtIndexPaths:[NSArray arrayWithObject:indexPath] withRowAnimation:UITableViewRowAnimationAutomatic];
}

-(void)showAlertViewWithMessage:(NSString *)message
{
    UIAlertView *alertView = [[UIAlertView alloc] initWithTitle:@"" message:message delegate:self cancelButtonTitle:@"Dismiss" otherButtonTitles:nil];
    [alertView show];
}

-(void)resumeAllInterruptedDownloads
{
    [self initializeDownloadingArrayIfNot];
    NSMutableArray *tempArray = [NSMutableArray arrayWithContentsOfFile:interruptedDownloadsArrayFileDestination];
    for(int i=0;i<tempArray.count;i++)
        [self resumeInterruptedDownloads:nil :[tempArray objectAtIndex:i]];
}

#pragma mark - ASIHTTPRequest Delegate -
-(void)requestStarted:(ASIHTTPRequest *)request
{
    if([self.delegate respondsToSelector:@selector(downloadRequestStarted:)])
        [self.delegate downloadRequestStarted:request];
}

-(void)request:(ASIHTTPRequest *)request didReceiveResponseHeaders:(NSDictionary *)responseHeaders
{
    [downloadingArray enumerateObjectsUsingBlock:^(ASIHTTPRequest *req, NSUInteger index, BOOL *stop){
        if([req isEqual:request])
        {
            NSFileHandle *fileHandle = [req.userInfo objectForKey:keyForFileHandler];
            if(!fileHandle)
            {
                if(![req requestHeaders])
                {
                    fileHandle = [NSFileHandle fileHandleForWritingAtPath:req.temporaryFileDownloadPath];
                    [req.userInfo setValue:fileHandle forKey:keyForFileHandler];
                }
            }
            long long length = [[req.userInfo objectForKey:keyForTotalFileSize] longLongValue];
            if(length == 0)
            {
                length = [req contentLength];
                if (length != NSURLResponseUnknownLength)
                {
                    NSNumber *totalSize = [NSNumber numberWithUnsignedLongLong:(unsigned long long)length];
                    [req.userInfo setValue:totalSize forKey:keyForTotalFileSize];
                }
                [req.userInfo setValue:[NSDate date] forKey:keyForTimeInterval];
            }
            if([request requestHeaders])
            {
                NSString *range = [[request requestHeaders] objectForKey:@"Range"];
                NSString *numbers = [range stringByTrimmingCharactersInSet:[[NSCharacterSet decimalDigitCharacterSet] invertedSet]];
                unsigned long long size = [numbers longLongValue];
                
                if(length != 0)
                {
                    [req.userInfo setValue:[NSNumber numberWithUnsignedLongLong:length] forKey:keyForRemainingFileSize];
                    length = length + size;
                    NSNumber *totalSize = [NSNumber numberWithUnsignedLongLong:(unsigned long long)length];
                    [req.userInfo setValue:totalSize forKey:keyForTotalFileSize];
                    
                    
                    fileHandle = [NSFileHandle fileHandleForUpdatingAtPath:req.temporaryFileDownloadPath];
                    [req.userInfo setValue:fileHandle forKey:keyForFileHandler];
                    [fileHandle seekToFileOffset:size];
                }
            }
            [self updateProgressForCell:[zeeDownloadTableView cellForRowAtIndexPath:[NSIndexPath indexPathForRow:index inSection:0]] withRequest:req];
            //if([self.delegate respondsToSelector:@selector(downloadRequestReceivedResponseHeaders:responseHeaders:)])
                //[self.delegate downloadRequestReceivedResponseHeaders:request responseHeaders:responseHeaders];
            *stop = YES;
        }
    }];
}

-(void)request:(ASIHTTPRequest *)request didReceiveData:(NSData *)data
{
    [downloadingArray enumerateObjectsUsingBlock:^(ASIHTTPRequest *req, NSUInteger index, BOOL *stop){
        if([req isEqual:request])
        {
            NSFileHandle *fileHandle = [req.userInfo objectForKey:keyForFileHandler];
			[fileHandle writeData:data];
            
            [self updateProgressForCell:[zeeDownloadTableView cellForRowAtIndexPath:[NSIndexPath indexPathForRow:index inSection:0]] withRequest:req];
            *stop = YES;
        }
    }];
}

-(void)requestDone:(ASIHTTPRequest *)request
{
    [self removeURLStringFromInterruptedDownloadFileIfRequestCancelByTheUser:request.url.absoluteString];
    [self removeRequest:request :[NSIndexPath indexPathForRow:[downloadingArray indexOfObject:request] inSection:0]];
    if([self.delegate respondsToSelector:@selector(downloadRequestFinished:)])
        [self.delegate downloadRequestFinished:request];
    // Pop
    if([self downloadingCount]==0)
    {
        [self.navigationController popViewControllerAnimated:YES];
    }
}

- (void)requestWentWrong:(ASIHTTPRequest *)request
{
    if([request.error.localizedDescription isEqualToString:@"The request was cancelled"])
    {
        
    }
    else
    {
        [Utils showOKAlertWithTitle:kAlertTitle message:[NSString stringWithFormat:@"\'%@\' failed to download, please try again.",[request.userInfo objectForKey:kTitle]]];
        UITableViewCell *cell = [zeeDownloadTableView cellForRowAtIndexPath:[NSIndexPath indexPathForRow:[downloadingArray indexOfObject:request] inSection:0]];
        NSIndexPath *indexPath = [zeeDownloadTableView indexPathForCell:cell];
        
        [self removeURLStringFromInterruptedDownloadFileIfRequestCancelByTheUser:[[[downloadingArray objectAtIndex:indexPath.row]url]absoluteString]];
        NSError *error=NULL;
        [[NSFileManager defaultManager] removeItemAtPath:[[downloadingArray objectAtIndex:indexPath.row] temporaryFileDownloadPath] error:&error];
        if(error)
            NSLog(@"Error while deleting filehandle %@",error);
        
        if([self.delegate respondsToSelector:@selector(downloadRequestCanceled:)])
            [self.delegate downloadRequestCanceled:[downloadingArray objectAtIndex:indexPath.row]];
        if(downloadingArray.count != 0)
            [[downloadingArray objectAtIndex:indexPath.row] cancel];
        if(downloadingArray.count != 0)
            [self removeRequest:[downloadingArray objectAtIndex:indexPath.row] :indexPath];
        
//        [self showAlertViewWithMessage:request.error.localizedDescription];
//        UITableViewCell *cell = [zeeDownloadTableView cellForRowAtIndexPath:[NSIndexPath indexPathForRow:[downloadingArray indexOfObject:request] inSection:0]];
//        [cell.subviews enumerateObjectsUsingBlock:^(UIView *cellSubview, NSUInteger index, BOOL *stop){
//            if(cellSubview.tag == 1000)
//            {
//                UIButton *pauseButton = (UIButton *)cellSubview;
//                [pauseButton setTitle:@"Retry" forState:UIControlStateNormal];
//                *stop = YES;
//            }
//        }];
    }
    if([self.delegate respondsToSelector:@selector(downloadRequestFailed:)])
        [self.delegate downloadRequestFailed:request];
}


@end
