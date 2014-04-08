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
	import flash.events.EventDispatcher;
	import flash.utils.ByteArray;
	import flash.utils.Dictionary;
	import flash.utils.Endian;
	
	import org.libspark.exif.entity.Tag;
	import org.libspark.exif.entity.TagDefine;

	/**
	 * JPEG形式のバイナリデータからExif情報を読み取るクラスです
	 */
	public class ExifReader extends EventDispatcher implements IExifReader
	{
		/** Marker Segments - Start of Image(Start of compressed data) */ 
		static private const SOI:uint = 0xFFD8;
		///** Marker Segments - Application Segment 1(Exif attribute information) */ 
		//static private const APP1:uint = 0xFFE1;
		///** Marker Segments - Application Segment 2(Exif extended data) */ 
		//static private const APP2:uint = 0xFFE2;
		///** Marker Segments - Define Quantization Table(Quantization table definition) */ 
		//static private const DQT:uint = 0xFFDB;
		///** Marker Segments - Define Huffman Table(Huffman table definition) */ 
		//static private const DHT:uint = 0xFFC4;
		///** Marker Segments - Define Restart Interoperability(Restart Interoperability definition) */ 
		//static private const DRI:uint = 0xFFDD;
		///** Marker Segments - Start of Frame(Parameter data relationg to frame) */ 
		//static private const SOF:uint = 0xFFC0;
		///** Marker Segments - Start of Scan(Parameters relating to components) */ 
		//static private const SOS:uint = 0xFFDA;
		///** Marker Segments - End of Image(End of compressed data) */ 
		//static private const EOI:uint = 0xFFD9;
		
		private var _tiffTags:Array;
		private var _exifTags:Array;
		private var _gpsTags:Array;
		private var _interoperabilityTags:Array;
		
		private var _tagNameHash:Dictionary;
		
		private var _tiffTagIdHash:Dictionary;
		private var _exifTagIdHash:Dictionary;
		private var _gpsTagIdHash:Dictionary;
		private var _interoperabilityTagIdHash:Dictionary;

		private var _thumbnailTags:Array;
		private var _thumbnailTagIdHash:Dictionary;
		private var _thumbnailTagNameHash:Dictionary;
		private var _thumbnailData:ByteArray;

		public function ExifReader(data:ByteArray=null)
		{
			this._tiffTags = [];
			this._exifTags = [];
			this._gpsTags = [];
			this._interoperabilityTags = [];
			this._thumbnailTags = [];
			this._thumbnailData = null;
			
			if(data)readData(data);
		}
		
		public function readData(data:ByteArray):void
		{
			if (checkJpeg(data) && checkExif(data)) {
				_tiffTags = parseIFD(data,20,12,TagDefine.IFD_0TH);
				
				var exifTag:Tag = getTiffTagById(0x8769);
				if(exifTag)_exifTags = parseIFD(data,(exifTag.value as uint)+12,12,TagDefine.IFD_EXIF);
				
				var gpsTag:Tag = getTiffTagById(0x8825);
				if(gpsTag)_gpsTags = parseIFD(data,(gpsTag.value as uint)+12,12,TagDefine.IFD_GPS);
				
				var interoperabilityTag:Tag = getExifTagById(0xa005);
				if(interoperabilityTag)_interoperabilityTags = parseIFD(data,(interoperabilityTag.value as uint)+12,12,TagDefine.IFD_INTEROPERABILITY);
				
				var nextIFDPointer:uint = getNextIFDPointer(data,20);
				if(nextIFDPointer>0){
					_thumbnailTags = parseIFD(data,nextIFDPointer+12,12,TagDefine.IFD_1ST);
					var offsetTag:Tag = getThumbnailTagById(0x0201);
					var lengthTag:Tag = getThumbnailTagById(0x0202);
					if(offsetTag&&lengthTag){
						var thumbnailOffset:uint = offsetTag.value as uint;
						var thumbnailLength:uint = lengthTag.value as uint;
						_thumbnailData = new ByteArray();
						data.position = thumbnailOffset+12;
						data.readBytes(_thumbnailData,0,thumbnailLength);
					}
				}
				
				this.clearHash();
			}
		}
		
		private function checkJpeg(data:ByteArray):Boolean
		{
			/*
			 * Structure of JPEG
			 * 00-01  SOI Marker Segment
			 * 02-xx  APP1
			 * xx-xx (APP2)
			 * xx-xx  DQT
			 * xx-xx  DHT
			 * xx-xx (DRI)
			 * xx-xx  SOF
			 * xx-xx  SOS
			 * xx-xx  Compressed Data
			 * xx-xx  EOI
			 */
			data.endian = Endian.BIG_ENDIAN;
			data.position = 0;
			var soi:uint = data.readUnsignedShort();
			return soi==SOI;
		}
		
		private function checkExif(data:ByteArray):Boolean
		{
			/*
			 * Structure of APP1
			 * 02-03(2 byte)  APP1 Marker Segment
			 * 04-05(2 byte)  Length of field(bytes)
			 * 06-09(4 byte)  "Exif"(0x45,0x78,0x69,0x66)
			 * 10-11(2 byte)  null+Padding(0x0000)
			 * 12-19(8 byte)  TIFF Header
			 *			12-13(2 byte)  Endian, "II"(0x4949)(little endial), "MM"(0x4D4D)(big endian)
			 *			14-15(2 byte)  0x002A(fixed)
			 *			16-19(4 byte)  Offset to the 0th IFD(from the TIFF Header, it is written as 0x8);
			 * 20-xx  0th IDF
			 * xx-xx  0th IFD Value
			 * xx-xx  1st IFD
			 * xx-xx  1st IFD Value
			 * xx-xx  1st IDF Image Data(Thumbnail)
			 */
			data.endian = Endian.BIG_ENDIAN;
			data.position = 6;
			var exif:uint = data.readUnsignedInt();
			return exif==0x45786966;
		}
		
		private function getEndian(data:ByteArray):String
		{
			data.position = 12;
			var endian:uint = data.readUnsignedShort();
			if (endian == 0x4949) {
				return Endian.LITTLE_ENDIAN;
			}else if(endian == 0x4d4d){
				return Endian.BIG_ENDIAN;
			}
			return null;
		}
		
		private function parseIFD(data:ByteArray, ifdPosition:uint, tiffPosition:uint, ifdType:uint):Array
		{
			/*
			 * Structure of 0th IFD
			 * 20-21( 2 byte)  number of fields(UnsignedShort)
			 * 22-33(12 byte)  Tag(12-byte field)
			 *			22-23(2 byte) Tag ID
			 *			24-25(2 byte) Type(See Tag.as)
			 *			26-29(4 byte) Count of value
			 *			30-33(4 byte) Offset to the value(If length of value is less or equal 4 byte, the value is stored here)
			 * 34-45(12 byte)  Tag
			 * xx-xx(12 byte)  Tag
			 *   :
			 * xx-xx(12 byte)  Tag
			 * xx-xx( 4 byte)  Offset to the next IFD(from the TIFF Header)
			 */
			data.endian = getEndian(data);
			data.position = ifdPosition;
			var numField:uint = data.readUnsignedShort();
			var tags:Array = [];
			for (var i:uint = 0; i < numField; i++) {
				var position:uint = ifdPosition + 2 + i * 12;
				tags.push(new Tag().readData(data, data.endian, position, tiffPosition, ifdType));
			}
			return tags;
		}
		
		private function getNextIFDPointer(data:ByteArray, ifdPosition:uint):uint
		{
			data.endian = getEndian(data);
			data.position = ifdPosition;
			var numField:uint = data.readUnsignedShort();
			data.position = data.position+numField*12;
			return data.readUnsignedShort();
		}
		
		public function get hasExifData():Boolean
		{
			return this._tiffTags&&this._tiffTags.length>0;
		}
		
		public function getTiffTagById(tagid:uint):Tag
		{
			if(!_tiffTags)return null;
			
			if(!_tiffTagIdHash){
				var newDic:Dictionary = new Dictionary(true);
				var l:uint = _tiffTags.length;
				for(var i:uint=0;i<l;i++)newDic[(_tiffTags[i] as Tag).id]=(_tiffTags[i] as Tag).clone();
				_tiffTagIdHash = newDic;
			}
			return _tiffTagIdHash[tagid];
		}
		
		public function getExifTagById(tagid:uint):Tag
		{
			if(!_exifTags)return null;
			
			if(!_exifTagIdHash){
				var newDic:Dictionary = new Dictionary(true);
				var l:uint = _exifTags.length;
				for(var i:uint=0;i<l;i++)newDic[(_exifTags[i] as Tag).id]=(_exifTags[i] as Tag).clone();
				_exifTagIdHash = newDic;
			}
			return _exifTagIdHash[tagid];
		}
		
		public function getGpsTagById(tagid:uint):Tag
		{
			if(!_gpsTags)return null;
			
			if(!_gpsTagIdHash){
				var newDic:Dictionary = new Dictionary(true);
				var l:uint = _gpsTags.length;
				for(var i:uint=0;i<l;i++)newDic[(_gpsTags[i] as Tag).id]=(_gpsTags[i] as Tag).clone();
				_gpsTagIdHash = newDic;
			}
			return _gpsTagIdHash[tagid];
		}
		
		public function getInteroperabilityTagById(tagid:uint):Tag
		{
			if(!_interoperabilityTags)return null;
			
			if(!_interoperabilityTagIdHash){
				var newDic:Dictionary = new Dictionary(true);
				var l:uint = _interoperabilityTags.length;
				for(var i:uint=0;i<l;i++)newDic[(_interoperabilityTags[i] as Tag).id]=(_interoperabilityTags[i] as Tag).clone();
				_interoperabilityTagIdHash = newDic;
			}
			return _interoperabilityTagIdHash[tagid];
		}
		
		public function getTagByName(name:String):Tag
		{
			if(!_tiffTags)return null;
			
			if(!_tagNameHash){
				var newDic:Dictionary = new Dictionary(true);
				var tags:Array = allTags;
				var l:uint = tags.length;
				for(var i:uint=0;i<l;i++)newDic[(tags[i] as Tag).name]=tags[i] as Tag;
				_tagNameHash = newDic;
			}
			return _tagNameHash[name];
		}

		/** internal use only */		
		private function clearHash():void
		{
			this._tiffTagIdHash = null;
			this._exifTagIdHash = null;
			this._gpsTagIdHash = null;
			this._interoperabilityTagIdHash = null;
			this._tagNameHash = null;
			this._thumbnailTagIdHash = null;
			this._thumbnailTagNameHash = null;
		}
		
		public function get allTags():Array
		{
			return tiffTags.concat(exifTags).concat(gpsTags).concat(interoperabilityTags);
		}
		
		public function get tiffTags():Array
		{
			if(!_tiffTags)return [];

			var newAry:Array = [];
			var l:uint = _tiffTags.length;
			for(var i:uint=0;i<l;i++)newAry[i]=(_tiffTags[i] as Tag).clone();
			return newAry;
		}
		
		public function get exifTags():Array
		{
			if(!_exifTags)return [];

			var newAry:Array = [];
			var l:uint = _exifTags.length;
			for(var i:uint=0;i<l;i++)newAry[i]=(_exifTags[i] as Tag).clone();
			return newAry;
		}
		
		public function get gpsTags():Array
		{
			if(!_gpsTags)return [];

			var newAry:Array = [];
			var l:uint = _gpsTags.length;
			for(var i:uint=0;i<l;i++)newAry[i]=(_gpsTags[i] as Tag).clone();
			return newAry;
		}
		
		public function get interoperabilityTags():Array
		{
			if(!_interoperabilityTags)return [];

			var newAry:Array = [];
			var l:uint = _interoperabilityTags.length;
			for(var i:uint=0;i<l;i++)newAry[i]=(_interoperabilityTags[i] as Tag).clone();
			return newAry;
		}
		
		public function get hasThumbnail():Boolean
		{
			return _thumbnailData&&_thumbnailData.length>0;
		}
		
		public function get thumbnailTags():Array
		{
			if(!_thumbnailTags)return [];

			var newAry:Array = [];
			var l:uint = _thumbnailTags.length;
			for(var i:uint=0;i<l;i++)newAry[i]=(_thumbnailTags[i] as Tag).clone();
			return newAry;
		}

		public function getThumbnailTagById(tagid:uint):Tag
		{
			if(!_thumbnailTags)return null;
			
			if(!_thumbnailTagIdHash){
				var newDic:Dictionary = new Dictionary(true);
				var l:uint = _thumbnailTags.length;
				for(var i:uint=0;i<l;i++)newDic[(_thumbnailTags[i] as Tag).id]=(_thumbnailTags[i] as Tag).clone();
				_thumbnailTagIdHash = newDic;
			}
			return _thumbnailTagIdHash[tagid];
		}

		public function getThumbnailTagByName(name:String):Tag
		{
			if(!_thumbnailTags)return null;
			
			if(!_thumbnailTagNameHash){
				var newDic:Dictionary = new Dictionary(true);
				var l:uint = _thumbnailTags.length;
				for(var i:uint=0;i<l;i++)newDic[(_thumbnailTags[i] as Tag).name]=(_thumbnailTags[i] as Tag).clone();
				_thumbnailTagNameHash = newDic;
			}
			return _thumbnailTagNameHash[name];
		}
		
		public function get thumbnailData():ByteArray
		{
			return _thumbnailData;
		}
		
	}
}