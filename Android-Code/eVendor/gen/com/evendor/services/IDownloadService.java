/*
 * This file is auto-generated.  DO NOT MODIFY.
 * Original file: D:\\Android_MONIKA\\latestinformpatientworkspace\\eVendor\\src\\com\\evendor\\services\\IDownloadService.aidl
 */
package com.evendor.services;
public interface IDownloadService extends android.os.IInterface
{
/** Local-side IPC implementation stub class. */
public static abstract class Stub extends android.os.Binder implements com.evendor.services.IDownloadService
{
private static final java.lang.String DESCRIPTOR = "com.evendor.services.IDownloadService";
/** Construct the stub at attach it to the interface. */
public Stub()
{
this.attachInterface(this, DESCRIPTOR);
}
/**
 * Cast an IBinder object into an com.evendor.services.IDownloadService interface,
 * generating a proxy if needed.
 */
public static com.evendor.services.IDownloadService asInterface(android.os.IBinder obj)
{
if ((obj==null)) {
return null;
}
android.os.IInterface iin = obj.queryLocalInterface(DESCRIPTOR);
if (((iin!=null)&&(iin instanceof com.evendor.services.IDownloadService))) {
return ((com.evendor.services.IDownloadService)iin);
}
return new com.evendor.services.IDownloadService.Stub.Proxy(obj);
}
@Override public android.os.IBinder asBinder()
{
return this;
}
@Override public boolean onTransact(int code, android.os.Parcel data, android.os.Parcel reply, int flags) throws android.os.RemoteException
{
switch (code)
{
case INTERFACE_TRANSACTION:
{
reply.writeString(DESCRIPTOR);
return true;
}
case TRANSACTION_startManage:
{
data.enforceInterface(DESCRIPTOR);
this.startManage();
reply.writeNoException();
return true;
}
case TRANSACTION_addTask:
{
data.enforceInterface(DESCRIPTOR);
java.lang.String _arg0;
_arg0 = data.readString();
java.lang.String _arg1;
_arg1 = data.readString();
java.lang.String _arg2;
_arg2 = data.readString();
java.lang.String _arg3;
_arg3 = data.readString();
java.lang.String _arg4;
_arg4 = data.readString();
java.lang.String _arg5;
_arg5 = data.readString();
java.lang.String _arg6;
_arg6 = data.readString();
java.lang.String _arg7;
_arg7 = data.readString();
java.lang.String _arg8;
_arg8 = data.readString();
java.lang.String _arg9;
_arg9 = data.readString();
java.lang.String _arg10;
_arg10 = data.readString();
java.lang.String _arg11;
_arg11 = data.readString();
java.lang.String _arg12;
_arg12 = data.readString();
java.lang.String _arg13;
_arg13 = data.readString();
java.lang.String _arg14;
_arg14 = data.readString();
java.lang.String _arg15;
_arg15 = data.readString();
this.addTask(_arg0, _arg1, _arg2, _arg3, _arg4, _arg5, _arg6, _arg7, _arg8, _arg9, _arg10, _arg11, _arg12, _arg13, _arg14, _arg15);
reply.writeNoException();
return true;
}
case TRANSACTION_pauseTask:
{
data.enforceInterface(DESCRIPTOR);
java.lang.String _arg0;
_arg0 = data.readString();
this.pauseTask(_arg0);
reply.writeNoException();
return true;
}
case TRANSACTION_deleteTask:
{
data.enforceInterface(DESCRIPTOR);
java.lang.String _arg0;
_arg0 = data.readString();
this.deleteTask(_arg0);
reply.writeNoException();
return true;
}
case TRANSACTION_continueTask:
{
data.enforceInterface(DESCRIPTOR);
java.lang.String _arg0;
_arg0 = data.readString();
this.continueTask(_arg0);
reply.writeNoException();
return true;
}
}
return super.onTransact(code, data, reply, flags);
}
private static class Proxy implements com.evendor.services.IDownloadService
{
private android.os.IBinder mRemote;
Proxy(android.os.IBinder remote)
{
mRemote = remote;
}
@Override public android.os.IBinder asBinder()
{
return mRemote;
}
public java.lang.String getInterfaceDescriptor()
{
return DESCRIPTOR;
}
@Override public void startManage() throws android.os.RemoteException
{
android.os.Parcel _data = android.os.Parcel.obtain();
android.os.Parcel _reply = android.os.Parcel.obtain();
try {
_data.writeInterfaceToken(DESCRIPTOR);
mRemote.transact(Stub.TRANSACTION_startManage, _data, _reply, 0);
_reply.readException();
}
finally {
_reply.recycle();
_data.recycle();
}
}
@Override public void addTask(java.lang.String url, java.lang.String iconPath, java.lang.String bookTitle, java.lang.String bookPrice, java.lang.String bookDescription, java.lang.String bookUrl, java.lang.String bookId, java.lang.String bookPath, java.lang.String bookAuthor, java.lang.String bookPublisherName, java.lang.String bookPublishedDate, java.lang.String bookSize, java.lang.String bookCategory, java.lang.String filename, java.lang.String priceText, java.lang.String country_id) throws android.os.RemoteException
{
android.os.Parcel _data = android.os.Parcel.obtain();
android.os.Parcel _reply = android.os.Parcel.obtain();
try {
_data.writeInterfaceToken(DESCRIPTOR);
_data.writeString(url);
_data.writeString(iconPath);
_data.writeString(bookTitle);
_data.writeString(bookPrice);
_data.writeString(bookDescription);
_data.writeString(bookUrl);
_data.writeString(bookId);
_data.writeString(bookPath);
_data.writeString(bookAuthor);
_data.writeString(bookPublisherName);
_data.writeString(bookPublishedDate);
_data.writeString(bookSize);
_data.writeString(bookCategory);
_data.writeString(filename);
_data.writeString(priceText);
_data.writeString(country_id);
mRemote.transact(Stub.TRANSACTION_addTask, _data, _reply, 0);
_reply.readException();
}
finally {
_reply.recycle();
_data.recycle();
}
}
@Override public void pauseTask(java.lang.String url) throws android.os.RemoteException
{
android.os.Parcel _data = android.os.Parcel.obtain();
android.os.Parcel _reply = android.os.Parcel.obtain();
try {
_data.writeInterfaceToken(DESCRIPTOR);
_data.writeString(url);
mRemote.transact(Stub.TRANSACTION_pauseTask, _data, _reply, 0);
_reply.readException();
}
finally {
_reply.recycle();
_data.recycle();
}
}
@Override public void deleteTask(java.lang.String url) throws android.os.RemoteException
{
android.os.Parcel _data = android.os.Parcel.obtain();
android.os.Parcel _reply = android.os.Parcel.obtain();
try {
_data.writeInterfaceToken(DESCRIPTOR);
_data.writeString(url);
mRemote.transact(Stub.TRANSACTION_deleteTask, _data, _reply, 0);
_reply.readException();
}
finally {
_reply.recycle();
_data.recycle();
}
}
@Override public void continueTask(java.lang.String url) throws android.os.RemoteException
{
android.os.Parcel _data = android.os.Parcel.obtain();
android.os.Parcel _reply = android.os.Parcel.obtain();
try {
_data.writeInterfaceToken(DESCRIPTOR);
_data.writeString(url);
mRemote.transact(Stub.TRANSACTION_continueTask, _data, _reply, 0);
_reply.readException();
}
finally {
_reply.recycle();
_data.recycle();
}
}
}
static final int TRANSACTION_startManage = (android.os.IBinder.FIRST_CALL_TRANSACTION + 0);
static final int TRANSACTION_addTask = (android.os.IBinder.FIRST_CALL_TRANSACTION + 1);
static final int TRANSACTION_pauseTask = (android.os.IBinder.FIRST_CALL_TRANSACTION + 2);
static final int TRANSACTION_deleteTask = (android.os.IBinder.FIRST_CALL_TRANSACTION + 3);
static final int TRANSACTION_continueTask = (android.os.IBinder.FIRST_CALL_TRANSACTION + 4);
}
public void startManage() throws android.os.RemoteException;
public void addTask(java.lang.String url, java.lang.String iconPath, java.lang.String bookTitle, java.lang.String bookPrice, java.lang.String bookDescription, java.lang.String bookUrl, java.lang.String bookId, java.lang.String bookPath, java.lang.String bookAuthor, java.lang.String bookPublisherName, java.lang.String bookPublishedDate, java.lang.String bookSize, java.lang.String bookCategory, java.lang.String filename, java.lang.String priceText, java.lang.String country_id) throws android.os.RemoteException;
public void pauseTask(java.lang.String url) throws android.os.RemoteException;
public void deleteTask(java.lang.String url) throws android.os.RemoteException;
public void continueTask(java.lang.String url) throws android.os.RemoteException;
}
