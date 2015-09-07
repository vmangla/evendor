package com.evender.Comprator;

import java.util.Comparator;

import com.evendor.Modal.BooksData;

public class MComprater implements Comparator<BooksData>{

	
	/**@author MIPC27
	 * Change from getGenre() to getTitle() as sorting was required to be done 
	 * based on Title only.
	 */
	@Override
	public int compare(BooksData lhs, BooksData rhs) {
		
		
		return lhs.getTitle().compareTo(rhs.getTitle());
	}
}



