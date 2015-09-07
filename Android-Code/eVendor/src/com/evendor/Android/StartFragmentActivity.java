package com.evendor.Android;

import java.text.DateFormat;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ActionBar;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageInfo;
import android.content.res.Configuration;
import android.graphics.Color;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.FragmentActivity;
import android.text.Editable;
import android.text.TextWatcher;
import android.util.Log;
import android.view.Display;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemSelectedListener;
import android.widget.ArrayAdapter;
import android.widget.BaseAdapter;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.ListPopupWindow;
import android.widget.PopupWindow;
import android.widget.RelativeLayout;
import android.widget.Spinner;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import com.evender.Constant.Constant;
import com.evender.HTTPConnection.HTTP_Connection;
import com.evender.parser.Parser;
import com.evendor.Android.LibraryLeftpane.OnLibraryRowSelected;
import com.evendor.Android.SettingLeftpane.OnArticleSelectedListener;
import com.evendor.Modal.Allcategories;
import com.evendor.Modal.BooksData;
import com.evendor.Modal.Store;
import com.evendor.adapter.TimeAdapter;
import com.evendor.appsetting.AppSettings;
import com.evendor.utils.CMDialog;
import com.evendor.utils.Utils;
import com.evendor.webservice.CommonAsynTask;
import com.evendor.webservice.OnResponseListener;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class StartFragmentActivity extends FragmentActivity implements OnArticleSelectedListener,OnLibraryRowSelected,OnResponseListener,ServiceResultListener {

	MenuItem bookshelveMenuButton;
	MenuItem accountMenuButton;
	MenuItem libraryMenuButton;
	MenuItem downloadMenuButton;
	MenuItem purchaseMenuButton;
	 String key =null;
	 Bundle bndl =null;
	boolean mFlag=false;
	MenuItem downloadedMenuButton;
	public static boolean taskFlag=false;
	public static MenuItem addself;
	View homeButton,store_view;
	public PurchaseBookFragment purchase ;
	public  BookShelveFragment bookselvefrag;
	public ProfileFragment profilefrag;
	ActionBar actionBar;
	Menu menu;
	//	EditText landscapeSearch;
	public static ArrayList<Store> storeList=new ArrayList<Store>();
	TextView newArrivals;
	private int searchtype;

	public static boolean store_flag=false;
	public static boolean featured_flag=false;
	public static boolean newarrivals_flag=false;
	public static boolean topsaller_flag=false;
	public static boolean getfulllib_flag=false;

	public static boolean isRunningTask=false;
	public static boolean sheduleremoveflag=false;



	public static boolean gellaryRefresh_flag=false;
	public static boolean trnsaction_flag=false;
	public static boolean setting_flag=false;


	private TextView all;
	private TextView books;
	private TextView magazines;
	private TextView newspaper;
	private TextView flyer;
	private TableRow tablerow;

	public static ArrayList<BooksData> searchListByCat=new ArrayList<BooksData>();
	public static ArrayList<BooksData> categoryList=new ArrayList<BooksData>();
	private List<String> list = new ArrayList<String>();

	private List<String> list_shedule = new ArrayList<String>();
	private TextView featured;
	TextView topSeller;
	LinearLayout libaray_filter_menu;
	String tab_selected="",List_Title="";
	public static FrameLayout leftPane;
	public FrameLayout rightPane;
	public static RelativeLayout libraryFilerLayout;
	public static LinearLayout llcatagory;
	public static LinearLayout filerLayoutMenu;
	PopupWindow popupWindow;
	boolean isPopupWindowShow;
	TextView filters;
	TextView allstore;
	TextView groupbooks;
	TextView countrytStrores;
	public static JSONArray Allstores;
	private LinearLayout llfeature,lltopsaller,llnewarrivals;
	String contryIdToAddToUrl; 
	private String url;
	private EditText search;

	private  Spinner spinner,spinner_shedule;


	boolean cachingLibraryArrayWhileSortingTopandBestSeller;
	public static ArrayList<Allcategories> catList;
	public static ArrayList<BooksData> booksdata;
	public static ArrayList<BooksData> newArrivalsBooksdata;
	public static ArrayList<BooksData> topSellersBooksdata;
	public static ArrayList<BooksData> featuredBooksdata;
	public static ArrayList<BooksData> getFullBooksdata;
	//	private ProgressBar prog;

	UrlMakingClass urlMakingObject;
	public static  JSONArray libraryLeftJSONarray;
	public static CommonAsynTask recoveryTask;
	AppSettings appSetting ;
	ListPopupWindow popup;
	public static String versionName="";

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		//		setContentView(R.layout.main_fragmentactivity);
		setContentView(R.layout.start_fragment);
		try{
		PackageInfo pInfo = getPackageManager().getPackageInfo(getPackageName(), 0);
		versionName = pInfo.versionName;
		//Toast.makeText(StartFragmentActivity.this, versionName, 2000).show();
		Log.e("APP_VERSION", "="+versionName.indexOf("."));
		versionName= versionName.substring(0, versionName.indexOf(".")+2);
		Log.e("APP_VERSION", "="+versionName);
		}catch(Exception e){
			e.printStackTrace();
		}
		leftPane = (FrameLayout) findViewById(R.id.left_fragment);
		rightPane = (FrameLayout) findViewById(R.id.right_fragment);
		libraryFilerLayout = (RelativeLayout) findViewById(R.id.libaray_filter_menu);
		filerLayoutMenu = (LinearLayout) findViewById(R.id.libaray_filter_menu2);
		//		prog=(ProgressBar) findViewById(R.id.progressBar1);
		//		landscapeSearch = (EditText) findViewById(R.id.landscape_search);
		//		searchLibraryBookInMainFrgment();
		newArrivals = (TextView) findViewById(R.id.new_arrivals);
		topSeller = (TextView) findViewById(R.id.top_seller);
		filters = (TextView)findViewById(R.id.filters);
		featured = (TextView)findViewById(R.id.featured);
		search=(EditText) findViewById(R.id.editText1serarch);

		countrytStrores = (TextView)findViewById(R.id.countryStore);
		allstore = (TextView)findViewById(R.id.allstore);
		groupbooks = (TextView)findViewById(R.id.groupbooks);

		llcatagory=(LinearLayout) findViewById(R.id.cetagry);


		all=(TextView) findViewById(R.id.all);
		magazines=(TextView) findViewById(R.id.magazine);
		newspaper=(TextView) findViewById(R.id.newspaper);
		books=(TextView) findViewById(R.id.book);
		flyer=(TextView)findViewById(R.id.flyer);
		tablerow=(TableRow)findViewById(R.id.tableRow1);
		spinner=(Spinner) findViewById(R.id.spinner1);
		spinner_shedule=(Spinner) findViewById(R.id.spinner2);

		llfeature=(LinearLayout) findViewById(R.id.llfeature);
		lltopsaller=(LinearLayout) findViewById(R.id.lltopseller);
		llnewarrivals=(LinearLayout) findViewById(R.id.llnewarriwals);

		setcolortab(R.id.llfeature);
		//		llnewarrivals.setBackgroundColor(Color.parseColor("#0080FF"));
		//		llfeature.setBackgroundColor(Color.TRANSPARENT);
		//		lltopsaller.setBackgroundColor(Color.TRANSPARENT);


		list.add("SEARCH BY NAME");
		list.add("SEARCH BY AUTHOR");
		list.add("SEARCH BY PUBLISHER");


		list_shedule.add("This Month");
		list_shedule.add("This Week");
		list_shedule.add("Today");

		bookselvefrag=new BookShelveFragment();
		
		/**@author MIPC27
		 * Adapter Change
		 */
		ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(this,
				R.layout.spinnerserachbyname_inflate, list);
		
		dataAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
		spinner.setAdapter(dataAdapter);
/**@author MIPC27
 * Adapter Change
 */

		TimeAdapter myAdapter = new TimeAdapter(this, R.layout.spinnerinflate, list_shedule);

		spinner_shedule.setAdapter(myAdapter);
		
		spinner_shedule.setSelection(0);
		
		//			
		//			ArrayAdapter<String> dataAdapter1 = new ArrayAdapter<String>(this,
		//					android.R.layout.simple_spinner_item, list_shedule);
		//				dataAdapter1.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
		//				spinner_shedule.setAdapter(dataAdapter1);
		//				
		allstore.setOnClickListener(m_click);
		groupbooks.setOnClickListener(m_click);
		all.setOnClickListener(m_click);
		magazines.setOnClickListener(m_click);
		newspaper.setOnClickListener(m_click);
		books.setOnClickListener(m_click);
		flyer.setOnClickListener(m_click);
		searchLibraryBookInMainFrgment();
		appSetting  = new AppSettings(StartFragmentActivity.this);
		if(appSetting.getString("Country")!=null && !appSetting.getString("Country").trim().equalsIgnoreCase("")){
			countrytStrores.setText(appSetting.getString("Country"));
		}
		trnsaction_flag=false;
		setting_flag=false;
		gellaryRefresh_flag=true;
		sheduleremoveflag=false;
//		new TheTask().execute(Constant.GalarryURL+"GetAllCategories/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),Constant.GalarryURL+"GetOnlyFeatured/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),"Featured");


		spinner.setOnItemSelectedListener(new OnItemSelectedListener() {

			@Override
			public void onItemSelected(AdapterView<?> arg0, View v,
					int pos, long id) {
				searchtype=pos;

			}

			@Override
			public void onNothingSelected(AdapterView<?> arg0) {


			}
		});

		featured.setOnClickListener(new OnClickListener() {

			@Override
			public void onClick(View v) {

				//				booksdata=featuredBooksdata;

				
				/**@author MIPC27
				 * Adapter Change
				 */
				TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this,R.layout.spinnerinflate, list_shedule);
				spinner_shedule.setAdapter(myAdapter);
				search.setText("");
				allstore.setTextColor(Color.parseColor("#00f5ff"));
				groupbooks.setTextColor(Color.parseColor("#00f5ff"));
				setcolortab(R.id.llfeature);
				sheduleremoveflag=false;
				spinner_shedule.setVisibility(View.VISIBLE);
				if(featured_flag){
					
					//					booksdata=newArrivalsBooksdata;
					booksdata.clear();
					for(int i=0;i<featuredBooksdata.size();i++){
						booksdata.add(featuredBooksdata.get(i));
					}
					

					SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
					try {
						getcurrent(timeFormat.parse(getcurrentDate()));
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
					
					selectTab(R.id.all);
					searchByCategoryName("");
				}else{
					Log.e("featured...", "s++++++++++++++++++++++++++etOnClickListener"+Constant.GalarryURL);
					ApplicationManager.libraryChacingArray = null;
					cachingLibraryArrayWhileSortingTopandBestSeller = true;
					//	url="http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetOnlyNewArrivals/apikey/998905797b8646fd44134910d1f88c33/StoreId/226/UserId/104";
					new TheTask().execute(null,Constant.GalarryURL+"GetOnlyFeatured/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),"Featured");
				}
				//				if(booksdata!=null)
				//					booksdata.clear();
				//				if(featuredBooksdata!=null)
				//					for(int i=0;i<featuredBooksdata.size();i++){
				//						booksdata.add(featuredBooksdata.get(i));
				//					}
				//				selectTab(R.id.all);
				//				searchByCategoryName("");
				//				SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
				//				try {
				//					getcurrent(timeFormat.parse(getcurrentDate()));
				//				} catch (ParseException e) {
				//					// TODO Auto-generated catch block
				//					e.printStackTrace();
				//				}
			}

		});

		newArrivals.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View arg0) {
			
				/**@author MIPC27
				 * Adapter Change
				 */
				TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this, R.layout.spinnerinflate, list_shedule);

				spinner_shedule.setAdapter(myAdapter);

				search.setText("");
				setcolortab(R.id.llnewarriwals);
				sheduleremoveflag=false;
				spinner_shedule.setVisibility(View.VISIBLE);
				allstore.setTextColor(Color.parseColor("#00f5ff"));
				groupbooks.setTextColor(Color.parseColor("#00f5ff"));
				if(newarrivals_flag){
					//					booksdata=newArrivalsBooksdata;
					booksdata.clear();
					for(int i=0;i<newArrivalsBooksdata.size();i++){
						booksdata.add(newArrivalsBooksdata.get(i));
					}
					selectTab(R.id.all);
					searchByCategoryName("");

					SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
					try {
						getcurrent(timeFormat.parse(getcurrentDate()));
					} catch (ParseException e) {
					
						e.printStackTrace();
					}
				}else{
					Log.e("newArrivals...", "s++++++++++++++++++newArrivals++++++++etOnClickListener");
					ApplicationManager.libraryChacingArray = null;
					cachingLibraryArrayWhileSortingTopandBestSeller = true;
					//	url="http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetOnlyNewArrivals/apikey/998905797b8646fd44134910d1f88c33/StoreId/226/UserId/104";
					new TheTask().execute(null,Constant.GalarryURL+"GetOnlyNewArrivals/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),"NewArrivals");
				}
				//				libraryLoading("NewArrivals",null);//Featured
			}
		});

		topSeller.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View arg0) {
				
				/**@author MIPC27
				 * Adapter Change
				 */
				TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this, R.layout.spinnerinflate, list_shedule);

				spinner_shedule.setAdapter(myAdapter);

				search.setText("");
				allstore.setTextColor(Color.parseColor("#00f5ff"));
				groupbooks.setTextColor(Color.parseColor("#00f5ff"));
				spinner_shedule.setVisibility(View.VISIBLE);
				sheduleremoveflag=false;
				setcolortab(R.id.lltopseller);
				if(topsaller_flag){
					//					booksdata=topSellersBooksdata;
					booksdata.clear();
					for(int i=0;i<topSellersBooksdata.size();i++){
						booksdata.add(topSellersBooksdata.get(i));
					}
					selectTab(R.id.all);
					searchByCategoryName("");
					SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
					try {
						getcurrent(timeFormat.parse(getcurrentDate()));
					} catch (ParseException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
				}else{
					Log.e("topSeller", "s++++++++++++++++++topSeller++++++++etOnClickListener");
					ApplicationManager.libraryChacingArray = null;
					cachingLibraryArrayWhileSortingTopandBestSeller = true;
					//				libraryLoading("TopSellers",null);
					new TheTask().execute(null,Constant.GalarryURL+"GetOnlyBestSeller/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),"BestSeller");
				}
			}
		});

		filters.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View arg0) {
				Log.e("filter...", "****");
				addTopPopup();
			}
		});

		countrytStrores.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View arg0) {
			
				/**@author MIPC27
				 * Adapter Change
				 */
				TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this,R.layout.spinnerinflate, list_shedule);

				spinner_shedule.setAdapter(myAdapter);

				search.setText("");
				Log.e("countrytStrores...", "****");
				if(store_flag){
					getfulllib_flag=false;
					showListPopup(countrytStrores);
				}else{

					urlMakingObject= new UrlMakingClass(StartFragmentActivity.this);
					urlMakingObject.setServiceResultListener(StartFragmentActivity.this);
					urlMakingObject.stroreName();
					getfulllib_flag=false;
				}

			}
		});


		//    	ApplicationManager.changeRightFragment(this, new PurchaseBookFragment(),"purchaseBookFragment");

		//ApplicationManager.changeRightFragment(this, new BookShelveFragment(),"bookShelveFragment");

		
		
		 bndl = getIntent().getExtras();
		
		
	 
		if(bndl!=null){
		  key = bndl.getString(Constant.notification);
		 
		 if(key!=null)
			 if(key.equalsIgnoreCase("yes")){
				 
				// onOptionsItemSelected(purchaseMenuButton);
			 }
		 
		}else {
			
			new TheTask().execute(Constant.GalarryURL+"GetAllCategories/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),Constant.GalarryURL+"GetOnlyFeatured/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),"Featured");
			
			leftPane.setVisibility(View.VISIBLE);
			
		}
		
		setUpActionBar();
		
		
//		
		//
		
	}



	public void SignUp(View v)
	{
		startActivity(new Intent(this, LoginScreen.class));
		finish();

	}



	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		MenuInflater inflater = getMenuInflater();
		inflater.inflate(R.menu.main, menu);
		this.menu = menu;
		bookshelveMenuButton = menu.findItem(R.id.bookshelveMenuButton);
		accountMenuButton = menu.findItem(R.id.accountMenuButton);
		libraryMenuButton = menu.findItem(R.id.libraryMenuButton);
		downloadMenuButton = menu.findItem(R.id.downloadMenuButton);
		purchaseMenuButton = menu.findItem(R.id.purchaseMenuButton);
		downloadedMenuButton = menu.findItem(R.id.downloadedMenuButton);
		addself=menu.findItem(R.id.additem);
		addself.setVisible(false);
		bookshelveMenuButton.setIcon(getResources()
				.getDrawable(R.drawable.enable_bookshelve));
		if(bndl!=null)
			if(key!=null && key.equalsIgnoreCase("yes"))
		 onOptionsItemSelected(purchaseMenuButton);
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {

		case android.R.id.home:
			
			
			
			search.setText("");
			if(trnsaction_flag){
				trnsaction_flag=true;
				setting_flag=false;
				gellaryRefresh_flag=false;
				if(purchase!=null)
					purchase.refresh();
			}else if(setting_flag){

				trnsaction_flag=false;
				setting_flag=true;
				gellaryRefresh_flag=false;
				if(profilefrag!=null)
					profilefrag.refresh();
			}else if(gellaryRefresh_flag){
				trnsaction_flag=false;
				setting_flag=false;
				gellaryRefresh_flag=true;
				store_flag=false;
				featured_flag=false;
				newarrivals_flag=false;
				topsaller_flag=false;
				getfulllib_flag=false;
				mFlag=true;
				sheduleremoveflag=false;
				  spinner_shedule.setVisibility(View.VISIBLE);
				  
				  /**@author MIPC27
				   * Adapter Change
				   */
					TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this, R.layout.spinnerinflate, list_shedule);

					spinner_shedule.setAdapter(myAdapter);

				new TheTask().execute(Constant.GalarryURL+"GetAllCategories/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),Constant.GalarryURL+"GetOnlyFeatured/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),"Featured");
				setcolortab(R.id.llfeature);
			}
			break;

		case R.id.libraryMenuButton:

			//			if(taskFlag){
			//				prog.setVisibility(View.VISIBLE);
			//			}else{
			//				prog.setVisibility(View.GONE);
			//			}
			trnsaction_flag=false;
			gellaryRefresh_flag=true;
			setting_flag=false;
			hideSoftKeyboard(this);
			cachingLibraryArrayWhileSortingTopandBestSeller = false;
			addself.setVisible(false);
			libraryLoading("default",null);

			break;

		case R.id.bookshelveMenuButton:
			//			prog.setVisibility(View.GONE);
			trnsaction_flag=false;
			gellaryRefresh_flag=false;
			setting_flag=false;
			hideSoftKeyboard(this);
			changeTheMenuItemIcon(R.id.bookshelveMenuButton);
			llcatagory.setVisibility(View.GONE);
			//			bookselvefrag=new BookShelveFragment();
			ApplicationManager.changeRightFragment(this,bookselvefrag ,"bookShelveFragment");
			leftPane.setVisibility(View.GONE);
			libraryFilerLayout.setVisibility(View.GONE);

			addself.setVisible(false);
			dismissPopupWindow();

			break;

		case R.id.purchaseMenuButton:
			//			prog.setVisibility(View.GONE);
			
			purchaseFragment();
			
//			trnsaction_flag=true;
//			gellaryRefresh_flag=false;
//			setting_flag=false;
//			hideSoftKeyboard(this);
//			changeTheMenuItemIcon(R.id.purchaseMenuButton);
//			llcatagory.setVisibility(View.GONE);
//			purchase =new PurchaseBookFragment();
//			ApplicationManager.changeRightFragment(this, purchase,"purchaseBookFragment");
//			leftPane.setVisibility(View.GONE);
//			libraryFilerLayout.setVisibility(View.GONE);
//
//			addself.setVisible(false);
//			dismissPopupWindow();
			break;
		case R.id.downloadMenuButton:
			//prog.setVisibility(View.GONE);
			trnsaction_flag=false;
			gellaryRefresh_flag=false;
			setting_flag=false;
			hideSoftKeyboard(this);
			changeTheMenuItemIcon(R.id.downloadMenuButton);
			llcatagory.setVisibility(View.GONE);
			ApplicationManager.changeRightFragment(this, new DownloadBookFragment(),"downloadBookFragment");
			libraryFilerLayout.setVisibility(View.GONE);
			leftPane.setVisibility(View.GONE);
			llcatagory.setVisibility(View.GONE);
			addself.setVisible(false);
			dismissPopupWindow();
			break;

		case R.id.accountMenuButton:
			//			prog.setVisibility(View.GONE);
			trnsaction_flag=false;
			gellaryRefresh_flag=false;
			setting_flag=true;
			hideSoftKeyboard(this);
			changeTheMenuItemIcon(R.id.accountMenuButton);
			leftPane.setVisibility(View.VISIBLE);
			llcatagory.setVisibility(View.GONE);
			libraryFilerLayout.setVisibility(View.GONE);

			addself.setVisible(false);
			profilefrag=new ProfileFragment();
			ApplicationManager.changeRightFragment(this,profilefrag ,"profile");
			ApplicationManager.changeLeftFragment(this, new SettingLeftpane(),"profileList");
			dismissPopupWindow();

			break;


		case R.id.downloadedMenuButton:
			//			prog.setVisibility(View.GONE);
			trnsaction_flag=false;
			gellaryRefresh_flag=false;
			setting_flag=false;
			hideSoftKeyboard(this);
			changeTheMenuItemIcon(R.id.downloadedMenuButton);
			leftPane.setVisibility(View.GONE);
			libraryFilerLayout.setVisibility(View.GONE);
			llcatagory.setVisibility(View.GONE);
			addself.setVisible(false);
			ApplicationManager.changeRightFragment(this, new DownloadedBookFragment(),"downloaded Books");
			dismissPopupWindow();

			break;

		case R.id.additem:

			//			bookselvefrag=new BookShelveFragment();
			//			ApplicationManager.changeRightFragment(this,bookselvefrag ,"bookShelveFragment");
			//			bookselvefrag.
			llcatagory.setVisibility(View.GONE);
			bookselvefrag.createBookSelfDialog(StartFragmentActivity.this);

			break;

		default:
			return super.onOptionsItemSelected(item);
		}

		return true;
	}

	
	
	private void setUpActionBar()
	{
		actionBar = getActionBar();
		//actionBar.
		Drawable d=getResources().getDrawable(R.drawable.header_bg);  
		actionBar.setBackgroundDrawable(d);
		//actionBar.setSubtitle("");

		actionBar.setTitle("Version "+versionName);
		//actionBar.setLogo(R.drawable.evendor_logo_large);
		/*actionBar.setHomeButtonEnabled(true);
		actionBar.setLogo(R.drawable.evendor_logo_large);*/
	}


	public  void changeTheMenuItemIcon(int iconId)
	{
		int bookShelveDrawable = R.drawable.disable_bookshelve;
		int accountDrawable = R.drawable.disable_account;
		int libraryDrawable = R.drawable.disable_library;
		int downloadDrawable = R.drawable.disable_download;
		int purchaseDrawable = R.drawable.disable_purchase;
		int downloadedDrawable = R.drawable.downloaded_books;

		if(iconId == R.id.bookshelveMenuButton)
		{
			bookShelveDrawable = R.drawable.enable_bookshelve;
			//actionBar.setTitle("Bookshelf"+ " Version "+versionName); 
			actionBar.setTitle("Version "+versionName);
		}
		else if(iconId == R.id.accountMenuButton)
		{
			accountDrawable = R.drawable.enable_account;
			//actionBar.setTitle("Settings"+ " Version "+versionName);
			actionBar.setTitle("Version "+versionName);
		}
		else if(iconId == R.id.libraryMenuButton)
		{
			libraryDrawable = R.drawable.enable_library;
			//actionBar.setTitle("Library"+ " Version "+versionName);
			actionBar.setTitle("Version "+versionName);
		}
		else if(iconId == R.id.downloadMenuButton)
		{
			downloadDrawable = R.drawable.enable_download;	
			//actionBar.setTitle("Downloads"+ " Version "+versionName);
			actionBar.setTitle("Version "+versionName);
		}
		else if(iconId == R.id.purchaseMenuButton)
		{
			purchaseDrawable = R.drawable.enable_purchase;	
			//actionBar.setTitle("Transaction History"+ " Version "+versionName);
			actionBar.setTitle("Version "+versionName);
		}

		else if(iconId == R.id.downloadedMenuButton)
		{
			downloadedDrawable = R.drawable.enable_downloaded_books;	
			//actionBar.setTitle("Downloaded Books"+ " Version "+versionName);
			actionBar.setTitle("Version "+versionName);
		}


		bookshelveMenuButton.setIcon(getResources()
				.getDrawable(bookShelveDrawable));
		accountMenuButton.setIcon(getResources()
				.getDrawable(accountDrawable));
		libraryMenuButton.setIcon(getResources()
				.getDrawable(libraryDrawable));
		downloadMenuButton.setIcon(getResources()
				.getDrawable(downloadDrawable));
		purchaseMenuButton.setIcon(getResources()
				.getDrawable(purchaseDrawable));
		downloadedMenuButton.setIcon(getResources()
				.getDrawable(downloadedDrawable));

	}

	@Override
	public void onArticleSelected(int postion) {
		
		InputMethodManager imm = (InputMethodManager) getSystemService(
				INPUT_METHOD_SERVICE);
		if(postion == 0)
			ApplicationManager.changeRightFragment(this, new ProfileFragment(),"profile");
		if(postion == 1)
			ApplicationManager.changeRightFragment(this, new ChangePassswordFragment(),"changePassword");

		if(postion==2){
			ApplicationManager.changeRightFragment(this, new About(),"About");
		}

		if(postion == 3)
		{
			
			showSignoutDialog("Are you sure you want to sign out?");
			
//			finish();
		}

	}
	
	private void showSignoutDialog(String Message) 
	{				
		final CMDialog cmDialog = new CMDialog(this);
		cmDialog.setContent("eVendor", Message);
		cmDialog.setPossitiveButton(CMDialog.YES, new OnClickListener() 
		{

			public void onClick(View v) 
			{
				AppSettings appSetting  = new AppSettings(StartFragmentActivity.this);
				appSetting.saveBoolean(AppSettings.LOGIN_STATE,false);
				 Intent intent = new Intent(StartFragmentActivity.this,
		                    LoginScreen.class);
		            intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
		            intent.addFlags(Intent.FLAG_ACTIVITY_SINGLE_TOP);
		            startActivity(intent);
		            finish();
				cmDialog.cancel();					
			}
		});
		cmDialog.setNegativeButton(CMDialog.CANCEL, new OnClickListener() 
		{

			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}

	
	

	@Override
	public void onRowSelected(int position) {
		// TODO Auto-generated method stub
		tab_selected="";
				
		//Toast.makeText(this, ""+postion , Toast.LENGTH_SHORT).show();
		
		/**@author MIPC27
		 * Adapter Change
		 */
		TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this, R.layout.spinnerinflate, list_shedule);
		spinner_shedule.setAdapter(myAdapter);
		search.setText("");
		llnewarrivals.setBackgroundColor(Color.TRANSPARENT);
		llfeature.setBackgroundColor(Color.TRANSPARENT);
		lltopsaller.setBackgroundColor(Color.TRANSPARENT);
		newArrivals.setTextColor(Color.parseColor("#00f5ff"));
		topSeller.setTextColor(Color.parseColor("#00f5ff"));
		featured.setTextColor(Color.parseColor("#00f5ff"));
		allstore.setTextColor(Color.parseColor("#00f5ff"));
		groupbooks.setTextColor(Color.parseColor("#00f5ff"));
		sheduleremoveflag=true;
		spinner_shedule.setVisibility(View.GONE);
		LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");
		//		
		String Title = null;

		try {
			Title = catList.get(position).getGenre();
		List_Title= Title;
		Log.i("		List_TitleTitle", 		List_Title + "		List_Title");
		} catch (Exception e) {
		
			e.printStackTrace();
		}
		selectTab(R.id.all);

		if(!getfulllib_flag){
			new getFullLibTask().execute(null,Constant.GalarryURL+"GetFullLibrary/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/UserId/"+appSetting.getString("UserId"),Title);
		}else{
			if(booksdata!=null)
				booksdata.clear();
			if(getFullBooksdata!=null)
				for(int i=0;i<getFullBooksdata.size();i++){
					booksdata.add(getFullBooksdata.get(i));
				}
			if(booksdata!=null)
			searchByTitle(Title);
			setList();
			
			
		}
		//		frag.notifyDataChange(Title);

	}

	//	public void ShowPopUp(View v)
	//	{
	//		addTopPopup();
	//	}


	private void addTopPopup() {

		/*if (isPopupWindowShow) {
			// if popupWindow already shows

			isPopupWindowShow = false;
			popupWindow.dismiss();

			return;

		}*/
/**@author MIPC27
 * chnaged x offset in showasDropDown method from 0 to 60 and 
 * popupwindow 200, 250.
 */
		final LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");

		LayoutInflater layoutInflater = (LayoutInflater) getBaseContext()
				.getSystemService(LAYOUT_INFLATER_SERVICE);
		View popupView = layoutInflater
				.inflate(R.layout.library_popup, null);

		popupWindow = new PopupWindow(popupView, 150, 220);
		
		popupWindow.setOutsideTouchable(true);
		popupWindow.setBackgroundDrawable(new BitmapDrawable());

		isPopupWindowShow = true;

		popupWindow.showAsDropDown(findViewById(R.id.popup_attach), 60, 0);

		final TextView filterOne = (TextView) popupView
				.findViewById(R.id.filter_one);
		final TextView filterTwo = (TextView) popupView
				.findViewById(R.id.filter_two);
		final TextView filterThree = (TextView) popupView
				.findViewById(R.id.filter_three);
		final TextView filterFour = (TextView) popupView
				.findViewById(R.id.filter_four);

		filterOne.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub


				filters.setText(filterOne.getText());
				frag.sortByAlphabetically("filterOne",searchListByCat);
				isPopupWindowShow = false;
				popupWindow.dismiss();


			}
		});

		filterTwo.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {


				filters.setText(filterTwo.getText());
				// TODO Auto-generated method stub
				frag.sortByAlphabetically("filterTwo",searchListByCat);
				isPopupWindowShow = false;
				popupWindow.dismiss();

			}
		});

		filterThree.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
//				filters.setText("SEARCH BY PUBLISHER");
				filters.setText(filterThree.getText());
				frag.sortByAlphabetically("filterThree",searchListByCat);
				isPopupWindowShow = false;
				popupWindow.dismiss();
			}
		});

		filterFour.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				
				filters.setText(filterFour.getText());
//				filters.setText("");
				frag.sortByAlphabetically("filterFour",searchListByCat);
				isPopupWindowShow = false;
				popupWindow.dismiss();

			}
		});	

	}

	private void dismissPopupWindow()
	{

		if(popupWindow !=null)
		{
			popupWindow.dismiss();
		}

	}

	//	private void startCommonAsyncTask(String urlDecidedString , String countryStoreId)
	//	{
	//		Log.i("urlDecidedStringur", urlDecidedString);
	//		recoveryTask = new CommonAsynTask(this, urlDecidedString , countryStoreId);
	//		recoveryTask.setListener(this);
	//		recoveryTask.execute((Void[])null);
	//	}


	@Override
	public void onAsynTaskSuccess() {
		// TODO Auto-generated method stub

		ApplicationManager.libraryChacingArray = recoveryTask.getLibraryJsonArray();
		ApplicationManager.changeRightFragment(this, new LibraryGridFragment(),"libraryGridView");
		ApplicationManager.changeLeftFragment(this, new LibraryLeftpane(),"libraryListView");

		if(cachingLibraryArrayWhileSortingTopandBestSeller)
		{
			ApplicationManager.libraryChacingArray = null;
		}

	}

	@Override
	public void onAsynTaskFailure(String message) {
		// TODO Auto-generated method stub
		ApplicationManager.showNoBookDialog = true;
		ApplicationManager.changeRightFragment(this, new LibraryGridFragment(),"libraryGridView");
		ApplicationManager.changeLeftFragment(this, new LibraryLeftpane(),"libraryListView");
	}

	@Override
	public void onConfigurationChanged(Configuration newConfig) {
		// TODO Auto-generated method stub
		super.onConfigurationChanged(newConfig);

		// Checks the orientation of the screen
		if (newConfig.orientation == Configuration.ORIENTATION_LANDSCAPE) {

			LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, LayoutParams.WRAP_CONTENT);
			//        	llp.gravity = Gravity.CENTER_VERTICAL;
			//            llp.setMargins(30, 0, 0, 0);
			//            newArrivals.setLayoutParams(llp);

			//			landscapeSearch.setVisibility(View.VISIBLE);
			if(LibraryGridFragment.portraitSearch !=null)
				LibraryGridFragment.portraitSearch.setVisibility(View.GONE);
			//  Toast.makeText(this, "landscape", Toast.LENGTH_SHORT).show();

		} else if (newConfig.orientation == Configuration.ORIENTATION_PORTRAIT){

			LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, LayoutParams.WRAP_CONTENT);
			//        	llp.gravity = Gravity.CENTER_VERTICAL;
			//            llp.setMargins(-60, 0, 0, 0);
			//            newArrivals.setLayoutParams(llp);
			if(LibraryGridFragment.portraitSearch !=null)
				LibraryGridFragment.portraitSearch.setVisibility(View.VISIBLE);
			//			landscapeSearch.setVisibility(View.GONE);
			//  Toast.makeText(this, "portrait", Toast.LENGTH_SHORT).show();

		}

	}


	private void libraryLoading(String urlDecidedString , String countryStoreId)
	{

		changeTheMenuItemIcon(R.id.libraryMenuButton);
		leftPane.setVisibility(View.VISIBLE);
		libraryFilerLayout.setVisibility(View.VISIBLE);
		llcatagory.setVisibility(View.VISIBLE);


		//    	if(ApplicationManager.libraryChacingArray == null)
		//    	{
		//    	startCommonAsyncTask(urlDecidedString ,countryStoreId);
		//    	}
		//    	else
		{
			ApplicationManager.changeRightFragment(this, new LibraryGridFragment(),"libraryGridView");
			ApplicationManager.changeLeftFragment(this, new LibraryLeftpane(),"libraryListView");
		}

		int  newConfig = getscrOrientation();

		Log.i("ORIENTATION", newConfig + "");

		if (newConfig == Configuration.ORIENTATION_LANDSCAPE) {

			/*LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, LayoutParams.WRAP_CONTENT);
	        	llp.gravity = Gravity.CENTER_VERTICAL;
	            llp.setMargins(30, 0, 0, 0);
	            newArrivals.setLayoutParams(llp);*/
			//			landscapeSearch.setVisibility(View.VISIBLE);

		} else if (newConfig == Configuration.ORIENTATION_PORTRAIT){
			//	        	LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, LayoutParams.WRAP_CONTENT);
			//	        	llp.gravity = Gravity.CENTER_VERTICAL;
			//	            llp.setMargins(-60, 0, 0, 0);
			//	            newArrivals.setLayoutParams(llp);
			//			landscapeSearch.setVisibility(View.GONE);
		}

		dismissPopupWindow();

	}


	private int getscrOrientation() {
		Display getOrient = getWindowManager().getDefaultDisplay();

		int orientation = getOrient.getOrientation();

		// Sometimes you may get undefined orientation Value is 0
		// simple logic solves the problem compare the screen
		// X,Y Co-ordinates and determine the Orientation in such cases
		if (orientation == Configuration.ORIENTATION_UNDEFINED) {

			Configuration config = getResources().getConfiguration();
			orientation = config.orientation;

			if (orientation == Configuration.ORIENTATION_UNDEFINED) {
				// if height and width of screen are equal then
				// it is square orientation
				if (getOrient.getWidth() == getOrient.getHeight()) {
					//orientation = Configuration.ORIENTATION_PORTRAIT;
					orientation = Configuration.ORIENTATION_SQUARE;
				} else { // if width is less than height than it is portrait
					if (getOrient.getWidth() < getOrient.getHeight()) {
						orientation = Configuration.ORIENTATION_PORTRAIT;
					} else { // if it is not any of the above it will definitely
						// be landscape
						orientation = Configuration.ORIENTATION_LANDSCAPE;
					}
				}
			}
		}
		return orientation; // return value 1 is portrait and 2 is Landscape
		// Mode
	}


	public void showListPopup(View anchor) {
		popup = new ListPopupWindow(this);
		popup.setAnchorView(anchor);
		popup.setHeight(370);
		popup.setWidth(300);
		
		StoreAdapter adp=new StoreAdapter(StartFragmentActivity.this, storeList);
		//   ListAdapter adapter = new MyAdapter(this);
		popup.setAdapter(adp);
		store_flag=true;
		popup.show();

	}


	public void showListPopupbyDMT(View anchor) {
		Log.e("-----------------------", "showListPopupbyDMT");
		popup = new ListPopupWindow(this);
		popup.setAnchorView(anchor);
		popup.setHeight(180);
		popup.setWidth(250);
		StoreAdapter adp=new StoreAdapter(StartFragmentActivity.this, storeList);
		//   ListAdapter adapter = new MyAdapter(this);
		popup.setAdapter(adp);
		//		store_flag=true;
		popup.show();

	}


	@Override
	public void onSuccess(final String result) {
		// TODO Auto-generated method stub
		Log.e("STORES1", Allstores +"");
		runOnUiThread(new Runnable() {

			

			public void run() 
			{
				 JSONObject obj;

				try {
					JSONObject responseInJSONObject=new JSONObject(result);

					Allstores =responseInJSONObject.getJSONArray("Allstores");
					storeList.clear();
					for(int i=0;i<Allstores.length();i++){

						if(Allstores.getJSONObject(i).getString(Store.TAG_STORE)!=null && (Allstores.getJSONObject(i).getString(Store.TAG_STORE).trim().length()>0)){
							Store store=new Store();
							store.setId(Allstores.getJSONObject(i).getString(Store.TAG_ID));
							store.setCountry_flag(Allstores.getJSONObject(i).getString(Store.TAG_COUNTRY_FLAG));
							if(Allstores.getJSONObject(i).getString(Store.TAG_COUNTRY_FLAG_URL)!=null)
								store.setCountry_flag_url(Allstores.getJSONObject(i).getString(Store.TAG_COUNTRY_FLAG_URL));
							store.setStore(Allstores.getJSONObject(i).getString(Store.TAG_STORE));
							Log.e("*****************",Allstores.getJSONObject(i).getString(Store.TAG_STORE));
							
							/**@author MI Date : 03/April/14
							 * added if condition for 
							 * moving International store to 0 position.
							 */
							
							if (Allstores.getJSONObject(i).getString(Store.TAG_STORE).contains("International Store") ) {
							    storeList.add(0,store);
							  }else{
							storeList.add(store);
							  }
						}
					}
					

					//Log.i("STORES", Allstores +"");

					//						 showListPopup(filerLayoutMenu);	//filerLayoutMenu
					showListPopup(countrytStrores);
					//						 initiatePopupWindow();

				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}

			}

		});

	}

	@Override
	public void onError(final String errorMessage) {
		// TODO Auto-generated method stub

		runOnUiThread(new Runnable() {

			public void run() {
				if(errorMessage.equals("-389"))
				{
					showErrorDialog("Network error");
				}
				else if(errorMessage.equals("-390"))
				{
					Utils.ConnectionErrorDialog(StartFragmentActivity.this);
				}
			}
		});

	}


	private void searchLibraryBookInMainFrgment()
	{

		search.addTextChangedListener(new TextWatcher() {
			@Override
			public void onTextChanged(CharSequence s, int start, int before,
					int count) {
			}

			@Override
			public void beforeTextChanged(CharSequence s, int start, int count,
					int after) {
			}

			@Override
			public void afterTextChanged(Editable theWatchedText) {

				final LibraryGridFragment frag = (LibraryGridFragment)
						getSupportFragmentManager().findFragmentByTag("libraryGridView");
				//        	JSONArray  type_name_filter = new JSONArray();

				String text = theWatchedText.toString();
				//				ArrayList<BooksData> searchList=new ArrayList<BooksData>();
				searchListByCat.clear();
				if(booksdata!=null)
					for (int i = 0; i < booksdata.size(); i++){


						String title = null;

						try {
							if(searchtype==1){
								title = booksdata.get(i).getAuthor_name();
							}else if(searchtype==2){
								title = booksdata.get(i).getPublisher_name();
							}else{
								title = booksdata.get(i).getTitle();
							}
						} catch (Exception e) {
							// TODO Auto-generated catch block
							e.printStackTrace();
						}


						if ((title.toLowerCase()).contains(text
								.toLowerCase())){

							searchListByCat.add(booksdata.get(i));


						}


					}

				selectTab(R.id.all);
				if(frag!=null)
					if(sheduleremoveflag){
						frag.searchOfBooks(searchListByCat); 
					}else{
						SimpleDateFormat timeFormat1 = new SimpleDateFormat("yyyy-MM-dd");
						try {

							frag.getcurrent1(timeFormat1.parse(getcurrentDate()));
						} catch (ParseException e) {
							// TODO Auto-generated catch block
							e.printStackTrace();
						}
					}
				//frag.searchOfBooks(searchListByCat);

			}
		});
	}

	public static void hideSoftKeyboard(Activity activity) {
		InputMethodManager inputMethodManager = (InputMethodManager)  activity.getSystemService(Activity.INPUT_METHOD_SERVICE);
		inputMethodManager.hideSoftInputFromWindow(activity.getCurrentFocus().getWindowToken(), 0);
	}

	private void showErrorDialog(String errorMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(this);
		cmDialog.setContent("Network", errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{

			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}


	class TheTask extends AsyncTask<String, String, String>{

		ProgressDialog pd;
		HTTP_Connection conn;
		String key="";
		String resp,respData;
		String FeaturedLoad="";
		@Override
		protected void onPreExecute() {

			super.onPreExecute();
			//			prog.setVisibility(View.VISIBLE);
			//			taskFlag=true;
			pd=new ProgressDialog(StartFragmentActivity.this);
			pd.setCancelable(false);
			pd.setIndeterminate(false);
			pd.setMessage("Please wait..");
			pd.show();
		}

		@Override
		protected String doInBackground(String... param) {
			taskFlag=true;
			isRunningTask=true;
			conn=new HTTP_Connection(StartFragmentActivity.this);
			if(param[0]!=null)
			{
				//				"http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetAllCategories/apikey/998905797b8646fd44134910d1f88c33/StoreId/226/UserId/104"
				 resp=conn.getresponseData(param[0]);
				try {
					if(resp!=null){
						JSONObject obj=new JSONObject(resp);
						catList=Parser.getAllCategory(obj.getJSONArray("Allcategories"));

					}
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
			//			"http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetOnlyFeatured/apikey/998905797b8646fd44134910d1f88c33/StoreId/226/UserId/104"
			 respData = conn.getresponseData(param[1]);
			try {
				if(respData!=null){
					if(booksdata!=null)
						booksdata.clear();
					JSONObject obj=new JSONObject(respData);
					
					
					if(obj.has("error") && obj.get("error").equals("true")){
						Log.e("param[2]", "*******"+obj.toString());
						//if(obj.get("error").equals(true)){
							//Toast.makeText(StartFragmentActivity.this, obj.getString("Message"), 2000).show();
					//	}
					return respData;
					
					
					}else {
					if(param[2].equalsIgnoreCase("NewArrivals")){
						if(newArrivalsBooksdata!=null){
							newArrivalsBooksdata.clear();
						}
						newArrivalsBooksdata=Parser.getArrayBooks(obj.getJSONArray(param[2]));
						booksdata=Parser.getArrayBooks(obj.getJSONArray(param[2]));
						newarrivals_flag=true;

					}else if(param[2].equalsIgnoreCase("BestSeller")){
						if(topSellersBooksdata!=null){
							topSellersBooksdata.clear();
						}
						topSellersBooksdata =Parser.getArrayBooks(obj.getJSONArray(param[2]));
						booksdata=Parser.getArrayBooks(obj.getJSONArray(param[2]));
						topsaller_flag=true;

					}else if(param[2].equalsIgnoreCase("Featured")){
						FeaturedLoad= "Featured";
						if(featuredBooksdata!=null){
							featuredBooksdata.clear();
						}
						featuredBooksdata=Parser.getArrayBooks(obj.getJSONArray(param[2]));
						booksdata=Parser.getArrayBooks(obj.getJSONArray(param[2]));
						featured_flag=true;
					}else{
						if(booksdata!=null)
							booksdata.clear();
						booksdata=Parser.getArrayBooks(obj.getJSONArray(param[2]));
					}
					if(searchListByCat!=null){

						searchListByCat.clear();
						for(int i=0;i<booksdata.size();i++){
							searchListByCat.add(booksdata.get(i));

						}
					}
				

					//				Collections.sort(booksdata, new SortByPriceAcs());
				}
				}
				key=param[2];
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}

			return null;
		}



		@Override
		protected void onPostExecute(String result) {
			// TODO Auto-generated method stub
			super.onPostExecute(result);
			 boolean Error = false;
			 JSONObject obj = null;
			isRunningTask=false;
			if(pd!=null){
				if(pd.isShowing()){
					pd.dismiss();

				}
			}
		
			if(respData==null){
				/**@author MIPC27
				 * changed "Library book" to "Store book" 30-jun-2014
				 */
				noBookDialog("no internet connection","Store book", StartFragmentActivity.this);
			}else{
				  Log.e("books_TAG", "respData-"+respData);
				try {
					 obj=new JSONObject(respData);
					if(obj.has("error") && obj.get("error").equals("true")){
						Error= true;
					}else
						Error=false;
				} catch (JSONException e) {
					Error=false;
					e.printStackTrace();
				}
				
				if(!Error){
				 if(booksdata==null ){
			Log.e("hellllllllllll", "band--------");
			 if(key.equalsIgnoreCase("Allcategories")){
    			 noBookDialog("No books","Store book", StartFragmentActivity.this);
    		 }else if(key.equalsIgnoreCase("Featured")){
    			 noBookDialog("No Featured books found","Store book", StartFragmentActivity.this);
    		 }else if(key.equalsIgnoreCase("BestSeller")){
    			 noBookDialog("No Top Sellers books found","Store book", StartFragmentActivity.this);
    		 }else if(key.equalsIgnoreCase("NewArrivals")){
    			 noBookDialog("No NewArrival books found","Store book", StartFragmentActivity.this);
    		 }else
			    	  noBookDialog("no books","Store book", StartFragmentActivity.this);
			      }else{
			    	  Log.e("books_TAG", "SIZE-"+booksdata.size());
			    	  if(booksdata.size()>0){
			    		  AppSettings app=new AppSettings(StartFragmentActivity.this);
							countrytStrores.setText(app.getString("Country"));
							selectTab(R.id.all);
							libraryLoading("", "");
							setlisteneronsheduler();
			  			  
			    	  }else{
			    		  if(key.equalsIgnoreCase("groupBooks")){
			    			  noBookDialog("No group publication is subscribed for you","Group books", StartFragmentActivity.this);
			    		  }else{
			    			/**@author MIPC27
			    			 * Changed here: books not getting refreshed.
			    			 */
			    			  Log.e("books_TAG", "SIZEbooksdatabooksdata-"+booksdata.size());
			    		 if(key.equalsIgnoreCase("Allcategories")){
			    			 noBookDialog("no books","Store book", StartFragmentActivity.this);
			    		 }else if(key.equalsIgnoreCase("Featured")){
			    			 noBookDialog("No Featured books found","Store book", StartFragmentActivity.this);
			    		 }else if(key.equalsIgnoreCase("BestSeller")){
			    			 noBookDialog("No Top Sellers books found","Store book", StartFragmentActivity.this);
			    		 }else if(key.equalsIgnoreCase("NewArrivals")){
			    			 noBookDialog("No New Arrival books found","Store book", StartFragmentActivity.this);
			    		 }
			    		 
			    		  }
			    	  }
			    	  
			      }
				}else{
				

					if(obj!=null){
						if(obj.has("Message")){
							 try {
								noBookDialog(obj.getString("Message"),"Store book", StartFragmentActivity.this);
								/**@author MIPC27
								 * added to refresh data in LibraryGridFragment class
								 */
								getcurrent(null);
							} catch (JSONException e) {
								// TODO Auto-generated catch block
								e.printStackTrace();
							}
						}
					}
				}
			}
			
			 AppSettings app=new AppSettings(StartFragmentActivity.this);
				countrytStrores.setText(app.getString("Country"));
					countrytStrores.setText(app.getString("Country"));
					
					/*selectTab(R.id.all);
					libraryLoading("", "");
					setlisteneronsheduler();*/
				/*if(FeaturedLoad.equals("Featured")){
					libraryLoading("","");
					SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
					try {
						getcurrent(timeFormat.parse(getcurrentDate()));
					} catch (ParseException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
				}*/
			//			taskFlag=false;
			//			prog.setVisibility(View.GONE);
			//			search.setText("");
			
			//			searchByCategoryName("");
			
			//if(key.equalsIgnoreCase("groupBooks")||key.equalsIgnoreCase("Library")){
			//			spinner_shedule.setVisibility(View.GONE);
			//			sheduleremoveflag=true;
			//}else{
			//	spinner_shedule.setVisibility(View.VISIBLE);
			//	sheduleremoveflag=false;
			//}
			
			
//			if(mFlag){
//				mFlag=false;
//				SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");

//				
//			}else{
//				
//			}
			
			

		}

	}


	private void selectTab(int id){

		switch (id) {
		case R.id.all:
			all.setBackgroundResource(R.drawable.rev_round_left);
			//all.setBackgroundColor(Color.parseColor("#00f5ff"));
			all.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.newspaper);
			resetColor(R.id.magazine);
			resetColor(R.id.flyer);

			break;
		case R.id.book:
			books.setBackgroundColor(Color.parseColor("#00f5ff"));
			books.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.all);
			resetColor(R.id.newspaper);
			resetColor(R.id.magazine);
			resetColor(R.id.flyer);
			break;

		case R.id.newspaper:
			newspaper.setBackgroundColor(Color.parseColor("#00f5ff"));
			newspaper.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.all);
			resetColor(R.id.magazine);
			resetColor(R.id.flyer);
			break;

		case R.id.magazine:
			magazines.setBackgroundColor(Color.parseColor("#00f5ff"));
			magazines.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.newspaper);
			resetColor(R.id.all);
			resetColor(R.id.flyer);
			break;
		case R.id.flyer:
			//			flyer.setBackgroundResource(R.drawable.cell_shape);
			flyer.setBackgroundColor(Color.parseColor("#00f5ff"));
			flyer.setTextColor(Color.parseColor("#000000"));
			resetColor(R.id.book);
			resetColor(R.id.newspaper);
			resetColor(R.id.magazine);
			resetColor(R.id.all);
			//			showListPopupbyDMT(flyer);
			break;

		}


	}


	private OnClickListener m_click=new OnClickListener() {

		@Override
		public void onClick(View v) {
			switch (v.getId()) {
			case R.id.all:
				selectTab(R.id.all);
				searchByCategoryName("");

				break;
			case R.id.book:
				selectTab(R.id.book);
				searchByCategoryName("eBook");
				break;

			case R.id.newspaper:
				selectTab(R.id.newspaper);
				searchByCategoryName("Newspapers");

				break;

			case R.id.magazine:
				selectTab(R.id.magazine);
				searchByCategoryName("Magazines");

				break;
			case R.id.flyer:
				selectTab(R.id.flyer);
				searchByCategoryName("Flyers");

				break;

			case R.id.allstore:

				/**@author MIPC27
				 * Adapter Change
				 */
				TimeAdapter myAdapter = new TimeAdapter(StartFragmentActivity.this, R.layout.spinnerinflate, list_shedule);
				/**@author MIPC27
				 * added flag value
				 */

				
				spinner_shedule.setAdapter(myAdapter);
				filters.setText("SORT BY");
				hideKeyboard(search);
				sheduleremoveflag=true;
				spinner_shedule.setVisibility(View.GONE);
				String key=search.getText().toString();
				allstore.setTextColor(Color.parseColor("#ffffff"));
				groupbooks.setTextColor(Color.parseColor("#00f5ff"));
				resetColor();
				if(key.trim().equalsIgnoreCase("")){
					Toast.makeText(StartFragmentActivity.this, "search field should not be empty.", Toast.LENGTH_SHORT).show();
				}else{
					spinner_shedule.setVisibility(View.GONE);
					sheduleremoveflag=true;
					new TheTask().execute(null,Constant.GalarryURL+"GetFullStoreLibrary/apikey/"+appSetting.getString("apikey")+"/StoreId/"+appSetting.getString("countryid")+"/Keyword/"+key+"/UserId/"+appSetting.getString("UserId"),"Library");
				}
				break;

			case R.id.groupbooks:

				/**@author MIPC27
				 * Adapter Change
				 */
				TimeAdapter yAdapter = new TimeAdapter(StartFragmentActivity.this, R.layout.spinnerinflate, list_shedule);

				spinner_shedule.setAdapter(yAdapter);

				allstore.setTextColor(Color.parseColor("#00f5ff"));
				groupbooks.setTextColor(Color.parseColor("#ffffff"));
				resetColor();
				spinner_shedule.setVisibility(View.GONE);
				sheduleremoveflag=true;
				new TheTask().execute(null,Constant.GalarryURL+"GetFreeLibrary/apikey/"+appSetting.getString("apikey")+"/UserId/"+appSetting.getString("UserId"),"groupBooks");
				break;


			}

		}
	};

	private void resetColor(int id){

		switch (id) {
		case R.id.all:
			//			all.setBackgroundColor(Color.parseColor("#000000"));
			all.setBackgroundResource(R.drawable.roundleft);
			all.setTextColor(Color.parseColor("#00f5ff"));
			break;
		case R.id.book:
			books.setBackgroundResource(R.drawable.cell_shape);
			books.setTextColor(Color.parseColor("#00f5ff"));
			break;

		case R.id.newspaper:
			newspaper.setBackgroundResource(R.drawable.cell_shape);
			newspaper.setTextColor(Color.parseColor("#00f5ff"));
			break;

		case R.id.magazine:
			magazines.setBackgroundResource(R.drawable.cell_shape);
			magazines.setTextColor(Color.parseColor("#00f5ff"));
			break;
		case R.id.flyer:
			flyer.setBackgroundResource(R.drawable.cell_shape);
			//			flyer.setBackgroundColor(Color.parseColor("#000000"));
			flyer.setTextColor(Color.parseColor("#00f5ff"));
			break;

		}
	}


	private void searchByCategoryName(String CategoryName){
		String categString = CategoryName;
		if(categString.equals("Flyers"))
			categString="Brochures";
		LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");
		//    	JSONArray  type_name_filter = new JSONArray();

		//      if(!CategoryName.equalsIgnoreCase("all"))
		{
			categoryList.clear();
			//searchListByCat.clear();
			if(StartFragmentActivity.searchListByCat!=null)
				for (int i = 0; i < StartFragmentActivity.searchListByCat.size(); i++){


					String catname = null;

					try {
						catname = StartFragmentActivity.searchListByCat.get(i).getCategory_name();
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}


					if ((catname.toLowerCase()).contains(CategoryName
							.toLowerCase())){

						categoryList.add(StartFragmentActivity.searchListByCat.get(i));

					}

				}
			if(frag!=null){
			if(searchListByCat!=null && categoryList!=null)
				frag.searchOfBooks(categoryList);
			}
			/**@author MI
			 * Added if condition to test
			 */
			if(categoryList.size()<1){
			
			{
				if(!tab_selected.equals("")){
					if(CategoryName.equals("")){
					//	noBookDialog("You have no publications found.", "eVendor", StartFragmentActivity.this);
					}else{
						
							noBookDialog("No "+tab_selected+" "+categString+" found.", "eVendor", StartFragmentActivity.this);
					
					}
					}else if(!List_Title.equals("")){
						if(CategoryName.equals("")){
							noBookDialog("No "+List_Title+" publications found.", "eVendor", StartFragmentActivity.this);
						}else{
							
								noBookDialog("No "+List_Title+" "+categString+" found.", "eVendor", StartFragmentActivity.this);
						
						}
					}
				}
			}
		}
	}


	private void searchByTitle(String title){

		LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");
		//    	JSONArray  type_name_filter = new JSONArray();

		//      if(!CategoryName.equalsIgnoreCase("all"))
		{

			booksdata.clear();
			if(StartFragmentActivity.getFullBooksdata!=null)
				for (int i = 0; i < StartFragmentActivity.getFullBooksdata.size(); i++){


					String catname = null;

					try {
						catname = StartFragmentActivity.getFullBooksdata.get(i).getGenre();
					} catch (Exception e) {
					
						e.printStackTrace();
					}


					if ((catname).equalsIgnoreCase(title
							)){

						booksdata.add(StartFragmentActivity.getFullBooksdata.get(i));
						
					
					}

				}
			/**@author MI
			 * Added else if condition to test
			 */
			if(booksdata!=null){
				if(frag!=null)
				frag.searchOfBooks(booksdata);
			}else
				Log.e("S@@@@@@@@@@@@@@@@@@","searchByTitle searchListByCat is null @@@@@@@@@");
				
		
			if(booksdata.size()<1){
				noBookDialog("No "+List_Title+" publications found.", "eVendor", StartFragmentActivity.this);
			}
		}

	}


	class StoreAdapter extends BaseAdapter{

		private Context mContext;
		ImageLoader imageLoader;

		private LayoutInflater  mInflater;
		private ArrayList<Store> mlist;
		public StoreAdapter(Context ctxt,ArrayList<Store> list){
			mInflater = (LayoutInflater) ctxt.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
			mContext=ctxt;
			mlist=list;
			imageLoader=new ImageLoader(ctxt);
		}

		@Override
		public int getCount() {
			// TODO Auto-generated method stub
			if(mlist==null){
				return 0;
			}
			return mlist.size();
		}

		@Override
		public Object getItem(int arg0) {
			// TODO Auto-generated method stub
			return mlist.get(arg0);
		}

		@Override
		public long getItemId(int position) {
			// TODO Auto-generated method stub
			return position;
		}

		@Override
		public View getView(final int position, View convertView, ViewGroup parent) {

			// View       view = convertView;
			ViewHolder viewHolder;

			if (convertView == null) {
				convertView = mInflater.inflate(R.layout.store_item, parent, false);

				viewHolder = new ViewHolder();

				viewHolder.storeIcon  = (ImageView) convertView.findViewById(R.id.imageView1);
				viewHolder.title  = (TextView) convertView.findViewById(R.id.textView1);
				viewHolder.rl=(RelativeLayout) convertView.findViewById(R.id.RelativeLayout1);
				convertView.setTag(viewHolder);
			}
			else {
				viewHolder = (ViewHolder) convertView.getTag();
			}

		
			viewHolder.title.setText(mlist.get(position).getStore());
		  
			if(mlist.get(position).getCountry_flag_url()!=null){
				//				if(mlist.get(position).getCountry_flag_url().endsWith(".jpg")||mlist.get(position).getCountry_flag_url().endsWith(".png")||mlist.get(position).getCountry_flag_url().endsWith(".JPEG"))
				{
					imageLoader.DisplayImage(mlist.get(position).getCountry_flag_url(), viewHolder.storeIcon);
					//Log.e("-----------------", "   "+mlist.get(position).getCountry_flag_url());
				}
				//				else{
				//					viewHolder.storeIcon.setBackgroundResource(R.drawable.e_logo);
				//				}
			}


			viewHolder.rl.setOnClickListener(new OnClickListener() {

				@Override
				public void onClick(View v) {
					popup.dismiss();
					
					 appSetting.saveString("Country",storeList.get(position).getStore());
					appSetting.saveString("countryid", storeList.get(position).getId());
					allstore.setTextColor(Color.parseColor("#00f5ff"));
					groupbooks.setTextColor(Color.parseColor("#00f5ff"));
					setcolortab(R.id.llfeature);
					sheduleremoveflag=false;
					spinner_shedule.setVisibility(View.VISIBLE);
					search.setText("");
					countrytStrores.setText(storeList.get(position).getStore());
					//					sheduleremoveflag=true;
					trnsaction_flag=false;
					setting_flag=false;
					gellaryRefresh_flag=true;
					store_flag=false;
					featured_flag=false;
					newarrivals_flag=false;
					topsaller_flag=false;
					getfulllib_flag=false;
					new TheTask().execute(Constant.GalarryURL+"GetAllCategories/apikey/"+appSetting.getString("apikey")+"/StoreId/"+storeList.get(position).getId()+"/UserId/"+appSetting.getString("UserId"),
							Constant.GalarryURL+"GetOnlyFeatured/apikey/"+appSetting.getString("apikey")+"/StoreId/"+storeList.get(position).getId()+"/UserId/"+appSetting.getString("UserId"),"Featured");

				}
			});


			return convertView;
		}

		private class ViewHolder {
			public ImageView storeIcon;
			public TextView title;

			public RelativeLayout rl;

		}

	}

	private static Date[] getDaysOfWeek(Date refDate, int firstDayOfWeek) {
		Calendar calendar = Calendar.getInstance();
		calendar.setTime(refDate);
		calendar.set(Calendar.DAY_OF_WEEK, firstDayOfWeek);
		Date[] daysOfWeek = new Date[7];
		for (int i = 0; i < 7; i++) {
			daysOfWeek[i] = calendar.getTime();
			calendar.add(Calendar.DAY_OF_MONTH, 1);
		}
		return daysOfWeek;
	}



	private void filterbyCurrWeek(){
		LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");

		Date refDate = new Date();

		Date[] days = getDaysOfWeek(refDate, Calendar.getInstance().getFirstDayOfWeek());
		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		searchListByCat.clear();
		if(booksdata!=null){
			for(int i=0;i<booksdata.size();i++){
				for (Date day : days) {

					String finalDate = timeFormat.format(day);


					try {
						if(timeFormat.parse(booksdata.get(i).getAdd_time()).compareTo(timeFormat.parse(finalDate))==0){
							Log.e("---------------","'''''''''''''" +finalDate);
							searchListByCat.add(booksdata.get(i));
						}
					} catch (ParseException e) {
					
						e.printStackTrace();
					}
					//  System.out.println(day);
				}
			}
		}

		if(searchListByCat!=null){
			if(frag!=null)
				frag.searchOfBooks(searchListByCat);
		}else{
			Log.e("S@@@@@@@@@@@@@@@@@@","searchListByCat is null @@@@@@@@@");
		}
		if(searchListByCat.size()==0){
			if(!tab_selected.equals(""))
			  noBookDialog("No "+tab_selected +" books found.","eVendor", StartFragmentActivity.this);
			else
				  noBookDialog("Sorry! No books found corresponding this category","eVendor", StartFragmentActivity.this);
		}
		//getcurrentDate();

	}


	private void getTodayList(){

		LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");

		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		searchListByCat.clear();
		if(booksdata!=null){
			for(int i=0;i<booksdata.size();i++){

				String finalDate = getcurrentDate();


				try {
					/**@author MIPC27
					 * changed the method from if(timeFormat.parse(booksdata.get(i).getPublish_time()).compareTo(timeFormat.parse(finalDate))==0){ to 
					 * getAdd_time()
					 */
					if(timeFormat.parse(booksdata.get(i).getAdd_time()).compareTo(timeFormat.parse(finalDate))==0){
						Log.e("---------------","'''''''''''''" +finalDate);
						searchListByCat.add(booksdata.get(i));
					}
				} catch (ParseException e) {
				
					e.printStackTrace();
				}
				//  System.out.println(day);

			}
		}
		
		if(searchListByCat!=null){
			if(frag!=null)
				frag.searchOfBooks(searchListByCat);
		}else
			Log.e("S@@@@@@@@@@@@@@@@@@","getTodayList searchListByCat is null @@@@@@@@@");
		if(searchListByCat.size()==0){
			if(!tab_selected.equals(""))
			  noBookDialog("No "+tab_selected +" books found.","eVendor", StartFragmentActivity.this);
			else
				  noBookDialog("No books found","eVendor", StartFragmentActivity.this);
		}
	}

	private String getcurrentDate(){

		DateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");
		//get current date time with Date()
		Date date = new Date();
		Log.e("*************************8", "  "+dateFormat.format(date));
		return dateFormat.format(date);
	}


	private void getcurrent(Date curdate){
		Log.e("------getcurrent--------","'''getcurrent'''" );
		/*Calendar c = Calendar.getInstance();
		c.setTime(curdate);*/


		//		 Calendar c1 = Calendar.getInstance();
		//		 c1.setTime(curdate);

		final LibraryGridFragment frag = (LibraryGridFragment)
				getSupportFragmentManager().findFragmentByTag("libraryGridView");

	
		SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
		searchListByCat.clear();
		if(booksdata!=null)
			for(int i=0;i<booksdata.size();i++){

				try {
					String finalDate = getcurrentDate();
					Calendar c1 = Calendar.getInstance();
					c1.setTime(timeFormat.parse(booksdata.get(i).getPublish_time()));
					/** @author MI Date : 03/April/14
					 * commented below code : if condition
					 * this brings the library - Featured books by Current Month
					 * Thus Adding all the books
					 */
					/*if(c.get(Calendar.YEAR)==c1.get(Calendar.YEAR)){
						if(c.get(Calendar.MONTH)==c1.get(Calendar.MONTH)){
							Log.e("---------------","'''''''''''''" +finalDate);
							searchListByCat.add(booksdata.get(i));
						}
					}*/
					Log.e("Fetaures categories", booksdata.get(i).getPublisher_name());
					searchListByCat.add(booksdata.get(i));
					
				} catch (ParseException e) {
				
					e.printStackTrace();
				}
				//  System.out.println(day);

			}
		

if(searchListByCat!=null){
	if(frag!=null)
			frag.searchOfBooks(searchListByCat);
}/*else
{
	if(frag!=null){
		searchListByCat = new ArrayList<BooksData>();
		Log.e("S@@@@@@@@@@@@@@@@@@","getcurrent searchListByCat is null @@@@@@@@@"+searchListByCat.si);
	frag.searchOfBooks(searchListByCat);
	}
	
}*/
if(featured_flag){
if(searchListByCat.size()==0){
	if(!tab_selected.equals(""))
	  noBookDialog("No "+tab_selected +" books found.","eVendor", StartFragmentActivity.this);
	else
		  noBookDialog("No books found.","eVendor", StartFragmentActivity.this);
}
}
	}

	class getFullLibTask extends AsyncTask<String, String, String>{

		ProgressDialog pd;
		HTTP_Connection conn;
		private String title;


		@Override
		protected void onPreExecute() {

			super.onPreExecute();

			//			prog.setVisibility(View.VISIBLE);
			//			taskFlag=true;
			pd=new ProgressDialog(StartFragmentActivity.this);
			pd.setCancelable(false);
			pd.setIndeterminate(true);
			pd.setMessage("Please wait..");
			pd.show();
		}

		@Override
		protected String doInBackground(String... param) {
			conn=new HTTP_Connection(StartFragmentActivity.this);
			taskFlag=true;
			//				
			//			"http://www.miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/evendor/api/index/apicall/GetOnlyFeatured/apikey/998905797b8646fd44134910d1f88c33/StoreId/226/UserId/104"
			title=param[2];
			String respData=conn.getresponseData(param[1]);
			try {
				if(respData!=null){
					JSONObject obj=new JSONObject(respData);

					if(obj.getString("Message").equalsIgnoreCase("Success.")){
						
						if(getFullBooksdata!=null)
							getFullBooksdata.clear();
						getFullBooksdata=Parser.getArrayBooks(obj.getJSONArray("Library"));
						if(booksdata!=null)
							booksdata.clear();
						booksdata=Parser.getArrayBooks(obj.getJSONArray("Library"));
						getfulllib_flag=true;
					
					}

					if(searchListByCat!=null){

						searchListByCat.clear();
						for(int i=0;i<booksdata.size();i++){
							searchListByCat.add(booksdata.get(i));

						}
					}


					//				Collections.sort(booksdata, new SortByPriceAcs());
				}
			} catch (JSONException e) {
			
				e.printStackTrace();
			}

			return null;
		}



		@Override
		protected void onPostExecute(String result) {
			// TODO Auto-generated method stub
			super.onPostExecute(result);
			//			prog.setVisibility(View.GONE);
			//			taskFlag=false;
			if(pd!=null){
				if(pd.isShowing()){
					pd.dismiss();

				}
			}
			
      if(booksdata==null){
    	  
    	  noBookDialog("No internet connection","Store book", StartFragmentActivity.this);
      }else{
    	  if(booksdata.size()>0){
    		  selectTab(R.id.all);//
  			
  			
  			searchByTitle(title);  
  			setList();
    	  }else{
    		  Log.e("o }else{", "*******onPostExecute**********");
    		  noBookDialog("No "+title+" books found","Store book", StartFragmentActivity.this);
    	  }
    	  
      }
           
			
			//			SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
			//			try {
			//				getcurrent(timeFormat.parse(getcurrentDate()));
			//			} catch (ParseException e) {
			//			
			//				e.printStackTrace();
			//			}
			//			setlisteneronsheduler();
			//			searchByTitle(title);

			//							searchByCategoryName("");
//						libraryLoading("", "");

		}

	}

	private void setlisteneronsheduler(){
		
		spinner_shedule.setOnItemSelectedListener(new OnItemSelectedListener() {

			@Override
			public void onItemSelected(AdapterView<?> arg0, View v,
					int pos, long id) {
				Log.e("spinner pos", "*******************   "+pos);
				selectTab(R.id.all);
				if(pos==0){
					SimpleDateFormat timeFormat = new SimpleDateFormat("yyyy-MM-dd");
					try {
						getcurrent(timeFormat.parse(getcurrentDate()));
					} catch (ParseException e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
				}else
					if(pos==1){
						filterbyCurrWeek();
					}else if(pos==2){
						getTodayList();
					}
			}

			@Override
			public void onNothingSelected(AdapterView<?> arg0) {


			}
		});
	}

	private void setcolortab(int id){

		switch (id) {
		case R.id.llfeature:
			tab_selected="Featured";
			List_Title="";
			ApplicationManager.POSITION=-1;
			if(  LibraryLeftpane.listAdapter!=null)
				LibraryLeftpane.listAdapter.notifyDataSetChanged();
			llnewarrivals.setBackgroundColor(Color.TRANSPARENT);
			llfeature.setBackgroundColor(Color.parseColor("#0080FF"));
			lltopsaller.setBackgroundColor(Color.TRANSPARENT);
			newArrivals.setTextColor(Color.parseColor("#00f5ff"));
			topSeller.setTextColor(Color.parseColor("#00f5ff"));
			featured.setTextColor(Color.parseColor("#ffffff"));
		Log.e("Hselloooo", "Lfeature----");


			break;

		case R.id.lltopseller:
			tab_selected="Top Sellers";
			List_Title="";
			ApplicationManager.POSITION=-1;
			if(  LibraryLeftpane.listAdapter!=null)
				LibraryLeftpane.listAdapter.notifyDataSetChanged();
			llnewarrivals.setBackgroundColor(Color.TRANSPARENT);
			llfeature.setBackgroundColor(Color.TRANSPARENT);
			lltopsaller.setBackgroundColor(Color.parseColor("#0080FF"));
			newArrivals.setTextColor(Color.parseColor("#00f5ff"));
			topSeller.setTextColor(Color.parseColor("#ffffff"));
			featured.setTextColor(Color.parseColor("#00f5ff"));



			break;

		case R.id.llnewarriwals:
			tab_selected="New Arrival";
			List_Title="";
			ApplicationManager.POSITION=-1;
			if(  LibraryLeftpane.listAdapter!=null)
				LibraryLeftpane.listAdapter.notifyDataSetChanged();
			llnewarrivals.setBackgroundColor(Color.parseColor("#0080FF"));
			llfeature.setBackgroundColor(Color.TRANSPARENT);
			lltopsaller.setBackgroundColor(Color.TRANSPARENT);

			newArrivals.setTextColor(Color.parseColor("#ffffff"));
			topSeller.setTextColor(Color.parseColor("#00f5ff"));
			featured.setTextColor(Color.parseColor("#00f5ff"));

			break;
		default:
			tab_selected="";
			List_Title="";
			break;

		}
	}

	public void resetColor(){
		tab_selected="";
		ApplicationManager.POSITION=-1;
		if(  LibraryLeftpane.listAdapter!=null)
			LibraryLeftpane.listAdapter.notifyDataSetChanged();
		llnewarrivals.setBackgroundColor(Color.TRANSPARENT);
		llfeature.setBackgroundColor(Color.TRANSPARENT);
		lltopsaller.setBackgroundColor(Color.TRANSPARENT);
		newArrivals.setTextColor(Color.parseColor("#00f5ff"));
		topSeller.setTextColor(Color.parseColor("#00f5ff"));
		featured.setTextColor(Color.parseColor("#00f5ff"));
	}

	private void setList(){
//		new Thread(new Runnable() {
//
//			@Override
//			public void run() {
				StartFragmentActivity.searchListByCat.clear();
				for(int i=0;i<StartFragmentActivity.booksdata.size();i++){

					StartFragmentActivity.searchListByCat.add(StartFragmentActivity.booksdata.get(i));
				}
				
//			}
//		}).start();
	}

/**@AUTHOR MI
 * 
 * @param errorMsg- message for alert
 * @param title - Title for alert box
 * @param ctx - Context
 */
	public  void noBookDialog(String errorMsg,String title , Context ctx) 
	{				
		final CMDialog cmDialog = new CMDialog(ctx);
		cmDialog.setContent(title, errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
//		cmDialog.setPossitiveButton(CMDialog.CANCEL, new OnClickListener() 
//		{
//			public void onClick(View v) 
//			{
//				cmDialog.cancel();					
//			}
//		});
		cmDialog.show();
	}

	
	private void hideKeyboard(EditText myEditText){

		InputMethodManager imm = (InputMethodManager)getSystemService(
				Context.INPUT_METHOD_SERVICE);
		imm.hideSoftInputFromWindow(myEditText.getWindowToken(), 0);
	}
	
	@Override
	protected void onStart() {
		// TODO Auto-generated method stub
		super.onStart();
		// EasyTracker.getInstance(this).activityStart(this);  // Add this method.
		
	}
	
	@Override
	protected void onStop() {
		// TODO Auto-generated method stub
		super.onStop();
		
		// The rest of your onStop() code.
		   // EasyTracker.getInstance(this).activityStop(this);  // Add this method.
		
	}
	
	
	
	private void purchaseFragment(){
		
		trnsaction_flag=true;
		gellaryRefresh_flag=false;
		setting_flag=false;
		hideSoftKeyboard(this);
		changeTheMenuItemIcon(R.id.purchaseMenuButton);
		llcatagory.setVisibility(View.GONE);
		purchase =new PurchaseBookFragment();
		ApplicationManager.changeRightFragment(this, purchase,"purchaseBookFragment");
		leftPane.setVisibility(View.GONE);
		libraryFilerLayout.setVisibility(View.GONE);

		addself.setVisible(false);
		dismissPopupWindow();
		
	}
}


