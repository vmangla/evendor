//
//  PickerViewC.m
//  EVendor
//
//  Created by MIPC-52 on 21/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "PickerViewC.h"

@interface PickerViewC ()

@end

@implementation PickerViewC
@synthesize isSearch, delegate, selectedRow, isSort;

- (id)initWithArray:(NSArray*) arr
{
    self = [super initWithNibName:@"PickerViewC" bundle:nil];
    if (self) {
        // Custom initialization
        ReleaseObj(mainArray);
        mainArray = [[NSMutableArray alloc] initWithArray:arr];
    }
    return self;
}

- (void)dealloc
{
    [mainArray release];
    [_mPicker release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    
    // Set RoundRect
    [Utils makeRoundRectInView:self.view];
    self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    
    if(mainArray.count==0 && self.isSearch == YES && self.isSort == NO)
    {
        ReleaseObj(mainArray);
        NSDictionary    *aDic1 = [NSDictionary dictionaryWithObjectsAndKeys:@"SEARCH BY NAME",kStoreName,@"1",kId,nil];
        NSDictionary    *aDic2 = [NSDictionary dictionaryWithObjectsAndKeys:@"SEARCH BY AUTHOR",kStoreName,@"2",kId,nil];
        NSDictionary    *aDic3 = [NSDictionary dictionaryWithObjectsAndKeys:@"SEARCH BY PUBLISHER",kStoreName,@"3",kId,nil];
        mainArray = [[NSMutableArray alloc] initWithObjects:aDic1,aDic2,aDic3, nil];
    }
    if(mainArray.count==0 && self.isSearch == NO && self.isSort == YES)
    {
        ReleaseObj(mainArray);
        NSDictionary    *aDic1 = [NSDictionary dictionaryWithObjectsAndKeys:@"A TO Z",kStoreName,@"1",kId,nil];
        NSDictionary    *aDic2 = [NSDictionary dictionaryWithObjectsAndKeys:@"Z TO A",kStoreName,@"2",kId,nil];
        NSDictionary    *aDic3 = [NSDictionary dictionaryWithObjectsAndKeys:@"PRICE HIGHEST",kStoreName,@"3",kId,nil];
        NSDictionary    *aDic4 = [NSDictionary dictionaryWithObjectsAndKeys:@"PRICE LOWEST",kStoreName,@"4",kId,nil];
        mainArray = [[NSMutableArray alloc] initWithObjects:aDic1,aDic2,aDic3,aDic4, nil];
    }
    
    [self.mPicker reloadAllComponents];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}


- (IBAction)cancelClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}

- (IBAction)doneClicked:(id)sender
{
    if(self.delegate)
        [self.delegate selectedPickerValue:[mainArray objectAtIndex:self.selectedRow]];
}

#pragma mark - Picker Delegate and Datasource
#pragma mark - Picker Delegates
- (NSInteger)numberOfComponentsInPickerView:(UIPickerView *)pickerView
{
    return 1;
}

- (NSInteger)pickerView:(UIPickerView *)pickerView numberOfRowsInComponent:(NSInteger)component;
{
    return [mainArray count];
}

- (NSString *)pickerView:(UIPickerView *)pickerView titleForRow:(NSInteger)row forComponent:(NSInteger)component
{
    NSString   *value = [[mainArray objectAtIndex:row] objectForKey:kStoreName];
    return value;
}

- (void)pickerView:(UIPickerView *)pickerView didSelectRow:(NSInteger)row inComponent:(NSInteger)component
{
    self.selectedRow = row;
    
}

@end
