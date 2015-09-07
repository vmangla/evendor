package com.evendor.Android;

import java.text.SimpleDateFormat;
import java.util.Date;

import org.json.JSONArray;

import android.app.Activity;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentActivity;
import android.view.inputmethod.InputMethodManager;

import com.evendor.Android.R;
import com.evendor.model.ListModel;




public class ApplicationManager
{
	public static final int OFFLINE_FILE_CONTEXTMENU	= 1001;
	public static int POSITION = 0;
	
	
	public static String currentScreenPath;
	
	public static String nextScreenPath; 
	
	public static String pathMantaining2;
	
	public static String pathMantaining3;
	
	public static boolean showReadButton = false;
	
	public static boolean showNoBookDialog = false;
	
	public static JSONArray libraryChacingArray;
	
//	public static final ListModel[] gridModel = { 
//        new ListModel(0, "222 W. Washington Ave.", R.drawable.thumb_222_w_washington),
//        new ListModel(1, "150 E. Gilman St", R.drawable.thumb_150_e_gilman), 
//        new ListModel(2, "114 State St", R.drawable.thumb_114_state_st),
//        new ListModel(3, "23 N. Pinckney St", R.drawable.thumb_23_n_pinckney)
//    };
	
	public static final ListModel[] gridModel = { 
        new ListModel(0, "BOOK 1", 0),
        new ListModel(1, "BOOK 2", 0), 
        new ListModel(2, "BOOK 3", 0),
        new ListModel(3, "BOOK 4", 0),
        new ListModel(0, "BOOK 5", 0),
        new ListModel(1, "BOOK 6", 0), 
        new ListModel(2, "BOOK 7", 0),
        new ListModel(3, "BOOK 8", 0),
        new ListModel(0, "BOOK 10", 0),
        new ListModel(1, "BOOK 11", 0), 
        new ListModel(2, "BOOK 12", 0),
        new ListModel(3, "BOOK 13", 0),
        new ListModel(0, "BOOK 14", 0),
        new ListModel(1, "BOOK 15", 0), 
        new ListModel(2, "BOOK 16", 0),
        new ListModel(3, "BOOK 17", 0),
        new ListModel(3, "BOOK 18", 0)
    };
	
	
	public static final ListModel[] sLocations = { 
        new ListModel(0, "Recommended", 0),
        new ListModel(1, "Business", 0), 
        new ListModel(2, "Art & Culture", 0),
        new ListModel(3, "Technology", 0),
        new ListModel(0, "Politics", 0),
        new ListModel(1, "Humour", 0), 
        new ListModel(2, "Sports", 0),
        new ListModel(3, "Travel", 0),
        new ListModel(3, "Automative", 0)
    };
	
	public static final ListModel[] settingArray = { 
        new ListModel(0, "Profile Setting", 0),
        new ListModel(1, "Change Password", 0), 
        new ListModel(2, "Payment Information", 0),
        new ListModel(3, "Technology", 0),
        new ListModel(0, "Bookmarks", 0),
        new ListModel(1, "Store", 0), 
        new ListModel(2, "Sign Out", 0),
        
    };
	
	
//	public static final ListModel[] gridModel1 = { 
//        new ListModel(0, "222 W. Washington Ave.", R.drawable.thumb_222_w_washington)
//        
//    };
	
	public static void changeRightFragment(FragmentActivity activity, Fragment newFragment,String fragmentName)
	{
		
		
		android.support.v4.app.FragmentTransaction transaction = activity.getSupportFragmentManager().beginTransaction();
		transaction.replace(R.id.right_fragment, newFragment, fragmentName);

		// Replace whatever is in the fragment_container view with this fragment,
		// and add the transaction to the back stack
		/*if(fragmentName.equals("bookShelveFragment"))
		{
		transaction.addToBackStack(null);
		}*/

		// Commit the transaction
		transaction.commit();
		
	}
	
	public static void changeLeftFragment(FragmentActivity activity, Fragment newFragment,String fragmentName)
	{
		
		
		android.support.v4.app.FragmentTransaction transaction = activity.getSupportFragmentManager().beginTransaction();
		transaction.replace(R.id.left_fragment, newFragment,fragmentName);

		// Replace whatever is in the fragment_container view with this fragment,
		// and add the transaction to the back stack
		
		//transaction.addToBackStack(null);

		// Commit the transaction
		transaction.commit();
		
	}
	
	
	public static void addLeftFragment(FragmentActivity activity, Fragment newFragment)
	{
		
		
		android.support.v4.app.FragmentTransaction transaction = activity.getSupportFragmentManager().beginTransaction();
		transaction.add(R.id.left_fragment, newFragment);

		// Replace whatever is in the fragment_container view with this fragment,
		// and add the transaction to the back stack
		
		//transaction.addToBackStack(null);

		// Commit the transaction
		transaction.commit();
		
	}
	
	
	
	
	public static void hideSoftKeyboard(Activity activity) {
	    InputMethodManager inputMethodManager = (InputMethodManager)  activity.getSystemService(Activity.INPUT_METHOD_SERVICE);
	    inputMethodManager.hideSoftInputFromWindow(activity.getCurrentFocus().getWindowToken(), 0);
	}
	
	
	public static String getconvertdate1(String date)
	{
		SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
	 //   inputFormat.setTimeZone(TimeZone.getTimeZone("UTC"));
		SimpleDateFormat outputFormat = new SimpleDateFormat("yyyy-MM-dd");
	//    SimpleDateFormat outputFormat = new SimpleDateFormat("dd MMM yyyy");
	    Date parsed = new Date();
	   
	        try {
				parsed = inputFormat.parse(date);
			} catch (java.text.ParseException e) {
				e.printStackTrace();
			}
	   
	    String outputText = outputFormat.format(parsed);
	    return outputText;
	}
		
}
