package com.evendor.model;

// A very simple model that defines the location data that this app deals with.
public class ListModel {
    public int    id;
    public String address;
    public int    picture;
    
    public ListModel(int id, String address, int picture) {
        this.id      = id;
        this.address = address;
        this.picture = picture;
    }
}
