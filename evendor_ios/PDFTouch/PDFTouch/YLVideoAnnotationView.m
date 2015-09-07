//
//  YLVideoAnnotationView.m
//
//  Created by Kemal Taskin on 5/23/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLVideoAnnotationView.h"
#import "YLAnnotation.h"
#import "YLPDFViewController.h"

@interface YLVideoAnnotationView () {
    MPMoviePlayerController *_moviePlayer;
    MPMoviePlayerViewController *_moviePlayerController;
    UIButton *_button;
    BOOL _autostart;
    BOOL _modal;
}

- (void)buttonTapped;

@end

@implementation YLVideoAnnotationView

@synthesize annotation = _annotation;
@synthesize pdfViewController = _pdfViewController;
@synthesize moviePlayer = _moviePlayer;
@synthesize autostart = _autostart;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        _annotation = nil;
        _pdfViewController = nil;
        
        _autostart = NO;
        _modal = NO;
        _moviePlayer = nil;
        _moviePlayerController = nil;
        _button = nil;
    }
    
    return self;
}

- (void)dealloc {
    [_annotation release];
    [_moviePlayer release];
    [_moviePlayerController release];
    [_button release];
    
    [super dealloc];
}


#pragma mark -
#pragma mark Instance Methods
- (void)setAnnotation:(YLAnnotation *)annotation {
    if(_annotation) {
        [_annotation release];
        _annotation = nil;
    }
    
    _annotation = [annotation retain];
    if(_annotation) {
        if(_moviePlayer) {
            [_moviePlayer stop];
            if(!_modal) {
                [_moviePlayer.view removeFromSuperview];
            }
            [_moviePlayer release];
            _moviePlayer = nil;
        }
        
        if(_annotation.params) {
            NSString *autostart = [_annotation.params objectForKey:@"autostart"];
            if(autostart && [autostart isEqualToString:@"1"]) {
                _autostart = YES;
            }
            NSString *modal = [_annotation.params objectForKey:@"modal"];
            if(modal && [modal isEqualToString:@"1"]) {
                _modal = YES;
            }
        }
        
        if(!_modal) {
            _moviePlayer = [[MPMoviePlayerController alloc] initWithContentURL:_annotation.url];
            if(_moviePlayer) {
                [_moviePlayer setControlStyle:MPMovieControlStyleEmbedded];
                [_moviePlayer.view setFrame:self.bounds];
                [self addSubview:_moviePlayer.view];
                
                [_moviePlayer setShouldAutoplay:_autostart];
            }
        } else {
            self.userInteractionEnabled = YES;
            
            _button = [[UIButton alloc] initWithFrame:self.bounds];
            [_button addTarget:self action:@selector(buttonTapped) forControlEvents:UIControlEventTouchUpInside];
            [self addSubview:_button];
        }
    }
}

- (void)setAutostart:(BOOL)autostart {
    _autostart = autostart;
    if(_moviePlayer) {
        [_moviePlayer setShouldAutoplay:_autostart];
    }
}

- (MPMoviePlayerController *)moviePlayer {
    if(_moviePlayer) {
        return _moviePlayer;
    } else if(_moviePlayerController) {
        return _moviePlayerController.moviePlayer;
    } else {
        return nil;
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)buttonTapped {
    if(_moviePlayerController == nil) {
        _moviePlayerController = [[MPMoviePlayerViewController alloc] initWithContentURL:_annotation.url];
    }
    
    if(_moviePlayerController && _pdfViewController) {
        [_pdfViewController presentModalViewController:_moviePlayerController animated:YES];
        [_moviePlayerController.moviePlayer prepareToPlay];
        if(_autostart) {
            [_moviePlayerController.moviePlayer play];
        }
    }
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    if(page == _annotation.page) {
        if(!_modal && _moviePlayer) {
            [_moviePlayer prepareToPlay];
            if(_autostart) {
                [_moviePlayer play];
            }
        }
    }
}

- (void)willHidePage:(NSUInteger)page {
    if(page == _annotation.page && _moviePlayer) {
        [_moviePlayer stop];
    }
}

@end
