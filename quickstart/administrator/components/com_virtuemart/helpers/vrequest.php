<?php
/**
 * Class vRequest
 * Gets filtered request values.
 *
 * @package    VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2014 iStraxx UG (haftungsbeschränkt). All rights reserved.
 * @license MIT, see http://opensource.org/licenses/MIT
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 *  http://virtuemart.net
 */

defined('FILTER_FLAG_NO_ENCODE') or define ('FILTER_FLAG_NO_ENCODE',!FILTER_FLAG_ENCODE_LOW);

class vRequest {


	public static function getUword($field, $default='', $custom=''){
		$source = self::getVar($field,$default);
		return self::filterUword($source,$custom);
	}

	//static $filters = array( '' =>);
	public static function uword($field, $default='', $custom=''){
		$source = self::getVar($field,$default);
		return self::filterUword($source,$custom);
	}

	public static function filterUword($source, $custom,$replace=''){
		if(function_exists('mb_ereg_replace')){
			//$source is string that will be filtered, $custom is string that contains custom characters
			return mb_ereg_replace('[^\w'.preg_quote($custom).']', $replace, $source);
		} else {
			return preg_replace("~[^\w".preg_quote($custom,'~')."]~", $replace, $source);	//We use Tilde as separator, and give the preq_quote function the used separator
		}
	}

	/**
	 * This function does not allow unicode, replacement for JPath::clean
	 * and makesafe
	 * @param      $string
	 * @param bool $forceNoUni
	 * @return mixed|string
	 */
	static function filterPath($str) {

		if (empty($str)) {
			vmError('filterPath empty string check your paths ');
			vmTrace('Critical error, empty string in filterPath');
			return VMPATH_ROOT;
		}
		$str = trim($str);

		// Delete all '?'
		$str = str_replace('?', '', $str);

		// Replace double byte whitespaces by single byte (East Asian languages)
		$str = preg_replace('/\xE3\x80\x80/', ' ', $str);

		$unicodeslugs = VmConfig::get('transliteratePaths',false);
		if($unicodeslugs){
			$lang = JFactory::getLanguage();
			$str = $lang->transliterate($str);
		}

		//This is a path, so remove all strange slashes
		$str = str_replace('/', DS, $str);

		//Clean from possible injection
		while(strpos($str,'..')!==false){
			$str  = str_replace('..', '', $str);
		};
		$str  = preg_replace('#[/\\\\]+#', DS, $str);
		$str = filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
		return $str;
	}

	public static function getBool($name, $default = 0){
		$tmp = self::get($name, $default, FILTER_SANITIZE_NUMBER_INT);
		if($tmp){
			$tmp = true;
		} else {
			$tmp = false;
		}
		return $tmp;
	}

	public static function getInt($name, $default = 0, $source = 0){
		return self::get($name, $default, FILTER_SANITIZE_NUMBER_INT,FILTER_FLAG_NO_ENCODE,$source);
	}

	public static function getFloat($name,$default=0.0){
		return self::get($name,$default,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_SCIENTIFIC|FILTER_FLAG_ALLOW_FRACTION);
	}

	/**
	 * - Strips all characters <32 and over 127
	 * - Strips all html.
	 */
	public static function getCmd($name, $default = ''){
		return self::get($name, $default, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
	}

	/**
	 * - Strips all characters <32
	 * - Strips all html.
	 */
	public static function getWord($name, $default = ''){
		return self::get($name, $default, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW);
	}

	/**
	 * - Encodes all characters that has a numerical value <32.
	 * - encodes <> and similar, so html and scripts do not work
	 */
	public static function getVar($name, $default = null){
		return self::get($name, $default, FILTER_SANITIZE_SPECIAL_CHARS,FILTER_FLAG_ENCODE_LOW );
	}

	/**
	 * - Encodes all characters that has a numerical value <32.
	 * - strips html
	 */
	public static function getString($name, $default = ''){
		return self::get($name, $default, FILTER_SANITIZE_STRING,FILTER_FLAG_ENCODE_LOW);
	}

	/**
	 * - Encodes all characters that has a numerical value <32.
	 * - keeps "secure" html
	 */
	public static function getHtml($name, $default = '', $input = 0){
		$tmp = self::get($name, $default,FILTER_UNSAFE_RAW,FILTER_FLAG_ENCODE_LOW,$input);
		if(is_array($tmp)){
			foreach($tmp as $k =>$v){
				$tmp[$k] = JComponentHelper::filterText($v);
			}
			return $tmp;
		} else {
			return JComponentHelper::filterText($tmp);
		}
	}

	public static function getEmail($name, $default = ''){
		return self::get($name, $default, FILTER_VALIDATE_EMAIL,FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
	}

	public static function filterUrl($url){

		if(!is_array($url)){
			$url = urldecode($url);
		} else {
			foreach($url as $k => $u){
				$url[$k] = self::filterUrl($u);
			}
		}
		$url = strip_tags($url);

		//$url = self::filter($url,FILTER_SANITIZE_URL,'');
		return self::filter($url,FILTER_SANITIZE_STRING,FILTER_FLAG_ENCODE_LOW);
	}

	/**
	 * Main filter function, called by the others with set Parameters
	 * The standard filter is non restrictiv.
	 *
	 * @author Max Milbers
	 * @param $name
	 * @param null $default
	 * @param int $filter
	 * @param int $flags
	 * @return mixed|null
	 */
	public static function get($name, $default = null, $filter = FILTER_UNSAFE_RAW, $flags = FILTER_FLAG_NO_ENCODE,$source = 0){
		//vmSetStartTime();
		if(!empty($name)){

			if($source===0){
				$source = $_REQUEST;
			} else if($source=='GET'){
				$source = $_GET;
				if(JVM_VERSION>2){
					$router = JFactory::getApplication()->getRouter();
					$vars = $router->getVars();
					if($router->getMode() and !empty($vars)){
						$source = array_merge($_GET,$vars);
					}
				}
			} else if($source=='POST'){
				$source = $_POST;
			}

			if(isset($source[$name])){
				return self::filter($source[$name],$filter,$flags);
			} else {
				return $default;
			}

		} else {
			vmTrace('empty name in vRequest::get');
			return $default;
		}

	}

	public static function filter($var, $filter, $flags, $array=false){
		if($array or is_array($var)){
			if(!is_array($var)) $var = array($var);
			self::recurseFilter($var, $filter, $flags);
			return $var;
		}
		else {
			return filter_var($var, $filter, $flags);
		}
	}

	public static function recurseFilter(&$var, $filter, $flags = FILTER_FLAG_STRIP_LOW){
		foreach($var as $k=>&$v){
			if(!empty($k) and !is_numeric($k)){
				$t = filter_var($k, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
				if($t!=$k){
					$var[$t] = $v;
					unset($var[$k]);
					vmdebug('unset invalid key',$k,$t);
				}
			}
			if(!empty($v)){
				if( is_array($v) ){	//and count($v)>1){
					self::recurseFilter($v, $filter, $flags);
				} else {
					$v = filter_var($v, $filter, $flags);
				}
			}

		}
		//filter_var_array($var, $filter);
	}

	/**
	 * Gets the request and filters it directly. It uses the standard php function filter_var_array,
	 * The standard filter allows all chars, also the special ones. But removes dangerous html tags.
	 *
	 * @author Max Milbers
	 * @param array $filter
	 * @return mixed cleaned $_REQUEST
	 */
	public static function getRequest( $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = FILTER_FLAG_ENCODE_LOW ){
		return self::filter($_REQUEST, $filter, $flags,true);
		//return  filter_var_array($_REQUEST, $filter);
	}
	
	public static function getPost( $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = FILTER_FLAG_ENCODE_LOW ){
		return self::filter($_POST, $filter, $flags,true);
	}
	
	public static function getGet( $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = FILTER_FLAG_ENCODE_LOW ){
		$source = $_GET;
		if(JVM_VERSION>2){
			$router = JFactory::getApplication()->getRouter();
			$vars = $router->getVars();
			if($router->getMode() and !empty($vars)){
				$source = array_merge($_GET,$vars);
			}
		}
		return self::filter($source, $filter, $flags,true);
	}
	
	public static function getFiles( $name, $filter = FILTER_SANITIZE_STRING, $flags = FILTER_FLAG_STRIP_LOW){
		if(empty($_FILES[$name])) return false;
		return  self::filter($_FILES[$name], $filter, $flags);
	}

	public static function setVar($name, $value = null){
		if(isset($_REQUEST[$name])){
			$tmp = $_REQUEST[$name];
			$_REQUEST[$name] = $value;
			return $tmp;
		} else {
			$_REQUEST[$name] = $value;
			return null;
		}
	}

	public static function vmSpecialChars($c){
		if (version_compare(phpversion(), '5.4.0', '<')) {
			// php version isn't high enough
			$c = htmlspecialchars ($c,ENT_QUOTES,'UTF-8',false);	//ENT_SUBSTITUTE only for php5.4 and higher
		} else {
			$c = htmlspecialchars ($c,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8',false);
		}
		return $c;
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * @return  boolean  True if token valid
	 */
	public static function vmCheckToken($redirectMsg=0){

		$token = self::getFormToken();

		if (!self::uword($token, false)){

			if ($rToken = self::uword('token', false)){
				if($rToken == $token){
					return true;
				}
			}

			$session = JFactory::getSession();

			if ($session->isNew()){
				// Redirect to login screen.
				$app = JFactory::getApplication();
				$app->redirect(JRoute::_('index.php'), vmText::_('JLIB_ENVIRONMENT_SESSION_EXPIRED'));
				$app->close();
				return false;
			}
			else {
				if($redirectMsg===0){
					$redirectMsg = 'Invalid Token, in ' . vRequest::getCmd('options') .' view='.vRequest::getCmd('view'). ' task='.vRequest::getCmd('task');
					//jexit('Invalid Token, in ' . vRequest::getCmd('options') .' view='.vRequest::getCmd('view'). ' task='.vRequest::getCmd('task'));
				} else {
					$redirectMsg =  vmText::_($redirectMsg);
				}
				// Redirect to login screen.
				$app = JFactory::getApplication();
				$session->close();
				$app->redirect(JRoute::_('index.php'), $redirectMsg);
				$app->close();
				return false;
			}
		}
		else {
			return true;
		}
	}

	public static function getFormToken($fNew = false){

		$sess = JFactory::getSession();
		$user = JFactory::getUser();

		if(empty($user->id)) $user->id = 0;
		if(!class_exists('vmCrypt'))
			require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');

		$token = $sess->get('session.token');
		if ($token === null || $fNew) {
			$token = vmCrypt::getToken();
			$sess->set('session.token', $token);
		}
		$hash = self::getHash($user->id . $token);

		return $hash;
	}

	public static function getHash($seed) {
		return md5(VmConfig::getSecret() . $seed);
	}
}