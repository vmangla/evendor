package com.evendor.Android;

import android.graphics.Bitmap;

public class BookItem {

	private String author;
	private String title;
	private Bitmap image;

	public BookItem() {
		// TODO Auto-generated constructor stub
	}

	public BookItem(String _author, String _title) {
		this.author = _author;
		this.title = _title;
//		this.image = _image;
	}

	public String getAuthor() {
		return this.author;
	}

	public void setAuthor(String _author) {
		this.author = _author;
	}

	public String getTitle() {
		return this.title;
	}

	public void setTitle(String _title) {
		this.title = _title;
	}

	public Bitmap getImage() {
		return this.image;
	}

	public void setImage(Bitmap _image) {
		this.image = _image;
	}
	
	
	public static final BookItem[] ALL_BOOKS={
        new BookItem("Nahjolbalaqe","Dashti"),
        new BookItem("Prof Tan","Text Mining"),
        new BookItem("Ralph","Java convenient"),
        new BookItem("Islam","Ali"),
        new BookItem("Ralph","Java"),
        new BookItem("Ralph","J2ME"),
        new BookItem("Ralph","24 hours with Java"),
        new BookItem("Ralph","Java Workshop"),
        new BookItem("Prof Tan","Introduce DataMining"),
        new BookItem("Dr. Salimi","Algorithms"),
        new BookItem("Prof Tan","Proffessional DataMining"),
        new BookItem("Dr. Salimi","DataMining 1"),
        new BookItem("Dr. Salimi","Data Structures"),
        new BookItem("Dr. Salimi","DataMinig 2"),
        new BookItem("Dr. Salimi","C++ Programing"),
        new BookItem("Dr. Salimi","24 hours with C#"),
        new BookItem("Dr. Salimi","24 hours with C++"),
        new BookItem("Mark Murphy","Begining Android 3"),
        new BookItem("Mark Murphy","Begining Android 2"),
        new BookItem("Mark Murphy","Pro Android 3"),
        new BookItem("Mark Murphy","Pro Android Games"),
        new BookItem("Mark Murphy","Begining Android Games"),
        new BookItem("Reto Meier","C# Programing"),
        new BookItem("Reto Meier","Android Programing"),
        new BookItem("Reto Meier","C++ Programing"),
        new BookItem("Reto Meier","PHP Programing"),
        new BookItem("Reto Meier","Design Pattern"),
        new BookItem("Reto Meier",".Net Framework 3.5"),
        new BookItem("Reto Meier",".Net Framework 4"),
        new BookItem("Reto Meier","ASP.net Web services"),
        new BookItem("Reto Meier","Android Games"),
        new BookItem("Dr. Salimi","TextMining")
    };
}