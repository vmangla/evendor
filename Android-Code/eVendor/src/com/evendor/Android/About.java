package com.evendor.Android;

import android.content.pm.PackageInfo;
import android.content.pm.PackageManager.NameNotFoundException;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import com.evendor.Android.R;

public class About extends Fragment{
	
	View rootView;
	TextView version;
	private String versionName;

	@Override
	public View onCreateView(LayoutInflater inflater, ViewGroup container,
			Bundle savedInstanceState) {
		 rootView= inflater.inflate(R.layout.about, container, false);
		 version=(TextView) rootView.findViewById(R.id.appversion);
		 try{
				PackageInfo pInfo = getActivity().getPackageManager().getPackageInfo(getActivity().getPackageName(), 0);
				versionName = pInfo.versionName;
				versionName= versionName.substring(0, versionName.indexOf(".")+2);
				}catch(Exception e){
					e.printStackTrace();
					versionName=getVersion();
				}
		 
		 version.setText("Version  "+versionName);
		 return rootView;
		
	}

	@Override
	public void onViewCreated(View view, Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onViewCreated(view, savedInstanceState);
	}

	@Override
	public void onActivityCreated(Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onActivityCreated(savedInstanceState);
	}
	
	private String  getVersion(){
		
		
		PackageInfo pInfo = null;
		try {
			pInfo = getActivity().getPackageManager().getPackageInfo(getActivity().getPackageName(), 0);
		} catch (NameNotFoundException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return pInfo.versionName;
	}

}
