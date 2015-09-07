//
//  YLAudioAnnotationView.m
//
//  Created by Kemal Taskin on 5/23/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLAudioAnnotationView.h"
#import "YLAnnotation.h"
#import "YLPDFViewController.h"

@interface YLAudioAnnotationView () {
    MPMoviePlayerController *_audioPlayer;
    MPMoviePlayerViewController *_audioPlayerController;
    UIButton *_button;
    BOOL _autostart;
    BOOL _modal;
}

- (void)buttonTapped;

@end

@implementation YLAudioAnnotationView

@synthesize annotation = _annotation;
@synthesize pdfViewController = _pdfViewController;
@synthesize audioPlayer = _audioPlayer;
@synthesize autostart = _autostart;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        _annotation = nil;
        _pdfViewController = nil;
        
        _autostart = NO;
        _modal = NO;
        _audioPlayer = nil;
        _audioPlayerController = nil;
        _button = nil;
    }
    
    return self;
}

- (void)dealloc {
    [_annotation release];
    [_audioPlayer release];
    [_audioPlayerController release];
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
        if(_audioPlayer) {
            [_audioPlayer stop];
            if(!_modal) {
                [_audioPlayer.view removeFromSuperview];
            }
            [_audioPlayer release];
            _audioPlayer = nil;
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
            _audioPlayer = [[MPMoviePlayerController alloc] initWithContentURL:_annotation.url];
            if(_audioPlayer) {
                [_audioPlayer setControlStyle:MPMovieControlStyleEmbedded];
                [_audioPlayer.view setFrame:self.bounds];
                [self addSubview:_audioPlayer.view];
                
                [_audioPlayer setShouldAutoplay:_autostart];
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
    if(_audioPlayer) {
        [_audioPlayer setShouldAutoplay:_autostart];
    }
}

- (MPMoviePlayerController *)audioPlayer {
    if(_audioPlayer) {
        return _audioPlayer;
    } else if(_audioPlayerController) {
        return _audioPlayerController.moviePlayer;
    } else {
        return nil;
    }
}


#pragma mark -
#pragma mark Private Methods
- (void)buttonTapped {
    if(_audioPlayerController == nil) {
        _audioPlayerController = [[MPMoviePlayerViewController alloc] initWithContentURL:_annotation.url];
    }
    
    if(_audioPlayerController && _pdfViewController) {
        [_pdfViewController presentModalViewController:_audioPlayerController animated:YES];
        if(_autostart) {
            [_audioPlayerController.moviePlayer prepareToPlay];
            [_audioPlayerController.moviePlayer play];
        }
    }
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    if(page == _annotation.page) {
        if(!_modal && _audioPlayer) {
            [_audioPlayer prepareToPlay];
            if(_autostart) {
                [_audioPlayer play];
            }
        }
    }
}

- (void)willHidePage:(NSUInteger)page {
    if(page == _annotation.page && _audioPlayer) {
        [_audioPlayer stop];
    }
}

@end
