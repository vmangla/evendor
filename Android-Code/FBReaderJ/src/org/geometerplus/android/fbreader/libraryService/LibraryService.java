/*
 * Copyright (C) 2010-2013 Geometer Plus <contact@geometerplus.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

package org.geometerplus.android.fbreader.libraryService;

import java.util.*;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;
import android.os.FileObserver;

import org.geometerplus.zlibrary.core.filesystem.ZLFile;

import org.geometerplus.zlibrary.text.view.ZLTextPosition;
import org.geometerplus.zlibrary.text.view.ZLTextFixedPosition;

import org.geometerplus.fbreader.Paths;
import org.geometerplus.fbreader.book.*;

import org.geometerplus.android.fbreader.api.TextPosition;

public class LibraryService extends Service {
	static final String BOOK_EVENT_ACTION = "fbreader.library-service.book-event";
	static final String BUILD_EVENT_ACTION = "fbreader.library-service.build-event";

	private static final class Observer extends FileObserver {
		private static final int MASK =
			MOVE_SELF | MOVED_TO | MOVED_FROM | DELETE_SELF | DELETE | CLOSE_WRITE | ATTRIB;

		private final String myPrefix;
		private final BookCollection myCollection;

		public Observer(String path, BookCollection collection) {
			super(path, MASK);
			myPrefix = path + '/';
			myCollection = collection;
		}

		@Override
		public void onEvent(int event, String path) {
			event = event & ALL_EVENTS;
			System.err.println("Event " + event + " on " + path);
			switch (event) {
				case MOVE_SELF:
					// TODO: File(path) removed; stop watching (?)
					break;
				case MOVED_TO:
					myCollection.rescan(myPrefix + path);
					break;
				case MOVED_FROM:
				case DELETE:
					myCollection.rescan(myPrefix + path);
					break;
				case DELETE_SELF:
					// TODO: File(path) removed; watching is stopped automatically (?)
					break;
				case CLOSE_WRITE:
				case ATTRIB:
					myCollection.rescan(myPrefix + path);
					break;
				default:
					System.err.println("Unexpected event " + event + " on " + myPrefix + path);
					break;
			}
		}
	}

	public final class LibraryImplementation extends LibraryInterface.Stub {
		private final BooksDatabase myDatabase;
		private final List<FileObserver> myFileObservers = new LinkedList<FileObserver>();
		private BookCollection myCollection;

		LibraryImplementation() {
			myDatabase = SQLiteBooksDatabase.Instance(LibraryService.this);
			reset(Collections.singletonList(Paths.BooksDirectoryOption().getValue()), true);
		}

		public void reset(List<String> bookDirectories, boolean force) {
			if (!force && myCollection != null && bookDirectories.equals(myCollection.BookDirectories)) {
				return;
			}

			deactivate();
			myFileObservers.clear();

			myCollection = new BookCollection(myDatabase, bookDirectories);
			for (String path : bookDirectories) {
				final Observer observer = new Observer(path, myCollection);
				observer.startWatching();
				myFileObservers.add(observer);
			}

			myCollection.addListener(new BookCollection.Listener() {
				public void onBookEvent(BookEvent event, Book book) {
					final Intent intent = new Intent(BOOK_EVENT_ACTION);
					intent.putExtra("type", event.toString());
					intent.putExtra("book", SerializerUtil.serialize(book));
					sendBroadcast(intent);
				}

				public void onBuildEvent(BookCollection.Status status) {
					final Intent intent = new Intent(BUILD_EVENT_ACTION);
					intent.putExtra("type", status.toString());
					sendBroadcast(intent);
				}
			});
			myCollection.startBuild();
		}

		public void deactivate() {
			for (FileObserver observer : myFileObservers) {
				observer.stopWatching();
			}
		}

		public String status() {
			return myCollection.status().toString();
		}

		public int size() {
			return myCollection.size();
		}

		public List<String> books() {
			return SerializerUtil.serializeBookList(myCollection.books());
		}

		public List<String> booksForAuthor(String author) {
			return SerializerUtil.serializeBookList(myCollection.booksForAuthor(Util.stringToAuthor(author)));
		}

		public List<String> booksForTag(String tag) {
			return SerializerUtil.serializeBookList(myCollection.booksForTag(Util.stringToTag(tag)));
		}

		public List<String> booksForSeries(String series) {
			return SerializerUtil.serializeBookList(myCollection.booksForSeries(series));
		}

		public List<String> booksForSeriesAndAuthor(String series, String author) {
			return SerializerUtil.serializeBookList(
				myCollection.booksForSeriesAndAuthor(series, Util.stringToAuthor(author))
			);
		}

		public List<String> booksForTitlePrefix(String prefix) {
			return SerializerUtil.serializeBookList(myCollection.booksForTitlePrefix(prefix));
		}

		public boolean hasBooksForPattern(String pattern) {
			return myCollection.hasBooksForPattern(pattern);
		}

		public List<String> booksForPattern(String pattern) {
			return SerializerUtil.serializeBookList(myCollection.booksForPattern(pattern));
		}

		public List<String> recentBooks() {
			return SerializerUtil.serializeBookList(myCollection.recentBooks());
		}

		public List<String> booksForLabel(String label) {
			return SerializerUtil.serializeBookList(myCollection.booksForLabel(label));
		}

		public String getRecentBook(int index) {
			return SerializerUtil.serialize(myCollection.getRecentBook(index));
		}

		public String getBookByFile(String file) {
			return SerializerUtil.serialize(myCollection.getBookByFile(ZLFile.createFileByPath(file)));
		}

		public String getBookById(long id) {
			return SerializerUtil.serialize(myCollection.getBookById(id));
		}

		public String getBookByUid(String type, String id) {
			return SerializerUtil.serialize(myCollection.getBookByUid(new UID(type, id)));
		}

		public List<String> authors() {
			final List<Author> authors = myCollection.authors();
			final List<String> strings = new ArrayList<String>(authors.size());
			for (Author a : authors) {
				strings.add(Util.authorToString(a));
			}
			return strings;
		}

		public boolean hasSeries() {
			return myCollection.hasSeries();
		}

		public List<String> series() {
			return myCollection.series();
		}

		public List<String> tags() {
			final List<Tag> tags = myCollection.tags();
			final List<String> strings = new ArrayList<String>(tags.size());
			for (Tag t : tags) {
				strings.add(Util.tagToString(t));
			}
			return strings;
		}

		public List<String> titles() {
			return myCollection.titles();
		}

		public List<String> firstTitleLetters() {
			return myCollection.firstTitleLetters();
		}

		public List<String> titlesForAuthor(String author, int limit) {
			return myCollection.titlesForAuthor(Util.stringToAuthor(author), limit);
		}

		public List<String> titlesForSeries(String series, int limit) {
			return myCollection.titlesForSeries(series, limit);
		}

		public List<String> titlesForSeriesAndAuthor(String series, String author, int limit) {
			return myCollection.titlesForSeriesAndAuthor(series, Util.stringToAuthor(author), limit);
		}

		public List<String> titlesForTag(String tag, int limit) {
			return myCollection.titlesForTag(Util.stringToTag(tag), limit);
		}

		public List<String> titlesForTitlePrefix(String prefix, int limit) {
			return myCollection.titlesForTitlePrefix(prefix, limit);
		}

		public boolean saveBook(String book, boolean force) {
			return myCollection.saveBook(SerializerUtil.deserializeBook(book), force);
		}

		public void removeBook(String book, boolean deleteFromDisk) {
			myCollection.removeBook(SerializerUtil.deserializeBook(book), deleteFromDisk);
		}

		public void addBookToRecentList(String book) {
			myCollection.addBookToRecentList(SerializerUtil.deserializeBook(book));
		}

		public List<String> labels() {
			return myCollection.labels();
		}

		public List<String> labelsForBook(String book) {
			return myCollection.labels(SerializerUtil.deserializeBook(book));
		}

		public void setLabel(String book, String label) {
			myCollection.setLabel(SerializerUtil.deserializeBook(book), label);
		}

		public void removeLabel(String book, String label) {
			myCollection.removeLabel(SerializerUtil.deserializeBook(book), label);
		}

		public TextPosition getStoredPosition(long bookId) {
			final ZLTextPosition position = myCollection.getStoredPosition(bookId);
			if (position == null) {
				return null;
			}

			return new TextPosition(
				position.getParagraphIndex(), position.getElementIndex(), position.getCharIndex()
			);
		}

		public void storePosition(long bookId, TextPosition position) {
			if (position == null) {
				return;
			}
			myCollection.storePosition(bookId, new ZLTextFixedPosition(
				position.ParagraphIndex, position.ElementIndex, position.CharIndex
			));
		}

		public boolean isHyperlinkVisited(String book, String linkId) {
			return myCollection.isHyperlinkVisited(SerializerUtil.deserializeBook(book), linkId);
		}

		public void markHyperlinkAsVisited(String book, String linkId) {
			myCollection.markHyperlinkAsVisited(SerializerUtil.deserializeBook(book), linkId);
		}

		public List<String> invisibleBookmarks(String book) {
			return SerializerUtil.serializeBookmarkList(
				myCollection.invisibleBookmarks(SerializerUtil.deserializeBook(book))
			);
		}

		public List<String> bookmarks(long fromId, int limitCount) {
			return SerializerUtil.serializeBookmarkList(myCollection.bookmarks(fromId, limitCount));
		}

		public List<String> bookmarksForBook(String book, long fromId, int limitCount) {
			return SerializerUtil.serializeBookmarkList(myCollection.bookmarksForBook(
				SerializerUtil.deserializeBook(book), fromId, limitCount
			));
		}

		public String saveBookmark(String serialized) {
			final Bookmark bookmark = SerializerUtil.deserializeBookmark(serialized);
			myCollection.saveBookmark(bookmark);
			return SerializerUtil.serialize(bookmark);
		}

		public void deleteBookmark(String serialized) {
			myCollection.deleteBookmark(SerializerUtil.deserializeBookmark(serialized));
		}
	}

	private volatile LibraryImplementation myLibrary;

	@Override
	public void onStart(Intent intent, int startId) {
		onStartCommand(intent, 0, startId);
	}

	@Override
	public int onStartCommand(Intent intent, int flags, int startId) {
		return START_STICKY;
	}

	@Override
	public IBinder onBind(Intent intent) {
		return myLibrary;
	}

	@Override
	public void onCreate() {
		super.onCreate();
		myLibrary = new LibraryImplementation();
	}

	@Override
	public void onDestroy() {
		if (myLibrary != null) {
			myLibrary.deactivate();
			myLibrary = null;
		}
		super.onDestroy();
	}
}
