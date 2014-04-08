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
	public class TagDefine
	{
		public function TagDefine()
		{
		}
		
		static public const IFD_0TH :uint=0;
		static public const IFD_EXIF:uint=1;
		static public const IFD_GPS :uint=2;
		static public const IFD_INTEROPERABILITY:uint=3;
		static public const IFD_1ST :uint=4;
		
		static public function getTagDefine(ifdType:uint,tagid:uint):Object
		{
			switch(ifdType){
				case IFD_0TH :return IFD_DEFINE_0TH[tagid];
				case IFD_EXIF:return IFD_DEFINE_EXIF[tagid];
				case IFD_GPS :return IFD_DEFINE_GPS[tagid];
				case IFD_INTEROPERABILITY:return IFD_DEFINE_INTEROPERABILITY[tagid];
				case IFD_1ST :return IFD_DEFINE_1ST[tagid];
			}
			return undefined;
		}
		
		static private const IFD_DEFINE_0TH:Object=
		{
			 0x0100:{name:"ImageWidth",description:"Image width",description_ja:""}
			,0x0101:{name:"ImageLength",description:"Image height",description_ja:""}
			,0x0102:{name:"BitsPerSample",description:"Number of bits per component",description_ja:""}
			,0x0103:{name:"Compression",description:"Compression scheme",description_ja:""}
			,0x0106:{name:"PhotometricInterpretation",description:"Pixel composition",description_ja:""}
			,0x010e:{name:"ImageDescription",description:"Image title",description_ja:""}
			,0x010f:{name:"Make",description:"Manufacturer of image input equipment",description_ja:""}
			,0x0110:{name:"Model",description:"Model of image input equipment",description_ja:""}
			,0x0111:{name:"StripOffsets",description:"Image data location",description_ja:""}
			,0x0112:{name:"Orientation",description:"Orientation of image",description_ja:""}
			,0x0115:{name:"SamplesPerPixel",description:"Number of components",description_ja:""}
			,0x0116:{name:"RowsPerStrip",description:"Number of rows per strip",description_ja:""}
			,0x0117:{name:"StripByteCounts",description:"Bytes per compressed strip",description_ja:""}
			,0x011a:{name:"XResolution",description:"Image resolution in width direction",description_ja:""}
			,0x011b:{name:"YResolution",description:"Image resolution in height direction",description_ja:""}
			,0x011c:{name:"PlanarConfiguration",description:"Image data arrangement",description_ja:""}
			,0x0128:{name:"ResolutionUnit",description:"Unit of X and Y resolution",description_ja:""}
			,0x012d:{name:"TransferFunction",description:"Transfer function",description_ja:""}
			,0x0131:{name:"Software",description:"Software used",description_ja:""}
			,0x0132:{name:"DateTime",description:"File change date and time",description_ja:""}
			,0x013b:{name:"Artist",description:"Person who created the image",description_ja:""}
			,0x013e:{name:"WhitePoint",description:"White point chromaticity",description_ja:""}
			,0x013f:{name:"PrimaryChromaticities",description:"Chromaticities of primaries",description_ja:""}
			,0x0201:{name:"JPEGInterchangeFormat",description:"Offset to JPEG SOI",description_ja:""}
			,0x0202:{name:"JPEGInterchangeFormatLength",description:"Bytes of JPEG data",description_ja:""}
			,0x0211:{name:"YCbCrCoefficients",description:"Color space transformation matrix coefficients",description_ja:""}
			,0x0212:{name:"YCbCrSubSampling",description:"Subsampling ratio of Y to C",description_ja:""}
			,0x0213:{name:"YCbCrPositioning",description:"Y and C positioning",description_ja:""}
			,0x0214:{name:"ReferenceBlackWhite",description:"Pair of black and white reference values",description_ja:""}
			,0x8298:{name:"Copyright",description:"Copyright holder",description_ja:""}
			,0x8769:{name:"Exif IFD Pointer",description:"Exif tag",description_ja:""}
			,0x8825:{name:"GPSInfo IFD Pointer",description:"GPS tag",description_ja:""}
		};
		
		static private const IFD_DEFINE_EXIF:Object=
		{
			 0x829a:{name:"ExposureTime",description:"Exposure time",description_ja:""}
			,0x829d:{name:"FNumber",description:"F number",description_ja:""}
			,0x8822:{name:"ExposureProgram",description:"Exposure program",description_ja:""}
			,0x8824:{name:"SpectralSensitivity",description:"",description_ja:"Spectral sensitivity"}
			,0x8827:{name:"ISOSpeedRatings",description:"ISO speed ratings",description_ja:""}
			,0x8828:{name:"coefficient",description:"Optoelectric",description_ja:""}
			,0x9000:{name:"ExifVersion",description:"Exif Version",description_ja:""}
			,0x9003:{name:"DateTimeOriginal",description:"Date and time original image was generated",description_ja:""}
			,0x9004:{name:"DateTimeDigitized",description:"Date and time image was made digital data",description_ja:""}
			,0x9101:{name:"ComponentsConfiguration",description:"Meaning of each component",description_ja:""}
			,0x9102:{name:"CompressedBitsPerPixel",description:"Image compression mode",description_ja:""}
			,0x9201:{name:"ShutterSpeedValue",description:"Shutter speed",description_ja:""}
			,0x9202:{name:"ApertureValue",description:"Aperture",description_ja:""}
			,0x9203:{name:"BrightnessValue",description:"Brightness",description_ja:""}
			,0x9204:{name:"ExposureBiasValue",description:"Exposure bias",description_ja:""}
			,0x9205:{name:"MaxApertureValue",description:"Maximum lens aperture",description_ja:""}
			,0x9206:{name:"SubjectDistance",description:"Subject distance",description_ja:""}
			,0x9207:{name:"MeteringMode",description:"Metering mode",description_ja:""}
			,0x9208:{name:"LightSource",description:"Light source",description_ja:""}
			,0x9209:{name:"Flash",description:"Flash",description_ja:""}
			,0x920a:{name:"FocalLength",description:"Lens focal length",description_ja:""}
			,0x9214:{name:"SubjectArea",description:"Subject area",description_ja:""}
			,0x927c:{name:"MakerNote",description:"Manufacturer notes",description_ja:""}
			,0x9286:{name:"UserComment",description:"User comments",description_ja:""}
			,0x9290:{name:"SubSecTime",description:"DateTime subseconds",description_ja:""}
			,0x9291:{name:"SubSecTimeOriginal",description:"DateTimeOriginal subseconds",description_ja:""}
			,0x9292:{name:"SubSecTimeDigitized",description:"DateTimeDigitized subseconds",description_ja:""}
			,0xa000:{name:"FlashpixVersion",description:"Supported Flashpix version",description_ja:""}
			,0xa001:{name:"ColorSpace",description:"Color space information",description_ja:""}
			,0xa002:{name:"PixelXDimension",description:"Valid image width",description_ja:""}
			,0xa003:{name:"PixelYDimension",description:"Valid image height",description_ja:""}
			,0xa004:{name:"RelatedSoundFile",description:"Related audio file",description_ja:""}
			,0xa005:{name:"Interoperability IFD Pointer",description:"Interoperability tag",description_ja:""}
			,0xa20b:{name:"FlashEnergy",description:"Flash energy",description_ja:""}
			,0xa20c:{name:"SpatialFrequencyResponse",description:"Spatial frequency response",description_ja:""}
			,0xa20e:{name:"FocalPlaneXResolution",description:"Focal plane X resolution",description_ja:""}
			,0xa20f:{name:"FocalPlaneYResolution",description:"Focal plane Y resolution",description_ja:""}
			,0xa210:{name:"FocalPlaneResolutionUnit",description:"Focal plane resolution unit",description_ja:""}
			,0xa214:{name:"SubjectLocation",description:"Subject location",description_ja:""}
			,0xa215:{name:"ExposureIndex",description:"Exposure index",description_ja:""}
			,0xa217:{name:"SensingMethod",description:"Sensing method",description_ja:""}
			,0xa300:{name:"FileSource",description:"File source",description_ja:""}
			,0xa301:{name:"SceneType",description:"Scene type",description_ja:""}
			,0xa302:{name:"CFAPattern",description:"CFA pattern",description_ja:""}
			,0xa401:{name:"CustomRendered",description:"Custom image processing",description_ja:""}
			,0xa402:{name:"ExposureMode",description:"Exposure mode",description_ja:""}
			,0xa403:{name:"WhiteBalance",description:"White balance",description_ja:""}
			,0xa404:{name:"DigitalZoomRatio",description:"Digital zoom ratio",description_ja:""}
			,0xa405:{name:"FocalLengthIn35mmFilm",description:"Focal length in 35 mm film",description_ja:""}
			,0xa406:{name:"SceneCaptureType",description:"Scene capture type",description_ja:""}
			,0xa407:{name:"GainControl",description:"Gain control",description_ja:""}
			,0xa408:{name:"Contrast",description:"Contrast",description_ja:""}
			,0xa409:{name:"Saturation",description:"Saturation",description_ja:""}
			,0xa40a:{name:"Sharpness",description:"Sharpness",description_ja:""}
			,0xa40b:{name:"DeviceSettingDescription",description:"Device settings description",description_ja:""}
			,0xa40c:{name:"SubjectDistanceRange",description:"Subject distance range",description_ja:""}
			,0xa420:{name:"ImageUniqueID",description:"Unique image ID",description_ja:""}
		};
		
		static private const IFD_DEFINE_GPS:Object=
		{
			 0x0000:{name:"GPSVersionID",description:"GPS tag version",description_ja:""}
			,0x0001:{name:"GPSLatitudeRef",description:"North or South Latitude",description_ja:""}
			,0x0002:{name:"GPSLatitude",description:"Latitude",description_ja:""}
			,0x0003:{name:"GPSLongitudeRef",description:"East or West Longitude",description_ja:""}
			,0x0004:{name:"GPSLongitude",description:"Longitude",description_ja:""}
			,0x0005:{name:"GPSAltitudeRef",description:"Altitude reference",description_ja:""}
			,0x0006:{name:"GPSAltitude",description:"Altitude",description_ja:""}
			,0x0007:{name:"GPSTimeStamp",description:"GPS time (atomic clock)",description_ja:""}
			,0x0008:{name:"GPSSatellites",description:"GPS satellites used for measurement",description_ja:""}
			,0x0009:{name:"GPSStatus",description:"GPS receiver status",description_ja:""}
			,0x000a:{name:"GPSMeasureMode",description:"GPS measurement mode",description_ja:""}
			,0x000b:{name:"GPSDOP",description:"Measurement precision",description_ja:""}
			,0x000c:{name:"GPSSpeedRef",description:"Speed unit",description_ja:""}
			,0x000d:{name:"GPSSpeed",description:"Speed of GPS receiver",description_ja:""}
			,0x000e:{name:"GPSTrackRef",description:"Reference for direction of movement",description_ja:""}
			,0x000f:{name:"GPSTrack",description:"Direction of movement",description_ja:""}
			,0x0010:{name:"GPSImgDirectionRef",description:"Reference for direction of image",description_ja:""}
			,0x0011:{name:"GPSImgDirection",description:"Direction of image",description_ja:""}
			,0x0012:{name:"GPSMapDatum",description:"Geodetic survey data used",description_ja:""}
			,0x0013:{name:"GPSDestLatitudeRef",description:"Reference for latitude of destination",description_ja:""}
			,0x0014:{name:"GPSDestLatitude",description:"Latitude of destination",description_ja:""}
			,0x0015:{name:"GPSDestLongitudeRef",description:"Reference for longitude of destination",description_ja:""}
			,0x0016:{name:"GPSDestLongitude",description:"Longitude of destination",description_ja:""}
			,0x0017:{name:"GPSDestBearingRef",description:"Reference for bearing of destination",description_ja:""}
			,0x0018:{name:"GPSDestBearing",description:"Bearing of destination",description_ja:""}
			,0x0019:{name:"GPSDestDistanceRef",description:"Reference for distance to destination",description_ja:""}
			,0x001a:{name:"GPSDestDistance",description:"Distance to destination",description_ja:""}
			,0x001b:{name:"GPSProcessingMethod",description:"Name of GPS processing method",description_ja:""}
			,0x001c:{name:"GPSAreaInformation",description:"Name of GPS area",description_ja:""}
			,0x001d:{name:"GPSDateStamp",description:"GPS date",description_ja:""}
			,0x001e:{name:"GPSDifferential",description:"GPS differential correction",description_ja:""}
		};
		
		static private const IFD_DEFINE_INTEROPERABILITY:Object=
		{
			 0x0001:{name:"InteroperabilityIndex",description:"Interoperability Identification",description_ja:""}
		};
		
		static private const IFD_DEFINE_1ST:Object=
		{
			 0x0100:{name:"ImageWidth",description:"Image width",description_ja:""}
			,0x0101:{name:"ImageLength",description:"Image height",description_ja:""}
			,0x0102:{name:"BitsPerSample",description:"Number of bits per component",description_ja:""}
			,0x0103:{name:"Compression",description:"Compression scheme",description_ja:""}
			,0x0106:{name:"PhotometricInterpretation",description:"Pixel composition",description_ja:""}
			,0x010e:{name:"ImageDescription",description:"Image title",description_ja:""}
			,0x010f:{name:"Make",description:"Manufacturer of image input equipment",description_ja:""}
			,0x0110:{name:"Model",description:"Model of image input equipment",description_ja:""}
			,0x0111:{name:"StripOffsets",description:"Image data location",description_ja:""}
			,0x0112:{name:"Orientation",description:"Orientation of image",description_ja:""}
			,0x0115:{name:"SamplesPerPixel",description:"Number of components",description_ja:""}
			,0x0116:{name:"RowsPerStrip",description:"Number of rows per strip",description_ja:""}
			,0x0117:{name:"StripByteCounts",description:"Bytes per compressed strip",description_ja:""}
			,0x011a:{name:"XResolution",description:"Image resolution in width direction",description_ja:""}
			,0x011b:{name:"YResolution",description:"Image resolution in height direction",description_ja:""}
			,0x011c:{name:"PlanarConfiguration",description:"Image data arrangement",description_ja:""}
			,0x0128:{name:"ResolutionUnit",description:"Unit of X and Y resolution",description_ja:""}
			,0x012d:{name:"TransferFunction",description:"Transfer function",description_ja:""}
			,0x0131:{name:"Software",description:"Software used",description_ja:""}
			,0x0132:{name:"DateTime",description:"File change date and time",description_ja:""}
			,0x013b:{name:"Artist",description:"Person who created the image",description_ja:""}
			,0x013e:{name:"WhitePoint",description:"White point chromaticity",description_ja:""}
			,0x013f:{name:"PrimaryChromaticities",description:"Chromaticities of primaries",description_ja:""}
			,0x0201:{name:"JPEGInterchangeFormat",description:"Offset to JPEG SOI",description_ja:""}
			,0x0202:{name:"JPEGInterchangeFormatLength",description:"Bytes of JPEG data",description_ja:""}
			,0x0211:{name:"YCbCrCoefficients",description:"Color space transformation matrix coefficients",description_ja:""}
			,0x0212:{name:"YCbCrSubSampling",description:"Subsampling ratio of Y to C",description_ja:""}
			,0x0213:{name:"YCbCrPositioning",description:"Y and C positioning",description_ja:""}
			,0x0214:{name:"ReferenceBlackWhite",description:"Pair of black and white reference values",description_ja:""}
			,0x8298:{name:"Copyright",description:"Copyright holder",description_ja:""}
			,0x8769:{name:"Exif IFD Pointer",description:"Exif tag",description_ja:""}
			,0x8825:{name:"GPSInfo IFD Pointer",description:"GPS tag",description_ja:""}
		};

	}
}