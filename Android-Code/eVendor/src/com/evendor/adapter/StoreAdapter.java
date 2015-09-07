package com.evendor.adapter;


//public class StoreAdapter extends BaseAdapter{
//	
//	private Context mContext;
//	ImageLoader imageLoader;
//	 private LayoutInflater  mInflater;
//	private ArrayList<Store> mlist;
//	public StoreAdapter(Context ctxt,ArrayList<Store> list){
//		 mInflater = (LayoutInflater) ctxt.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
//		mContext=ctxt;
//		mlist=list;
//		imageLoader=new ImageLoader(ctxt);
//	}
//
//	@Override
//	public int getCount() {
//		// TODO Auto-generated method stub
//		if(mlist==null){
//			return 0;
//		}
//		return mlist.size();
//	}
//
//	@Override
//	public Object getItem(int arg0) {
//		// TODO Auto-generated method stub
//		return mlist.get(arg0);
//	}
//
//	@Override
//	public long getItemId(int position) {
//		// TODO Auto-generated method stub
//		return position;
//	}
//
//	@Override
//	public View getView(int position, View convertView, ViewGroup parent) {
//		 
//        View       view = convertView;
//        ViewHolder viewHolder;
//        
//        if (view == null) {
//            view = mInflater.inflate(R.layout.store_item, parent, false);
//            
//            viewHolder = new ViewHolder();
//           
//            viewHolder.storeIcon  = (ImageView) view.findViewById(R.id.imageView1);
//            viewHolder.title  = (TextView) view.findViewById(R.id.textView1);
//           
//            view.setTag(viewHolder);
//        }
//        else {
//            viewHolder = (ViewHolder) view.getTag();
//        }
//        
//        viewHolder.title.setText(mlist.get(position).getStore());
//        if(mlist.get(position).getCountry_flag_url()!=null){
//        	if(mlist.get(position).getCountry_flag_url().endsWith(".jpg")||mlist.get(position).getCountry_flag_url().endsWith(".png")||mlist.get(position).getCountry_flag_url().endsWith(".JPEG")){
//        imageLoader.DisplayImage(mlist.get(position).getCountry_flag_url(), viewHolder.storeIcon);
//        	}
//        }
//        
//      
//        
//		return view;
//	}
//
//	private class ViewHolder {
//    	public ImageView storeIcon;
//        public TextView title;
//      
//        
//        
//    }
//	
//}
