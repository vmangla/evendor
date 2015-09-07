//
//  YLSearchResultCell.m
//
//  Created by Kemal Taskin on 5/3/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLSearchResultCell.h"
#import "TTTAttributedLabel.h"
#import "YLSearchResult.h"
#import "YLGlobal.h"
#import "NSBundle+PDFTouch.h"

@implementation YLSearchResultCell

@synthesize pageLabel = _pageLabel;
@synthesize contextLabel = _contextLabel;
@synthesize searchResult = _searchResult;

- (id)initWithStyle:(UITableViewCellStyle)style reuseIdentifier:(NSString *)reuseIdentifier {
    self = [super initWithStyle:style reuseIdentifier:reuseIdentifier];
    if (self) {
        _contextLabel = [[TTTAttributedLabel alloc] initWithFrame:CGRectMake(10, 20, 300, 20)];
        if(YLIsIOS7OrGreater()) {
            _contextLabel.backgroundColor = [UIColor clearColor];
        } else {
            _contextLabel.backgroundColor = [UIColor whiteColor];
        }
        _contextLabel.textColor = [UIColor blackColor];
        _contextLabel.font = [UIFont systemFontOfSize:14];
        _contextLabel.lineBreakMode = NSLineBreakByWordWrapping;
        _contextLabel.numberOfLines = 0;
        _contextLabel.textAlignment = NSTextAlignmentLeft;
        _contextLabel.verticalAlignment = TTTAttributedLabelVerticalAlignmentTop;
        [self.contentView addSubview:_contextLabel];
        
        _pageLabel = [[UILabel alloc] initWithFrame:CGRectMake(10, 2, 300, 18)];
        if(YLIsIOS7OrGreater()) {
            _pageLabel.backgroundColor = [UIColor clearColor];
        } else {
            _pageLabel.backgroundColor = [UIColor whiteColor];
        }
        _pageLabel.textColor = [UIColor darkGrayColor];
        _pageLabel.textAlignment = NSTextAlignmentLeft;
        _pageLabel.font = [UIFont boldSystemFontOfSize:14];
        [self.contentView addSubview:_pageLabel];
    }
    
    return self;
}

- (void)dealloc {
    [_pageLabel release];
    [_contextLabel release];
    [_searchResult release];
    
    [super dealloc];
}

- (void)setSearchResult:(YLSearchResult *)searchResult {
    if(_searchResult == searchResult) {
        return;
    }
    
    if(_searchResult) {
        [_searchResult release];
        _searchResult = nil;
    }
    
    [_pageLabel setText:@""];
    [_contextLabel setText:@""];
    
    _searchResult = [searchResult retain];
    if(_searchResult) {
        [_pageLabel setText:[NSString stringWithFormat:[NSBundle myLocalizedStringForKey:@"Page %d"], _searchResult.page]];
        
        [_contextLabel setText:_searchResult.shortText afterInheritingLabelAttributesAndConfiguringWithBlock:^NSMutableAttributedString *(NSMutableAttributedString *mutableAttributedString) {
            UIFont *boldSystemFont = [UIFont boldSystemFontOfSize:14];
            CTFontRef font = CTFontCreateWithName((CFStringRef)boldSystemFont.fontName, boldSystemFont.pointSize, NULL);
            if(font) {
                [mutableAttributedString addAttribute:(NSString *)kCTFontAttributeName value:(id)font range:_searchResult.boldRange];
                CFRelease(font);
            }
            
            return mutableAttributedString;
        }];
    }
    
    [self setNeedsDisplay];
}

- (void)setSelected:(BOOL)selected animated:(BOOL)animated {
    [super setSelected:selected animated:animated];
}

- (void)layoutSubviews {
    [super layoutSubviews];
    
    self.textLabel.hidden = YES;
    self.detailTextLabel.hidden = YES;
}

@end
