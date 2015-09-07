//
//  Defines.h
//  EVendor
//
//  Created by MIPC-52 on 01/11/13.
//  Copyright (c) 2013 MIPC-52. All rights reserved.
//

#define     ReleaseObj(obj)     if(obj){[obj release]; obj = nil;}


#define     kBgColor       [UIColor colorWithRed:36.0/255.0 green:36.0/255.0 blue:36.0/255.0 alpha:1]
#define     kNavyBlue      [UIColor colorWithRed:0.0/255.0 green:208.0/255.0 blue:255.0/255.0 alpha:1.0]
#define     kWhiteColor    [UIColor colorWithRed:255.0/255.0 green:250.0/255.0 blue:250.0/255.0 alpha:1.0]
#define     kBlueColor     [UIColor colorWithRed:35.0/255.0 green:98.0/255.0 blue:168.0/255.0 alpha:1.0]
#define     kGreenColor    [UIColor colorWithRed:64.0/255.0 green:143.0/255.0 blue:51.0/255.0 alpha:1.0]
#define     kOrangeColor   [UIColor colorWithRed:247.0/255.0 green:94.0/255.0 blue:20.0/255.0 alpha:1.0]

typedef enum TheRequestType
{
    LoginRequest = 1,
    RegisterRequest,
    ForgetPasswordRequest,
    FeaturedRequest,
    NewArrivalsRequest,
    BestSellersRequest,
    CategoryRequest,
    LibraryRequest,
    CountryRequest,
    AllStoreSearchRequest,
    ChangeProfileRequest,
    ChangePasswordRequest,
    GetUserDetailsRequest,
    GetAllGroupBooksRequest,
    GetBookPurchaseStatus
    
}ServerRequestType;

/// DEFINES

//http://www.miprojects2.com.php53-6.ord1-1.websitetestlink.com/

#define     kBaseURL            @"http://www.miprojects2.com.php53-6.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall"
//#define     kBaseURL            @"http://evendornigeria.com/api/index/apicall"
//#define     kBaseURL            @"http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall"

#define     kApiKey             @"apikey/998905797b8646fd44134910d1f88c33"

#define     kBookShelfRefreshNoti    @"BookShelfRefreshNotification"
#define     kDownloadedRefreshNoti   @"DownloadedBooksRefreshNotification"

#define     kLoginApi           @"UserLogin"
#define     kRegistrationApi    @"UserRegistration"
#define     kForgetPasswordApi  @"UserForgotPassword"
#define     kFeaturedApi        @"GetOnlyFeatured"
#define     kNewArrivalsApi     @"GetOnlyNewArrivals"
#define     kBestSellersApi     @"GetOnlyBestSeller"
#define     kLibraryApi         @"GetFullLibrary"
#define     kCategoryApi        @"GetAllCategories"
#define     kStoresApi          @"GetStores"
#define     kStoreDownloadsApi  @"StoreDownloads"
#define     kPurchaseHistoryApi @"GetPurchaseHistoryNew1"
#define     kAllStoreSearchApi  @"GetFullStoreLibrary"
#define     kChangePasswordApi  @"ChangePwdIphone"
#define     kUserDetailApi      @"UserDetailIphone"
#define     kUpdateProfileApi   @"UserUpdateIphone"
#define     kRateBookApi        @"ReviewRatingIphone"
#define     kGroupBooksApi      @"GetFreeLibrary"


///////
#define     kEmailId            @"EmailId"
#define     kPassword           @"Password"
#define     kFirstName          @"FirstName"
#define     kLastName           @"LastName"
#define     kCountry            @"Country"
#define     kError              @"error"
#define     kUserName           @"Username"
#define     kCountryId          @"countryid"
#define     kUserId             @"userid"
#define     kStoreId            @"StoreId"
#define     kAllCategories      @"Allcategories"
#define     kLibrary            @"Library"
#define     kFeatured           @"Featured"
#define     kNewArrivals        @"NewArrivals"
#define     kBestSellers        @"BestSeller"
#define     kAllStores          @"Allstores"
#define     kGenre              @"genre"
#define     kProductThumbnail   @"ProductThumbnail"
#define     kProductURL         @"Producturl"
#define     kRating             @"rating"
#define     kTitle              @"title"
#define     kAuthorName         @"author_name"
#define     kPrice              @"price"
#define     kPriceText          @"priceText"
#define     kPublisher          @"publisher"
#define     kDescription        @"description"
#define     kIsFree             @"is_free"
#define     kGroupName          @"group_name"
#define     kPurchase           @"Purchase"
#define     kAddDate            @"add_date"
#define     kTotalAmount        @"TotalTransaction"
#define     kStoreName          @"store"
#define     kId                 @"id"
#define     kSearchKey          @"Keyword"
#define     kBookId             @"bookId"
#define     kIsPurchase         @"isPurchase"
#define     kShelfId            @"shelfId"
#define     kShelfTitle         @"shelfTitle"
#define     kShelfColor         @"shelfColorName"
#define     kFileSize           @"file_size"
#define     kFileType           @"total_pages"
#define     kGroupBooks         @"groupBooks"
#define     kCategory           @"cat_id"
#define     kCategoryName       @"category_name"
#define     kCountryName        @"country_name"
#define     kCountryFlagUrl     @"country_flag_url"
#define     kPublishDate        @"publish_time"


///////
#define     kAlertTitle         @"eVendor"
#define     kPleaseWait         @"Please wait..."
#define     kFalse              @"false"
#define     kTrue               @"true"
#define     kMessage            @"Message"
#define     kBlueShelf          @"Blue"
#define     kGreenShelf         @"Green"
#define     kOrangeShelf        @"Orange"
#define     kShelfDict          @"shelfDict"
#define     kBookArr            @"bookArr"

//////
#define     kCurrentSpineIndex          @"currentSpineIndex"
#define     kCurrentPageInSpineIndex    @"currentPageInSpineIndex"
#define     kCurrentTextSize            @"currentTextSize"
#define     kCurrentPageText            @"currentPageText"










