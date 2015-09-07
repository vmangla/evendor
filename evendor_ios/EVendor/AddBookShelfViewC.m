//
//  AddBookShelfViewC.m
//  EVendor
//
//  Created by MIPC-52 on 18/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#import "AddBookShelfViewC.h"

@interface AddBookShelfViewC ()

@end

@implementation AddBookShelfViewC
@synthesize delegate, isMove;

- (id)initWithArray:(NSArray*) arr
{
    self = [super initWithNibName:@"AddBookShelfViewC" bundle:nil];
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
    [_mTableView release];
    [_doneBtn release];
    [_addBtn release];
    [super dealloc];
}

- (void)viewDidLoad
{
    [super viewDidLoad];
    // Do any additional setup after loading the view from its nib.
    // Set RoundRect
    [Utils makeRoundRectInView:self.view];
    [Utils setRoundWithLayer:self.view cornerRadius:1.0 borderWidth:1.0 borderColor:kBgColor maskBounds:YES];
    if ([Utils isiOS8]) {
        //self.popoverC.popoverContentSize = CGSizeMake(400, 500);
    }
    else{
        
        
        self.contentSizeForViewInPopover = CGSizeMake(self.view.frame.size.width, self.view.frame.size.height);
    }
    selectedShelf = -1;
    
    [self.mTableView reloadData];
    
    if(isMove == YES)
    {
        self.addBtn.hidden = YES;
        [self.doneBtn setTitle:@"Move" forState:UIControlStateNormal];
    }
    else
    {
        self.addBtn.hidden = NO;
        [self.doneBtn setTitle:@"Done" forState:UIControlStateNormal];
    }
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma mark - TableView Delegate/DataSource
- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath
{
    return 40.0;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    return [mainArray count];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    static NSString *cellIdentifier=@"cell";
    UITableViewCell *cell=[tableView dequeueReusableCellWithIdentifier:cellIdentifier];
    if(cell==nil)
    {
        cell = [[[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:cellIdentifier] autorelease];
        cell.selectionStyle = UITableViewCellSelectionStyleNone;
        cell.textLabel.textColor = [UIColor whiteColor];
        cell.textLabel.backgroundColor = [UIColor clearColor];
        cell.contentView.backgroundColor = [UIColor clearColor];
        cell.backgroundColor = [UIColor clearColor];
        
    }
    cell.accessoryType = UITableViewCellAccessoryNone;
    NSDictionary    *aDic = [mainArray objectAtIndex:indexPath.row];
    NSString    *title = [aDic objectForKey:kShelfTitle];
    cell.textLabel.text = title;
    cell.textLabel.font = [UIFont systemFontOfSize:17.0];
    if(indexPath.row == selectedShelf)
        cell.accessoryType = UITableViewCellAccessoryCheckmark;
    cell.textLabel.textAlignment = NSTextAlignmentLeft;
    
    return cell;
}
-(void)tableView:(UITableView *)tableView willDisplayCell:(UITableViewCell *)cell forRowAtIndexPath:(NSIndexPath *)indexPath{
    
    cell.contentView.backgroundColor = [UIColor clearColor];
    
}
- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath
{
    selectedShelf = indexPath.row;
    [self.mTableView reloadData];
}


- (IBAction)addShelfClicked:(id)sender
{
    if(self.delegate)
        [self.delegate plusBtnClicked];
}

- (IBAction)doneClicked:(id)sender
{
    BOOL isValidIndex = [mainArray count] > selectedShelf && selectedShelf != -1;
    if(isValidIndex)
    {
        NSDictionary    *aDic = [mainArray objectAtIndex:selectedShelf];
        NSString    *btnTitle = [self.doneBtn titleForState:UIControlStateNormal];
        if([@"Move" isEqualToString:btnTitle]) // Move
        {
            NSString    *aId = [aDic objectForKey:kShelfId];
            if(self.delegate)
                [self.delegate moveBookToShelf:aId];
        }
        else  // Done
        {
            NSString    *aId = [aDic objectForKey:kShelfId];
            if(self.delegate)
                [self.delegate addBookWithShelfId:aId];
        }
    }
    else
        [Utils showOKAlertWithTitle:kAlertTitle message:@"Please select a shelf."];
}

- (IBAction)cancelClicked:(id)sender
{
    if(self.delegate)
        [self.delegate popoverCancel];
}
@end
