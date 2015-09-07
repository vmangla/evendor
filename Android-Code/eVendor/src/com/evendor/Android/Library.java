package com.evendor.Android;

import java.util.ArrayList;
import java.util.Arrays;

public class Library {

	private ArrayList<BookItem> arrayBookItem;
	public static final int AUTHOR = 1;
	public static final int TITLE = 2;
	public static final int RATE = 3;
	public static final int DOWNLOAD_DATE = 4;

	public Library() {
		arrayBookItem = new ArrayList<BookItem>();
	}

	public void setColectionBookItem(ArrayList<BookItem> _array) {
		this.arrayBookItem = _array;
	}

	public void addBookItem(BookItem _bi) {
		this.arrayBookItem.add(_bi);
	}

	public ArrayList<ArrayList<BookItem>> groupbyArrayBookItem(int type) {

		ArrayList<BookItem> groupedBook = new ArrayList<BookItem>();
		BookItem[] books = BookItem.ALL_BOOKS;
		groupedBook = new java.util.ArrayList(Arrays.asList(books));
		ArrayList<ArrayList<BookItem>> groupList = new ArrayList<ArrayList<BookItem>>();
		String getType = "";
		
		switch (type) {
		case AUTHOR:
			getType = "bookitem.getAuthor()";
			break;
		case TITLE:
			getType = "bookitem.getTitle()";
			break;
		case DOWNLOAD_DATE:
			getType = "bookitem.getDownloadDate()";
			break;
		case RATE:
			getType = "bookitem.getRate()";
			break;
		default:
			return groupList;
		}

		/*
		 * books is a object of BookItem
		 * bookitem is item for point to list
		 * getType is a string value for set type of grouping
		 * groupbyArrayBookItem return back array of array of items
		 */
//		Iterable<Group> groups = 
//				org.quaere.DSL.from("bookitem").in(books).group("bookitem")
//				.by(getType).into("g").select("g");
//
//		for (Group group : groups) {
//			ArrayList<BookItem> obj = new ArrayList<BookItem>();
//			for (Object Item : group.getGroup()) {
//				obj.add((BookItem) Item);
//			}
//			groupList.add(obj);
//		}
		
		groupList.add(groupedBook);
		groupList.add(groupedBook);
		groupList.add(groupedBook);
		groupList.add(groupedBook);

		return groupList;
	}
}
