package com.evendor.Modal;

import java.io.Serializable;

public class BooksData implements Serializable{
	

	public static final String Tag_id="id";
	public static final String Tag_product_type="product_type";
	public static final String  Tag_publisher_id="publisher_id";
	public static final String Tag_author_id="author_id";
	public static final String  Tag_title="title";
	public static final String Tag_edition_id="edition_id";
	public static final String  Tag_description="description";
	public static final String  Tag_isbn_number="isbn_number";
	public static final String Tag_publisher="publisher";
	public static final String   Tag_total_pages="total_pages";
	public static final String  Tag_cat_id="cat_id";
	public static final String  Tag_parent_brand_id="parent_brand_id";
	public static final String Tag_file_name="file_name";
	public static final String Tag_file_size="file_size";
	public static final String Tag_status="status";
	public static final String Tag_is_featured="is_featured";
	public static final String  Tag_admin_approve="admin_approve";
	public static final String Tag_publish_time="publish_time";
	public static final String Tag_add_time="add_time";
	public static final String Tag_best_seller="best_seller";
	public static final String Tag_no_download="no_download";
	public static final String Tag_category_name="category_name";
	public static final String Tag_genre="genre";
	public static final String Tag_product_id="product_id";
	public static final String Tag_country_id="country_id";
	public static final String Tag_language_id="language_id";
	public static final String Tag_price="price";
	public static final String Tag_country_name="country_name";
	public static final String Tag_priceText="priceText";
	public static final String Tag_ProductThumbnail="ProductThumbnail";
	public static final String Tag_Producturl="Producturl";
	public static final String Tag_publisher_name="publisher_name";
	public static final String Tag_author_name="author_name";
	public static final String Tag_rating="rating";
	public static final String  Tag_books_status="books_status";
	public static final String  Tag_is_free="is_free";
	public static final String Tag_group_name="group_name";
	
	
	
	
	String id;
	String product_type;
	String  publisher_id;
	String author_id;
	String  title;
	String edition_id;
	String  description;
	String  isbn_number;
	String publisher;
	String   total_pages;
	String  cat_id;
	String  parent_brand_id;
	String file_name;
	String file_size;
	String status;
	String is_featured;
	String  admin_approve;
	String publish_time;
	String add_time;
	String best_seller;
	String no_download;
	String category_name;
	String genre;
	String product_id;
	String country_id;
	String language_id;
	String price;
	String country_name;
	String priceText;
	String ProductThumbnail;
	String Producturl;
	String publisher_name;
	String author_name;
	String rating;
	String  books_status;
	String  is_free;
	String group_name;
	
	
	
	public String getId() {
		return id;
	}
	public void setId(String id) {
		this.id = id;
	}
	public String getProduct_type() {
		return product_type;
	}
	public void setProduct_type(String product_type) {
		this.product_type = product_type;
	}
	public String getPublisher_id() {
		return publisher_id;
	}
	public void setPublisher_id(String publisher_id) {
		this.publisher_id = publisher_id;
	}
	public String getAuthor_id() {
		return author_id;
	}
	public void setAuthor_id(String author_id) {
		this.author_id = author_id;
	}
	public String getTitle() {
		return title;
	}
	public void setTitle(String title) {
		this.title = title;
	}
	public String getEdition_id() {
		return edition_id;
	}
	public void setEdition_id(String edition_id) {
		this.edition_id = edition_id;
	}
	public String getDescription() {
		return description;
	}
	public void setDescription(String description) {
		this.description = description;
	}
	public String getIsbn_number() {
		return isbn_number;
	}
	public void setIsbn_number(String isbn_number) {
		this.isbn_number = isbn_number;
	}
	public String getPublisher() {
		return publisher;
	}
	public void setPublisher(String publisher) {
		this.publisher = publisher;
	}
	public String getTotal_pages() {
		return total_pages;
	}
	public void setTotal_pages(String total_pages) {
		this.total_pages = total_pages;
	}
	public String getCat_id() {
		return cat_id;
	}
	public void setCat_id(String cat_id) {
		this.cat_id = cat_id;
	}
	public String getParent_brand_id() {
		return parent_brand_id;
	}
	public void setParent_brand_id(String parent_brand_id) {
		this.parent_brand_id = parent_brand_id;
	}
	public String getFile_name() {
		return file_name;
	}
	public void setFile_name(String file_name) {
		this.file_name = file_name;
	}
	public String getFile_size() {
		return file_size;
	}
	public void setFile_size(String file_size) {
		this.file_size = file_size;
	}
	public String getStatus() {
		return status;
	}
	public void setStatus(String status) {
		this.status = status;
	}
	public String getIs_featured() {
		return is_featured;
	}
	public void setIs_featured(String is_featured) {
		this.is_featured = is_featured;
	}
	public String getAdmin_approve() {
		return admin_approve;
	}
	public void setAdmin_approve(String admin_approve) {
		this.admin_approve = admin_approve;
	}
	public String getPublish_time() {
		return publish_time;
	}
	public void setPublish_time(String publish_time) {
		this.publish_time = publish_time;
	}
	public String getAdd_time() {
		return add_time;
	}
	public void setAdd_time(String add_time) {
		this.add_time = add_time;
	}
	public String getBest_seller() {
		return best_seller;
	}
	public void setBest_seller(String best_seller) {
		this.best_seller = best_seller;
	}
	public String getNo_download() {
		return no_download;
	}
	public void setNo_download(String no_download) {
		this.no_download = no_download;
	}
	public String getCategory_name() {
		return category_name;
	}
	public void setCategory_name(String category_name) {
		this.category_name = category_name;
	}
	public String getGenre() {
		return genre;
	}
	public void setGenre(String genre) {
		this.genre = genre;
	}
	public String getProduct_id() {
		return product_id;
	}
	public void setProduct_id(String product_id) {
		this.product_id = product_id;
	}
	public String getCountry_id() {
		return country_id;
	}
	public void setCountry_id(String country_id) {
		this.country_id = country_id;
	}
	public String getLanguage_id() {
		return language_id;
	}
	public void setLanguage_id(String language_id) {
		this.language_id = language_id;
	}
	public String getPrice() {
		return price;
	}
	public void setPrice(String price) {
		this.price = price;
	}
	public String getCountry_name() {
		return country_name;
	}
	public void setCountry_name(String country_name) {
		this.country_name = country_name;
	}
	public String getPriceText() {
		return priceText;
	}
	public void setPriceText(String priceText) {
		this.priceText = priceText;
	}
	public String getProductThumbnail() {
		return ProductThumbnail;
	}
	public void setProductThumbnail(String productThumbnail) {
		ProductThumbnail = productThumbnail;
	}
	public String getProducturl() {
		return Producturl;
	}
	public void setProducturl(String producturl) {
		Producturl = producturl;
	}
	public String getPublisher_name() {
		return publisher_name;
	}
	public void setPublisher_name(String publisher_name) {
		this.publisher_name = publisher_name;
	}
	public String getAuthor_name() {
		return author_name;
	}
	public void setAuthor_name(String author_name) {
		this.author_name = author_name;
	}
	public String getRating() {
		return rating;
	}
	public void setRating(String rating) {
		this.rating = rating;
	}
	public String getBooks_status() {
		return books_status;
	}
	public void setBooks_status(String books_status) {
		this.books_status = books_status;
	}
	public String getIs_free() {
		return is_free;
	}
	public void setIs_free(String is_free) {
		this.is_free = is_free;
	}
	public String getGroup_name() {
		return group_name;
	}
	public void setGroup_name(String group_name) {
		this.group_name = group_name;
	}
	
	
	
	
	
	
	

}
