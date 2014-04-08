/**
 * The MIT License
 * 
 * Copyright (c) 2009 http://www.libspark.org/
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
package org.libspark.exif.impl
{
	import flash.events.Event;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.SecurityErrorEvent;
	import flash.net.URLLoader;
	import flash.net.URLLoaderDataFormat;
	import flash.net.URLRequest;
	import flash.utils.ByteArray;
	
	import mx.core.IMXMLObject;
	
	import org.libspark.exif.ExifReader;
	import org.libspark.exif.IExifReader;
	import org.libspark.exif.entity.Tag;
	import org.libspark.exif.event.ExifEvent;

	[Event(name="exif_complete", type="org.libspark.exif.event.ExifEvent")]

	public class ExifURLLoader extends URLLoader implements IExifReader, IMXMLObject
	{
		private var exifObject:IExifReader;
		public var request:URLRequest;
		
		public function ExifURLLoader(request:URLRequest=null)
		{
			super(request);
			exifObject = new ExifReader();
			dataFormat = URLLoaderDataFormat.BINARY;
			addEventListener(Event.COMPLETE,completeHandler);
			addEventListener(IOErrorEvent.IO_ERROR,errorHandler);
			addEventListener(SecurityErrorEvent.SECURITY_ERROR,errorHandler);
		}
		
		public function set url(value:String):void
		{
			if(value)request = new URLRequest(value);
		}
		
		public function send(request:URLRequest=null):void
		{
			if(!request&&!this.request)return;
			this.load(request?request:this.request);
		}

		public function initialized(document:Object, id:String):void
		{
		}
		
		private function errorHandler(event:Event):void
		{	
			trace(event);
		}
		
		private function completeHandler(event:Event):void
		{
			var loader:URLLoader = event.currentTarget as URLLoader;
			if(!loader)return;
			
			var data:ByteArray = loader.data as ByteArray;
			if(data){
				exifObject.readData(data);
				dispatchEvent(new Event("hasExifDataChanged"));
				dispatchEvent(new Event("allTagsChanged"));
				dispatchEvent(new Event("tiffTagsChanged"));
				dispatchEvent(new Event("exifTagsChanged"));
				dispatchEvent(new Event("gpsTagsChanged"));
				dispatchEvent(new Event("interoperabilityTagsChanged"));
				dispatchEvent(new Event("hasThumbnailChanged"));
				dispatchEvent(new Event("thumbnailTagsChanged"));
				dispatchEvent(new Event("thumbnailDataChanged"));
				dispatchEvent(new ExifEvent(ExifEvent.EXIF_COMPLETE));
			}
		}
		
		public function readData(data:ByteArray):void
		{
			exifObject.readData(data);
		}
		
		[Bindable("hasExifDataChanged")]
		public function get hasExifData():Boolean
		{
			return exifObject.hasExifData;
		}
		
		[Bindable("allTagsChanged")]
		public function get allTags():Array
		{
			return exifObject.allTags;
		}
		
		public function getTagByName(name:String):Tag
		{
			return exifObject.getTagByName(name);
		}
		
		[Bindable("tiffTagsChanged")]
		public function get tiffTags():Array
		{
			return exifObject.tiffTags;
		}
		
		[Bindable("exifTagsChanged")]
		public function get exifTags():Array
		{
			return exifObject.exifTags;
		}
		
		[Bindable("gpsTagsChanged")]
		public function get gpsTags():Array
		{
			return exifObject.gpsTags;
		}
		
		[Bindable("interoperabilityTagsChanged")]
		public function get interoperabilityTags():Array
		{
			return exifObject.interoperabilityTags;
		}
		
		public function getTiffTagById(tagid:uint):Tag
		{
			return exifObject.getTiffTagById(tagid);
		}
		
		public function getExifTagById(tagid:uint):Tag
		{
			return exifObject.getExifTagById(tagid);
		}
		
		public function getGpsTagById(tagid:uint):Tag
		{
			return exifObject.getGpsTagById(tagid);
		}
		
		public function getInteroperabilityTagById(tagid:uint):Tag
		{
			return exifObject.getInteroperabilityTagById(tagid);
		}
		
		[Bindable("hasThumbnailChanged")]
		public function get hasThumbnail():Boolean
		{
			return exifObject.hasThumbnail;
		}
		
		[Bindable("thumbnailTagsChanged")]
		public function get thumbnailTags():Array
		{
			return exifObject.thumbnailTags;
		}
		
		public function getThumbnailTagByName(name:String):Tag
		{
			return exifObject.getThumbnailTagByName(name);
		}
		
		public function getThumbnailTagById(tagid:uint):Tag
		{
			return exifObject.getThumbnailTagById(tagid);
		}
		
		[Bindable("thumbnailDataChanged")]
		public function get thumbnailData():ByteArray
		{
			return exifObject.thumbnailData;
		}
		
	}
}