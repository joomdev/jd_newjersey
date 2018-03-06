<?php
/**
 * vmLanguage class
 *
 * initialises and holds the JLanguage objects for VirtueMart
 *
 * @package	VirtueMart
 * @subpackage Language
 * @author Max Milbers
 * @copyright Copyright (c) 2016 - 2017 VirtueMart Team. All rights reserved.
 */

class vmLanguage {

	public static $jSelLangTag = false;
	public static $currLangTag = false;
	public static $jLangCount = 1;
	public static $languages = array();

	/**
	 * Initialises the vm language class. Attention the vm debugger is not working in this function, because the right checks are done after the language
	 * initialisiation.
	 * @param bool $siteLang
	 */
	static public function initialise($siteLang = false){

		if(self::$jSelLangTag!==false){
			return ;
		}

		self::$jLangCount = 1;

		//Determine the shop default language (default joomla site language)
		if(VmConfig::$jDefLang===false){
			VmConfig::$jDefLangTag = self::getShopDefaultSiteLangTagByJoomla();
			VmConfig::$jDefLang = strtolower(strtr(VmConfig::$jDefLangTag,'-','_'));
		}

		$l = JFactory::getLanguage();
		//Set the "joomla selected language tag" and the joomla language to vmText
		self::$jSelLangTag = $l->getTag();
		self::$languages[self::$jSelLangTag] = $l;
		vmText::$language = $l;

		$siteLang = self::$currLangTag = self::$jSelLangTag;
		if( JFactory::getApplication()->isAdmin()){
			$siteLang = vRequest::getString('vmlang',$siteLang );
			if (!$siteLang) {
				$siteLang = self::$jSelLangTag;
			}
		}

		self::setLanguageByTag($siteLang);

	}

	static public function getShopDefaultSiteLangTagByJoomla(){

		$l= VmConfig::get('vmDefLang','');
		if(empty($l)) {
			if (class_exists('JComponentHelper') && (method_exists('JComponentHelper', 'getParams'))) {
				$params = JComponentHelper::getParams('com_languages');
				$l = $params->get('site', 'en-GB');
			} else {
				$l = 'en-GB';//use default joomla
				vmError('JComponentHelper not found');
			}
		}
		return $l;
	}

	static public function setLanguageByTag($siteLang, $alreadyLoaded = true){

		if(empty($siteLang)){
			$siteLang = self::$currLangTag;
		} else {
			if($siteLang!=self::$currLangTag){
				self::$cgULF = null;
				self::$cgULFS = null;
			}

		}
		self::setLanguage($siteLang);

		// this code is uses logic derived from language filter plugin in j3 and should work on most 2.5 versions as well
		if (class_exists('JLanguageHelper') && (method_exists('JLanguageHelper', 'getLanguages'))) {
			$languages = JLanguageHelper::getLanguages('lang_code');
			self::$jLangCount = count($languages);
			if(isset($languages[$siteLang])){
				VmConfig::$vmlangSef = $languages[$siteLang]->sef;
			} else {

				if(isset($languages[self::$jSelLangTag])){
					VmConfig::$vmlangSef = $languages[self::$jSelLangTag]->sef;
				}
			}
		}

		$langs = (array)VmConfig::get('active_languages',array(VmConfig::$jDefLangTag));
		VmConfig::$langCount = count($langs);
		if(!in_array($siteLang, $langs)) {
			$siteLang = VmConfig::$jDefLangTag;	//Set to shop language
		}

		VmConfig::$vmlangTag = $siteLang;
		VmConfig::$vmlang = strtolower(strtr($siteLang,'-','_'));

		VmConfig::$defaultLangTag = VmConfig::$jDefLangTag;
		VmConfig::$defaultLang = strtolower(strtr(VmConfig::$jDefLangTag,'-','_'));

		if(count($langs)>1){
			$lfbs = VmConfig::get('vm_lfbs','');
			/*	This cannot work this way, because the SQL would need a union with left and right join, much too expensive.
			 *	even worse, the old construction would prefer the secondary language over the first. It can be tested using the customfallback
			 *  for example en-GB~de-DE for en-GB as shop language
			 * if(count($langs)==2 and VmConfig::$vmlangTag==VmConfig::$defaultLangTag and VmConfig::get('dualFallback',false) ){
				foreach($langs as $lang){
					if($lang!=VmConfig::$vmlangTag){
						VmConfig::$defaultLangTag = $lang;
						VmConfig::$defaultLang = strtolower(strtr(VmConfig::$defaultLangTag,'-','_'));
					}
				}
			} else */
			if(!empty($lfbs)){
				vmdebug('my lfbs '.$lfbs);
				$pairs = explode(';',$lfbs);
				if($pairs and count($pairs)>0){
					$fbsAssoc = array();
					foreach($pairs as $pair){
						$kv = explode('~',$pair);
						if($kv and count($kv)===2){
							$fbsAssoc[$kv[0]] = $kv[1];
						}
					}
					if(isset($fbsAssoc[$siteLang])){
						VmConfig::$defaultLangTag = $fbsAssoc[$siteLang];
						VmConfig::$defaultLang = strtolower(strtr(VmConfig::$defaultLangTag,'-','_'));
						//VmConfig::$jDefLangTag = $fbsAssoc[$siteLang];
					}
					VmConfig::set('fbsAssoc',$fbsAssoc);
				}
			}
		}

		//JLangTag if also activevmlang set as FB, ShopLangTag($jDefLangTag), vmLangTag, vm_lfbs overwrites
		if(!empty(self::$_loaded) and $alreadyLoaded){
			//vmdebug('Loaded not empty, lets start',self::$_loaded);
			self::loadUsedLangFiles();
		}
		//@deprecated just fallback
		defined('VMLANG') or define('VMLANG', VmConfig::$vmlang );
		self::debugLangVars();
	}

	static public function loadUsedLangFiles(){

		//vmSetStartTime('loadUsedLangFiles');
		if(!empty(self::$_loaded['com'])){
			if(!empty(self::$_loaded['com'][0])){
				foreach(self::$_loaded['com'][0] as $name){
					self::loadJLang($name,0);
				}
			}
			if(!empty(self::$_loaded['com'][1])){
				foreach(self::$_loaded['com'][1] as $name){
					self::loadJLang($name,1);
				}
			}
		}

		if(!empty(self::$_loaded['mod'])){
			foreach(self::$_loaded['mod'] as $name){
				self::loadModJLang($name);
			}
		}

		if(!empty(self::$_loaded['plg'])){
			foreach(self::$_loaded['plg'] as $cvalue=>$name){

				$t = explode(';',$cvalue);
				//vmdebug('loadUsedLangFiles',$t[0],$t[1],$name);
				vmPlugin::loadJLang($t[0],$t[1],$name);
			}
		}
		//vmTime('loadUsedLangFiles','loadUsedLangFiles');
		//vmRam('loadUsedLangFiles');
	}

	static public function debugLangVars(){
		//vmdebug('LangCount: '.VmConfig::$langCount.' $siteLang: '.$siteLang.' VmConfig::$vmlangSef: '.VmConfig::$vmlangSef.' self::$_jpConfig->lang '.VmConfig::$vmlang.' DefLang '.VmConfig::$defaultLang);
		if(VmConfig::$langCount==1){
			$l = VmConfig::$langCount.' Language, default shoplanguage (VmConfig::$jDefLang): '.VmConfig::$jDefLang.' '.VmConfig::$jDefLangTag;
		} else {
			$l = VmConfig::$langCount.' Languages, default shoplanguage (VmConfig::$jDefLang): '.VmConfig::$jDefLang.' '.VmConfig::$jDefLangTag;
			//if(VmConfig::$jDefLang!=VmConfig::$defaultLang){
			if(self::getUseLangFallback()){
				$l .= ' Fallback language (VmConfig::$defaultLang): '.VmConfig::$defaultLang;
			}
			$l .= ' Selected VM language (VmConfig::$vmlang): '.VmConfig::$vmlang.' '.VmConfig::$vmlangTag.' SEF: '.VmConfig::$vmlangSef;
		}
		vmdebug($l);
	}


	static public function setLanguage($tag){

		if(!isset(self::$languages[$tag])) {
			self::getLanguage($tag);
		}
		if(!empty(self::$languages[$tag])) {
			vmText::$language = self::$languages[$tag];
			//vmdebug('vmText is now set to '.$tag,vmText::$language );
		}
		self::$currLangTag = $tag;

	}

	static public function getLanguage($tag = 0){

		if(empty($tag)) {
			$tag = VmConfig::$vmlangTag;	//When the tag was changed, the jSelLangTag would be wrong
		}

		if(!isset(self::$languages[$tag])) {
			if($tag == self::$jSelLangTag) {
				self::$languages[$tag] = JFactory::getLanguage();
				//vmdebug('loadJLang created $l->getTag '.$tag);
			} else {
				self::$languages[$tag] = JLanguage::getInstance($tag, false);
				//vmdebug('loadJLang created JLanguage::getInstance '.$tag,self::$languages[$tag]);
			}

		}

		return self::$languages[$tag];
	}

	static public $_loaded = array();
	/**
	 * loads a language file, the trick for us is that always the config option enableEnglish is tested
	 * and the path are already set and the correct order is used
	 * We use first the english language, then the default
	 *
	 * @author Max Milbers
	 * @static
	 * @param $name
	 * @return bool
	 */
	static public function loadJLang($name, $site = false, $tag = 0, $cache = true){

		static $loaded = array();
		//VmConfig::$echoDebug  = 1;
		if(empty($tag)) {
			$tag = self::$currLangTag;
		}
		$site = (int)$site;
		self::$_loaded['com'][$site][$name] = $name;
		self::getLanguage($tag);

		$h = $site.$tag.$name;
		if($cache and isset($loaded[$h])){
			vmText::$language = self::$languages[$tag];
			return self::$languages[$tag];
		} else {
			if(!isset(self::$languages[$tag])){
				vmdebug('No language loaded '.$tag.' '.$name);
				VmConfig::$logDebug = true;
				vmTrace('No language loaded '.$tag.' '.$name,true);
				return false ;
			}
		}

		if($site){
			$path = $basePath = VMPATH_SITE;
		} else {
			$path = $basePath = VMPATH_ADMIN;
		}

		if($tag!='en-GB' and VmConfig::get('enableEnglish', true) ){
			$testpath = $basePath.'/language/en-GB/en-GB.'.$name.'.ini';
			if(!file_exists($testpath)){
				if($site){
					$epath = VMPATH_ROOT;
				} else {
					$epath = VMPATH_ADMINISTRATOR;
				}
			} else {
				$epath = $path;
			}
			self::$languages[$tag]->load($name, $epath, 'en-GB', true, false);
		}

		$testpath = $basePath.'/language/'.$tag.'/'.$tag.'.'.$name.'.ini';
		if(!file_exists($testpath)){
			if($site){
				$path = VMPATH_ROOT;
			} else {
				$path = VMPATH_ADMINISTRATOR;
			}
		}

		self::$languages[$tag]->load($name, $path, $tag, true, true);
		$loaded[$h] = true;
		//vmdebug('loaded '.$h.' '.$path.' '.self::$languages[$tag]->getTag());
		vmText::$language = self::$languages[$tag];
		//vmText::setLanguage(self::$languages[$tag]);
		return self::$languages[$tag];
	}

	/**
	 * @static
	 * @author Max Milbers, Valerie Isaksen
	 * @param $name
	 */
	static public function loadModJLang($name){

		$tag = self::$currLangTag;
		self::$_loaded['mod'][$name] = $name;
		self::getLanguage($tag);

		$path = $basePath = JPATH_VM_MODULES.'/'.$name;
		if(VmConfig::get('enableEnglish', true) and $tag!='en-GB'){
			if(!file_exists($basePath.'/language/en-GB/en-GB.'.$name.'.ini')){
				$path = JPATH_ADMINISTRATOR;
			}
			self::$languages[$tag]->load($name, $path, 'en-GB');
			$path = $basePath = JPATH_VM_MODULES.'/'.$name;
		}

		if(!file_exists($basePath.'/language/'.$tag.'/'.$tag.'.'.$name.'.ini')){
			$path = JPATH_ADMINISTRATOR;
		}
		self::$languages[$tag]->load($name, $path,$tag,true);

		return self::$languages[$tag];
	}

	static $cgULF = null;

	static public function getUseLangFallback(){

		//static $cgULF = null;
		if(self::$cgULF===null){
			if(VmConfig::$langCount>1 and VmConfig::$defaultLang!=VmConfig::$vmlang and !VmConfig::get('prodOnlyWLang',false) ){
				self::$cgULF = true;
			} else {
				self::$cgULF = false;
			}
		}

		return self::$cgULF;
	}

	static $cgULFS = null;

	static public function getUseLangFallbackSecondary(){

		if(self::$cgULFS===null){
			if(self::getUseLangFallback() and VmConfig::$defaultLang!=VmConfig::$jDefLang and VmConfig::$jDefLang!=VmConfig::$vmlang){
				self::$cgULFS = true;
			} else {
				self::$cgULFS = false;
			}
		}
		return self::$cgULFS;
	}
}