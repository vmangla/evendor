//
//  YLDocument.m
//
//  Created by Kemal Taskin on 3/26/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLDocument.h"
#import "YLPageInfo.h"
#import "YLPageRenderer.h"
#import "YLGlobal.h"
#import "YLOutlineParser.h"
#import "YLDocumentScanner.h"
#import "YLAnnotationParser.h"
#import <CommonCrypto/CommonDigest.h>
#import <fcntl.h>

NSString *const kYLDocumentMetadataKeyTitle = @"Title";
NSString *const kYLDocumentMetadataKeyAuthor = @"Author";
NSString *const kYLDocumentMetadataKeySubject = @"Subject";
NSString *const kYLDocumentMetadataKeyKeywords = @"Keywords";
NSString *const kYLDocumentMetadataKeyCreator = @"Creator";
NSString *const kYLDocumentMetadataKeyProducer = @"Producer";
NSString *const kYLDocumentMetadataKeyCreationDate = @"CreationDate";
NSString *const kYLDocumentMetadataKeyModDate = @"ModDate";
NSString *const kYLDocumentMetadataKeyVersion = @"Version";

@interface YLDocument () {
    NSString *_uuid;
    NSString *_path;
    NSString *_title;
    NSString *_password;
    
    NSString *_filePath;
    NSString *_bookmarksPath;
    NSURL *_fileURL;
    
    CGPDFDocumentRef _documentRef;
    NSUInteger _pageCount;
    
    BOOL _locked;
    BOOL _validPDF;
    BOOL _isEncrypted;
    BOOL _didCheckEncryption;
    BOOL _receivedMemoryWarning;
    BOOL _bookmarksChanged;
    BOOL _metadataLoaded;
    
    YLOutlineParser *_outlineParser;
    YLDocumentScanner *_scanner;
    YLAnnotationParser *_annotationParser;

    dispatch_queue_t _syncQueue;
    dispatch_queue_t _lockQueue;
    NSMutableDictionary *_pageInfoDic;
    NSMutableDictionary *_bookmarkDic;
    NSMutableDictionary *_metadataDic;
    NSArray *_sortedBookmarks;
}

- (NSString *)uniqueID;
- (void)openPDFDocument;
- (CGPDFDocumentRef)openPDFDocumentUnlocked;
- (void)checkEncryption;
- (BOOL)isEncrypted;
- (BOOL)unlockPDFDocument:(CGPDFDocumentRef)document;
- (void)saveBookmarks;
- (void)loadBookmarks;
- (void)loadMetadata;
- (void)onMemoryWarning:(NSNotification *)notification;

@end

@implementation YLDocument

@synthesize uuid = _uuid;
@synthesize path = _path;
@synthesize title = _title;
@synthesize password = _password;
@synthesize pageCount = _pageCount;
@synthesize locked = _locked;
@synthesize validPDF = _validPDF;
@synthesize outlineParser = _outlineParser;
@synthesize scanner = _scanner;
@synthesize annotationParser = _annotationParser;

+ (YLDocument *)YLDocumentWithFilePath:(NSString *)path {
    return [[[YLDocument alloc] initWithFilePath:path] autorelease];
}

- (id)initWithFilePath:(NSString *)path {
    if(path == nil) {
        return nil;
    }
    if(![[NSFileManager defaultManager] fileExistsAtPath:path]) {
        return nil;
    }
    
    self = [super init];
    if(self) {
        _uuid = nil;
        _path = [path copy];
        _title = nil;
        _password = nil;
        _filePath = [path retain];
        _fileURL = [[NSURL fileURLWithPath:_filePath] retain];
 
        _validPDF = NO;
        const char *path = [_filePath fileSystemRepresentation];
        int fd = open(path, O_RDONLY);
        if(fd > 0) {
            const unsigned char sig[4];
            ssize_t len = read(fd, (void *)&sig, sizeof(sig));
            if(len == 4 && sig[0] == '%' && sig[1] == 'P' && sig[2] == 'D' && sig[3] == 'F') {
                _validPDF = YES;
            }
            
            close(fd);
        }
        
        _pageCount = 0;
        _locked = YES;
        _isEncrypted = NO;
        _didCheckEncryption = NO;
        _receivedMemoryWarning = NO;
        _bookmarksChanged = NO;
        _metadataLoaded = NO;
        _documentRef = NULL;
        _pageInfoDic = [[NSMutableDictionary alloc] init];
        
        _outlineParser = [[YLOutlineParser alloc] initWithDocument:self];
        _scanner = [[YLDocumentScanner alloc] initWithDocument:self];
        _annotationParser = [[YLAnnotationParser alloc] initWithDocument:self];
        
        _syncQueue = dispatch_get_global_queue(DISPATCH_QUEUE_PRIORITY_DEFAULT, 0);
        _lockQueue = dispatch_queue_create("com.yakamozlabs.pdftouch.documentlock", 0);
        
        _title = [[[_fileURL lastPathComponent] stringByDeletingPathExtension] copy];
        _uuid = [[self uniqueID] retain];
        //DLog(@"title: %@ - uuid: %@", _title, _uuid);
        [self loadBookmarks];
        if(_validPDF) {
            [self openPDFDocument];
        }
        
        [[NSNotificationCenter defaultCenter] addObserver:self 
                                                 selector:@selector(onMemoryWarning:) 
                                                     name:UIApplicationDidReceiveMemoryWarningNotification
                                                   object:nil];
    }
    
    return self;
}

- (void)dealloc {
    [[NSNotificationCenter defaultCenter] removeObserver:self];
    
    [_uuid release];
    [_path release];
    [_title release];
    [_password release];
    [_filePath release];
    [_bookmarksPath release];
    [_fileURL release];
    [_pageInfoDic release];
    [_bookmarkDic release];
    [_metadataDic release];
    [_sortedBookmarks release];
    [_outlineParser cancel];
    [_outlineParser release];
    [_scanner release];
    [_annotationParser release];
    
    dispatch_sync(_lockQueue, ^{});
    dispatch_release(_lockQueue);
    
    if(_documentRef) {
        CGPDFDocumentRelease(_documentRef);
    }
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (BOOL)isLocked {
    return _locked;
}

- (BOOL)unlockWithPassword:(NSString *)password {
    if(_documentRef == NULL) {
        return NO;
    }
    
    if(password) {
        [_password release]; 
        _password = nil;
        _password = [password copy];
    }
    
    BOOL unlocked = [self unlockPDFDocument:_documentRef];
    if(unlocked) {
        _locked = NO;
        _pageCount = CGPDFDocumentGetNumberOfPages(_documentRef);
       // DLog(@"number of pages: %lu", (unsigned long)_pageCount);
        [self loadMetadata];
        [_outlineParser parse];
    }
    
    return unlocked;
}

// page is starting from 0. CGPDFGetPage starts from 1, so we'll do the conversion here
- (YLPageInfo *)pageInfoForPage:(NSUInteger)page {
    if(page > _pageCount) {
        return nil;
    }
    
    NSUInteger CGPage = page + 1;
    YLPageInfo *pageInfo = [_pageInfoDic objectForKey:[NSNumber numberWithUnsignedInteger:CGPage]];
    if(pageInfo) {
        return pageInfo;
    }
    
    CGPDFDocumentRef documentRef = [self requestDocumentRef];
    if(documentRef == NULL) {
        return nil;
    }
    
    CGPDFDocumentRetain(documentRef);
    CGPDFPageRef pageRef = CGPDFDocumentGetPage(documentRef, CGPage);
    CGRect origRect = CGPDFPageGetBoxRect(pageRef, kCGPDFCropBox);
    int rotation = CGPDFPageGetRotationAngle(pageRef);
    pageInfo = [YLPageInfo YLPageInfoWithPage:page rect:origRect rotation:rotation];
    [_pageInfoDic setObject:pageInfo forKey:[NSNumber numberWithUnsignedInteger:CGPage]];
    
    CGPDFDocumentRelease(documentRef);
    
    return pageInfo;
}

- (CGPDFDocumentRef)requestDocumentRef {
    __block CGPDFDocumentRef documentRef = NULL;
    dispatch_sync(_lockQueue, ^{
        if(_receivedMemoryWarning) {
            _receivedMemoryWarning = NO;
            if(_documentRef != NULL) {
                CGPDFDocumentRelease(_documentRef);
                _documentRef = NULL;
                _documentRef = [self openPDFDocumentUnlocked];
            }
        }
        
        documentRef = _documentRef;
    });
    
    return documentRef;
}

- (NSString *)metadataForKey:(NSString *)key {
    if(!_metadataLoaded) {
        return nil;
    }
    
    return [_metadataDic objectForKey:key];
}

- (void)renderPage:(NSUInteger)page targetRect:(CGRect)targetRect scale:(CGFloat)scale inContext:(CGContextRef)context {
    YLPageInfo *pageInfo = [self pageInfoForPage:page];
    if(pageInfo == nil) {
        return;
    }
    
    CGPDFDocumentRef documentRef = [self requestDocumentRef];
    if(documentRef == NULL) {
        return;
    }
    
    CGPDFDocumentRetain(documentRef);
    CGPDFPageRef pageRef = CGPDFDocumentGetPage(documentRef, (page + 1));
    if(pageRef == NULL) {
        CGPDFDocumentRelease(documentRef);
        return;
    }
    
    [YLPageRenderer renderPage:pageRef inContext:context atPoint:CGPointZero withZoom:scale pageInfo:pageInfo search:nil];
    CGPDFDocumentRelease(documentRef);
}

- (void)renderPage:(NSUInteger)page targetRect:(CGRect)targetRect inContext:(CGContextRef)context {
    YLPageInfo *pageInfo = [self pageInfoForPage:page];
    if(pageInfo == nil) {
        return;
    }
    
    CGPDFDocumentRef documentRef = [self requestDocumentRef];
    if(documentRef == NULL) {
        return;
    }
    
    CGPDFDocumentRetain(documentRef);
    CGPDFPageRef pageRef = CGPDFDocumentGetPage(documentRef, (page + 1));
    if(pageRef == NULL) {
        CGPDFDocumentRelease(documentRef);
        return;
    }
    
    NSArray *searchResults = [_scanner searchResultsForPage:page];
    [YLPageRenderer renderPage:pageRef inContext:context inRectangle:targetRect pageInfo:pageInfo search:searchResults];
    CGPDFDocumentRelease(documentRef);
}

- (BOOL)addBookmarkForPage:(NSUInteger)page {
    if(page >= _pageCount) {
        return NO;
    }
    
    NSNumber *pageNumber = [NSNumber numberWithUnsignedInteger:page];
    if([_bookmarkDic objectForKey:pageNumber]) {
        return YES;
    }
    
    [_bookmarkDic setObject:@"" forKey:pageNumber];
    [self saveBookmarks];
    
    return YES;
}

- (BOOL)removeBookmarkForPage:(NSUInteger)page {
    if(page >= _pageCount) {
        return NO;
    }

    NSNumber *pageNumber = [NSNumber numberWithUnsignedInteger:page];
    if([_bookmarkDic objectForKey:pageNumber] == nil) {
        return NO;
    }
    
    [_bookmarkDic removeObjectForKey:pageNumber];
    [self saveBookmarks];
    
    return YES;
}

- (BOOL)hasBookmarkForPage:(NSUInteger)page {
    return ([_bookmarkDic objectForKey:[NSNumber numberWithUnsignedInteger:page]] != nil);
}

- (NSArray *)bookmarkedPageNumbers {
    if(_sortedBookmarks == nil || _bookmarksChanged) {
        if(_sortedBookmarks) {
            [_sortedBookmarks release];
            _sortedBookmarks = nil;
        }

        NSArray *pages = [_bookmarkDic allKeys];
        _sortedBookmarks = [[pages sortedArrayUsingComparator:^NSComparisonResult(id obj1, id obj2) {
            NSNumber *n1 = (NSNumber *)obj1;
            NSNumber *n2 = (NSNumber *)obj2;
            return [n1 compare:n2];
        }] retain];
        
        _bookmarksChanged = NO;
    }
    
    return _sortedBookmarks;
}

- (NSArray *)outline {
    return [_outlineParser outline];
}


#pragma mark -
#pragma mark Private Methods
- (NSString *)uniqueID {
    const char *ptr = [[_fileURL description] UTF8String];
    unsigned char md5Buffer[CC_MD5_DIGEST_LENGTH];
    
    CC_MD5(ptr, (CC_LONG)(strlen(ptr)), md5Buffer);
    NSMutableString *output = [NSMutableString stringWithCapacity:CC_MD5_DIGEST_LENGTH * 2];
    for(int i = 0; i < CC_MD5_DIGEST_LENGTH; i++) {
        [output appendFormat:@"%02x",md5Buffer[i]];
    }
    
    return output;
}

- (void)openPDFDocument {
    if(_documentRef != NULL) {
        return;
    }
    
    _documentRef = CGPDFDocumentCreateWithURL((CFURLRef)_fileURL);
    if(_documentRef != NULL) {
        if([self isEncrypted]) {
            _locked = YES;
        } else {
            _locked = NO;
            _pageCount = CGPDFDocumentGetNumberOfPages(_documentRef);
            //DLog(@"number of pages: %lu", (unsigned long)_pageCount);
            [self loadMetadata];
            [_outlineParser parse];
        }
    }
}

- (CGPDFDocumentRef)openPDFDocumentUnlocked {
    CGPDFDocumentRef documentRef = NULL;
    
    documentRef = CGPDFDocumentCreateWithURL((CFURLRef)_fileURL);
    if(documentRef != NULL) {
        if(_isEncrypted) {
            if(![self unlockPDFDocument:documentRef]) {
                CGPDFDocumentRelease(documentRef);
                documentRef = NULL;
            }
        }
    }
    
    return documentRef;
}

- (void)checkEncryption {
    dispatch_sync(_syncQueue, ^{
        if(!_didCheckEncryption) {
            if(_documentRef != NULL) {
                _didCheckEncryption = YES;
                
                _isEncrypted = CGPDFDocumentIsEncrypted(_documentRef);
            }
        }
    });
}

- (BOOL)isEncrypted {
    if(!_didCheckEncryption) {
        [self checkEncryption];
    }
    
    return _isEncrypted;
}

- (BOOL)unlockPDFDocument:(CGPDFDocumentRef)document {
    if(document == NULL) {
        return NO;
    }
    
    if(CGPDFDocumentUnlockWithPassword(document, "")) {
        // pdf is unlocked successfully with empty password
        return YES;
    }
    
    if(_password && [_password length] > 0) {
        char pText[128];
        [_password getCString:pText maxLength:126 encoding:NSUTF8StringEncoding];
        if(CGPDFDocumentUnlockWithPassword(document, pText)) {
            // pdf is unlocked successfully by the given password
            return YES;
        } else {
            // pdf cannot be unlocked with the given password
            return NO;
        }
    }
    
    // pdf is encrypted and no password is given
    return NO;
}

- (void)saveBookmarks {
    if(_bookmarkDic.count == 0) {
        [[NSFileManager defaultManager] removeItemAtPath:_bookmarksPath error:nil];
    } else {
        [NSKeyedArchiver archiveRootObject:_bookmarkDic toFile:_bookmarksPath];
    }
    
    _bookmarksChanged = YES;
}

- (void)loadBookmarks {
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSLibraryDirectory, NSUserDomainMask, YES);
    NSString *documentsPath = [paths objectAtIndex:0];
    documentsPath = [documentsPath stringByAppendingPathComponent:@"Private Docs/Bookmarks"];
    NSError *error = nil;
    [[NSFileManager defaultManager] createDirectoryAtPath:documentsPath withIntermediateDirectories:YES attributes:nil error:&error];
    if(error) {
        //DLog(@"Bookmarks directory could not be created: %@", error.description);
    }
    
    _bookmarksPath = [[documentsPath stringByAppendingPathComponent:[NSString stringWithFormat:@"%@.plist", _uuid]] retain];
    if([[NSFileManager defaultManager] fileExistsAtPath:_bookmarksPath]) {
        NSMutableDictionary *bookmarks = [NSKeyedUnarchiver unarchiveObjectWithFile:_bookmarksPath];
        if(bookmarks) {
            _bookmarkDic = [bookmarks retain];
        } else {
            _bookmarkDic = [[NSMutableDictionary alloc] init];
        }
    } else {
        _bookmarkDic = [[NSMutableDictionary alloc] init];
    }
    
    _sortedBookmarks = nil;
}

- (void)loadMetadata {
    if(_metadataLoaded || _documentRef == NULL) {
        return;
    }
    
    if(_metadataDic == nil) {
        _metadataDic = [[NSMutableDictionary alloc] init];
    }

    CGPDFStringRef string;
    int majorVersion, minorVersion;
    
    CGPDFDocumentGetVersion(_documentRef, &majorVersion, &minorVersion);
    NSString *version = [NSString stringWithFormat:@"%d.%d", majorVersion, minorVersion];
    [_metadataDic setObject:version forKey:kYLDocumentMetadataKeyVersion];
	
    CGPDFDictionaryRef infoDict = CGPDFDocumentGetInfo(_documentRef);
    if(infoDict) {
        if(CGPDFDictionaryGetString(infoDict, "Title", &string)) {
            CFStringRef s = CGPDFStringCopyTextString(string);
            if(s != NULL) {
                [_metadataDic setObject:(NSString *)s forKey:kYLDocumentMetadataKeyTitle];
                self.title = (NSString *)s;
                CFRelease(s);
            }
        }
        if(CGPDFDictionaryGetString(infoDict, "Author", &string)) {
            CFStringRef s = CGPDFStringCopyTextString(string);
            if(s != NULL) {
                [_metadataDic setObject:(NSString *)s forKey:kYLDocumentMetadataKeyAuthor];
                CFRelease(s);
            }
        }
        if(CGPDFDictionaryGetString(infoDict, "Keywords", &string)) {
            CFStringRef s = CGPDFStringCopyTextString(string);
            if(s != NULL) {
                [_metadataDic setObject:(NSString *)s forKey:kYLDocumentMetadataKeyKeywords];
                CFRelease(s);
            }
        }
        if(CGPDFDictionaryGetString(infoDict, "Creator", &string)) {
            CFStringRef s = CGPDFStringCopyTextString(string);
            if(s != NULL) {
                [_metadataDic setObject:(NSString *)s forKey:kYLDocumentMetadataKeyCreator];
                CFRelease(s);
            }
        }
        if(CGPDFDictionaryGetString(infoDict, "Producer", &string)) {
            CFStringRef s = CGPDFStringCopyTextString(string);
            if(s != NULL) {
                [_metadataDic setObject:(NSString *)s forKey:kYLDocumentMetadataKeyProducer];
                CFRelease(s);
            }
        }
        if(CGPDFDictionaryGetString(infoDict, "CreationDate", &string)) {
            CFDateRef date = CGPDFStringCopyDate(string);
            if(date != NULL) {
                [_metadataDic setObject:[(NSDate *)date description] forKey:kYLDocumentMetadataKeyCreationDate];
                CFRelease(date);
            }
        }
        if(CGPDFDictionaryGetString(infoDict, "ModDate", &string)) {
            CFDateRef date = CGPDFStringCopyDate(string);
            if(date != NULL) {
                [_metadataDic setObject:[(NSDate *)date description] forKey:kYLDocumentMetadataKeyModDate];
                CFRelease(date);
            }
        }
    }
    
    _metadataLoaded = YES;
}

- (void)onMemoryWarning:(NSNotification *)notification {
    _receivedMemoryWarning = YES;
}


@end
