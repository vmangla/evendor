package com.evendor.Android;

import android.app.ActionBar;
import android.app.Activity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Toast;
import com.evendor.Android.R;





public class LibraryFragmentActivity extends Activity  {
	
	MenuItem bookshelveMenuButton;
	MenuItem accountMenuButton;
	MenuItem libraryMenuButton;
	MenuItem downloadMenuButton;
	MenuItem purchaseMenuButton;
	View homeButton;
	ActionBar actionBar;
	Menu menu;
	

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.library_fragmentactivity);
		//ApplicationManager.changeFragment(this, new ProfileFragment());
		
		Toast.makeText(this, "Activity Stared", Toast.LENGTH_LONG).show();
	//	setUpActionBar();
		
		
	}
	
	
	
	/*public void SignUp(View v)
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
		
		
		actionBar.setTitle("Library"); 
		libraryMenuButton.setIcon(getResources()
     			.getDrawable(R.drawable.enable_library));
	    
	    return true;
	}
	
	

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
	    switch (item.getItemId()) {
//	        case android.R.id.home:
//	            // app icon in action bar clicked; go home
//	            Intent intent = new Intent(this, HomeActivity.class);
//	            intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
//	            startActivity(intent);
//	            return true;
//	            break;
	            
	        case R.id.bookshelveMenuButton:
               changeTheMenuItemIcon(R.id.bookshelveMenuButton);
               startActivity(new Intent(this, RegistrationScreen.class));
       		
       		   finish();
	           
	            break;
	            
	        case R.id.purchaseMenuButton:
	        	changeTheMenuItemIcon(R.id.purchaseMenuButton);
	       
	           
	            break;
	        case R.id.downloadMenuButton:
	        	changeTheMenuItemIcon(R.id.downloadMenuButton);
	           
	            break;
	        case R.id.libraryMenuButton:
	        	changeTheMenuItemIcon(R.id.libraryMenuButton);
	       
	           
	            break;
	        case R.id.accountMenuButton:
	        	changeTheMenuItemIcon(R.id.accountMenuButton);
	        	
	           
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
		Drawable d=getResources().getDrawable(R.drawable.download_bg_light);  
		actionBar.setBackgroundDrawable(d);
		//actionBar.setSubtitle("");
		actionBar.setTitle("Download"); 
		
	}

	
	private void changeTheMenuItemIcon(int iconId)
	{
		int bookShelveDrawable = R.drawable.disable_bookshelve;
		int accountDrawable = R.drawable.disable_account;
		int libraryDrawable = R.drawable.disable_library;
		int downloadDrawable = R.drawable.disable_download;
		int purchaseDrawable = R.drawable.disable_purchase;
		
		
		
		if(iconId == R.id.bookshelveMenuButton)
		{
			bookShelveDrawable = R.drawable.enable_bookshelve;
			actionBar.setTitle("BookShelve"); 
		}
		else if(iconId == R.id.accountMenuButton)
		{
			accountDrawable = R.drawable.enable_account;
			actionBar.setTitle("Settings"); 
		}
		else if(iconId == R.id.libraryMenuButton)
		{
			libraryDrawable = R.drawable.enable_library;
			actionBar.setTitle("Library"); 
		}
	    else if(iconId == R.id.downloadMenuButton)
	   {
	    	downloadDrawable = R.drawable.enable_download;	
	    	actionBar.setTitle("Downloads"); 
	   }
	    else if(iconId == R.id.purchaseMenuButton)
	   {
	    	purchaseDrawable = R.drawable.enable_purchase;	
	    	actionBar.setTitle("Purchase"); 
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
	
	

}

	@Override
	public void onRowSelected(int postion) {
		// TODO Auto-generated method stub
		
		Toast.makeText(this, ""+postion , Toast.LENGTH_SHORT).show();
		LibraryGridFragment frag = (LibraryGridFragment)
		            getSupportFragmentManager().findFragmentById(R.id.book_fragment);

		
	//	frag.notifyDataChange();
		
	}

	

	

//	@Override
//	public void onRowSelected(int postion) {
//		// TODO Auto-generated method stub
//		
//		Toast.makeText(this, ""+postion , Toast.LENGTH_SHORT).show();
//		// LocationGridFragment frag = (LocationGridFragment) getSupportFragmentManager().findFragmentById(R.id.frag_capt);
//		LibraryGridFragment frag = (LibraryGridFragment)
//		            getSupportFragmentManager().findFragmentById(R.id.book_fragment);
//
//		
//		frag.notifyDataChange();
//		
//	}
*/	
	

}
