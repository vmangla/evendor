package com.evendor.Modal;

import java.io.Serializable;

public class Allcategories implements Serializable{
	
	public static final String Tag_id="id";
	public static final String Tag_parent_id="parent_id";
	public static final String Tag_genre="genre";
	public static final String Tag_added_date="added_date";
	public static final String Tag_updated_date="updated_date";
	public static final String Tag_status="status";
	
	String id;
	String parent_id;
	String genre;
	String added_date;
	String updated_date;
	String status;
	
	
	
	
	public String getId() {
		return id;
	}
	public void setId(String id) {
		this.id = id;
	}
	public String getParent_id() {
		return parent_id;
	}
	public void setParent_id(String parent_id) {
		this.parent_id = parent_id;
	}
	public String getGenre() {
		return genre;
	}
	public void setGenre(String genre) {
		this.genre = genre;
	}
	public String getAdded_date() {
		return added_date;
	}
	public void setAdded_date(String added_date) {
		this.added_date = added_date;
	}
	public String getUpdated_date() {
		return updated_date;
	}
	public void setUpdated_date(String updated_date) {
		this.updated_date = updated_date;
	}
	public String getStatus() {
		return status;
	}
	public void setStatus(String status) {
		this.status = status;
	}
  
	
	
	

}
