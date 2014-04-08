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
package org.libspark.exif.entity
{
	import flash.utils.ByteArray;
	import flash.utils.Dictionary;
	
	public class Tag
	{
		/** An 8-bit unsigned integer */
		static private const BYTE:uint = 1;
		/** An 8-bit byte containing one 7-bit ASCII code, The final byte is terminated with NULL */
		static private const ASCII:uint = 2;
		/** A 16-bit unsigned integer */
		static private const SHORT:uint = 3;
		/** A 32-bit unsigned integer */
		static private const LONG:uint = 4;
		/** Two LONGs, The first LONG is the numerator and the second LONG expresses the denominator*/
		static private const RATIONAL:uint = 5;
		/** An 8-bit byte that can take any value depending on the field definition */
		static private const UNDEFINED:uint = 7;
		/** A 32-bit signed integer */
		static private const SLONG:uint = 9;
		/** Two SLONGs, the first SLONG is the numerator and the second SLONG is the denominator */
		static private const SRATIONAL:uint = 10;
		
		/** Length of each Type */
		static private const LENGTH:Dictionary = new Dictionary();
		LENGTH[BYTE] = 1;
		LENGTH[ASCII] = 1;
		LENGTH[SHORT] = 2;
		LENGTH[LONG] = 4;
		LENGTH[RATIONAL] = 8;
		LENGTH[UNDEFINED] = 1;
		LENGTH[SLONG] = 4;
		LENGTH[SRATIONAL] = 8;
		
		[Bindable]
		public var id:uint;
		[Bindable]
		public var type:uint;
		[Bindable]
		public var count:uint;
		[Bindable]
		public var value:*;
		
		[Bindable]
		public var name:String;
		[Bindable]
		public var description:String;
		[Bindable]
		public var description_ja:String;
		
		public function Tag()
		{
			name = "unknownTag";
		}
		
		public function readData(data:ByteArray,endian:String,position:uint,tiffPosition:uint,ifdType:uint):Tag
		{
			data.endian = endian;
			data.position = position;
			this.id = data.readUnsignedShort();
			this.type = data.readUnsignedShort();
			this.count = data.readUnsignedInt();

			var datas:Array;
			var i:uint;
			if(LENGTH[this.type]*this.count>4){
				data.position = data.readUnsignedInt() + tiffPosition;
			}
			switch(this.type){
				case BYTE:
					if(this.count==1){
						this.value = data.readByte();
					}else{
						datas = [];
						for(i=0;i<this.count;i++)datas.push(data.readByte());
						this.value = datas;
					}
					break;
				case ASCII:
					this.value = data.readUTFBytes(this.count);
					break;
				case SHORT:
					if(this.count==1){
						this.value = data.readUnsignedShort();
					}else{
						datas = [];
						for(i=0;i<this.count;i++)datas.push(data.readUnsignedShort());
						this.value = datas;
					}
					break;
				case LONG:
					if(this.count==1){
						this.value = data.readUnsignedInt();
					}else{
						datas = [];
						for(i=0;i<this.count;i++)datas.push(data.readUnsignedInt());
						this.value = datas;
					}
					break;
				case RATIONAL:
					if(this.count==1){
						this.value = new Rational(data.readUnsignedInt(),data.readUnsignedInt());
					}else{
						datas = [];
						for(i=0;i<this.count;i++)datas.push(new Rational(data.readUnsignedInt(),data.readUnsignedInt()));
						this.value = datas;
					}
					break;
				case UNDEFINED:
					var bytes:ByteArray = new ByteArray();
					data.readBytes(bytes,0,this.count);
					this.value = bytes;
					break;
				case SLONG:
					if(this.count==1){
						this.value = data.readInt();
					}else{
						datas = [];
						for(i=0;i<this.count;i++)datas.push(data.readInt());
						this.value = datas;
					}
					break;
				case SRATIONAL:
					if(this.count==1){
						this.value = new Rational(data.readInt(),data.readInt());
					}else{
						datas = [];
						for(i=0;i<this.count;i++)datas.push(new Rational(data.readInt(),data.readInt()));
						this.value = datas;
					}
					break;
			}
			var tagInfo:Object = TagDefine.getTagDefine(ifdType, this.id);
			if(tagInfo){
				this.name = tagInfo.name;
				this.description = tagInfo.description;
				this.description_ja = tagInfo.description_ja;
			}
			return this;
		}
		
		public function toString():String
		{
			return "id=" + id + ", name=" + this.name + ", type=" + type + ", count=" + count + ", value=" + value;
		}
		
		public function clone():Tag
		{
			var newObj:Tag = new Tag();
			newObj.id = this.id;
			newObj.type = this.type;
			newObj.count = this.count;
			newObj.value = this.value;
			newObj.name = this.name;
			newObj.description = this.description;
			newObj.description_ja = this.description_ja;
			return newObj;
		}
	}
}