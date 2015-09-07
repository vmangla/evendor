package com.evendor.Android;

import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.EditText;

import com.evendor.utils.CMDialog;
import com.evendor.utils.Utils;
import com.evendor.webservice.UrlMakingClass;
import com.evendor.webservice.WebServiceBase.ServiceResultListener;

public class FBreaderAction extends Activity  {
	

	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
	    // TODO Auto-generated method stub
	    super.onCreate(savedInstanceState);
	    //setContentView(R.layout.main);
	    Intent intent = new Intent(Intent.ACTION_VIEW,Uri.parse("file:///android_asset/testBook.epub"));
	    startActivity(intent);
	}

	

}
