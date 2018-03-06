<?php
/**
 * Media file handler class
 *
 * This class provides some file handling functions that are used throughout the VirtueMart shop.
 *  Uploading, moving, deleting
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011-2016 VirtueMart Team. All rights reserved by the author.
 */

defined('_JEXEC') or die();

/**
 * Sanitizes the filenames and transliterates them also for non latin languages
 * maybe we should move this to vmfilter
 * @author constantined
 *
 */
class vmFile {

	/**
	 * This function does not allow unicode
	 * @param      $string
	 * @param bool $forceNoUni
	 * @return mixed|string
	 */
	static function makeSafe($str,$forceNoUni=false) {

		return vRequest::filterPath($str);
	}
}

class VmMediaHandler {

	var $media_attributes = 0;
	var $setRole = false;
	var $file_name = '';
	var $file_extension = '';
	var $virtuemart_media_id = '';


	function __construct($id=0){

		$this->virtuemart_media_id = $id;

		$this->theme_url = VmConfig::get('vm_themeurl',0);
		if(empty($this->theme_url)){
			$this->theme_url = 'components/com_virtuemart/';
		}
	}

	/**
	 * The type of the media determines the used path for storing them
	 *
	 * @author Max Milbers
	 * @param string $type type of the media, allowed values product, category, shop, vendor, manufacturer, forSale
	 */
	public function getMediaUrlByView($type){

		//the problem is here, that we use for autocreatoin the name of the model, here products
		//But for storing we use the product to build automatically the table out of it (product_medias)
		$choosed = false;
		if($type == 'product' || $type == 'products'){
			$relUrl = VmConfig::get('media_product_path');
			$choosed = true;
		}
		else if($type == 'category' || $type == 'categories'){
			$relUrl = VmConfig::get('media_category_path');
			$choosed = true;
		}
		else if($type == 'shop'){
			$relUrl = VmConfig::get('media_path');
			$choosed = true;
		}
		else if($type == 'vendor' || $type == 'vendors'){
			$relUrl = VmConfig::get('media_vendor_path');
			$choosed = true;
		}
		else if($type == 'manufacturer' || $type == 'manufacturers'){
			$relUrl = VmConfig::get('media_manufacturer_path');
			$choosed = true;
		}
		else if($type == 'forSale' || $type== 'file_is_forSale'){
			if (!class_exists ('shopFunctionsF'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
			$relUrl = shopFunctions::checkSafePath();
			if($relUrl){
				$choosed = true;
				$this->file_is_forSale=1;
			}

		}

		if($choosed && empty($relUrl)){
			$link =JURI::root() . 'administrator/index.php?option=com_virtuemart&view=config';
			vmInfo('COM_VIRTUEMART_MEDIA_NO_PATH_TYPE',$type,$link );
			//Todo add general media_path to config
			//$relUrl = VmConfig::get('media_path');
			$relUrl = self::getStoriesFb().'/';
			$this->setRole=true;
			// 		} else if(!$choosed and empty($relUrl) and $this->file_is_forSale==0){
		} else if(!$choosed and empty($relUrl) ){

			if(empty($this->file_type) and !empty($this->file_url)){
				vmAdminInfo('COM_VIRTUEMART_MEDIA_CHOOSE_TYPE',$this->file_title );
				// 	vmError('Ignore this message, when it appears while the media synchronisation process, else report to http://forum.virtuemart.net/index.php?board=127.0 : cant create media of unknown type, a programmers error, used type ',$type);
			}
			$relUrl = self::getStoriesFb('typeless').'/';
			$this->setRole=true;

		} else if(!$choosed and $this->file_is_forSale==1){
			$relUrl = '';
			$this->setRole=false;
		}

		return $relUrl;
	}

	static function getStoriesFb($suffix = ''){

		if(!class_exists('JFolder')){
			require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
		}
		$url = 'images/virtuemart/'. $suffix ;

		if(JFolder::exists(VMPATH_ROOT .'/'.$url)) {
			return $url;
		} else {
			$urlOld = 'images/stories/virtuemart/'. $suffix;
			if(JFolder::exists(VMPATH_ROOT .'/'.$urlOld)){
				return $urlOld;
			}
		}

		if(JFolder::create(VMPATH_ROOT .'/'.$url)) {
			return $url;
		} else {
			return false;
		}
	}

	/**
	 * This function determines the type of a media and creates it.
	 * When you want to write a child class of the mediahandler, you need to manipulate this function.
	 * We may use later here a hook for plugins or simular
	 *
	 * @author Max Milbers
	 * @param object $table
	 * @param string  $type vendor,product,category,...
	 * @param string $file_mimetype such as image/jpeg
	 */
	static public function createMedia($table,$type='',$file_mimetype=''){

		if(!class_exists('JFile')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'file.php');

		$extension = strtolower(JFile::getExt($table->file_url));

		$isImage = self::isImage($extension);

		if($isImage){
			if (!class_exists('VmImage')) require(VMPATH_ADMIN.DS.'helpers'.DS.'image.php');
			$media = new VmImage();
		} else {
			$media = new VmMediaHandler();
		}

		$attribsImage = $table->getProperties();
		foreach($attribsImage as $k=>$v){
			$media->$k = $v;
		}

		if(empty($type)){
			$type = $media->file_type;
		} else {
			$media->file_type = $type;
		}

		$media->setFileInfo($type);

		return $media;
	}

	/**
	 * This prepares the object for storing the data. This means it does the action
	 * and returns the data for storing in the table
	 *
	 * @author Max Milbers
	 * @param object $table
	 * @param array $data
	 * @param string $type
	 */
	static public function prepareStoreMedia($table,$data,$type){

		$media = VmMediaHandler::createMedia($table,$type);

		$data = $media->processAttributes($data);
		$data = $media->processAction($data);
		if($data===false) return false;
		$attribsImage = get_object_vars($media);
		foreach($attribsImage as $k=>$v){
			$data[$k] = $v;
		}

		return $data;
	}

	/**
	 * Sets the file information and paths/urls and so on.
	 *
	 * @author Max Milbers
	 * @param unknown_type $filename
	 * @param unknown_type $url
	 * @param unknown_type $path
	 */
	function setFileInfo($type=0){


		$this->file_url_folder = '';
		$this->file_path_folder = '';
		$this->file_url_folder_thumb = '';

		if($this->file_is_forSale==0 and $type!='forSale'){

			$this->file_url_folder = $this->getMediaUrlByView($type);
			$this->file_url_folder_thumb = $this->file_url_folder.'resized/';
			$this->file_path_folder = str_replace('/',DS,$this->file_url_folder);
		} else {
			if (!class_exists ('shopFunctions'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctions.php');
			$safePath = shopFunctions::checkSafePath();
			if(!$safePath){
				return FALSE;
			}
			$this->file_path_folder = $safePath;
			$this->file_url_folder = $this->file_path_folder;//str_replace(DS,'/',$this->file_path_folder);
			$this->file_url_folder_thumb = VmConfig::get('forSale_path_thumb');
		}

		//Clean from possible injection
		while(strpos($this->file_path_folder,'..')!==false){
			$this->file_path_folder  = str_replace('..', '', $this->file_path_folder);
		};
		$this->file_path_folder  = preg_replace('#[/\\\\]+#', DS, $this->file_path_folder);

		if(empty($this->file_url)){
			$this->file_url = $this->file_url_folder;
			$this->file_name = '';
			$this->file_extension = '';
		} else {
			if(!class_exists('JFile')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'file.php');

			if($this->file_is_forSale==1){

				$rdspos = strrpos($this->file_url,DS);
				if($rdspos!==false){
					$name = substr($this->file_url,$rdspos+1);
				}else {
					vmdebug('$name',$this->file_url,$rdspos);
				}
			} else {
				//This construction is only valid for the images, it is for own structuring using folders
				$name = str_replace($this->file_url_folder,'',$this->file_url);
			}

			if(!empty($name) && $name !=='/'){
				$this->file_name = JFile::stripExt($name);
				//$this->file_extension = strtolower(JFile::getExt($name));
				$this->file_extension = strtolower(JFile::getExt($name));

				//Ensure using right directory
				$file_url = $this->getMediaUrlByView($type).$name;

				if($this->file_is_forSale==1){
					if(JFile::exists($file_url)){
						$this->file_url = $file_url;
					} else {
						//	vmdebug('MediaHandler, file does not exist in safepath '.$file_url);
					}
				} else {
					$pathToTest = VMPATH_ROOT.DS.str_replace('/',DS,$file_url);
					if(JFile::exists($pathToTest)){
						$this->file_url = $file_url;
					} else {
						//	vmdebug('MediaHandler, file does not exist in '.$pathToTest);
					}
				}

			}


		}

		if($this->file_is_downloadable) $this->media_role = 'file_is_downloadable';
		if($this->file_is_forSale) $this->media_role = 'file_is_forSale';
		if(empty($this->media_role)) $this->media_role = 'file_is_displayable';

		$this->determineFoldersToTest();

		//Do we need this?
		/*if(!empty($this->file_url) && empty($this->file_url_thumb)){
			$this->displayMediaThumb('',true,'',false);
		}*/


	}

	public function getUrl(){
		return $this->file_url_folder.$this->file_name.'.'.$this->file_extension;
	}

	public function getThumbUrl(){
		return $this->file_url_folder_thumb.$this->file_name.'.'.$this->file_extension;
	}

	public function getFullPath(){

		$rel_path = str_replace('/',DS,$this->file_url_folder);
		return VMPATH_ROOT.DS.$rel_path.$this->file_name.'.'.$this->file_extension;
	}

	public function getThumbPath(){

		$rel_path = str_replace('/',DS,$this->file_url_folder);
		return VMPATH_ROOT.DS.$rel_path.$this->file_name_thumb.'.'.$this->file_extension;
	}

	/**
	 * Tests if a function is an image by mime or extension
	 *
	 * @author Max Milbers
	 * @param string $file_mimetype
	 * @param string $file_extension
	 */
	static private function isImage($file_extension=0){

		if($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif'){
			$isImage = TRUE;

		} else {
			$isImage = FALSE;
		}

		return $isImage;
	}

	private $_foldersToTest = array();

	/**
	 * This functions adds the folders to test for each media, you can add more folders to test with
	 * addFoldersToTest
	 * @author Max Milbers
	 */
	public function determineFoldersToTest(){

		if(VmAccess::manager('core')){
			$r = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
			$this->addFoldersToTest($r);
		}
		$file_path = str_replace('/',DS,$this->file_url_folder);
		if($this->file_is_forSale){
			$this->addFoldersToTest($file_path);
		} else {
			$this->addFoldersToTest(VMPATH_ROOT.DS.$file_path);
		}

		$file_path_thumb = str_replace('/',DS,$this->file_url_folder_thumb);
		$this->addFoldersToTest(VMPATH_ROOT.DS.$file_path_thumb);

	}


	/**
	 * Add complete paths here to test/display if their are writable
	 *
	 * @author Max Milbers
	 * @param absolutepPath $folders
	 */
	public function addFoldersToTest($folders){
		if(!is_array($folders)) $folders = (array) $folders;
		$this->_foldersToTest = array_merge($this->_foldersToTest, $folders);
	}

	/**
	 * Displays for paths if they are writeable
	 * You set the folders to test with the function addFoldersToTest
	 * @author Max Milbers
	 */
	public function displayFoldersWriteAble(){

		$style = 'text-align:left;margin-left:20px;';
		$result = '<div class="vmquote" style="'.$style.'">';

		foreach( $this->_foldersToTest as $dir ) {
			$result .= $dir . ' :: ';
			$result .= is_writable( $dir )
			? '<span style="font-weight:bold;color:green;">'.vmText::_('COM_VIRTUEMART_WRITABLE').'</span>'
			: '<span style="font-weight:bold;color:red;">'.vmText::_('COM_VIRTUEMART_UNWRITABLE').'</span>';
			$result .= '<br/>';
		}
		$result .= '</div>';
		return $result;
	}

	/**
	 * Shows the supported file types for the server
	 *
	 * @author enyo 06-Nov-2003 03:32 http://www.php.net/manual/en/function.imagetypes.php
	 * @author Max Milbers
	 * @return multitype:string
	 */
	function displaySupportedImageTypes() {
		$aSupportedTypes = array();

		$aPossibleImageTypeBits = array(
		IMG_GIF=>'GIF',
		IMG_JPG=>'JPG',
		IMG_PNG=>'PNG',
		IMG_WBMP=>'WBMP'
		);

		foreach ($aPossibleImageTypeBits as $iImageTypeBits => $sImageTypeString) {

			if(function_exists('imagetypes')){
				if (imagetypes() & $iImageTypeBits) {
					$aSupportedTypes[] = $sImageTypeString;
				}
			}
		}

		$supportedTypes = '';
		if(function_exists('mime_content_type')){
			$supportedTypes .= vmText::_('COM_VIRTUEMART_FILES_FORM_MIME_CONTENT_TYPE_SUPPORTED').'<br />';
		} else {
			$supportedTypes .= vmText::_('COM_VIRTUEMART_FILES_FORM_MIME_CONTENT_TYPE_NOT_SUPPORTED').'<br />';
		}

		$supportedTypes .= vmText::_('COM_VIRTUEMART_FILES_FORM_IMAGETYPES_SUPPORTED'). implode($aSupportedTypes,', ');

		return $supportedTypes;
	}

	function filterImageArgs($imageArgs){
		if(!empty($imageArgs)){
			if(!is_array($imageArgs)){
				$imageArgs = str_replace(array('class','"','='),'',$imageArgs);
				$imageArgs = array('class' => $imageArgs.' '.$this->file_class);
			} else {
				if(!isset($imageArgs['class'])) $imageArgs['class'] = '';
				$imageArgs['class'] .= ' '.$this->file_class;
			}
		} else {
			$imageArgs = array('class' => $imageArgs.' '.$this->file_class);
		}
		return $imageArgs;
	}

	/**
	 * Just for overwriting purpose for childs. Take a look on VmImage to see an example
	 *
	 * @author Max Milbers
	 */
	function displayMediaFull(){
		return $this->displayMediaThumb(array('id'=>'vm_display_image'),false,'',true,true);
	}

	/**
	 * This function displays the image, when the image is not already a resized one,
	 * it tries to get first the resized one, or create a resized one or fallback in case
	 *
	 * @author Max Milbers
	 *
	 * @param string $imageArgs Attributes to be included in the <img> tag.
	 * @param boolean $lightbox alternative display method
	 * @param string $effect alternative lightbox display
	 * @param boolean $withDesc display the image media description
	 */
	function displayMediaThumb($imageArgs='',$lightbox=true,$effect="class='modal' rel='group'",$return = true,$withDescr = false,$absUrl = false, $width=0,$height=0){

		if(!empty($this->file_class)){
			$imageArgs = $this->filterImageArgs($imageArgs);
		}


		if(empty($this->file_name)){

			if($return){
				if($this->file_is_downloadable){
					$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('downloadable','zip.png');
					$file_alt = vmText::_('COM_VIRTUEMART_NO_IMAGE_SET').' '.$this->file_description;
					return $this->displayIt($file_url, $file_alt, '',true,'',$withDescr);
				} else {
					$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_set');
					$file_alt = vmText::_('COM_VIRTUEMART_NO_IMAGE_SET').' '.$this->file_description;
					return $this->displayIt($file_url, $file_alt, $imageArgs,$lightbox, $effect);
				}
			}
		}


		if($this->file_is_forSale){
			$toChk = $this->file_url;
		} else {
			$toChk = VMPATH_ROOT.'/'.$this->file_url;
		}
		if(!JFile::exists($toChk)){
			vmdebug('Media file does not exists',$toChk);
			vmError(vmText::sprintf('COM_VIRTUEMART_FILE_NOT_FOUND',$toChk));
		}

		$file_url_thumb = $this -> getFileUrlThumb($width, $height);

		$media_path = VMPATH_ROOT.DS.str_replace('/',DS,$file_url_thumb);

		if(empty($this->file_meta)){
			if(!empty($this->file_description)){
				$file_alt = $this->file_description;
			} else if(!empty($this->file_name)) {
				$file_alt = $this->file_name;
			} else {
				$file_alt = '';
			}
		} else {
			$file_alt = $this->file_meta;
		}

		if ((empty($file_url_thumb) || !file_exists($media_path)) && is_a($this,'VmImage')) {

			$file_url_thumb = $this->createThumb($width,$height);
			$media_path = VMPATH_ROOT.DS.str_replace('/',DS,$file_url_thumb);

		}
		//$this->file_url_thumb = $file_url_thumb;

		if($withDescr) $withDescr = $this->file_description;

		if (empty($file_url_thumb) || !file_exists($media_path)) {
			return $this->getIcon($imageArgs,$lightbox,$return,$withDescr,$absUrl);
		}

		if($return) return $this->displayIt($file_url_thumb, $file_alt, $imageArgs,$lightbox,$effect,$withDescr,$absUrl);

	}

	function getFileUrlThumb($width = 0,$height = 0){

		if(!empty($this->file_url_thumb)){
			$file_url_thumb = $this->file_url_thumb;
		} else if(is_a($this,'VmImage')) {
			$file_url_thumb = $this->createThumbFileUrl($width,$height);
		} else {
			$file_url_thumb = '';
		}

		return $file_url_thumb;
	}

	/**
	 * This function should return later also an icon, if there isnt any automatic thumbnail creation possible
	 * like pdf, zip, ...
	 *
	 * @author Max Milbers
	 * @param string $imageArgs
	 * @param boolean $lightbox
	 */
	function getIcon($imageArgs,$lightbox,$return=false,$withDescr=false,$absUrl = false){

		if(!empty($this->file_extension)){
			$file_url = $this->theme_url.'assets/images/vmgeneral/filetype_'.$this->file_extension.'.png';
			$file_alt = $this->file_description;
		} else {
			$file_url = $this->theme_url.'assets/images/vmgeneral/'.VmConfig::get('no_image_found');
			$file_alt = vmText::_('COM_VIRTUEMART_NO_IMAGE_FOUND').' '.$this->file_description;
		}
		if($return){
			if($this->file_is_downloadable){
				return $this->displayIt($file_url, $file_alt, '',true,'',$withDescr,$absUrl);
			} else {
				return $this->displayIt($file_url, $file_alt, $imageArgs,$lightbox,'',$withDescr,$absUrl);
			}
		}

	}

	/**
	 * This function is just for options how to display an image...
	 * we may add here plugins for displaying images
	 *
	 * @author Max Milbers
	 * @param string $file_url relative Url
	 * @param string $file_alt media description
	 * @param string $imageArgs attributes for displaying the images
	 * @param boolean $lightbox use lightbox
	 */
	function displayIt($file_url, $file_alt, $imageArgs,$lightbox, $effect ="class='modal'",$withDesc=false,$absUrl = false){

		if ($withDesc) $desc='<span class="vm-img-desc">'.$withDesc.'</span>';
		else $desc='';
		$root='';
		if($absUrl){
			$root = JURI::root(false);
		} else {
			$root = JURI::root(true).'/';
		}

		$args = '';
		if(is_array($imageArgs)){
			foreach($imageArgs as $k=>$v){
				$args .= ' '.$k.'="'.$v.'" ';
			}
		} else {
			$args = $imageArgs;
		}

		if($lightbox){
			$image = '<img src="' . $root.$file_url . '" alt="' . $file_alt . '" ' . $args . ' />';//JHtml::image($file_url, $file_alt, $imageArgs);
			if ($file_alt ) $file_alt = 'title="'.$file_alt.'"';
			if ($this->file_url and pathinfo($this->file_url, PATHINFO_EXTENSION) and substr( $this->file_url, 0, 4) != "http") $href = JURI::root() .$this->file_url ;
			else $href = $root.$file_url ;

			if ($this->file_is_downloadable) {
				$lightboxImage = '<a '.$file_alt.' '.$effect.' href="'.$href.'">'.$image.$desc.'</a>';
			} else {
				$lightboxImage = '<a '.$file_alt.' '.$effect.' href="'.$href.'">'.$image.'</a>';
				$lightboxImage = $lightboxImage.$desc;
			}

			return $lightboxImage;
		} else {

			return '<img src="' . $root.$file_url . '" alt="' . $file_alt . '" ' . $args . ' />'.$desc;
		}
	}

	/**
	 * Handles the upload process of a media, sets the mime_type, when success
	 *
	 * @author Max Milbers
	 * @param string $urlfolder relative url of the folder where to store the media
	 * @return name of the uploaded file
	 */
	function uploadFile($urlfolder,$overwrite = false){
		if(!class_exists('vmUploader')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmuploader.php');
		$r = vmUploader::uploadFile($urlfolder,$this,$overwrite);
		return $r;
	}


	/**
	 * Deletes a file
	 *
	 * @param string $url relative Url, gets adjusted to path
	 */
	function deleteFile($url, $absPathGiv = 0){

		if(!vmAccess::manager('media.delete')){
			vmWarn('Insufficient permissions to delete the media');
			return false;
		}

		if(empty($url)){
			vmTrace('deleteFile empty url was given');
			return false;
		}
		if(!class_exists('JFile')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'file.php');

		$file_path = str_replace('/',DS,$url);
		if($absPathGiv){
			//vmdebug('deleteFile absolut Path given',$file_path);
		} else {
			$file_path = VMPATH_ROOT.DS.$file_path;
			//vmdebug('deleteFile relative Path given',$file_path);
		}

		if(is_dir($file_path)){
			vmTrace('deleteFile path given '.$file_path);
			return false;
		}

		$app = JFactory::getApplication();

		$msg_path = '';

		if(vmAccess::manager('core')){
			$msg_path = $file_path;
		}


		if($res = JFile::delete( $file_path )){
			$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_FILE_DELETE_OK',$msg_path));
			return true;
		} else {
			$app->enqueueMessage(vmText::sprintf('COM_VIRTUEMART_FILE_DELETE_ERR',$res.' '.$msg_path));
		}
		return false;
	}

	function deleteThumbs(){

		$oldFileUrlThumb = $this->getFileUrlThumb();

		if(empty($oldFileUrlThumb)) return true;
		$filename = $this->file_name;
		if($this->file_is_forSale!=1){

			$dir = VMPATH_ROOT.DS.$this->file_url_folder.'resized';
			if($p = strpos($this->file_name,'/')){
				$dir .= DS.substr($this->file_name,0,$p);
				$filename = substr($this->file_name,$p+1);
			}

		} else {
			$dir = VmConfig::get('forSale_path_thumb', false);
			$dir = VMPATH_ROOT.DS.rtrim($dir,'/');
		}

		if(!is_dir($dir)){
			$m = 'deleteThumbs: Attention directoy is not accessible (does not exists or wrong rights) ';
			vmError($m.$dir,$m);
			//continue;
		}

		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if(!empty($file) and strpos($file,'.')!==0 and $file != 'index.html' and !is_dir($dir.DS.$file)){
					$hits = array();
					$regex = "/".$filename.'_\d{1,4}x\d{1,4}.\S{1,3}'."/";
					$res = preg_match($regex, $file, $hits);
					foreach($hits as $name){
						$this->deleteFile($dir.DS.$name,true);
					}
				}
			}
		}

		$this->deleteFile($oldFileUrlThumb, false);
	}

	/**
	 * Processes the choosed Action while storing the data, gets extend by the used child, use for the action clear commands.
	 * Useable commands in all medias upload, upload_delete, delete, and all of them with _thumb on it also.
	 *
	 * @author Max Milbers
	 * @param arraybyform $data
	 */
	function processAction($data){

		if(empty($data['media_action'])) return $data;

		if( $data['media_action'] == 'upload' ){

			$this->virtuemart_media_id=0;
			$this->file_url='';
			$this->file_url_thumb='';
			$file_name = $this->uploadFile($this->file_url_folder);
			if ($file_name===false) return false;
			$this->file_name = $file_name;
			$this->file_url = $this->file_url_folder.$this->file_name;
		}
		else if( $data['media_action'] == 'replace' ){

			//always delete the thumb
			$this->deleteThumbs();

			$oldFileUrl = $this->file_url;
			$file_name = $this->uploadFile($this->file_url_folder,true);
			if ($file_name===false) return false;
			$this->file_name = $file_name;
			$this->file_url = $this->file_url_folder.$this->file_name;

			if($this->file_url!=$oldFileUrl && !empty($this->file_name)){
				$this->deleteFile($oldFileUrl,$this->file_is_forSale);
			}


		}
		else if( $data['media_action'] == 'replace_thumb' ){

			$oldFileUrlThumb = $this->getFileUrlThumb();
			$oldFileUrl = $this->file_url_folder_thumb;
			$file_name = $this->uploadFile($this->file_url_folder_thumb,true);
			if ($file_name===false) return false;
			$this->file_name = $file_name;
			$this->file_url_thumb = $this->file_url_folder_thumb.$this->file_name;
			if($this->file_url_thumb!=$oldFileUrl&& !empty($this->file_name)){
				$this->deleteFile($oldFileUrlThumb);
			}
		}
		else if( $data['media_action'] == 'delete' ){
			//TODO this is complex, we must assure that the media entry gets also deleted.
			$mediaM = VmModel::getModel('media');
			$mediaM->removeFiles($this->virtuemart_media_id);
			//unset($data['active_media_id']);

		}


		if(empty($this->file_title) && !empty($file_name)) $this->file_title = $file_name;

		return $data;
	}


	/**
	 * For processing the Attributes of the media while the storing process
	 *
	 * @author Max Milbers
	 * @param unknown_type $data
	 */
	function processAttributes($data){

		$this->file_is_product_image = 0;
		$this->file_is_downloadable = 0;

		if(empty($data['media_roles'])) return $data;

		if($data['media_roles'] == 'file_is_downloadable'){
			$this->file_is_downloadable = 1;
			$this->file_is_forSale = 0;
		}
		else if($data['media_roles'] == 'file_is_forSale'){
			$this->file_is_downloadable = 0;
			$this->file_is_forSale = 1;
			$this->file_url_folder = VmConfig::get('forSale_path');
			$this->file_url_folder_thumb = VmConfig::get('forSale_path_thumb');

			$this->setRole = false;
		}

		if($this->setRole and $data['media_roles'] != 'file_is_forSale'){

			$this->file_url_folder = $this->getMediaUrlByView($data['media_attributes']);	//media_roles
			$this->file_url_folder_thumb = $this->file_url_folder.'resized/';

			$typelessUrl = 'images/stories/virtuemart/typeless/'.$this->file_name;
			vmdebug('the Urls',$data['media_roles'],$typelessUrl,$this->file_url_folder.$this->file_name);
			if(!file_exists($this->file_url_folder.$this->file_name) and file_exists($typelessUrl)){
				vmdebug('Execute move');
				if(!class_exists('JFile')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'file.php');
				JFile::move($typelessUrl, $this->file_url_folder.$this->file_name);
			}
		}

		if(!empty($data['active_languages'])) {
			$active_languages = implode(",", $data['active_languages']);
			$this->file_lang = $active_languages;
		}


		return $data;
	}

	private $_actions = array();
	/**
	 * This method can be used to add extra actions to the media
	 *
	 * @author Max Milbers
	 * @param string $optionName this is the value in the form
	 * @param string $langkey the langkey used
	 */
	function addMediaAction($optionName,$langkey){
		$this->_actions[$optionName] = $langkey ;
	}

	/**
	 * Adds the media action which are needed in the form for all media,
	 * you can use this function in your child calling parent. Look in VmImage for an exampel
	 * @author Max Milbers
	 */
	function addMediaActionByType(){

		$this->addMediaAction(0,'COM_VIRTUEMART_NONE');

		$view = vRequest::getCmd('view');
		if($view!='media' || empty($this->file_name)){
			$this->addMediaAction('upload','COM_VIRTUEMART_FORM_MEDIA_UPLOAD');
		}

		if(!empty($this->file_name)){
			$this->addMediaAction('replace','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_REPLACE');
			$this->addMediaAction('replace_thumb','COM_VIRTUEMART_FORM_MEDIA_UPLOAD_REPLACE_THUMB');
		}

	}


	private $_mLocation = array();

	/**
	 * This method can be used to add extra attributes to the media
	 *
	 * @author Max Milbers
	 * @param string $optionName this is the value in the form
	 * @param string $langkey the langkey used
	 */
	public function addMediaAttributes($optionName,$langkey=''){
		$this->_mLocation[$optionName] = $langkey ;
	}

	/**
	 * Adds the attributes which are needed in the form for all media,
	 * you can use this function in your child calling parent. Look in VmImage for an exampel
	 * @author Max Milbers
	 */
	public function addMediaAttributesByType(){


		if($this->setRole){
			// 				$this->addMediaAttributes('file_is_product_image','COM_VIRTUEMART_FORM_MEDIA_SET_PRODUCT');
			$this->addMediaAttributes('product','COM_VIRTUEMART_FORM_MEDIA_SET_PRODUCT'); // => file_is_displayable  =>location
			$this->addMediaAttributes('category','COM_VIRTUEMART_FORM_MEDIA_SET_CATEGORY');
			$this->addMediaAttributes('manufacturer','COM_VIRTUEMART_FORM_MEDIA_SET_MANUFACTURER');
			$this->addMediaAttributes('vendor','COM_VIRTUEMART_FORM_MEDIA_SET_VENDOR');

			$this->_mRoles['file_is_displayable'] = 'COM_VIRTUEMART_FORM_MEDIA_DISPLAYABLE' ;
			$this->_mRoles['file_is_downloadable'] = 'COM_VIRTUEMART_FORM_MEDIA_DOWNLOADABLE' ;
			$this->_mRoles['file_is_forSale'] = 'COM_VIRTUEMART_FORM_MEDIA_SET_FORSALE' ;
		} else {

			if($this->file_is_forSale==1){
				$this->_mRoles['file_is_forSale'] = 'COM_VIRTUEMART_FORM_MEDIA_SET_FORSALE' ;
			} else {
				$this->_mRoles['file_is_displayable'] = 'COM_VIRTUEMART_FORM_MEDIA_DISPLAYABLE' ;
				$this->_mRoles['file_is_downloadable'] = 'COM_VIRTUEMART_FORM_MEDIA_DOWNLOADABLE' ;

			}
		}

	}


	private $_hidden = array();

	/**
	 * Use this to adjust the hidden fields of the displayFileHandler to your form
	 *
	 * @author Max Milbers
	 * @param string $name for exampel view
	 * @param string $value for exampel media
	 */
	public function addHidden($name, $value=''){
		$this->_hidden[$name] = $value;
	}

	/**
	 * Adds the hidden fields which are needed for the form in every case
	 * @author Max Milbers
	 */
	private function addHiddenByType(){

		$this->addHidden('media[active_media_id]',$this->virtuemart_media_id);
		$this->addHidden('option','com_virtuemart');
		//		$this->addHidden('file_mimetype',$this->file_mimetype);

	}

	/**
	 * Displays file handler and file selector
	 *
	 * @author Max Milbers
	 * @param array $fileIds
	 */
	public function displayFilesHandler($fileIds,$type,$vendorId = 0){

		vmLanguage::loadJLang('com_virtuemart_media');
		$html = $this->displayFileSelection($fileIds,$type);
		$html .= $this->displayFileHandler($vendorId);

		if(empty($this->_db)) $this->_db = JFactory::getDBO();
		$this->_db->setQuery('SELECT FOUND_ROWS()');
		$imagetotal = $this->_db->loadResult();
		//vmJsApi::jQuery(array('easing-1.3.pack','mousewheel-3.0.4.pack','fancybox-1.3.4.pack'),'','fancybox');
		//$j = "jQuery(document).ready(function(){ jQuery('#ImagesContainer').vm2admin('media','".$type."','0') }); " ;


		$j = 'if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
	Virtuemart.medialink = "'. JURI::root(false) .'administrator/index.php?option=com_virtuemart&view=media&task=viewJson&format=json&mediatype='.$type.'";';
		$j .= "jQuery(document).ready(function(){ jQuery('#ImagesContainer').vmmedia('media','".$type."','0') }); " ;
		vmJsApi::addJScript('mediahandler.vars',$j);
		vmJsApi::addJScript('mediahandler');

		return $html;
	}


	/**
	 * Displays a possibility to select already uploaded media
	 * the getImagesList must be adjusted to have more search functions
	 * @author Max Milbers
	 * @param array $fileIds
	 */
	public function displayFileSelection($fileIds,$type = 0){

		$html='';
		$html .= '<fieldset class="checkboxes">' ;
		$html .= '<legend>'.vmText::_('COM_VIRTUEMART_IMAGES').'</legend>';
		$html .=  '<span style="height:18px;vertical-align: middle;margin:4px" class="hasTip always-left" title="'.vmText::_('COM_VIRTUEMART_SEARCH_MEDIA_TIP').'">'.vmText::_('COM_VIRTUEMART_SEARCH_MEDIA') . '</span>';
		$html .=   '
				<input type="text" name="searchMedia" id="searchMedia" style="height:18px;vertical-align: middle;margin:4px;width:250px" data-start="0" value="' .vRequest::getString('searchMedia') . '" class="text_area always-left" />
				<button class="reset-value fg-button" style="height:18px;vertical-align: middle;margin:4px">'.vmText::_('COM_VIRTUEMART_RESET') .'</button>
				<a style="height:18px;vertical-align: middle;margin:4px" class="js-pages js-previous fg-button ui-state-default fg-button-icon-left ui-corner-all" ><span class="ui-icon ui-icon-circle-minus" style="display:inline-block;"></span> 16 </a>
				<a style="height:18px;vertical-align: middle;margin:4px" class="js-pages js-next fg-button ui-state-default fg-button-icon-right ui-corner-all"> 16 <span class="ui-icon ui-icon-circle-plus" style="display:inline-block;"></span></a>';
		$html .='<br class="clear"/>';


		$html .= '<div id="ImagesContainer">';

		if(!empty($fileIds)) {
			$model = VmModel::getModel('Media');
			$medias = $model->createMediaByIds($fileIds, $type);
			foreach($medias as $k=>$id){
				$html .= $this->displayImage($id,$k );
			}
		}
		$html .= '</div>';

		return $html.'</fieldset><div class="clear"></div>';
	}


	function displayImage($image ,$key) {

		if (isset($image->file_url)) {
			$image->file_root = JURI::root(true).'/';
			$image->msg =  'OK';
			$file_url_thumb = $image->getFileUrlThumb();

			$media_path = VMPATH_ROOT.DS.str_replace('/',DS,$image->file_url_thumb);
			if ((empty($image->file_url_thumb) || !file_exists($media_path)) && is_a($image,'VmImage')) {
				$file_url_thumb = $image->createThumb();
			}

			return  '<div  class="vm_thumb_image"><input type="hidden" value="'.$image->virtuemart_media_id.'" name="virtuemart_media_id[]">
			<input class="ordering" type="hidden" name="mediaordering['.$image->virtuemart_media_id.']" value="'.$key.'">
		<a class="vm_thumb" rel="group1" title ="'.$image->file_title.'" href="'.JURI::root(true).'/'.$image->file_url.'" >
		<img src="' . JURI::root(true).'/'.$file_url_thumb . '" alt="' . $image->file_title . '"  />
		</a><div class="vmicon vmicon-16-remove 4remove" title="'.vmText::_('COM_VIRTUEMART_IMAGE_REMOVE').'"></div><div class="edit-24-grey" title="'.vmText::_('COM_VIRTUEMART_IMAGE_EDIT_INFO').'"></div></div>';
		} else {
			$fileTitle = empty($image->file_title)? 'no  title':$image->file_title;
			return  '<div  class="vm_thumb_image"><b>'.vmText::_('COM_VIRTUEMART_NO_IMAGE_SET').'</b><br />'.$fileTitle.'</div>';
		}

	}


	static function displayImages($types ='',$page=0,$max=16 ) {

		$Images = array();
		$list = VmMediaHandler::getImagesList($types,$page,$max);
		if (empty($list['images'])){
			$Images[0]['label'] = vmText::_('COM_VIRTUEMART_NO_MEDIA_FILES');
			$Images[0 ]['value'] = '';
			return $Images;
		}

		foreach ($list['images'] as $key =>$image) {
			$htmlImages ='';
			$image->file_url_thumb = $image->getFileUrlThumb();

			$media_path = VMPATH_ROOT.DS.str_replace('/',DS,$image->file_url_thumb);
			if ((empty($image->file_url_thumb) || !file_exists($media_path)) && is_a($image,'VmImage')) {
				$file_url_thumb = $image->createThumb();
			}
			if ($image->file_url_thumb > "0" ) {
				$htmlImages .= '<div class="vm_thumb_image">
				<span>'.JHtml::image($image->file_url_thumb,$image->file_title, ' title="'.$image->file_title.'" class="vm_thumb" ').'</span>';
			} else {
				$htmlImages .=  '<div class="vm_thumb_image">'.vmText::_('COM_VIRTUEMART_NO_IMAGE_SET').'<br />'.$image->file_title ;
			}
			$Images[$key ]['label'] = $htmlImages.'<input type="hidden" value="'.$image->virtuemart_media_id.'" name="virtuemart_media_id['.$image->virtuemart_media_id.']"><input class="ordering" type="hidden" name="mediaordering['.$image->virtuemart_media_id.']" value=""><div class="vmicon vmicon-16-remove 4remove" title="remove"></div><div title="edit image information" class="edit-24-grey"></div></div>';
			$Images[$key ]['value'] = $image->file_title.' :: '.$image->virtuemart_media_id;
		}
		//$list['htmlImages'] = $htmlImages;
		return $Images;
	}


	/**
	 * Retrieve a list of layouts from the default and chosen templates directory.
	 *
	 * We may use here the getFiles function of the media model or write something simular
	 * @author Max Milbers
	 * @param name of the view
	 * @return object List of flypage objects
	 */
	static function getImagesList($type = '',$limit=0, $max=16) {

		$db = JFactory::getDBO();
		$list = array();
		$vendorId = vmAccess::isSuperVendor();
		$q='SELECT SQL_CALC_FOUND_ROWS `virtuemart_media_id` FROM `#__virtuemart_medias` WHERE `published`=1
	AND (`virtuemart_vendor_id`= "'.(int)$vendorId.'" OR `shared` = "1")';
		if(!empty($type)){
			$q .= ' AND `file_type` = "'.$type.'" ';
		}
		$search = trim(vRequest::getString('term', false));
		if (!empty($search)){
			$search = '"%' . $db->escape( $search, true ) . '%"' ;
			$q .=  ' AND (`file_title` LIKE '.$search.' OR `file_description` LIKE '.$search.' OR `file_meta` LIKE '.$search.') ';
		}
		$q .= ' LIMIT '.(int)$limit.', '.(int)$max;

		$db->setQuery($q);

		if ($virtuemart_media_ids = $db->loadColumn()) {

			$model = VmModel::getModel('Media');

			$db->setQuery('SELECT FOUND_ROWS()');
			$list['total'] = $db->loadResult();

			$list['images'] = $model->createMediaByIds($virtuemart_media_ids, $type);
			return $list;
		}
		else return array();
	}


	/**
	 * This displays a media handler. It displays the full and the thumb (icon) of the media.
	 * It also gives a possibility to upload/change/thumbnail media
	 *
	 * @param string $imageArgs html atttributes, Just for displaying the fullsized image
	 */
	public function displayFileHandler($vendorId = 0){

		vmLanguage::loadJLang('com_virtuemart_media');

		$this->addHiddenByType();

		$html = '<fieldset class="checkboxes">' ;
		$html .= '<legend>'.vmText::_('COM_VIRTUEMART_IMAGE_INFORMATION').'</legend>';
		$html .= '<div class="vm__img_autocrop">';
		$imageArgs = array('id'=>'vm_display_image');
		$html .=  $this->displayMediaFull($imageArgs,false,'',false).'</div>';

		//This makes problems, when there is already a form, and there would be form in a form. breaks js in some browsers
		//		$html .= '<form name="adminForm" id="adminForm" method="post" enctype="multipart/form-data">';

		$html .= ' <table class="adminform"> ';

		if ($this->published || $this->virtuemart_media_id === 0){
			$checked = 1;
		} else {
			$checked = 0;
		}

		$html .= '<tr>';
		//  The following was removed bacause the check box (publish/unpublish) was not functioning...
		// 			$this->media_published = $this->published;
		$html .= '<td class="labelcell" style="width:20em">
	<label for="published">'. vmText::_('COM_VIRTUEMART_FILES_FORM_FILE_PUBLISHED') .'</label>
</td>
<td>';
		if(!class_exists('VmHtml')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
		$html .= VmHtml::checkbox('media[media_published]',$checked,1,0,'class="inputbox"','media[media_published]') ;
		//<input type="checkbox" class="inputbox" id="media_published'.$identify.'" name="media_published'.$identify.'" '.$checked.' size="16" value="1" />

		$html .='</td>';
		$imgWidth = VmConfig::get('img_width','');
		if(!empty($imgWidth)){
			$imgWidth = 'width:'.VmConfig::get('img_width',90).'px;';
		} else {
			$imgWidth = 'max-width:200px;width:auto;';
		}

		$imgHeight = VmConfig::get('img_height','');
		if(!empty($imgHeight)){
			$imgHeight = 'height:'.VmConfig::get('img_height',90).'px;';
		} else {
			$imgHeight = '';
		}

		$html .= '<td rowspan = "8" min-width = "'.(VmConfig::get('img_width',90)+10).'px" overflow="hidden">';
		$thumbArgs = array('class'=>'vm_thumb_image','style'=>'overflow: auto;'.$imgWidth.$imgHeight);
		$html .= $this->displayMediaThumb($thumbArgs); //JHTML::image($this->file_url_thumb, 'thumbnail', 'id="vm_thumb_image" style="overflow: auto; float: right;"');
		// $html .= $this->displayMediaThumb('',false,'id="vm_thumb_image" style="overflow: auto; float: right;"');
		$html .= '</td>';

		$html .= '</tr>';

		if(!vmAccess::manager('media')){
			$readonly = 'readonly';
		} else {
			$readonly = '';
		}

		$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_TITLE','file_title');
		$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_DESCRIPTION','file_description');
		$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_META','file_meta');
		$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_CLASS','file_class');

		$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_URL','file_url',$readonly);

		//remove the file_url_thumb in case it is standard
		$file_url_thumb = $this->getFileUrlThumb();
		if(empty($this->file_url_thumb) and is_a($this,'VmImage')) {
			$file_url_thumb = vmText::sprintf('COM_VIRTUEMART_DEFAULT_URL',$file_url_thumb);
			$html .= '<tr>
	<td class="labelcell">'.vmText::_('COM_VIRTUEMART_FILES_FORM_FILE_URL_THUMB').'</td>
	<td>
		<span class="hasTip" title="'.$file_url_thumb.'">
			<input type="text" '.$readonly.' class="inputbox" name="media[file_url_thumb]" size="50" value="" />
			<span>'.vmText::sprintf('COM_VIRTUEMART_DEFAULT_URL','').'</span>
		</span>
	</td>
</tr>';

		} else {
			$html .= $this->displayRow('COM_VIRTUEMART_FILES_FORM_FILE_URL_THUMB','file_url_thumb',$readonly,$file_url_thumb);
		}


		$this->addMediaAttributesByType();

		$html .= '<tr>
				<td class="labelcell">'.vmText::_('COM_VIRTUEMART_FILES_FORM_ROLE').'</td>
				<td><fieldset class="checkboxes">'.JHtml::_('select.radiolist', $this->getOptions($this->_mRoles), 'media[media_roles]', '', 'value', 'text', $this->media_role).'</fieldset></td></tr>';

		// 			$html .= '<tr><td class="labelcell">'.VmHTML::checkbox('file_is_forSale', $this->file_is_forSale);
		// 			$html .= VmHTML::checkbox('file_is_downloadable', $this->file_is_downloadable);

		if(!empty($this->file_type)){

			$html .= '<tr>
					<td class="labelcell">'.vmText::_('COM_VIRTUEMART_FILES_FORM_LOCATION').'</td>
					<td><fieldset class="checkboxes">'.vmText::_('COM_VIRTUEMART_FORM_MEDIA_SET_'.strtoupper($this->file_type)).'</fieldset></td></tr>';
		} else {
			$mediaattribtemp = $this->media_attributes;
			if(empty($this->media_attributes)){
				$mediaattribtemp = 'product';
			}
			$html .= '<tr>
					<td class="labelcell">'.vmText::_('COM_VIRTUEMART_FILES_FORM_LOCATION').'</td>
					<td><fieldset class="checkboxes">'.JHtml::_('select.radiolist', $this->getOptions($this->_mLocation), 'media[media_attributes]', '', 'value', 'text', $mediaattribtemp).'</fieldset></td></tr>';
		}


		// select language for image
		$active_languages = VmConfig::get('active_languages',array(VmConfig::$jDefLang));
		if (count($active_languages)>1) {
			$selectedImageLangue = explode(",", $this->file_lang);
			$configM = VmModel::getModel('config');
			$languages = $configM->getActiveLanguages($selectedImageLangue,'media[active_languages][]');
			$html .= '<tr>
					<td class="labelcell"><span class="hasTip" title="' . vmText::_ ('COM_VIRTUEMART_FILES_FORM_LANGUAGE_TIP') . '">' . vmText::_ ('COM_VIRTUEMART_FILES_FORM_LANGUAGE') . '</span></td>
					<td><fieldset class="inputbox">'.$languages.'</fieldset></td>
					</tr>';
		}

		if(VmConfig::get('multix','none')!='none'){
			if(empty($this->virtuemart_vendor_id) and $vendorId === 0){
				$vendorId = vmAccess::isSuperVendor();
			} else if(empty($vendorId)) {
				$vendorId = $this->virtuemart_vendor_id;
			}
			if (!class_exists('ShopFunctions'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
			$vendorList = ShopFunctions::renderVendorList($vendorId, 'media[virtuemart_vendor_id]');
			$html .=  VmHTML::row('raw','COM_VIRTUEMART_VENDOR', $vendorList );
		}


		$html .= '</table>';
		$html .='<br /></fieldset>';

		$this->addMediaActionByType();

		$html .= '<fieldset class="checkboxes">' ;
		$html .= '<legend>'.vmText::_('COM_VIRTUEMART_FILE_UPLOAD').'</legend>';
		$html .= vmText::_('COM_VIRTUEMART_IMAGE_ACTION'). JHtml::_('select.radiolist', $this->getOptions($this->_actions), 'media[media_action]', '', 'value', 'text', 0).'<br /><br style="clear:both" />';


		$html .= vmText::_('COM_VIRTUEMART_FILE_UPLOAD').' <input type="file" name="upload" id="upload" size="50" class="inputbox" /><br />';

		$html .= '<br />'.$this->displaySupportedImageTypes();
		$html .='<br /></fieldset>';
		$html .= $this->displayFoldersWriteAble();

		$html .= $this->displayHidden();

		//		$html .= '</form>';

		return $html;
	}

	/**
	 * child classes can add their own options and you can get them with this function
	 *
	 * @param array $optionsarray Allowed values are $this->_actions and $this->_attributes
	 */
	private function getOptions($optionsarray){

		$options=array();
		foreach($optionsarray as $optionName=>$langkey){
			$options[] = JHtml::_('select.option',  $optionName, vmText::_( $langkey ) );
		}
		return $options;
	}

	/**
	 * Just for creating simpel rows
	 *
	 * @author Max Milbers
	 * @param string $descr
	 * @param string $name
	 */
	private function displayRow($descr, $name,$readonly='',$value = null){
		$v = (isset($value))? $value: $this->$name;
		$html = '<tr>
	<td class="labelcell">'.vmText::_($descr).'</td>
	<td> <input type="text" '.$readonly.' class="inputbox" name="media['.$name.']" size="70" value="'.$v.'" /></td>
</tr>';
		return $html;
	}

	/**
	 * renders the hiddenfields added in the layout before (used to make the displayFileHandle reusable)
	 * @author Max Milbers
	 */
	private function displayHidden(){
		$html='';
		foreach($this->_hidden as $k=>$v){
			$html .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
		}
		return $html;
	}

}
