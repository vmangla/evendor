package com.evendor.Modal;

public class Store {
	
	private String id;
	private String store;
	private String country_flag;
	private String country_flag_url;
	public static final String TAG_ID="id";
	public static final String TAG_STORE="store";
	public static final String TAG_COUNTRY_FLAG="country_flag";
	public static final String TAG_COUNTRY_FLAG_URL="country_flag_url";
	
	
	
	
	public String getId() {
		return id;
	}
	public void setId(String id) {
		this.id = id;
	}
	public String getStore() {
		return store;
	}
	public void setStore(String store) {
		this.store = store;
	}
	public String getCountry_flag() {
		return country_flag;
	}
	public void setCountry_flag(String country_flag) {
		this.country_flag = country_flag;
	}
	public String getCountry_flag_url() {
		return country_flag_url;
	}
	public void setCountry_flag_url(String country_flag_url) {
		this.country_flag_url = country_flag_url;
	}

	
	
	
}
