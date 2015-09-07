package com.evender.Comprator;

import java.util.Comparator;

import com.evendor.Modal.BooksData;

public class AlphaDesc  implements Comparator<BooksData>{

	@Override
	public int compare(BooksData lhs, BooksData rhs) {
		
		
		return rhs.getGenre().compareTo(lhs.getGenre());
	}
}
