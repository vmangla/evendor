//
//  YLCache.m
//
//  Created by Kemal Taskin on 3/28/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLCache.h"
#import "YLDocument.h"
#import "GCDMulticastDelegate.h"
#import "YLLoadOperation.h"
#import "YLRenderOperation.h"
#import "YLWriteOperation.h"
#import "YLExtractTextOperation.h"
#import "YLThumbnailCell.h"
#import "YLFontHelper.h"

// 10 * 1024 * 1024
#define CACHE_LIMIT         10485760

@interface YLCacheContext : NSObject {
    NSString *_uuid;
    NSMutableIndexSet *_renderingSmallPages;
    NSMutableIndexSet *_renderingThumbPages;
    NSMutableIndexSet *_renderingOrigPages;
    NSMutableIndexSet *_loadingSmallPages;
    NSMutableIndexSet *_loadingThumbPages;
    NSMutableIndexSet *_loadingOrigPages;
    
    dispatch_queue_t _lockQueue;
}

- (id)initWithDocumentUUID:(NSString *)uuid;

- (BOOL)shouldLoadPage:(NSUInteger)page size:(YLPDFImageSize)size;
- (BOOL)shouldRenderPage:(NSUInteger)page size:(YLPDFImageSize)size;
- (void)stoppedLoadingPage:(NSUInteger)page size:(YLPDFImageSize)size;
- (void)stoppedRenderingPage:(NSUInteger)page size:(YLPDFImageSize)size;
- (void)reset;

@end

@implementation YLCacheContext

- (id)initWithDocumentUUID:(NSString *)uuid {
    self = [super init];
    if(self) {
        _uuid = [uuid copy];
        _renderingSmallPages = [[NSMutableIndexSet alloc] init];
        _renderingThumbPages = [[NSMutableIndexSet alloc] init];
        _renderingOrigPages = [[NSMutableIndexSet alloc] init];
        _loadingSmallPages = [[NSMutableIndexSet alloc] init];
        _loadingThumbPages = [[NSMutableIndexSet alloc] init];
        _loadingOrigPages = [[NSMutableIndexSet alloc] init];
        
        _lockQueue = dispatch_queue_create("com.yakamozlabs.ylpdfkit.ylcachecontext", 0);
    }
    
    return self;
}

- (void)dealloc {
    dispatch_sync(_lockQueue, ^{});
    dispatch_release(_lockQueue);
    [_uuid release];
    [_renderingSmallPages release];
    [_renderingThumbPages release];
    [_renderingOrigPages release];
    [_loadingSmallPages release];
    [_loadingThumbPages release];
    [_loadingOrigPages release];
    
    [super dealloc];
}

- (BOOL)shouldLoadPage:(NSUInteger)page size:(YLPDFImageSize)size {
    __block BOOL shouldLoad = NO;
    dispatch_sync(_lockQueue, ^{
        NSMutableIndexSet *indexSet;
        if(size == YLPDFImageSizeSmall) {
            indexSet = _loadingSmallPages;
        } else if(size == YLPDFImageSizeThumbnail) {
            indexSet = _loadingThumbPages;
        } else {
            indexSet = _loadingOrigPages;
        }
        shouldLoad = ![indexSet containsIndex:page];
        if(shouldLoad) {
            [indexSet addIndex:page];
        }
    });
    
    return shouldLoad;
}

- (BOOL)shouldRenderPage:(NSUInteger)page size:(YLPDFImageSize)size {
    __block BOOL shouldRender = NO;
    dispatch_sync(_lockQueue, ^{
        NSMutableIndexSet *indexSet;
        if(size == YLPDFImageSizeSmall) {
            indexSet = _renderingSmallPages;
        } else if(size == YLPDFImageSizeThumbnail) {
            indexSet = _renderingThumbPages;
        } else {
            indexSet = _renderingOrigPages;
        }
        shouldRender = ![indexSet containsIndex:page];
        if(shouldRender) {
            [indexSet addIndex:page];
        }
    });
    
    return shouldRender;
}

- (void)stoppedLoadingPage:(NSUInteger)page size:(YLPDFImageSize)size {
    dispatch_async(_lockQueue, ^{
        NSMutableIndexSet *indexSet;
        if(size == YLPDFImageSizeSmall) {
            indexSet = _loadingSmallPages;
        } else if(size == YLPDFImageSizeThumbnail) {
            indexSet = _loadingThumbPages;
        } else {
            indexSet = _loadingOrigPages;
        }
        [indexSet removeIndex:page];
    });
}

- (void)stoppedRenderingPage:(NSUInteger)page size:(YLPDFImageSize)size {
    dispatch_async(_lockQueue, ^{
        NSMutableIndexSet *indexSet;
        if(size == YLPDFImageSizeSmall) {
            indexSet = _renderingSmallPages;
        } else if(size == YLPDFImageSizeThumbnail) {
            indexSet = _renderingThumbPages;
        } else {
            indexSet = _renderingOrigPages;
        }
        [indexSet removeIndex:page];
    });
}

- (void)reset {
    dispatch_async(_lockQueue, ^{
        [_loadingSmallPages removeAllIndexes];
        [_loadingThumbPages removeAllIndexes];
        [_loadingOrigPages removeAllIndexes];
        [_renderingSmallPages removeAllIndexes];
        [_renderingThumbPages removeAllIndexes];
        [_renderingOrigPages removeAllIndexes];
    });
}

@end


@interface YLCache () {
    NSCache *_thumbCache;
    
    GCDMulticastDelegate<YLCacheDelegate> *_multicastDelegate;
    NSMutableDictionary *_callerDic;
    NSMutableDictionary *_cacheDirDic;
    NSMutableDictionary *_imageSizeDic;
    NSMutableDictionary *_cacheContextDic;
    
    NSString *_cacheDirectory;
    BOOL _caching;
    
    NSOperationQueue *_renderQueue;
    NSOperationQueue *_loadQueue;
}

- (void)setupCacheDirectory;
- (void)setupCacheDirectoryForDocument:(YLDocument *)document;
- (NSString *)pathForDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size;
- (NSString *)pathForDocument:(YLDocument *)document;
- (NSString *)cacheKeyForDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size;
- (YLCacheContext *)cacheContextForDocument:(YLDocument *)document;
- (void)removeCacheContextForDocument:(YLDocument *)document;
- (BOOL)isFastDevice;
- (NSString *)sizeToString:(YLPDFImageSize)size;

- (void)pauseCaching;
- (void)resumeCaching;

- (void)addLoadOperation:(YLLoadOperation *)operation;
- (void)addWriteOperation:(YLWriteOperation *)operation;
- (void)addRenderOperation:(YLRenderOperation *)operation;

@end

@implementation YLCache

+ (YLCache *)sharedCache {
    static YLCache *sharedCache = nil;
    static dispatch_once_t onceToken;
    dispatch_once(&onceToken, ^{
        sharedCache = [[YLCache alloc] init];
    });
    
    return sharedCache;
}

- (id)init {
    self = [super init];
    if(self) {
        _thumbCache = [[NSCache alloc] init];
        [_thumbCache setTotalCostLimit:CACHE_LIMIT];
        [_thumbCache setDelegate:self];
        
        _multicastDelegate = (GCDMulticastDelegate<YLCacheDelegate> *)[[GCDMulticastDelegate alloc] init];
        
        _callerDic = nil;
        _cacheDirDic = nil;
        _imageSizeDic = [[NSMutableDictionary alloc] initWithCapacity:3];
        [_imageSizeDic setObject:@"O" forKey:[NSNumber numberWithInt:YLPDFImageSizeOriginal]];
        [_imageSizeDic setObject:@"S" forKey:[NSNumber numberWithInt:YLPDFImageSizeSmall]];
        [_imageSizeDic setObject:@"T" forKey:[NSNumber numberWithInt:YLPDFImageSizeThumbnail]];
        _cacheContextDic = nil;
        _caching = YES;
        _cacheDirectory = nil;
        
        _loadQueue = [[NSOperationQueue alloc] init];
        [_loadQueue setName:@"YLPDFKit Load Queue"];
        [_loadQueue setMaxConcurrentOperationCount:NSOperationQueueDefaultMaxConcurrentOperationCount];
        
        _renderQueue = [[NSOperationQueue alloc] init];
        [_renderQueue setName:@"YLPDFKit Render Queue"];
        [_renderQueue setMaxConcurrentOperationCount:1];
        
        [self setupCacheDirectory];
    }
    
    return self;
}

- (void)dealloc {
    [_thumbCache removeAllObjects];
    [_thumbCache release];
    [_multicastDelegate release];
    [_callerDic release];
    [_cacheDirDic release];
    [_imageSizeDic release];
    [_cacheContextDic release];
    [_cacheDirectory release];
    
    [_loadQueue cancelAllOperations];
    [_loadQueue release];
    [_renderQueue cancelAllOperations];
    [_renderQueue release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)observeValueForKeyPath:(NSString *)keyPath ofObject:(id)object change:(NSDictionary *)change context:(void *)context {
    if([object isKindOfClass:[YLLoadOperation class]]) {
        if([keyPath isEqualToString:@"isFinished"]) {
            YLLoadOperation *operation = (YLLoadOperation *)object;
            [operation removeObserver:self forKeyPath:@"isFinished"];
            
            if([operation isCancelled]) {
                return;
            }
            
            YLCacheContext *cacheContext = [self cacheContextForDocument:operation.document];
            [cacheContext stoppedLoadingPage:operation.page size:operation.size];
            
            UIImage *image = [operation image];
            if(image == nil) {
                return;
            }
            
            NSString *key = [self cacheKeyForDocument:operation.document page:operation.page size:operation.size];
            [_thumbCache setObject:image forKey:key];
            
            //DLog(@"DID LOAD IMAGE FOR PAGE: %d - SIZE: %@", operation.page, [self sizeToString:operation.size]);
            
            [_multicastDelegate didCacheDocument:operation.document
                                            page:operation.page
                                            size:operation.size
                                           image:operation.image];
        }
    } else if([object isKindOfClass:[YLRenderOperation class]]) {
        if([keyPath isEqualToString:@"isFinished"]) {
            YLRenderOperation *operation = (YLRenderOperation *)object;
            [operation removeObserver:self forKeyPath:@"isFinished"];
            
            if([operation isCancelled]) {
                return;
            }
            
            UIImage *image = [operation image];
            if(image == nil) {
                YLCacheContext *context = [self cacheContextForDocument:operation.document];
                [context stoppedRenderingPage:operation.page size:operation.size];
                
                return;
            }
            
            if([operation shouldCache]) {
                NSString *key = [self cacheKeyForDocument:operation.document page:operation.page size:operation.size];
                int cost = image.size.width * image.size.height * 4;
                [_thumbCache setObject:image forKey:key cost:cost];
            }
            
            //DLog(@"DID RENDER IMAGE FOR PAGE: %d - SIZE: %@", operation.page, [self sizeToString:operation.size]);
            
            [_multicastDelegate didCacheDocument:operation.document
                                            page:operation.page
                                            size:operation.size
                                           image:operation.image];
            
            YLWriteOperation *writeOp = [[YLWriteOperation alloc] initWithDocument:operation.document
                                                                              page:operation.page
                                                                              size:operation.size
                                                                              path:operation.path];
            [writeOp setImage:image];
            [self addWriteOperation:writeOp];
            [writeOp release];
        }
    } else if([object isKindOfClass:[YLWriteOperation class]]) {
        if([keyPath isEqualToString:@"isFinished"]) {
            YLWriteOperation *operation = (YLWriteOperation *)object;
            [operation removeObserver:self forKeyPath:@"isFinished"];
            
            YLCacheContext *context = [self cacheContextForDocument:operation.document];
            [context stoppedRenderingPage:operation.page size:operation.size];
        }
    }
}

- (void)startCachingDocument:(YLDocument *)document startPage:(NSUInteger)page size:(YLPDFImageSize)size {
    if(![self isFastDevice]) {
        return;
    }
    
    NSUInteger pageCount = [document pageCount];
    if(page >= pageCount) {
        return;
    }
    
    [self setupCacheDirectoryForDocument:document];
    NSFileManager *fileManager = [NSFileManager defaultManager];
    YLCacheContext *cacheContext = [self cacheContextForDocument:document];
    
    for(NSUInteger i = page; i < pageCount; i++) {
        NSString *imagePath = [self pathForDocument:document page:i size:size];
        if(![fileManager fileExistsAtPath:imagePath]) {
            if([cacheContext shouldRenderPage:i size:size]) {
                YLRenderOperation *operation = [[YLRenderOperation alloc] initWithDocument:document 
                                                                                      page:i
                                                                                      size:size
                                                                                      path:imagePath];
                if(i < 2) {
                    [operation setQueuePriority:NSOperationQueuePriorityVeryHigh];
                } else {
                    [operation setQueuePriority:NSOperationQueuePriorityVeryLow];
                }
                [operation setShouldCache:NO];
                [self addRenderOperation:operation];
                [operation release];
            }
        }
    }
}

- (void)stopCachingDocument:(YLDocument *)document {
    [self pauseCaching:@"YLCache"];
    
    for(YLOperation *operation in _loadQueue.operations) {
        if([document.uuid isEqualToString:operation.document.uuid]) {
            [operation removeObserver:self forKeyPath:@"isFinished"];
            [operation cancel];
        }
    }
    
    for(YLOperation *operation in _renderQueue.operations) {
        if([document.uuid isEqualToString:operation.document.uuid]) {
            [operation removeObserver:self forKeyPath:@"isFinished"];
            [operation cancel];
        }
    }
    
    [self removeCacheContextForDocument:document];
    [self resumeCaching:@"YLCache"];
}

- (void)extractTextFromDocument:(YLDocument *)document {
    YLExtractTextOperation *operation = [[YLExtractTextOperation alloc] initWithDocument:document];
    [operation setQueuePriority:NSOperationQueuePriorityVeryLow];
    [operation addObserver:self forKeyPath:@"isFinished" options:NSKeyValueObservingOptionNew context:NULL];
    [_loadQueue addOperation:operation];
    [operation release];
}

- (void)cancelOperation:(YLOperation *)operation {
    if([operation isKindOfClass:[YLLoadOperation class]]) {
        YLCacheContext *cacheContext = [self cacheContextForDocument:operation.document];
        [cacheContext stoppedLoadingPage:operation.page size:operation.size];
        
        [operation cancel];
    } else if([operation isKindOfClass:[YLRenderOperation class]]) {
        YLCacheContext *cacheContext = [self cacheContextForDocument:operation.document];
        [cacheContext stoppedRenderingPage:operation.page size:operation.size];
        
        [operation cancel];
    }
}

- (id)cachedImageForDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size {
    NSString *cacheKey = [self cacheKeyForDocument:document page:page size:size];
    UIImage *image = [_thumbCache objectForKey:cacheKey];
    if(image) {
        //DLog(@"image cache hit for page: %d - size: %@", page, [self sizeToString:size]);
        return image;
    }
    
    [self setupCacheDirectoryForDocument:document];
    NSString *imagePath = [self pathForDocument:document page:page size:size];
    if([[NSFileManager defaultManager] fileExistsAtPath:imagePath]) {        
        YLCacheContext *cacheContext = [self cacheContextForDocument:document];
        if([cacheContext shouldLoadPage:page size:size]) {
            //DLog(@"STARTING LOAD OPERATION FOR PAGE: %d - (%@)", page, [self sizeToString:size]);
            YLLoadOperation *operation = [[YLLoadOperation alloc] initWithDocument:document
                                                                              page:page
                                                                              size:size
                                                                              path:imagePath];
            if(size == YLPDFImageSizeSmall) {
                [operation setQueuePriority:NSOperationQueuePriorityLow];
            } else {
                [operation setQueuePriority:NSOperationQueuePriorityHigh];
            }
            [self addLoadOperation:operation];
            return [operation autorelease];
        }
    } else {
        YLCacheContext *cacheContext = [self cacheContextForDocument:document];
        if([cacheContext shouldRenderPage:page size:size]) {
            //DLog(@"STARTING RENDER OPERATION FOR PAGE: %d - (%@)", page, [self sizeToString:size]);
            YLRenderOperation *operation = [[YLRenderOperation alloc] initWithDocument:document
                                                                                  page:page
                                                                                  size:size
                                                                                  path:imagePath];
            [operation setShouldCache:YES];
            if(size == YLPDFImageSizeSmall) {
                [operation setQueuePriority:NSOperationQueuePriorityLow];
            } else {
                [operation setQueuePriority:NSOperationQueuePriorityHigh];
            }
            [self addRenderOperation:operation];
            return [operation autorelease];
        } else {
            // Look if the pre-cache algorithm already started an operation for this page at original size. If so
            // increase its priority from very low to very high!
            YLRenderOperation *operation = nil;
            if(size == YLPDFImageSizeOriginal) {
                [_renderQueue setSuspended:YES];
                NSArray *operations = _renderQueue.operations;
                for(YLRenderOperation *op in operations) {
                    if(op.size == YLPDFImageSizeOriginal && op.page == page) {
                        op.queuePriority = NSOperationQueuePriorityVeryHigh;
                        operation = op;
                        break;
                    }
                }
                [_renderQueue setSuspended:NO];
                return operation;
            }
        }
    }
    
    return nil;
}

- (void)addDelegate:(id)delegate delegateQueue:(dispatch_queue_t)delegateQueue {
    [_multicastDelegate addDelegate:delegate delegateQueue:delegateQueue];
}

- (void)removeDelegate:(id)delegate {
    [_multicastDelegate removeDelegate:delegate];
}

- (void)pauseCaching:(NSString *)caller {
    if(_callerDic == nil) {
        _callerDic = [[NSMutableDictionary alloc] init];
    }
    
    if([_callerDic objectForKey:caller]) {
        return;
    }
    
    [_callerDic setObject:@"1" forKey:caller];
    [self pauseCaching];
}

- (void)resumeCaching:(NSString *)caller {
    if(_callerDic == nil) {
        return;
    }
    
    if([_callerDic objectForKey:caller]) {
        [_callerDic removeObjectForKey:caller];
        if([_callerDic count] == 0) {
            [self resumeCaching];
        }
    }
}

- (void)clearCache {
    [_thumbCache removeAllObjects];
    [[YLFontHelper sharedInstance] clearCache];
}

- (void)clearCacheForDocument:(YLDocument *)document {
    NSString *path = [self pathForDocument:document];
    [[NSFileManager defaultManager] removeItemAtPath:path error:nil];
    if(_cacheDirDic) {
        [_cacheDirDic removeObjectForKey:document.uuid];
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)setupCacheDirectory {
    if(_cacheDirectory != nil) {
        return;
    }
    
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSCachesDirectory, NSUserDomainMask, YES);
    NSString *cacheDir = [[paths objectAtIndex:0] stringByAppendingPathComponent:@"YLPDFKit"];
    if(![[NSFileManager defaultManager] fileExistsAtPath:cacheDir]) {
        NSError *error = nil;
        [[NSFileManager defaultManager] createDirectoryAtPath:cacheDir
                                  withIntermediateDirectories:YES
                                                   attributes:nil
                                                        error:&error];
        if(error) {
            //DLog(@"Cache directory could not be created! %@", [error description]);
        }
    }
    
    _cacheDirectory = [cacheDir retain];
}

- (void)setupCacheDirectoryForDocument:(YLDocument *)document {
    if(_cacheDirDic && [_cacheDirDic objectForKey:document.uuid]) {
        return;
    }
    
    if(_cacheDirDic == nil) {
        _cacheDirDic = [[NSMutableDictionary alloc] init];
    }
    
    NSString *path = [self pathForDocument:document];
    NSFileManager *fileManager = [NSFileManager defaultManager];
    if(![fileManager fileExistsAtPath:path]) {
        NSError *error = nil;
        [fileManager createDirectoryAtPath:path
               withIntermediateDirectories:YES
                                attributes:nil
                                     error:&error];
        if(error) {
            //DLog(@"Cache directory for document %@ could not be created! %@", document.title, [error description]);
            return;
        }
    }
    
    [_cacheDirDic setObject:@"1" forKey:document.uuid];
}

- (NSString *)pathForDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size {
    NSString *sizePrefix = [_imageSizeDic objectForKey:[NSNumber numberWithInt:size]];
    NSString *documentPath = [self pathForDocument:document];
    NSString *imageName = [NSString stringWithFormat:@"%@%lu.jpg", sizePrefix, (unsigned long)page];
    return [documentPath stringByAppendingPathComponent:imageName];
}

- (NSString *)pathForDocument:(YLDocument *)document {
    return [_cacheDirectory stringByAppendingPathComponent:[NSString stringWithFormat:@"%@", document.uuid]];
}

- (NSString *)cacheKeyForDocument:(YLDocument *)document page:(NSUInteger)page size:(YLPDFImageSize)size {
    NSString *sizePrefix = [_imageSizeDic objectForKey:[NSNumber numberWithInt:size]];
    
    return [NSString stringWithFormat:@"%@-%lu-%@", document.uuid, (unsigned long)page, sizePrefix];
}

- (YLCacheContext *)cacheContextForDocument:(YLDocument *)document {
    if(_cacheContextDic == nil) {
        _cacheContextDic = [[NSMutableDictionary alloc] init];
    }
    
    YLCacheContext *context = [_cacheContextDic objectForKey:document.uuid];
    if(context == nil) {
        context = [[[YLCacheContext alloc] initWithDocumentUUID:document.uuid] autorelease];
        [_cacheContextDic setObject:context forKey:document.uuid];
    }
    
    return context;
}

- (void)removeCacheContextForDocument:(YLDocument *)document {
    if(_cacheContextDic == nil) {
        return;
    }
    
    [_cacheContextDic removeObjectForKey:document.uuid];
}

- (BOOL)isFastDevice {
    if(UI_USER_INTERFACE_IDIOM() == UIUserInterfaceIdiomPad) {
        // iPad 2 or later
        return [UIImagePickerController isSourceTypeAvailable:UIImagePickerControllerSourceTypeCamera];
    } else {
        // iPhone 4 or later
        return ([[UIScreen mainScreen] scale] >= 2.0);
    }
}

- (NSString *)sizeToString:(YLPDFImageSize)size {
    if(size == YLPDFImageSizeSmall) {
        return @"Small";
    } else if(size == YLPDFImageSizeThumbnail) {
        return @"Thumbnail";
    } else if(size == YLPDFImageSizeOriginal) {
        return @"Original";
    } else {
        return @"Undefined";
    }
}

- (void)pauseCaching {
    if(!_caching) {
        return;
    }
    
    _caching = NO;
    [_loadQueue setSuspended:YES];
    [_renderQueue setSuspended:YES];
}

- (void)resumeCaching {
    if(_caching) {
        return;
    }
    
    _caching = YES;
    [_loadQueue setSuspended:NO];
    [_renderQueue setSuspended:NO];
}

- (void)addLoadOperation:(YLLoadOperation *)operation {
    [operation addObserver:self forKeyPath:@"isFinished" options:NSKeyValueObservingOptionNew context:NULL];
    [_loadQueue addOperation:operation];
}

- (void)addWriteOperation:(YLWriteOperation *)operation {
    [operation addObserver:self forKeyPath:@"isFinished" options:NSKeyValueObservingOptionNew context:NULL];
    [_loadQueue addOperation:operation];
}

- (void)addRenderOperation:(YLRenderOperation *)operation {
    [operation addObserver:self forKeyPath:@"isFinished" options:NSKeyValueObservingOptionNew context:NULL];
    [_renderQueue addOperation:operation];
}


#pragma mark -
#pragma mark NSCacheDelegate Methods
- (void)cache:(NSCache *)cache willEvictObject:(id)obj {
    //DLog(@"CACHE WILL EVICT OBJECT: %@", [obj description]);
}

@end
