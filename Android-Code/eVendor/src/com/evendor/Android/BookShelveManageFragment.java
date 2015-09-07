package com.evendor.Android;

import org.json.JSONObject;

import android.app.ActionBar;
import android.app.Activity;
import android.graphics.Color;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.ListAdapter;
import android.widget.ListPopupWindow;
import android.widget.TextView;

import com.evendor.Android.R;
import com.evendor.db.AppDataSavedMethhods;
import com.evendor.utils.CMDialog;

public class BookShelveManageFragment extends Fragment  {
	
	View rootView;
	EditText booksheleveName;
	EditText boolshelveColor;
	Button create;
	ActionBar actionBar;
	ListPopupWindow popup;

	@Override
	public void onCreate(Bundle savedInstanceState) {
		// TODO Auto-generated method stub
		super.onCreate(savedInstanceState);
		}
	
	public View onCreateView(LayoutInflater inflater, ViewGroup container,Bundle savedInstanceState) 
	{
		
		 super.onActivityCreated(savedInstanceState);
         rootView= inflater.inflate(R.layout.setting_bookshelves, container, false);
 
		 return rootView;
	}

	public void onActivityCreated(Bundle savedInstanceState) 
     {
		 super.onActivityCreated(savedInstanceState);
		 
		 Activity activity =getActivity();

		 booksheleveName  = (EditText) activity.findViewById(R.id.shelve_name);
		 
		 boolshelveColor  = (EditText) activity.findViewById(R.id.shelve_color);
		 
		 boolshelveColor.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				showListPopup(boolshelveColor);
			}
		});
		 
		 create = (Button) activity.findViewById(R.id.create);
		 

		 create.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				// TODO Auto-generated method stub
				
				String ShelfName =  booksheleveName.getText().toString();
				
				
				if(!booksheleveName.getText().toString().equals("") && !boolshelveColor.getText().toString().equals(""))
				{
				 AppDataSavedMethhods appDataSavedMethod = new AppDataSavedMethhods (getActivity());
                 appDataSavedMethod.SaveBookShelve(ShelfName, boolshelveColor.getText().toString());
                 ApplicationManager.changeRightFragment(getActivity(), new BookShelveFragment(),"bookShelveFragment");
//				 MainFragmentActivity.leftPane.setVisibility(View.GONE);
                 StartFragmentActivity.leftPane.setVisibility(View.GONE);
				 setUpActionBar();
				}
				else
				{
					showErrorDialog("All fields are mandatary");
				}
			}
		});
		 	
	}
	
	@Override
	public void onDetach() {
		// TODO Auto-generated method stub
		super.onDetach();
	}
	
	private void showErrorDialog(String errorMsg) 
	{				
		final CMDialog cmDialog = new CMDialog(getActivity());
		cmDialog.setContent(CMDialog.CREATE_SHELF, errorMsg);
		cmDialog.setPossitiveButton(CMDialog.OK, new OnClickListener() 
		{
			
			public void onClick(View v) 
			{
				cmDialog.cancel();					
			}
		});
		cmDialog.show();
	}
	
	
	private void setUpActionBar()
	{
		 actionBar = getActivity().getActionBar();
		 actionBar.setTitle("BookShelf"); 

	}
	
	public void showListPopup(View anchor) {
        popup = new ListPopupWindow(getActivity());
        popup.setAnchorView(anchor);

        ListAdapter adapter = new MyAdapter(getActivity());
        popup.setAdapter(adapter);

        popup.show();
    }

    public  class MyAdapter extends BaseAdapter implements ListAdapter {
       
    	private final String[] list = new String[] {"Blue","Green","Orange"};
        private Activity activity;
        
        JSONObject jsonObject = null;
        String country = null;

        public MyAdapter(Activity activity) {
            this.activity = activity;
        }

        @Override
        public int getCount() {
            return list.length;
        }

        @Override
        public Object getItem(int position) {
            return list[position];
        }

        @Override
        public long getItemId(int position) {
            return position;
        }

        private static final int textid = 1234;
        @Override
        public View getView(final int position, View convertView, ViewGroup parent) {
            TextView text = null;
            if (convertView == null) {
                LinearLayout layout = new LinearLayout(activity);
                layout.setOrientation(LinearLayout.HORIZONTAL);

                text = new TextView(activity);
                LinearLayout.LayoutParams llp = new LinearLayout.LayoutParams(LayoutParams.WRAP_CONTENT, 30);
                llp.setMargins(5, 0, 0, 0);
                text.setLayoutParams(llp);
                text.setId(textid);

                layout.addView(text);

                convertView = layout;
            }
            else {
                text = (TextView)convertView.findViewById(textid);
            }
            text.setText(list[position]);
            if(position == 0)
            {
            	convertView.setBackgroundColor(Color.BLUE);
            }
            else if(position == 1)
            {
            	convertView.setBackgroundColor(Color.GREEN);
            }
            
            else if(position == 2)
            {
            	convertView.setBackgroundColor(Color.parseColor("#FF6600"));
            }
            
            convertView.setOnClickListener(new View.OnClickListener() {
				
				@Override
				public void onClick(View v) {
					// TODO Auto-generated method stub
					
					boolshelveColor.setText(list[position]);
					popup.dismiss();
				}
			});
            return convertView;
        }
    }
      
}

	

	


