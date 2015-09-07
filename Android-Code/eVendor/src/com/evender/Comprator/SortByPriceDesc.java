package com.evender.Comprator;

import java.util.Comparator;

import com.evendor.Modal.BooksData;

public class SortByPriceDesc implements Comparator<BooksData>{

	@Override
	public int compare(BooksData lhs, BooksData rhs) {
		
		int pricelhs=Integer.parseInt(lhs.getPrice());
		int pricerhs=Integer.parseInt(rhs.getPrice());
		
		if(pricerhs>=pricelhs){
			return 1;	
		}else 
			return -1;
	}
}
