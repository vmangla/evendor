package com.evender.Comprator;

import java.util.Comparator;

import com.evendor.Modal.BooksData;

public class SortByPriceAcs implements Comparator<BooksData>{

	@Override
	public int compare(BooksData lhs, BooksData rhs) {
		
		int pricelhs=Integer.parseInt(lhs.getPrice());
		int pricerhs=Integer.parseInt(rhs.getPrice());
		
		/**@author MIPC27
		 * Changed the comparator: 	if(pricelhs>=pricerhs){
		 */
		if(pricelhs>=pricerhs){
			return 1;	
		}else 
			return -1;
//		return rhs.getPriceText().compareTo(lhs.getPriceText());
	}
}
