//
//  HostSettings.m
//  EVendor
//
//  Created by MIPC-52 on 30/12/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "HostSettings.h"
#import "NSObject+SBJSON.h"
#import "NSString+SBJSON.h"
#import "SBJSON.h"
#import "ServerResponseDelegate.h"
#import "Reachability.h"


@implementation HostSettings
@synthesize responseData;
@synthesize identifier;
@synthesize delegate;

#pragma mark -
#pragma mark Singleton Methods

+ (HostSettings*)host
{
    
	static HostSettings *_host;
    
	if(!_host)
    {
		static dispatch_once_t oncePredicate;
		dispatch_once(&oncePredicate, ^{
			_host = [[super allocWithZone:nil] init];
        });
    }
    
    return _host;
}

+ (id)allocWithZone:(NSZone *)zone
{
    
	return [self host];
}


- (id)copyWithZone:(NSZone *)zone
{
	return self;
}

#pragma mark -
#pragma mark Custom Methods

// Add your custom methods here

-(id)init
{
    self = [super init];
    
    if (self)
    {
        
    }
    
    return self;
}


#pragma mark - Server Request
- (void) requestWithUrl:(NSString*) aUrl
{
    // Check internet Connection
    Reachability *r = [Reachability reachabilityWithHostName:@"www.google.com"];
	NetworkStatus internetStatus = [r currentReachabilityStatus];
	if ((internetStatus != ReachableViaWiFi) && (internetStatus != ReachableViaWWAN))
	{
		[Utils showAlertView:kAlertTitle message:@"Internet connection is not available." delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil];
        if(delegate)
        {
            UIView  *aView = [self.delegate view];
            [Utils stopActivityIndicator:aView];
        }
        return;
	}
    
    NSURL *url = [NSURL URLWithString:[aUrl stringByAddingPercentEscapesUsingEncoding:NSUTF8StringEncoding]];
    
    NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:url
                                                           cachePolicy:NSURLRequestUseProtocolCachePolicy timeoutInterval:60.0];
    [request setHTTPMethod:@"GET"];
    //NSLog(@"Request URL ------------------>>>>> %@", url);
    NSURLConnection *connection = [[NSURLConnection alloc]initWithRequest:request delegate:self];
    if (connection) {
        self.responseData = [NSMutableData data];
    }
}


- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response
{
    [self.responseData setLength:0];
}

- (void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data
{
    [self.responseData appendData:data];
}



- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error
{
    [Utils showAlertView:kAlertTitle message:@"Request failed, please try again." delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil];
    if(delegate)
    {
        UIView  *aView = [self.delegate view];
        [Utils stopActivityIndicator:aView];
        if(delegate)
            [delegate didFailToGetResult];
    }
}

- (void)connectionDidFinishLoading:(NSURLConnection *)connection
{
    NSString *jsonString = [[[NSString alloc] initWithData:self.responseData encoding:NSUTF8StringEncoding] autorelease];
    //NSLog(@"Response String ------------------>>>>> %@", jsonString);
    
    NSDictionary    *aDic = [jsonString JSONValue];
    
    if(aDic.count>0)
    {
        if(delegate)
            [delegate getResult:aDic];
    }
    else
    {
        [Utils showAlertView:kAlertTitle message:@"Request failed, please try again." delegate:nil cancelButtonTitle:@"OK" otherButtonTitles:nil];
        if(delegate)
        {
            UIView  *aView = [self.delegate view];
            [Utils stopActivityIndicator:aView];
            if(delegate)
                [delegate didFailToGetResult];
        }
    }
}


@end
