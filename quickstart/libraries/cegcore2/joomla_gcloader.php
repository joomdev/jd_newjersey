<?php
/**
* COMPONENT FILE HEADER
**/
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
//basic checks
$success = array();
$fails = array();
if(version_compare(PHP_VERSION, '5.5.0') >= 0){
	$success[] = "PHP 5.5.0 or later found.";
}else{
	$fails[] = "Your PHP version is outdated: ".PHP_VERSION;
}
if(phpversion('pdo') !== false AND in_array('mysql', PDO::getAvailableDrivers())){
	$success[] = "PDO Extension is available and enabled and it has MySQL support.";
}else{
	//$fails[] = "PDO Extension is NOT available, disabled or may not have MySQL support.";
}
if(!empty($fails)){
	JError::raiseWarning(100, "Your PHP version should be 5.5 or later.");
	return;
}
//end basic checks
if(empty($fails)){
	if(!function_exists('r2')){
		function r2($url, $xhtml = false, $absolute = false, $ssl = null){
			$alters = array(
				'chronoforms6server' => 'com_chronoforms6server',
				//'chronomigrator' => 'com_chronomigrator',
				'chronoforms' => 'com_chronoforms6',
				'chronoconnectivity' => 'com_chronoconnectivity6',
				'chronoforums' => 'com_chronoforums2',
				//'chronolistings' => 'com_chronolistings',
				//'chronocommunity' => 'com_chronocommunity',
				//'chronosearch' => 'com_chronosearch',
				'chronocontact' => 'com_chronocontact',
				'chronohyper' => 'com_chronohyper',
				'chronodirector' => 'com_chronodirector',
				'chronomarket' => 'com_chronomarket',
				'chronosocial' => 'com_chronosocial',
			);
			
			$url = \G2\L\Route::translate($url);
			
			foreach($alters as $k => $v){
				$url = str_replace('ext='.$k, 'option='.$v, $url);
			}
			
			if(is_string($xhtml)){
				$flags = str_split($xhtml);
				$xhtml = in_array('x', $flags);
				$absolute = in_array('f', $flags);
				$ssl = in_array('s', $flags);
			}
			
			if(GCORE_SITE == 'front'){
				if($xhtml){
					$url = str_replace('&', '&amp;', $url);
				}
				if(!$absolute){
					return JRoute::_($url, false, $ssl);
				}else{
					return JRoute::_($url, false, -1); //dirty hack to get the full absolute url, fix later and create the full absolute url: \JURI::getInstance()->toString(array('scheme', 'host', 'port')));
				}
			}else{
				return $url;
			}
		}
	}
	
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'gcloader.php');
	
	class JoomlaGCLoader2{
		public static function initialize(){
			//require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'gcloader.php');
			
			\G2\Globals::set('app', 'joomla');
			\G2\Globals::set('inline', true);
			\G2\Globals::set('db_table_prefix', 'chronoengine_');
			
			\G2\Globals::ready();
			
			\G2\L\Config::set('db.adapter', 'joomla');
			
			\G2\Bootstrap::initialize('joomla');
		}
		
		function __construct($area, $joption, $extension, $setup = null, $cont_vars = array()){
			self::initialize();

			\G2\Globals::set('EXTENSIONS_PATHS', array(
				dirname(__FILE__).DS.'admin'.DS.'extensions'.DS => JPATH_SITE.DS.'administrator'.DS.'components'.DS,
				dirname(__FILE__).DS.'extensions'.DS => JPATH_SITE.DS.'components'.DS
			));
			\G2\Globals::set('EXTENSIONS_URLS', array(
				\JFactory::getURI()->root().'libraries/cegcore2/admin/extensions/' => \JFactory::getURI()->root().'administrator/components/',
				\JFactory::getURI()->root().'libraries/cegcore2/extensions/' => \JFactory::getURI()->root().'components/',
			));
			\G2\Globals::set('EXTENSIONS_NAMES', array(
				'chronoforms6server' => 'com_chronoforms6server',
				'chronoforms' => 'com_chronoforms6',
				'chronoconnectivity' => 'com_chronoconnectivity6',
				'chronoforums' => 'com_chronoforums2',
				'chronodirector' => 'com_chronodirector',
				'chronomarket' => 'com_chronomarket',
				'chronosocial' => 'com_chronosocial',
				$extension => 'com_'.$joption,
			));
			
			\GApp::document()->addCssFile(\G2\Globals::get('FRONT_URL').'assets/joomla/fixes.css');
			//G2\L\Url::$root_ext = array('components', 'com_'.$joption);
			//\G2\Bootstrap::initialize('joomla', array('component' => 'com_'.$joption, 'ext' => $extension));

			$tvout = !empty(\G2\L\Request::data('tvout')) ? \G2\L\Request::data('tvout') : '';
			$controller = \G2\L\Request::data('cont', '');
			$action = \G2\L\Request::data('act', '');
			
			if(is_callable($setup)){
				$return_vars = $setup();
				if(!empty($return_vars)){
					$cont_vars = array_merge($cont_vars, $return_vars);
				}
			}
			
			if(isset($cont_vars['controller'])){
				$controller = $cont_vars['controller'];
			}
			if(isset($cont_vars['action'])){
				$action = $cont_vars['action'];
			}
			//$cont_vars['_app_thread'] = 'gcore';
			//ob_start();
			
			$app = \GApp::call($area, $extension, $controller, $action, $cont_vars);
			$output = $app->getBuffer();
			//$output = ob_get_clean();
			$output = \G2\Globals::fix_urls($output);
			
			if(!empty($tvout) AND empty($cont_vars['director_call'])){
				if($tvout == 'inline'){
					$doc = \GApp::document();
					echo $doc::_header(true);
					echo '<!--headend-->';
					
					//if(empty($cont_vars['director_call'])){
						echo \G2\H\Message::render(\GApp::session()->flash());
					//}
				}
				echo $output;
				$mainframe = \JFactory::getApplication();
				$mainframe->close();
			}else{
				ob_start();
				
				if(empty($cont_vars['director_call'])){
					echo \G2\H\Message::render(\GApp::session()->flash());
				}
				
				$doc = \GApp::document();
				//$doc::_header();
				
				$system_output = ob_get_clean();
				
				//$system_output = \G2\Globals::fix_urls($system_output);
				echo $system_output;
				echo $output;
			}
		}
	}
}