package com.evender.parser;

import java.util.ArrayList;

import org.json.JSONArray;

import com.evendor.Modal.Allcategories;
import com.evendor.Modal.BooksData;

public class Parser {
	
	
	
	
	public static ArrayList<BooksData> getArrayBooks(JSONArray jarray){
		ArrayList<BooksData> list=new ArrayList<BooksData>();
		try{
		for(int i=0;i<jarray.length();i++){
			BooksData data=new BooksData();
			data.setId(jarray.getJSONObject(i).getString(BooksData.Tag_id));
			data.setProduct_type(jarray.getJSONObject(i).getString(BooksData.Tag_product_type));
			data.setAdd_time(jarray.getJSONObject(i).getString(BooksData.Tag_add_time));
			data.setAdmin_approve(jarray.getJSONObject(i).getString(BooksData.Tag_admin_approve));
			data.setAuthor_id(jarray.getJSONObject(i).getString(BooksData.Tag_author_id));
			data.setAuthor_name(jarray.getJSONObject(i).getString(BooksData.Tag_author_name));
			data.setBest_seller(jarray.getJSONObject(i).getString(BooksData.Tag_best_seller));
			data.setBooks_status(jarray.getJSONObject(i).getString(BooksData.Tag_books_status));
			data.setCat_id(jarray.getJSONObject(i).getString(BooksData.Tag_cat_id));
			data.setCategory_name(jarray.getJSONObject(i).getString(BooksData.Tag_category_name));
			data.setCountry_id(jarray.getJSONObject(i).getString(BooksData.Tag_country_id));
			data.setCountry_name(jarray.getJSONObject(i).getString(BooksData.Tag_country_name));
			
			data.setDescription(jarray.getJSONObject(i).getString(BooksData.Tag_description));
			data.setEdition_id(jarray.getJSONObject(i).getString(BooksData.Tag_edition_id));
			
			data.setFile_name(jarray.getJSONObject(i).getString(BooksData.Tag_file_name));
			data.setFile_size(jarray.getJSONObject(i).getString(BooksData.Tag_file_size));
			
			data.setGenre(jarray.getJSONObject(i).getString(BooksData.Tag_genre));
			
			data.setGroup_name(jarray.getJSONObject(i).getString(BooksData.Tag_group_name));
			data.setIs_featured(jarray.getJSONObject(i).getString(BooksData.Tag_is_featured));
			
			data.setIs_free(jarray.getJSONObject(i).getString(BooksData.Tag_is_free));
			data.setIsbn_number(jarray.getJSONObject(i).getString(BooksData.Tag_isbn_number));
			
			data.setLanguage_id(jarray.getJSONObject(i).getString(BooksData.Tag_language_id));
			
			data.setNo_download(jarray.getJSONObject(i).getString(BooksData.Tag_no_download));
			
			data.setParent_brand_id(jarray.getJSONObject(i).getString(BooksData.Tag_parent_brand_id));
			
			data.setPrice(jarray.getJSONObject(i).getString(BooksData.Tag_price));
			
			data.setPriceText(jarray.getJSONObject(i).getString(BooksData.Tag_priceText));
			
			data.setProduct_id(jarray.getJSONObject(i).getString(BooksData.Tag_product_id));
			
			data.setProductThumbnail(jarray.getJSONObject(i).getString(BooksData.Tag_ProductThumbnail));
			
			data.setProducturl(jarray.getJSONObject(i).getString(BooksData.Tag_Producturl));
			
			data.setPublish_time(jarray.getJSONObject(i).getString(BooksData.Tag_publish_time));
			
			data.setPublisher(jarray.getJSONObject(i).getString(BooksData.Tag_publisher));
			
			data.setPublisher_id(jarray.getJSONObject(i).getString(BooksData.Tag_publisher_id));
			data.setPublisher_name(jarray.getJSONObject(i).getString(BooksData.Tag_publisher_name));
			data.setRating(jarray.getJSONObject(i).getString(BooksData.Tag_rating));
			data.setStatus(jarray.getJSONObject(i).getString(BooksData.Tag_status));
			data.setTitle(jarray.getJSONObject(i).getString(BooksData.Tag_title));
			data.setTotal_pages(jarray.getJSONObject(i).getString(BooksData.Tag_total_pages));
			list.add(data);
			
		}
		return list;
		}catch(Exception e){
			
			e.printStackTrace();
			
		}
		
		return null;
		
		
		
	}
	
	public static ArrayList<Allcategories> getAllCategory(JSONArray jarray){
		
		ArrayList<Allcategories> list=new ArrayList<Allcategories>();
		try{
		for(int i=0;i<jarray.length();i++){
			Allcategories data=new Allcategories();
			data.setAdded_date(jarray.getJSONObject(i).getString(Allcategories.Tag_added_date));
			data.setGenre(jarray.getJSONObject(i).getString(Allcategories.Tag_genre));
			data.setId(jarray.getJSONObject(i).getString(Allcategories.Tag_id));
			data.setParent_id(jarray.getJSONObject(i).getString(Allcategories.Tag_parent_id));
			data.setStatus(jarray.getJSONObject(i).getString(Allcategories.Tag_status));
			data.setUpdated_date(jarray.getJSONObject(i).getString(Allcategories.Tag_updated_date));
			list.add(data);
		}
		return list;
		}catch(Exception e){
			
			e.printStackTrace();
		}
		
		return null;
	}
	
	

}
