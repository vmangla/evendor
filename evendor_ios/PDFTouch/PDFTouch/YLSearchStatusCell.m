//
//  YLSearchStatusCell.m
//
//  Created by Kemal Taskin on 5/3/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLSearchStatusCell.h"
#import "YLGlobal.h"
#import "NSBundle+PDFTouch.h"

@implementation YLSearchStatusCell

@synthesize searchStatus = _searchStatus;
@synthesize statusLabel = _statusLabel;
@synthesize detailLabel = _detailLabel;
@synthesize spinner = _spinner;

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        _searchStatus = kSearchStatusSearching;
        
        _statusLabel = [[UILabel alloc] initWithFrame:CGRectZero];
        if(YLIsIOS7OrGreater()) {
            _statusLabel.backgroundColor = [UIColor clearColor];
        } else {
            _statusLabel.backgroundColor = [UIColor whiteColor];
        }
        _statusLabel.textColor = [UIColor blackColor];
        _statusLabel.textAlignment = NSTextAlignmentCenter;
        _statusLabel.font = [UIFont boldSystemFontOfSize:16];
        [self addSubview:_statusLabel];
        
        _detailLabel = [[UILabel alloc] initWithFrame:CGRectMake(0, 22, 320, 18)];
        if(YLIsIOS7OrGreater()) {
            _detailLabel.backgroundColor = [UIColor clearColor];
        } else {
            _detailLabel.backgroundColor = [UIColor whiteColor];
        }
        _detailLabel.textColor = [UIColor darkGrayColor];
        _detailLabel.textAlignment = NSTextAlignmentCenter;
        _detailLabel.font = [UIFont systemFontOfSize:14];
        [self addSubview:_detailLabel];
        
        _spinner = [[UIActivityIndicatorView alloc] initWithActivityIndicatorStyle:UIActivityIndicatorViewStyleGray];
        _spinner.hidesWhenStopped = YES;
        [self addSubview:_spinner];
        
        [self setSearchStatus:kSearchStatusSearching];
    }
    
    return self;
}

- (void)dealloc {
    [_statusLabel release];
    [_detailLabel release];
    [_spinner release];
    
    [super dealloc];
}

- (void)setSearchStatus:(SearchStatus)searchStatus {
    _searchStatus = searchStatus;
    if(_searchStatus == kSearchStatusSearching) {
        _statusLabel.text = [NSBundle myLocalizedStringForKey:@"Searching"];
        [_statusLabel sizeToFit];
        
        CGRect lFrame = _statusLabel.frame;
        CGRect sFrame = _spinner.frame;
        CGRect cFrame = self.contentView.frame;
        int padding = 5;
        int startX = floorf((cFrame.size.width - (sFrame.size.width + padding + lFrame.size.width)) / 2);
        sFrame.origin.x = startX;
        sFrame.origin.y = floorf((cFrame.size.height - sFrame.size.height) / 2);
        _spinner.frame = sFrame;
        lFrame.origin.x = floorf(startX + sFrame.size.width + padding);
        lFrame.origin.y = floorf((cFrame.size.height - lFrame.size.height) / 2);
        _statusLabel.frame = lFrame;
        
        _detailLabel.text = @"";
        _detailLabel.hidden = YES;
        
        [_spinner startAnimating];
    } else {
        [_spinner stopAnimating];
        
        _statusLabel.text = [NSBundle myLocalizedStringForKey:@"Search Completed"];
        [_statusLabel sizeToFit];
        CGRect frame = _statusLabel.frame;
        frame.origin.x = (self.contentView.frame.size.width - frame.size.width) / 2;
        frame.origin.y = 2;
        _statusLabel.frame = frame;
        
        _detailLabel.hidden = NO;
    }
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated {
    [super setSelected:selected animated:animated];
}

@end
