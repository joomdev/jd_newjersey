<?php
 defined('_JEXEC') or die();
/**
 * Media file uploader class
 *
 * This class provides a uploader functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2015 VirtueMart Team. All rights reserved by the author.
 */



class vmUploader {

	static $fT = null;
	static $MimeTypes = null;

	static function getMime2ExtArray(){

		if(self::$MimeTypes===null){
			self::$MimeTypes = array();
			self::$MimeTypes['application/acad'] 		= 'dwg';	#		AutoCAD
			self::$MimeTypes['application/applefile'] 		= ''; 		# 		AppleFile-Dateien
			self::$MimeTypes['application/astound'] 		= 'asd';	# *.asn 	Astound-Dateien
			self::$MimeTypes['application/dsptype']  		= 'tsp'; 	# 		TSP-Dateien
			self::$MimeTypes['application/dxf']		 	= 'dxf'; 	#		AutoCAD-Dateien (nach CERN)
			self::$MimeTypes['application/futuresplash'] 	= 'spl'; 	#		Flash Futuresplash-Dateien
			self::$MimeTypes['application/gzip'] 		= 'gz';	 	#		GNU Zip-Dateien
			self::$MimeTypes['application/listenup'] 		= 'ptlk'; 	#		Listenup-Dateien
			self::$MimeTypes['application/mac-binhex40']		= 'hqx'; 	#		Macintosh Binärdateien
			self::$MimeTypes['application/mbedlet'] 		= 'mbd'; 	#		Mbedlet-Dateien
			self::$MimeTypes['application/mif'] 			= 'mif'; 	#		FrameMaker Interchange Format Dateien
			self::$MimeTypes['application/msexcel'] 		= 'xls'; 	# *.xla 	Microsoft Excel Dateien
			self::$MimeTypes['application/mshelp'] 		= 'hlp'; 	# *.chm 	Microsoft Windows Hilfe Dateien
			self::$MimeTypes['application/mspowerpoint'] 	= 'ppt'; 	# *.ppz *.pps *.pot 	Microsoft Powerpoint Dateien
			self::$MimeTypes['application/msword'] 		= 'doc'; 	# *.dot 	Microsoft Word Dateien
			self::$MimeTypes['application/octet-stream'] 	= 'bin'; 	# *.exe *.com *.dll *.class 	Nicht näher spezifizierte Binärdaten, z.B. ausführbare Dateien
			self::$MimeTypes['application/oda'] 			= 'oda'; 	# 		Oda-Dateien
			self::$MimeTypes['application/pdf'] 			= 'pdf'; 	#		Adobe PDF-Dateien
			self::$MimeTypes['application/postscript'] 		= 'ai'; 	# *.eps *.ps 	Adobe PostScript-Dateien
			self::$MimeTypes['application/rtc'] 			= 'rtc'; 	#		RTC-Dateien
			self::$MimeTypes['application/rtf'] 			= 'rtf'; 	# 		Microsoft RTF-Dateien
			self::$MimeTypes['application/studiom'] 		= 'smp'; 	#		Studiom-Dateien
			self::$MimeTypes['application/toolbook'] 		= 'tbk'; 	#		Toolbook-Dateien
			self::$MimeTypes['application/vocaltec-media-desc'] 	= 'vmd'; 	#		Vocaltec Mediadesc-Dateien
			self::$MimeTypes['application/vocaltec-media-file'] 	= 'vmf'; 	#		Vocaltec Media-Dateien
			self::$MimeTypes['application/xhtml+xml'] 		= 'htm'; 	# *.html *.shtml *.xhtml 	XHTML-Dateien
			self::$MimeTypes['application/xml'] 			= 'xml'; 	#		XML-Dateien
			self::$MimeTypes['application/x-bcpio'] 		= 'bcpio'; 	# 		BCPIO-Dateien
			self::$MimeTypes['application/x-compress'] 		= 'z'; 		#		zlib-komprimierte Dateien
			self::$MimeTypes['application/x-cpio'] 		= 'cpio'; 	# 		CPIO-Dateien
			self::$MimeTypes['application/x-csh'] 		= 'csh'; 	#		C-Shellscript-Dateien
			self::$MimeTypes['application/x-director'] 		= 'dcr'; 	# *.dir *.dxr 	Macromedia Director-Dateien
			self::$MimeTypes['application/x-dvi'] 		= 'dvi'; 	#		DVI-Dateien
			self::$MimeTypes['application/x-envoy'] 		= 'evy'; 	#		Envoy-Dateien
			self::$MimeTypes['application/x-gtar'] 		= 'gtar'; 	# 		GNU tar-Archivdateien
			self::$MimeTypes['application/x-hdf'] 		= 'hdf'; 	#		HDF-Dateien
			self::$MimeTypes['application/x-httpd-php'] 		= 'php';	# *.phtml 	PHP-Dateien
			self::$MimeTypes['application/x-javascript'] 	= 'js'; 	#		serverseitige JavaScript-Dateien
			self::$MimeTypes['application/x-latex'] 		= 'latex'; 	#		LaTeX-Quelldateien
			self::$MimeTypes['application/x-macbinary'] 		= 'bin'; 	#		Macintosh Binärdateien
			self::$MimeTypes['application/x-mif'] 		= 'mif'; 	# 		FrameMaker Interchange Format Dateien
			self::$MimeTypes['application/x-netcdf'] 		= 'nc'; 	# *.cdf 	Unidata CDF-Dateien
			self::$MimeTypes['application/x-nschat'] 		= 'nsc'; 	# 		NS Chat-Dateien
			self::$MimeTypes['application/x-sh'] 	    	= 'sh'; 	#		Bourne Shellscript-Dateien
			self::$MimeTypes['application/x-shar'] 	    	= 'shar'; 	#		Shell-Archivdateien
			self::$MimeTypes['application/x-shockwave-flash'] 	= 'swf';	# *.cab Flash Shockwave-Dateien
			self::$MimeTypes['application/x-sprite'] 		= 'spr'; 	# *.sprite 	Sprite-Dateien
			self::$MimeTypes['application/x-stuffit'] 		= 'sit'; 	#		Stuffit-Dateien
			self::$MimeTypes['application/x-supercard'] 		= 'sca'; 	#		Supercard-Dateien
			self::$MimeTypes['application/x-sv4cpio'] 		= 'sv4cpio'; 	# 		CPIO-Dateien
			self::$MimeTypes['application/x-sv4crc'] 		= 'sv4crc'; 	# 		CPIO-Dateien mit CRC
			self::$MimeTypes['application/x-tar'] 	    	= 'tar'; 	#		tar-Archivdateien
			self::$MimeTypes['application/x-tcl'] 	   	= 'tcl'; 	#		TCL Scriptdateien
			self::$MimeTypes['application/x-tex'] 	    	= 'tex';	# 		TeX-Dateien
			self::$MimeTypes['application/x-texinfo'] 		= 'texinfo'; 	# *.texi 	Texinfo-Dateien
			self::$MimeTypes['application/x-troff'] 	    	= 't'; 		# *.tr *.roff 	TROFF-Dateien (Unix)
			self::$MimeTypes['application/x-troff-man'] 		= 'man'; 	# *.troff 	TROFF-Dateien mit MAN-Makros (Unix)
			self::$MimeTypes['application/x-troff-me'] 		= 'me'; 	# *.troff 	TROFF-Dateien mit ME-Makros (Unix)
			self::$MimeTypes['application/x-troff-ms'] 		= 'me'; 	# *.troff 	TROFF-Dateien mit MS-Makros (Unix)
			self::$MimeTypes['application/x-ustar'] 		= 'ustar'; 	# 		tar-Archivdateien (Posix)
			self::$MimeTypes['application/x-wais-source'] 	= 'src'; 	# 		WAIS Quelldateien
			self::$MimeTypes['application/x-www-form-urlencoded'] = '';		#  		HTML-Formulardaten an CGI
			self::$MimeTypes['application/zip'] 			= 'zip'; 	# 		ZIP-Archivdateien
			self::$MimeTypes['audio/basic'] 			= 'au'; 	# *.snd 	Sound-Dateien
			self::$MimeTypes['audio/echospeech'] 		= 'es'; 	#		Echospeed-Dateien
			self::$MimeTypes['audio/tsplayer'] 			= 'tsi'; 	# 		TS-Player-Dateien
			self::$MimeTypes['audio/voxware'] 			= 'vox'; 	# 		Vox-Dateien
			self::$MimeTypes['audio/x-aiff'] 			= 'aif'; 	# *.aiff *.aifc AIFF-Sound-Dateien
			self::$MimeTypes['audio/x-dspeeh'] 			= 'dus'; 	# *.cht 	Sprachdateien
			self::$MimeTypes['audio/x-midi'] 			= 'mid'; 	# *.midi 	MIDI-Dateien
			self::$MimeTypes['audio/x-mpeg'] 			= 'mp2'; 	#		MPEG-Dateien
			self::$MimeTypes['audio/x-pn-realaudio'] 		= 'ram'; 	# *.ra 		RealAudio-Dateien
			self::$MimeTypes['audio/x-pn-realaudio-plugin'] 	= 'rpm'; 	# 		RealAudio-Plugin-Dateien
			self::$MimeTypes['audio/x-qt-stream'] 		= 'stream'; 	# 		Quicktime-Streaming-Dateien
			self::$MimeTypes['audio/x-wav'] 			= 'wav'; 	#		WAV-Dateien
			self::$MimeTypes['drawing/x-dwf'] 			= 'dwf'; 	# 		Drawing-Dateien
			self::$MimeTypes['image/cis-cod'] 			= 'cod'; 	# 		CIS-Cod-Dateien
			self::$MimeTypes['image/cmu-raster'] 		= 'ras'; 	#		CMU-Raster-Dateien
			self::$MimeTypes['image/fif'] 			= 'fif'; 	# 		FIF-Dateien
			self::$MimeTypes['image/gif'] 			= 'gif'; 	#		GIF-Dateien
			self::$MimeTypes['image/ief'] 			= 'ief'; 	#		IEF-Dateien
			self::$MimeTypes['image/jpeg'] 			= 'jpg';     	# *.jpeg *.jpe 	JPEG-Dateien
			self::$MimeTypes['image/png'] 			= 'png'; 	# 		PNG-Dateien
			self::$MimeTypes['image/tiff'] 			= 'tif';        # *.tiff  	TIFF-Dateien
			self::$MimeTypes['image/vasa'] 			= 'mcf'; 	# 		Vasa-Dateien
			self::$MimeTypes['image/vnd.wap.wbmp'] 		= 'wbmp'; 	# 		Bitmap-Dateien (WAP)
			self::$MimeTypes['image/x-freehand'] 		= 'fh4'; 	# *.fh5 *.fhc 	Freehand-Dateien
			self::$MimeTypes['image/x-icon'] 			= 'ico'; 	# 		Icon-Dateien (z.B. Favoriten-Icons)
			self::$MimeTypes['image/x-portable-anymap'] 		= 'pnm'; 	# 		PBM Anymap Dateien
			self::$MimeTypes['image/x-portable-bitmap'] 		= 'pbm'; 	# 		PBM Bitmap Dateien
			self::$MimeTypes['image/x-portable-graymap'] 	= 'pgm'; 	# 		PBM Graymap Dateien
			self::$MimeTypes['image/x-portable-pixmap'] 		= 'ppm'; 	# 		PBM Pixmap Dateien
			self::$MimeTypes['image/x-rgb'] 			= 'rgb'; 	# 		RGB-Dateien
			self::$MimeTypes['image/x-windowdump'] 		= 'xwd'; 	# 		X-Windows Dump
			self::$MimeTypes['image/x-xbitmap'] 			= 'xbm'; 	# 		XBM-Dateien
			self::$MimeTypes['image/x-xpixmap'] 			= 'xpm'; 	# 		XPM-Dateien
			self::$MimeTypes['message/external-body'] 		= ''; 		# 		Nachricht mit externem Inhalt
			self::$MimeTypes['message/http'] 	  		= '';		# 		HTTP-Headernachricht
			self::$MimeTypes['message/news'] 	  		= '';		# 		Newsgroup-Nachricht
			self::$MimeTypes['message/partial'] 	  		= '';		# 		Nachricht mit Teilinhalt
			self::$MimeTypes['message/rfc822'] 	  		= '';		# 		Nachricht nach RFC 2822
			self::$MimeTypes['model/vrml'] 			= 'wrl'; 	# 		Visualisierung virtueller Welten (VRML)
			self::$MimeTypes['multipart/alternative'] 		= '';  		# 		mehrteilige Daten gemischt
			self::$MimeTypes['multipart/byteranges'] 		= '';  		# 		mehrteilige Daten mit Byte-Angaben
			self::$MimeTypes['multipart/digest'] 	  	= '';		# 		mehrteilige Daten / Auswahl
			self::$MimeTypes['multipart/encrypted'] 	  	= '';		# 		mehrteilige Daten verschlüsselt
			self::$MimeTypes['multipart/form-data'] 	  	= '';		# 		mehrteilige Daten aus HTML-Formular (z.B. File-Upload)
			self::$MimeTypes['multipart/mixed'] 	  		= '';		# 		mehrteilige Daten gemischt
			self::$MimeTypes['multipart/parallel'] 	  	= '';		# 		mehrteilige Daten parallel
			self::$MimeTypes['multipart/related'] 	  	= '';		# 		mehrteilige Daten / verbunden
			self::$MimeTypes['multipart/report'] 	  	= '';		# 		mehrteilige Daten / Bericht
			self::$MimeTypes['multipart/signed'] 	  	= '';		# 		mehrteilige Daten / bezeichnet
			self::$MimeTypes['multipart/voice-message'] 		= '';  		# 		mehrteilige Daten / Sprachnachricht
			self::$MimeTypes['text/comma-separated-values'] 	= 'csv'; 	# 		kommaseparierte Datendateien
			self::$MimeTypes['text/css'] 			= 'css'; 	# 		CSS Stylesheet-Dateien
			self::$MimeTypes['text/html'] 			= 'htm'; 	# *.html *.shtml 	HTML-Dateien
			self::$MimeTypes['text/javascript'] 			= 'js';		# 		JavaScript-Dateien
			self::$MimeTypes['text/plain'] 			= 'txt'; 	# 		reine Textdateien
			self::$MimeTypes['text/richtext'] 			= 'rtx'; 	# 		Richtext-Dateien
			self::$MimeTypes['text/rtf'] 			= 'rtf';	# 		Microsoft RTF-Dateien
			self::$MimeTypes['text/x-php'] 			= 'php';	# 		PHP-Script-Dateien
			self::$MimeTypes['text/tab-separated-values'] 	= 'tsv'; 	# 		tabulator-separierte Datendateien
			self::$MimeTypes['text/vnd.wap.wml'] 		= 'wml'; 	# 		WML-Dateien (WAP)
			self::$MimeTypes['application/vnd.wap.wmlc'] 	= 'wmlc'; 	# 		WMLC-Dateien (WAP)
			self::$MimeTypes['text/vnd.wap.wmlscript'] 		= 'wmls'; 	# 		WML-Scriptdateien (WAP)
			self::$MimeTypes['application/vnd.wap.wmlscriptc'] 	= 'wmlsc'; 	# 		WML-Script-C-dateien (WAP)
			self::$MimeTypes['text/xml'] 			= 'xml'; 	# 		XML-Dateien
			self::$MimeTypes['text/xml-external-parsed-entity']  = ''; 		# 		extern geparste XML-Dateien
			self::$MimeTypes['text/x-setext'] 			= 'etx'; 	# 		SeText-Dateien
			self::$MimeTypes['text/x-sgml'] 			= 'sgm'; 	# *.sgml 	SGML-Dateien
			self::$MimeTypes['text/x-speech'] 			= 'talk'; 	# *.spc 	Speech-Dateien
			self::$MimeTypes['video/mpeg'] 			= 'mpeg'; 	# *.mpg *.mpe 	MPEG-Dateien
			self::$MimeTypes['video/quicktime'] 			= 'qt'; 	# *.mov 	Quicktime-Dateien
			self::$MimeTypes['video/vnd.vivo'] 			= 'viv'; 	# *.vivo 	Vivo-Dateien
			self::$MimeTypes['video/x-msvideo'] 			= 'avi'; 	# Microsoft AVI-Dateien
			self::$MimeTypes['video/x-sgi-movie'] 		= 'movie'; 	# Movie-Dateien
			self::$MimeTypes['workbook/formulaone'] 		= 'vts'; 	# *.vtts 	FormulaOne-Dateien
			self::$MimeTypes['x-world/x-3dmf'] 			= '3dmf'; 	# *.3dm *.qd3d *.qd3 	3DMF-Dateien
			self::$MimeTypes['x-world/x-vrml'] 			= 'wrl';	# ?
		}
		return self::$MimeTypes;
	}

	static function getSafeExt2MimeArray(){

		if(self::$fT===null){
			self::$fT = array();

			self::$fT['txt'] = 'text/plain';
			self::$fT['pdf'] = 'application/pdf';
			self::$fT['zip'] = 'application/zip';
			self::$fT['doc'] = 'application/msword';
			self::$fT['xls'] = 'application/vnd.ms-excel';
			self::$fT['ppt'] = 'application/vnd.ms-powerpoint';
			self::$fT['gif'] = 'image/gif';
			self::$fT['png'] = 'image/png';
			self::$fT['jpeg'] = 'image/jpg';
			self::$fT['jpg'] = 'image/jpg';
			self::$fT['rar'] = 'application/x-rar-compressed';
			self::$fT['epub'] = 'application/epub+zip';

			self::$fT['ra'] = 'audio/x-pn-realaudio';
			self::$fT['ram'] = 'audio/x-pn-realaudio';
			self::$fT['ogg'] = 'audio/x-pn-realaudio';

			self::$fT['wav'] = 'audio/wav';
			self::$fT['wmv'] = 'video/x-msvideo';
			self::$fT['avi'] = 'video/x-msvideo';
			self::$fT['asf'] = 'video/x-msvideo';
			self::$fT['divx'] = 'video/x-msvideo';

			self::$fT['mid'] = 'audio/midi';
			self::$fT['midi'] = 'audio/midi';
			self::$fT['mp3'] = 'audio/mpeg';
			self::$fT['mp4'] = 'audio/mpeg';
			self::$fT['mpeg'] = 'video/mpeg';
			self::$fT['mpg'] = 'video/mpeg';
			self::$fT['mpe'] = 'video/mpeg';
			self::$fT['mov'] = 'video/quicktime';
			self::$fT['3gp'] = 'video/quicktime';
			self::$fT['m4a'] = 'video/quicktime';
			self::$fT['aac'] = 'video/quicktime';
			self::$fT['m3u'] = 'video/quicktime';
		}
		
		
		return self::$fT;
	}
	
	/**
	 * Handles the upload process of a media, sets the mime_type, when success
	 *
	 * @author Max Milbers
	 * @param string $urlfolder relative url of the folder where to store the media
	 * @return name of the uploaded file
	 */
	static function uploadFile($urlfolder, &$obj, $overwrite = false){

		if(empty($urlfolder) OR strlen($urlfolder)<2){
			vmError('Not able to upload file, give path/url empty/too short '.$urlfolder.' please correct path in your virtuemart config');
			return false;
		}
		if(!class_exists('JFile')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'file.php');
		$media = vRequest::getFiles('upload');
		if(empty($media) or !isset($media['error']) ){
			vmError('Recieved no data for upload','Recieved no data for upload');
			vmdebug('no data in uploadFile ',$_FILES);
			return false;
		}

		$app = JFactory::getApplication();
		switch ($media['error']) {
			case 0:
				$path_folder = str_replace('/',DS,$urlfolder);

				//Sadly it does not work to upload unicode files,
				// the � for example is stored on windows as ä, this seems to be a php issue (maybe a config setting)
			/*	$dotPos = strrpos($media['name'],'.');
				$safeMediaName = vmFile::makeSafe( $media['name'] );
				if($dotPos!==FALSE){
					$mediaPure = substr($media['name'],0,$dotPos);
					$mediaExtension = strtolower(substr($media['name'],$dotPos));
				}
			*/

				$safeMediaName = vmFile::makeSafe( $media['name'] );
				$media['name'] = $safeMediaName;

				$mediaPure = JFile::stripExt($media['name']);
				$mediaExtension = strtolower(JFile::getExt($media['name']));
				if(empty($mediaExtension)){
					vmError('Invalid media; no extension '.$media['name']);
					return false;
				}

				if(!$overwrite){
					$i = 0;
					while (file_exists(VMPATH_ROOT.DS.$path_folder.$mediaPure.'.'.$mediaExtension) and $i<20) {
						$mediaPure = $mediaPure.rand(1,9);
						$i++;
					}
				}

				$media['name'] = $obj->file_name = $mediaPure.'.'.$mediaExtension;

				if(function_exists('exif_imagetype')){
					$type = exif_imagetype($media['tmp_name']);
				} else {
					$type = false;
				}

				if($type){
					vmdebug('Recognised image');
					if(!self::checkMediaType($type,$mediaExtension)){
						vmError('Invalid media, image type does not fit to extension '.$media['name'].' '.$type.'!='.$mediaExtension);
						return false;
					}
				} else if(!vmAccess::manager('media.potdang')){

					$m2ext = self::getMime2ExtArray();
					$realMime = self::getMimeType($media['tmp_name']);

					vmdebug('Uploading file $realMime',$realMime,$m2ext);
					if(isset($m2ext[$realMime])){
					//if($rExt = array_search($realMime,$m2ext)!==false){
						$rExt = $m2ext[$realMime];
						$hless = self::getSafeExt2MimeArray();
						vmdebug('Recognised nonimage, not safe ext',$rExt,$hless);
						//$rExt = $hless[$realMime];
						if(!isset($hless[$rExt])){
							vmError('Invalid media type, you are not allowed to upload this file, file type does not fit to mime '.$media['name']);
							return false;
						} else {
							vmdebug('Uploading file ',$hless[$rExt]);
						}
					} else {
						return false;
					}
				}

				if($obj->file_is_forSale==0){

					$uploadPath = VMPATH_ROOT.DS.$path_folder.$media['name'];
				} else {
					$uploadPath = $path_folder.$media['name'];
				}
				JFile::upload($media['tmp_name'], $uploadPath, false, vmAccess::manager('media.trusteduploader'));

				$obj->file_mimetype = $media['type'];
				$obj->media_published = 1;
				$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_FILE_UPLOAD_OK',VMPATH_ROOT.DS.$path_folder.$media['name']));
				return $media['name'];

			case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
				$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_PRODUCT_FILES_ERR_UPLOAD_MAX_FILESIZE',$media['name'],$media['tmp_name']), 'warning');
				break;
			case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
				$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_PRODUCT_FILES_ERR_MAX_FILE_SIZE',$media['name'],$media['tmp_name']), 'warning');
				break;
			case 3: //uploaded file was only partially uploaded
				$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_PRODUCT_FILES_ERR_PARTIALLY',$media['name'],$media['tmp_name']), 'warning');
				break;
			case 4: //no file was uploaded
				//$vmLogger->warning( "You have not selected a file/image for upload." );
				break;
			default: //a default error, just in case!  :)
				//$vmLogger->warning( "There was a problem with your upload." );
				break;
		}
		return false;
	}

	static function checkMediaType($type,$ext){

		if($type === IMAGETYPE_JPEG){
			if($ext!='jpg' and $ext!='jpeg') return false;
		}
		else if($type){
			if( '.'.$ext!=image_type_to_extension($type) ) return false;
		}
		return true;
	}

	static function getMimeType($p){
		if (version_compare(PHP_VERSION, '5.3.0') < 0) return false;

		if (!function_exists('finfo_open')){
			vmError('Please enable php_fileinfo.dll for more secure MIME-TYPE recognition, uploading file stopped','Could not recognise MIME, uploading stopped');
			return false;
		} else {
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mimeType = $finfo->file($p);
			return $mimeType;
		}
	}
}

if(!function_exists('mime_content_type'))
{
	function mime_content_type($filename, &$errortxt='')
	{
		#########################################################
		## Please do not use any direct user input for $filename
		#########################################################

		## for use on windows systems please install first:
		## http://gnuwin32.sourceforge.net/packages/file.htm
		$path = '';
		if (isset($_SERVER['WINDIR']))
		{
			//$path = "C:/Programme/GnuWin32/bin/";
		}

		$filepath = realpath($filename);
		$_mime = array();

		## escape spaces in $filename due to their separating effect
		$filepath = str_replace(" ","\\ ",$filepath);

		exec ($path . "file -bi $filepath", $_mime, $error);

		if (($error) or (count($_mime) != 1)) return false;

		if (strpos($_mime[0], "can't stat") !== false)
		{
			$errortxt = "unknown type";
			$mime = false;
		}
		elseif (strpos($_mime[0], "can't read") !== false)
		{
			$errortxt = "cannot read file";
			$mime = false;
		}
		elseif (strpos($_mime[0], "can't ") !== false)
		{
			$errortxt = "unspecified error";
			$mime = false;
		}
		else
		{
			$mime = trim($_mime[0]);
		}

		return $mime;
	}
}