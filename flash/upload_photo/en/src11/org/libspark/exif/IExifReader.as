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
package org.libspark.exif
{
	import flash.utils.ByteArray;
	
	import org.libspark.exif.entity.Tag;
	
	public interface IExifReader
	{
		function readData(data:ByteArray):void;
		
		function get hasExifData():Boolean;
		
		function get allTags():Array;
		function getTagByName(name:String):Tag;
		
		function get tiffTags():Array;
		function get exifTags():Array;
		function get gpsTags():Array;
		function get interoperabilityTags():Array;
		function getTiffTagById(tagid:uint):Tag;
		function getExifTagById(tagid:uint):Tag;
		function getGpsTagById(tagid:uint):Tag;
		function getInteroperabilityTagById(tagid:uint):Tag;
		
		function get hasThumbnail():Boolean;
		function get thumbnailTags():Array;
		function getThumbnailTagByName(name:String):Tag;
		function getThumbnailTagById(tagid:uint):Tag;
		function get thumbnailData():ByteArray;
	}
}