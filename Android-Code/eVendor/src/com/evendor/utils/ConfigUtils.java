package com.evendor.utils;

import java.util.ArrayList;
import java.util.List;

import android.content.Context;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;

public class ConfigUtils {

	public static final String PREFERENCE_NAME = "com.yyxu.download";

	public static SharedPreferences getPreferences(Context context) {
		return context.getSharedPreferences(PREFERENCE_NAME,
				Context.MODE_WORLD_WRITEABLE);
	}

	public static String getString(Context context, String key) {
		SharedPreferences preferences = getPreferences(context);
		if (preferences != null)
			return preferences.getString(key, "");
		else
			return "";
	}

	public static void setString(Context context, String key, String value) {
		SharedPreferences preferences = getPreferences(context);
		if (preferences != null) {
			Editor editor = preferences.edit();
			editor.putString(key, value);
			editor.commit();
		}
	}

	public static final int URL_COUNT = 3;
	public static final String KEY_URL = "url";
	public static final int ICON_PATH_COUNT = 3;
	public static final String  KEY_BOOK_ICON_PATH = "bookIconPath";
	public static final int BOOK_TITLE_COUNT = 3;
	public static final String  KEY_BOOK_TITLE = "bookTitle";
	public static final int BOOK_PRICE_COUNT = 3;
	public static final String  KEY_BOOK_PRICE = "bookPrice";//bookDescription
	
	public static final int bookDescription_COUNT = 3;
	public static final String  KEY_bookDescription = "bookDescription";//bookDescription

	public static final int bookUrl_COUNT = 3;
	public static final String  KEY_bookUrl = "bookUrl";//bookDescription

	public static final int bookId_COUNT = 3;
	public static final String  KEY_bookId = "bookId";//bookDescription

	public static final int bookPath_COUNT = 3;
	public static final String  KEY_bookPath = "bookPath";//bookDescription

	public static final int bookAuthor_COUNT = 3;
	public static final String  KEY_bookAuthor = "bookAuthor";//bookDescription

	public static final int bookPublisherName_COUNT = 3;
	public static final String  KEY_bookPublisherName = "bookPublisherName";//bookDescription

	public static final int bookPublishedDate_COUNT = 3;
	public static final String  KEY_bookPublishedDate = "bookPublishedDate";//bookDescription

	public static final int bookSize_COUNT = 3;
	public static final String  KEY_bookSize = "bookSize";//bookDescription

	public static final int bookCategory_COUNT = 3;
	public static final String  KEY_bookCategory = "bookCategory";//bookDescription
	
	public static final int filename_COUNT = 3;
	public static final String  KEY_filename = "filename";//bookDescription


	
	public static void storeURL(Context context, int index, String url) {
		setString(context, KEY_URL + index, url);
	}

	public static void clearURL(Context context, int index) {
		setString(context, KEY_URL + index, "");
	}

	public static String getURL(Context context, int index) {
		return getString(context, KEY_URL + index);
	}

	public static List<String> getURLArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < URL_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_URL + i));
			}
		}
		return urlList;
	}
	
	
	
	
	
	public static void storeIconPath(Context context, int index, String iconPath) {
		setString(context, KEY_BOOK_ICON_PATH + index, iconPath);
	}

	public static void clearIconPath(Context context, int index) {
		setString(context, KEY_BOOK_ICON_PATH + index, "");
	}

	public static String getIconPath(Context context, int index) {
		return getString(context, KEY_BOOK_ICON_PATH + index);
	}
	
	
	public static List<String> getIconPathArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < ICON_PATH_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_BOOK_ICON_PATH + i));
			}
		}
		return urlList;
	}
	
	
	public static void storeBookTitle(Context context, int index, String iconPath) {
		setString(context, KEY_BOOK_TITLE + index, iconPath);
	}

	public static void clearBookTitle(Context context, int index) {
		setString(context, KEY_BOOK_TITLE + index, "");
	}

	public static String getBookTitleString(Context context, int index) {
		return getString(context, KEY_BOOK_TITLE + index);
	}
	
	
	public static List<String> getBookTitleArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < BOOK_TITLE_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_BOOK_TITLE + i));
			}
		}
		return urlList;
	}
	
	
	public static void storefilename(Context context, int index, String iconPath) {
		setString(context, KEY_filename + index, iconPath);
	}

	public static void clearfilename(Context context, int index) {
		setString(context, KEY_filename + index, "");
	}

	public static String getfilenameString(Context context, int index) {
		return getString(context, KEY_filename + index);
	}
	
	
	public static List<String> getfilenameArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < filename_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_filename + i));
			}
		}
		return urlList;
	}
	
	
	
	
	
	public static void storeBookPrice(Context context, int index, String iconPath) {
		setString(context, KEY_BOOK_PRICE + index, iconPath);
	}

	public static void clearBookPrice(Context context, int index) {
		setString(context, KEY_BOOK_PRICE + index, "");
	}

	public static String getBookPriceString(Context context, int index) {
		return getString(context, KEY_BOOK_PRICE + index);
	}
	
	
	public static List<String> getBookPriceArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < BOOK_PRICE_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_BOOK_PRICE + i));
			}
		}
		return urlList;
	}
	

	public static void storebookDescription(Context context, int index, String iconPath) {
		setString(context, KEY_bookDescription + index, iconPath);
	}

	public static void clearbookDescription(Context context, int index) {
		setString(context, KEY_bookDescription + index, "");
	}

	public static String getbookDescriptionString(Context context, int index) {
		return getString(context, KEY_bookDescription + index);
	}
	
	
	public static List<String> getbookDescriptionArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookDescription_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookDescription + i));
			}
		}
		return urlList;
	}


	
	public static void storebookUrl(Context context, int index, String iconPath) {
		setString(context, KEY_bookUrl + index, iconPath);
	}

	public static void clearbookUrl(Context context, int index) {
		setString(context, KEY_bookUrl + index, "");
	}

	public static String getbookUrlString(Context context, int index) {
		return getString(context, KEY_bookUrl + index);
	}
	
	
	public static List<String> getbookUrlArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookUrl_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookUrl + i));
			}
		}
		return urlList;
	}


	
	public static void storebookId(Context context, int index, String iconPath) {
		setString(context, KEY_bookId + index, iconPath);
	}

	public static void clearbookId(Context context, int index) {
		setString(context, KEY_bookId + index, "");
	}

	public static String getbookIdString(Context context, int index) {
		return getString(context, KEY_bookId + index);
	}
	
	
	public static List<String> getbookIdArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookId_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookId + i));
			}
		}
		return urlList;
	}


	
	public static void storebookPath(Context context, int index, String iconPath) {
		setString(context, KEY_bookPath + index, iconPath);
	}

	public static void clearbookPath(Context context, int index) {
		setString(context, KEY_bookPath + index, "");
	}

	public static String getbookPathString(Context context, int index) {
		return getString(context, KEY_bookPath + index);
	}
	
	
	public static List<String> getbookPathArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookPath_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookPath + i));
			}
		}
		return urlList;
	}


	
	public static void storebookAuthor(Context context, int index, String iconPath) {
		setString(context, KEY_bookAuthor + index, iconPath);
	}

	public static void clearbookAuthor(Context context, int index) {
		setString(context, KEY_bookAuthor + index, "");
	}

	public static String getbookAuthorString(Context context, int index) {
		return getString(context, KEY_bookAuthor + index);
	}
	
	
	public static List<String> getbookAuthorArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookAuthor_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookAuthor + i));
			}
		}
		return urlList;
	}


	public static void storebookPublisherName(Context context, int index, String iconPath) {
		setString(context, KEY_bookPublisherName + index, iconPath);
	}

	public static void clearbookPublisherName(Context context, int index) {
		setString(context, KEY_bookPublisherName + index, "");
	}

	public static String getbookPublisherNameString(Context context, int index) {
		return getString(context, KEY_bookPublisherName + index);
	}
	
	
	public static List<String> getbookPublisherNameArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookPublisherName_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookPublisherName + i));
			}
		}
		return urlList;
	}


	
	public static void storebookPublishedDate(Context context, int index, String iconPath) {
		setString(context, KEY_bookPublishedDate + index, iconPath);
	}

	public static void clearbookPublishedDate(Context context, int index) {
		setString(context, KEY_bookPublishedDate + index, "");
	}

	public static String getbookPublishedDateString(Context context, int index) {
		return getString(context, KEY_bookPublisherName + index);
	}
	
	
	public static List<String> getbookPublishedDateArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookPublishedDate_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookPublishedDate + i));
			}
		}
		return urlList;
	}


	public static void storebookSize(Context context, int index, String iconPath) {
		setString(context, KEY_bookSize + index, iconPath);
	}

	public static void clearbookSize(Context context, int index) {
		setString(context, KEY_bookSize + index, "");
	}

	public static String getbookSizeString(Context context, int index) {
		return getString(context, KEY_bookSize + index);
	}
	
	
	public static List<String> getbookSizeArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookSize_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookSize + i));
			}
		}
		return urlList;
	}

	
	public static void bookCategorySize(Context context, int index, String iconPath) {
		setString(context, KEY_bookCategory + index, iconPath);
	}

	public static void bookCategorySize(Context context, int index) {
		setString(context, KEY_bookCategory + index, "");
	}

	public static String getbookCategoryString(Context context, int index) {
		return getString(context, KEY_bookCategory + index);
	}
	
	
	public static List<String> getbookCategoryArray(Context context) {
		List<String> urlList = new ArrayList<String>();
		for (int i = 0; i < bookCategory_COUNT; i++) {
			if (!TextUtils.isEmpty(getURL(context, i))) {
				urlList.add(getString(context, KEY_bookCategory+ i));
			}
		}
		return urlList;
	}
	

	public static final String KEY_RX_WIFI = "rx_wifi";
	public static final String KEY_TX_WIFI = "tx_wifi";
	public static final String KEY_RX_MOBILE = "tx_mobile";
	public static final String KEY_TX_MOBILE = "tx_mobile";
	public static final String KEY_Network_Operator_Name = "operator_name";

	public static int getInt(Context context, String key) {
		SharedPreferences preferences = getPreferences(context);
		if (preferences != null)
			return preferences.getInt(key, 0);
		else
			return 0;
	}

	public static void setInt(Context context, String key, int value) {
		SharedPreferences preferences = getPreferences(context);
		if (preferences != null) {
			Editor editor = preferences.edit();
			editor.putInt(key, value);
			editor.commit();
		}
	}

	public static long getLong(Context context, String key) {
		SharedPreferences preferences = getPreferences(context);
		if (preferences != null)
			return preferences.getLong(key, 0L);
		else
			return 0L;
	}

	public static void setLong(Context context, String key, long value) {
		SharedPreferences preferences = getPreferences(context);
		if (preferences != null) {
			Editor editor = preferences.edit();
			editor.putLong(key, value);
			editor.commit();
		}
	}

	public static void addLong(Context context, String key, long value) {
		setLong(context, key, getLong(context, key) + value);
	}
}
