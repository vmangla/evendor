//
//  YLMapAnnotationView.m
//
//  Created by Kemal Taskin on 8/23/12.
//  Copyright (c) 2012 Yakamoz Labs. All rights reserved.
//

#import "YLMapAnnotationView.h"
#import "YLAnnotation.h"
#import "YLPDFViewController.h"

@interface YLMapAnnotationView () {
    MKMapView *_mapView;
}

@end

@implementation YLMapAnnotationView

@synthesize annotation = _annotation;
@synthesize pdfViewController = _pdfViewController;
@synthesize mapView = _mapView;

- (id)initWithFrame:(CGRect)frame {
    self = [super initWithFrame:frame];
    if (self) {
        _annotation = nil;
        _pdfViewController = nil;
        
        _mapView = nil;
    }
    
    return self;
}

- (void)dealloc {
    [_annotation release];
    [_mapView release];
    
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
        _mapView = [[MKMapView alloc] initWithFrame:self.bounds];
        NSString *type = [_annotation.params objectForKey:@"type"];
        if([type isEqualToString:@"standard"]) {
            [_mapView setMapType:MKMapTypeStandard];
        } else if([type isEqualToString:@"hybrid"]) {
            [_mapView setMapType:MKMapTypeHybrid];
        } else if([type isEqualToString:@"satellite"]) {
            [_mapView setMapType:MKMapTypeSatellite];
        }
        
        [self addSubview:_mapView];
    }
}


#pragma mark -
#pragma mark YLAnnotationView Methods
- (void)didShowPage:(NSUInteger)page {
    if(page == _annotation.page) {
        CLLocationDegrees lat = [[_annotation.params objectForKey:@"lat"] doubleValue];
        CLLocationDegrees lon = [[_annotation.params objectForKey:@"lon"] doubleValue];
        CLLocationDegrees slat = [[_annotation.params objectForKey:@"slat"] doubleValue];
        CLLocationDegrees slon = [[_annotation.params objectForKey:@"slon"] doubleValue];
        
        CLLocationCoordinate2D c = CLLocationCoordinate2DMake(lat, lon);
        MKCoordinateSpan span = MKCoordinateSpanMake(slat, slon);
        [_mapView setRegion:MKCoordinateRegionMake(c, span)];
    }
}

- (void)willHidePage:(NSUInteger)page {

}

@end
